<?php
/**
* e107 Tracker
*
* @author Lee Howarth <mostvotedplayer@gmail.com>
*/

/* Load backend */
include_once($_SERVER['DOCUMENT_ROOT'] . '/class2.php');
include_once('File/Bittorrent2/Encode.php');

/* Required vars */
$valid = true;

foreach ( ['info_hash', 'peer_id', 'port', 'uploaded', 'downloaded', 'left', 'key', 'uid'] as $var )
{
    if ( ! isset( $_GET[$var] ) || ! is_string( $_GET[$var] ) )
    {
         $valid = false;
         break;
    }
 
    $$var = $_GET[$var];
}

/**
* Bencode error.
*
* @param  string      $text A brief message explaining what is wrong. 
* @return string|null       A bencoded string or null on failure.
*/
function error( $text )
{
    $encode = new File_Bittorrent2_Encode;
    if ( is_string( $text ) )
    {
         return $encode->encode(['failure reason' => $text]);
    }
    return null;
}

/* Missing key ? */
if ( ! $valid )
{
     echo error( 'Invalid request.' );
     return;
}

/* Invalid info_hash */
if ( strlen( $info_hash ) != 20 )
{ 
     echo error( 'Invalid infohash.' );
     return;
}

/* Invalid peer_id */
if ( strlen( $peer_id ) != 20 )
{
     echo error( 'Invalid peerid.' );
     return;
}

/* Invalid port */
if ( $port < 1 || $port > 0xffff )
{
     echo error( 'Invalid port.' );
     return;
}

/* Statistics */
$residual = 0 + $left;

/* Optional vars */
foreach ( ['compact', 'no_peer_id', 'event', 'ip'] as $var )
{
    if ( ! isset( $_GET[$var] ) || ! is_string( $_GET[$var] ) )
    {
         $$var = null;
         continue;
    }
  
    $$var = $_GET[$var];
}

/* Get user ip */
if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) )
{
     $_SERVER['REMOTE_ADDR'] = $ip; 
}

/* Prepare db */
$sql = e107::getDb();

/* Get torrent */
$sql->select( 'torrents', '*', 'infohash = \'' . $sql->escape($info_hash) . '\'' );
if ( ! $torrent = $sql->fetch() )
{  
     echo error( 'Unregistered .torrent' );
     return;
}
if ( $torrent['banned'] )
{
     echo error( '.torrent is banned' );
     return;
}

/* Get user */
$uid = $sql->retrieve( 'user_extended', 'user_extended_id', 'user_plugin_tracker_passkey = \'' . $sql->escape($uid) . '\'' );
if ( ! $uid )
{
     echo error( 'Invalid passkey.' );
     return;
}

/**/
$self = $sql->retrieve( 'peers', '*', 'uid = ' . $uid . ' AND tid = ' . $torrent['tid'] );

/* Prepare response */
$resp['complete']     = (int) $sql->retrieve( 'peers', 'COUNT(pid)', 'residual = 0 AND tid = ' . $torrent['tid'] );
$resp['incomplete']   = (int) $sql->retrieve( 'peers', 'COUNT(pid)', 'residual > 0 AND tid = ' . $torrent['tid'] );
$resp['downloaded']   = (int) $torrent['downloaded'];
$resp['interval']     = (int) e107::pref( 'tracker', 'announceMaxInterval' );
$resp['min interval'] = (int) e107::pref( 'tracker', 'announceMinInterval' );
$resp['peers']        = [];

/* Handle events */
switch ( $event )
{
    default: 
    case 'started':
          $sql->select( 'peers', 'ip,port,peerId', 'tid = ' . $torrent['tid'] . ' AND uid != ' . $uid );
          while ( $peer = $sql->fetch() )
          {
              $client['ip']   = $peer['ip'];
              $client['port'] = (int) $peer['port']; 
              if ( $no_peer_id == '0' )
              { 
                   $client['peer id'] = $peer['peerId'];
              }
              $resp['peers'][] = $client;
          }

          $fields = [
            'tid'        => $torrent['tid'],
            'uid'        => $uid,
            'ip'         => $_SERVER['REMOTE_ADDR'],
            'port'       => $port,
            'peerId'     => $peer_id,
            'uploaded'   => $uploaded,
            'downloaded' => $downloaded,
            'residual'   => $residual
          ];

          if ( $self )
          {
               $fields['WHERE'] = 'pid = ' . $self['pid'];
               $sql->update( 'peers', $fields );
          }
          else
          {
               $sql->insert( 'peers', $fields );
          }

          break;

    case 'stopped':
          if ( $self ) 
          {
               $sql->delete( 'peers', 'pid = ' . $self['pid'] );
          }
          break;

    case 'completed':
          if ( $self )
          {
               $sql->gen( "UPDATE #torrents SET downloaded = downloaded + 1 WHERE tid = " . $torrent['tid'] );
          }
          break;
}

$uploaded   = 0 + $uploaded;
$downloaded = 0 + $downloaded;
if ( $self )
{
     $uploaded   = $uploaded   - $self['uploaded'];
     $downloaded = $downloaded - $self['downloaded'];
}

if ( $uploaded > 0 || $downloaded > 0 )
{
     $sql->gen( "UPDATE #user_extended SET user_plugin_tracker_uploaded = user_plugin_tracker_uploaded + $uploaded, user_plugin_tracker_downloaded = user_plugin_tracker_downloaded + $downloaded  WHERE user_extended_id = $uid" );
}

$sql->update( 'torrents', [
  'active' => 1,
  'WHERE'  => 'tid = ' . $torrent['tid']
]);

/* Send headers */
header( 'Cache-Control: no-cache, must-revalidate' );
header( 'Expires: Fri, 30 Mar 1990 00:00:00 GMT' );
header( 'Pragma: no-cache' );
header( 'Content-Type: text/plain' );

/* Send response */
$encode = new File_Bittorrent2_Encode();
echo $encode->encode($resp);
