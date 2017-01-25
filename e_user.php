<?php
/**
* e107 Tracker
*
* @author Lee Howarth <mostvotedplayer@gmail.com>
*/

/**
* Plugin user.
*/
class tracker_user
{
    /**
    * User profile hook.
    *
    * @param  array $user
    * @return array
    */
    public function profile( array $user )
    {
        $fields[] = ['label' => 'Uploaded',   'text'  => e_file::file_size_encode($user['user_plugin_tracker_uploaded'])];
        $fields[] = ['label' => 'Downloaded', 'text'  => e_file::file_size_encode($user['user_plugin_tracker_downloaded'])]; 
        $fields[] = ['label' => 'Share %',    'text'  => number_format( $user['user_plugin_tracker_uploaded'] / $user['user_plugin_tracker_downloaded'] * 100 )];
        return $fields;
    }
}
