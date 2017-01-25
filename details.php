<?php
/**
* e107 Tracker
*
* @author Lee Howarth <mostvotedplayer@gmail.com>
*/

/* Load backend */
include_once($_SERVER['DOCUMENT_ROOT'] . '/class2.php');
include_once('File/Bittorrent2/Decode.php');
include_once('e107_handlers/comment_class.php');
include_once('e107_handlers/file_class.php');

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
     $tid = $_GET['tid'];
}

/* Get torrent */
$sql = e107::getDb();
$sql->select( 'torrents', '*', "tid = $tid AND banned = 0" );
if ( ! $torrent = $sql->fetch() )
{
     e107::redirect();
     exit;
}

$torrent['seeders']  = $sql->retrieve( 'peers', 'COUNT(pid)', "residual = 0 AND tid = $tid" );
$torrent['leechers'] = $sql->retrieve( 'peers', 'COUNT(pid)', "residual > 0 AND tid = $tid" );

/* Init helpers */
$comments = new comment();
$parser   = e107::getParser();
$decoder  = new File_Bittorrent2_Decode;

/* Get files */
$decode = $decoder->decodeFile(__DIR__ . "/uploads/$tid/$tid.torrent");
$files = '';
$count = 0;
if ( array_key_exists( 'files', $decode ) )
{
     foreach ( $decode['files'] as $file )
     {
         $files .= '<tr>' .
                   '<td>' . htmlspecialchars( $file['filename'] ) . '</td>' .
                   '<td>' . e_file::file_size_encode( $file['size'] ) . '</td>' .
                   '</tr>';
         $count++;
     }
     unset( $decode );
}

/* Links */
$links[] = '<a href="/torrent/download/' . $tid . '">Download .torrent</a>';
if ( $currentUser['user_name'] == $torrent['uploader'] || ADMIN || check_class( 'MODERATOR' ) )
{
     $links[] = '<a href="/torrent/edit/' . $tid . '">Edit .torrent</a>';
}

/* Link Uploader */
$torrent['uploader'] = htmlspecialchars( $torrent['uploader'] );
$uid = $sql->retrieve( 'user', 'user_id', 'user_name = \'' . $sql->escape($torrent['uploader']) . '\'' );
if ( $uid > 0 )
{
     $torrent['uploader'] = '<a href="/user.php?id.' . $uid . '">' . 
                             $torrent['uploader'] . 
                            '</a>';
}

include_once(HEADERF);

/* Torrent details */
$ns->tablerender( 'Torrent Details', '
<div class="panel panel-default">
  <div class="panel-heading">Torrent details for &quot;' . htmlspecialchars($torrent['name']) . '&quot;</div>
  <div class="panel-body">
  <div align="right">
  ' . join( ' | ', $links ) . '
  </div>
  <dl>
      <dt>Infohash</dt>
      <dd>' . sha1( $torrent['infohash'] ) . '</dd>
      <dt>Size</dt>
      <dd>' . e_file::file_size_encode( $torrent['size'] ) . '</dd>
      <dt>Files</dt>
      <dd>' . $count . ' <a href="" data-toggle="modal" data-target="#files">View File(s)</a></dd>
      <dt>Category</dt>
      <dd>' . htmlspecialchars( $torrent['category'] ) . '</dd>
      <dt>Seeders</dt>
      <dd>' . number_format( $torrent['seeders'] ) . '</dd>
      <dt>Leechers</dt>
      <dd>' . number_format( $torrent['leechers'] ) . '</dd>
      <dt>Uploaded</dt>
      <dd>' . $torrent['added'] . '</dd>
      <dt>Uploader</dt>
      <dd>' . $torrent['uploader'] . '</dd>
      <dt>Downloads</dt>
      <dd>' . number_format( $torrent['downloaded'] ) . '</dd>
      <dt>Description</dt>
      <dd>' . $parser->toHTML( $torrent['description'], true ) . '</dd>
  </dl>
  </div>
</div>

<div id="files" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">File listing</h4>
    </div>
    <div class="modal-body">
    <table class="table">
    <thead>
    <tr>
        <th>Path</th>
        <th>Size</th>
    </tr>
    </thead>
    <tbody>
    ' . $files . '
    </tbody>
    </table>
      </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
    </div>
    </div>
  </div>
</div>' );

/* Get comments */
$comments->compose_comment( 'tracker', 'comment', $tid, null, $torrent['name'] );

include_once(FOOTERF);  
