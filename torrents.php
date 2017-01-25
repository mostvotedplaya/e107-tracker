<?php
/**
* e107 Tracker
*
* @author Lee Howarth <mostvotedplayer@gmail.com>
*/

/* Load backend */
include_once($_SERVER['DOCUMENT_ROOT'] . '/class2.php');
include_once('e107_handlers/file_class.php');

/* Login check */
if ( ! USER ) 
{
     e107::redirect();
     exit;
}

/* Obtain pages */
$count = $sql->retrieve( 'torrents', 'COUNT(tid)', 'active = 1 AND banned = 0' );
$limit = 25;
$pages = round( $count / $limit );

$page = null;
if ( isset( $_GET['page'] ) && is_numeric( $_GET['page'] ) ) 
{
     $page = min( $_GET['page'], $pages );
}
$page = max( 1, $page );

/* Get torrents */
$sql->select( 'torrents', 'tid,name,size,category', 'active = 1 AND banned = 0 LIMIT ' . ($page - 1) . ',' . $limit );

$data = [];
while ( $row = $sql->fetch() )
{
    $data[] = $row;
}
foreach ( $data as $index => $value )
{
    $data[$index]['seeders']  = $sql->retrieve( 'peers', 'COUNT(pid)', 'residual = 0 AND tid = ' . $value['tid'] );
    $data[$index]['leechers'] = $sql->retrieve( 'peers', 'COUNT(pid)', 'residual > 0 AND tid = ' . $value['tid'] );
    $data[$index]['size']     = e_file::file_size_encode( $value['size'] );
}

/* Init pager */
$prev = 'javascript:void(0);';
$next = 'javascript:void(0);';

if ( $page > 1 )
{
     $prev = "/torrents/" . ($page - 1);
}
if ( $page < $pages )
{
     $next = "/torrents/" . ($page + 1);
}

/* Init helpers */
$alerts = e107::getMessage();

if ( ! $count )
{
     $output = $alerts->addInfo( 'No .torrents are available at this time.' )
                      ->render();
}
else
{
     $output = '<table class="table">
                <thead>
                <tr>
                    <th>Type</th>
                    <th>Name</th>
                    <th>Size</th>
                    <th>S</th>
                    <th>L</th>
                    <th><span class="glyphicon glyphicon-download-alt"></span></th>
                </tr>
                </thead>
                <tbody>';

     foreach ( $data as $item )
     {
         $output .= '<tr>
                          <td>' . htmlspecialchars( $item['category'] ) . '</td>
                          <td><a href="/torrent/details/' . $item['tid'] . '">' . htmlspecialchars( $item['name'] ) . '</a></td>
                          <td>' . $item['size'] . '</td>
                          <td>' . number_format( $item['seeders'] )  . '</td>
                          <td>' . number_format( $item['leechers'] ) . '</td>
                          <td><a href="/torrent/download/' . $item['tid'] . '"><span class="glyphicon glyphicon-download-alt"></span></a></td>
                     </tr>';
     }

     $output .= '</tbody>
                 </table>'; 

     $output .= '<ul class="pager">
                 <li>
                     <a href="' . $prev . '" data-toggle="tooltip" title="Prev">Prev</a>
                 </li>
                 <li>
                     <a href="' . $next . '" data-toggle="tooltip" title="Next">Next</a>
                 </li>
                 </ul>';
     
}

include_once(HEADERF);
$ns->tablerender( 'Torrents', $output );
include_once(FOOTERF);
