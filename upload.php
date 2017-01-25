<?php
/**
* e107 Tracker
*
* @author Lee Howarth <mostvotedplayer@gmail.com>
*/

/* Load backend */
include_once($_SERVER['DOCUMENT_ROOT'] . '/class2.php');
include_once(__DIR__ . '/backend/upload.php');
include_once('File/Bittorrent2/Decode.php');

/* Login check */
if ( ! USER ) 
{
     e107::redirect();
     exit;
}

/* Permission check */
if ( ! ADMIN && ! check_class( 'UPLOADER' ) )
{
     e107::redirect();
     exit;
}

/* Get categories */
$categories = (array) include( 'backend/categories.php' );

/* Handle upload */
if ( isset( $_POST['upload'] ) )
{
     $upload = new Upload( 'file' );
     if ( $e = $upload->getError() )
     {
          $error = $e;
     }
 
     if ( ! isset( $error ) )
     {
          try
          {
              $torrent = new File_Bittorrent2_Decode();
              $torrent = $torrent->decodeFile($upload->getPath());
          }
          catch ( Exception $e )
          {
              $error = $e;
          }
     }

     if ( ! isset( $error ) )
     {
          $announce = e107::pref( 'tracker', 'announceUrl' );

          if ( 0 !== strcmp( $torrent['announce'], $announce ) )
          {
               $error = 'Invalid announce URL.';
          }
     }

     if ( ! isset( $error ) )
     {
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

          if ( ! isset( $error ) )
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
     }
     
     if ( ! isset( $error ) )
     {
          $sql = e107::getDb();
          $tid = $sql->insert( 'torrents', [
            'name'        => $_POST['name'],
            'category'    => $_POST['type'],
            'description' => $_POST['desc'],
            'size'        => $torrent['size'],
            'infohash'    => pack( 'H*', $torrent['info_hash'] ),
            'uploader'    => USERNAME
          ]);
          if ( $tid > 0 )
          {
               if ( ! mkdir( __DIR__ . "/uploads/$tid" ) || ! file_put_contents( __DIR__ . "/uploads/$tid/$tid.torrent", $upload->getData() ) )
               {
                    $sql->delete( 'torrents', "tid = $tid" );
                    $error = 'Unable to save .torrent';
               }
          }
          else
          {
               $error = 'An error occurred.';
          }
     }
     
     if ( ! isset( $error ) )
     {
          e107::redirect( "/torrent/details/$tid" );
          exit();
     }  
}

/* Category select */
$select = '';
foreach ( $categories as $category )
{
    $select .= '<option value="' . htmlspecialchars( $category ) . '">';
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


/* Render page */
include_once(HEADERF);
$ns->tablerender( 'Upload', $alert . 
'<form method="post" action="/torrent/upload" enctype="multipart/form-data">
<input type="hidden" name="upload" value="1">
<fieldset>
 <legend>File Uploads</legend>

 <div class="form-group">
 <label for="file">Torrent</label>
 <input type="file"
        name="file"
        id="file"
        class="form-control" 
        required>
 </div>
</fieldset>

<fieldset>
 <legend>Additional Info</legend>

 <div class="form-group">
 <label for="name">Name</label> 
 <input type="text"
        name="name"
        id="name"
        class="form-control"
        required>
 </div>

 <div class="form-group">
 <label for="type">Type</label>
 <select name="type"
         id="type" 
         class="form-control">
 ' . $select . '
 </select>
 </div>

 <div class="form-group">
 <label for="desc">Description</label>
 <textarea name="desc"
           id="desc"
           cols="100%"
           rows="10"
           class="form-control"></textarea>
 </div>
</fieldset>

<fieldset>
  <button type="submit" class="btn btn-default">Upload</button>
</fieldset>
</form>');
include_once(FOOTERF);
