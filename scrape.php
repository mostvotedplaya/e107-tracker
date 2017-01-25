<?php
/**
* e107 Tracker
*
* @author Lee Howarth <mostvotedplayer@gmail.com>
*/

/* Load backend */
include_once($_SERVER['DOCUMENT_ROOT'] . '/class2.php');
include_once('File/Bittorrent2/Encode.php');

/* Parse infohash */
$infohash = null;
if ( preg_match_all( '@info_hash=([^&]+)@', $_SERVER['QUERY_STRING'], $m ) )
{
     $infohash = array_map( 'urldecode', $m[1] );
}

/* Prepare Db */
$sql = e107::getDb();
$sql->select( 'torrents', 'tid,infohash,downloaded,name', 'banned = 0' );

/* Send headers */
header( 'Cache-Control: no-cache, must-revalidate' );
header( 'Expires: Fri, 30 Mar 1990 00:00:00 GMT' );
header( 'Pragma: no-cache' );
header( 'Content-Type: text/plain' );

/* Send response */
$resp['flags']['min_request_interval'] = (int) e107::pref( 'tracker', 'scrapeMinInterval' );
$files = [];
while ( $row = $sql->fetch() )
{
    $files[] = $row;
}

$resp['files'] = [];
foreach ( $files as $file )
{
    $resp['files'][$file['infohash']] = [
        'complete'   => (int) $sql->retrieve( 'peers', 'COUNT(pid)', 'residual = 0 AND tid = ' . $file['tid'] ),
        'incomplete' => (int) $sql->retrieve( 'peers', 'COUNT(pid)', 'residual > 0 AND tid = ' . $file['tid'] ),
        'downloaded' => (int) $file['downloaded'],
        'name'       => $file['name'] 
    ];
}

$encode = new File_Bittorrent2_Encode();
echo $encode->encode($resp);
