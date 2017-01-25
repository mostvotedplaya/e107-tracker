<?php
/**
* e107 Tracker
*
* @author Lee Howarth <mostvotedplayer@gmail.com>
*/

/* Load backend */
include_once($_SERVER['DOCUMENT_ROOT'] . '/class2.php');

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
$sql->select( 'torrents', '*', "tid = $tid" );
if ( ! $torrent = $sql->fetch() )
{
     e107::redirect();
     exit;
}

$moderator = ADMIN || check_class( 'MODERATOR' );

if ( $torrent['uploader'] != $currentUser['user_name'] && ! $moderator )
{
     e107::redirect();
     exit;
}

/* Get categories */
$categories = (array) include( 'backend/categories.php' );

/* Update */
if ( array_key_exists( 'edit', $_POST ) )
{  
     $error = null;
 
     if (
          ! array_key_exists( 'name', $_POST ) ||
          ! array_key_exists( 'type', $_POST ) ||
          ! array_key_exists( 'desc', $_POST ) ||
          ! is_string( $_POST['name'] )        ||
          ! is_string( $_POST['type'] )        ||
          ! is_string( $_POST['desc'] )
        )
     {
          $error = 'Something went wrong.';
     }

     if ( $error === null )
     {
          if ( ! preg_match( '@^[a-zA-Z0-9-\.\(\)\[\] ]{1,255}$@', $_POST['name'] ) )
          {
               $error = 'Invalid filename.';
          }

          if ( ! in_array( $_POST['type'], $categories ) )
          {
               $error = 'Invalid category.';
          }

          if ( ! preg_match( '@^[\x20-\x7E\r\n\t]+$@', $_POST['desc'] ) )
          {
               $error = 'Invalid description.';
          }
     }

     if ( $error === null )
     {
          $sql->update( 'torrents', [  
             'name'        => $_POST['name'],
             'category'    => $_POST['type'],
             'description' => $_POST['desc'],
             'WHERE'       => "tid = $tid"
          ] );

          e107::redirect( "/torrent/details/$tid" );
          exit;
     }
}

/* Delete */
if ( array_key_exists( 'delete', $_POST ) && $moderator )
{
     /* Delete file(s) */
     $items = glob( __DIR__ . "/uploads/$tid/*" );
     if ( is_array( $items ) )
     {
          foreach ( $items as $item )
          {
              unlink( $item );
          }
          rmdir( __DIR__ . "/uploads/$tid" );
     }
    
     /* Delete SQL */
     $sql->delete( 'torrents', "tid = $tid" );
     $sql->delete( 'peers',    "tid = $tid" );
     $sql->delete( 'comments', "comment_item_id = $tid" ); 
 

     e107::redirect( "/torrents" );
     exit;
}

/* Update */
if ( array_key_exists( 'update', $_POST ) && $moderator )
{
     $sql->update( 'torrents', [
       'banned' => isset( $_POST['banned'] ) ? 1 : 0,
       'WHERE'  => "tid = $tid"
     ]);
 
     e107::redirect( "/torrent/details/$tid" );
     exit;
}

/* Category select */
$select = '';
foreach ( $categories as $category )
{
    $select .= '<option value="' . htmlspecialchars( $category ) . '"';
    if ( $torrent['category'] === $category ) 
         $select .= 'selected="selected"';
    $select .= '>';
    $select .= htmlspecialchars( $category );
    $select .= '</option>';
}

/* Init helpers */
$alerts = e107::getMessage();
$alert  = '';
if ( isset( $error ) )
{
     $alert = $alerts->addError( $error )
                     ->render();
}


include_once(HEADERF);
$ns->tablerender( 'Edit Torrent', $alert . 
'<form method="post" action="/torrent/edit/' . $tid . '">
<input type="hidden" name="edit" value="1">
<fieldset>
 <legend>Edit Torrent</legend>

 <div class="form-group">
 <label for="name">Name</label> 
 <input type="text"
        name="name"
        id="name"
        value="' . htmlspecialchars( $torrent['name'] ) . '"
        class="form-control"
        required>
 </div>
 
 <div class="form-group">
 <label for="type">Type</label>
 <select name="type"
         id="type"
         class="form-control"
         required>
 ' . $select . '
 </select>
 </div>
 
 <div class="form-group">
 <label for="desc">Description</label>
 <textarea name="desc"
           id="desc"
           cols="100%"
           rows="10"
           class="form-control">' . htmlspecialchars( $torrent['description'] ) . '</textarea>
 </div>
 
 <button type="submit" class="btn btn-default">Update</button>
</form>' ); 

if ( $moderator )
{
    $ns->tablerender( 'Moderate Torrent', '
    <form method="post" action="/torrent/edit/' . $tid . '">
    <input type="hidden" name="update" value="1">
    <fieldset>
    <legend>Moderate</legend>

    <div class="form-group">
    <label for="banned">Banned</label> 
    <input type="checkbox"
           name="banned"
           id="banned"
           ' . ( $torrent['banned'] ? 'checked="checked"' : null ) . '
           class="form-control">
    </div>

    <button type="submit" class="btn btn-default" name="update">Update</button>
    <button type="submit" class="btn btn-default" name="delete">Delete</button>
    </form>' );
}
include_once(FOOTERF);  
