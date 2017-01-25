<?php
/**
* e107 Tracker
*
* @author Lee Howarth <mostvotedplayer@gmail.com>
*/

/* Load backend */
include_once($_SERVER['DOCUMENT_ROOT'] . '/class2.php');
include_once('File/Bittorrent2/Decode.php');
include_once('File/Bittorrent2/Encode.php');

/* Login check */
if ( ! USER )
{
     e107::redirect();
     exit;
}

/* Get torrent id */
$tid = 0;
if ( isset( $_GET['tid'] ) && is_numeric( $_GET['tid'] ) )
{
     $tid = intval( $_GET['tid'] );
}

/* Get torrent */
$sql = e107::getDb();
$sql->select( 'torrents', 'infohash', "tid = $tid AND banned = 0" );
if ( ! $row = $sql->fetch() )
{
     e107::redirect();
     exit;
}

/* Generate passkey */
if ( ! $currentUser['user_plugin_tracker_passkey'] )
{
     $currentUser['user_plugin_tracker_passkey'] = sprintf( '%u', crc32( $currentUser['user_id'] . uniqid() ) );
     $sql->update( 'user_extended', [
       'user_plugin_tracker_passkey' => $currentUser['user_plugin_tracker_passkey'],
       'WHERE'                       => 'user_extended_id = ' . intval( $currentUser['user_id'] )
     ]);
}

/* Patch .torrent */
try
{
    $decoder = new File_Bittorrent2_Decode;
    $encoder = new File_Bittorrent2_Encode;
    $torrent = $decoder->decode( file_get_contents( __DIR__ . "/uploads/$tid/$tid.torrent" ) );
    $torrent['announce'] = sprintf( '%s?uid=%s', $torrent['announce'], $currentUser['user_plugin_tracker_passkey'] );                 
    $torrent = $encoder->encode( $torrent );
}
catch ( Exception $e )
{
    e107::redirect();
    exit;
}

/* Send headers */
header( 'Content-Disposition: attachment; filename="' . sha1( $row['infohash'] ) . '.torrent"' );
header( 'Content-Type: application/x-bittorrent' );

/* Send Response */
echo $torrent;