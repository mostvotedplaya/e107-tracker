<?php
/**
* e107 Tracker
*
* @author Lee Howarth <mostvotedplayer@gmail.com>
*/

/**
* Plugin cron.
*/
class tracker_cron
{
    /**
    * Register cron tasks.
    *
    * @return array
    */
    public function config()
    {
        $config[] = ['name' => 'Inactive Peers', 'function' => 'inactivePeers', 'category' => 'content', 'description' => 'Prune stale peers.'];
        $config[] = ['name' => 'Inactive Files', 'function' => 'inactiveFiles', 'category' => 'content', 'description' => 'Set .torrents inactive if no activity within given time.'];
        return $config;
    }    

    /**
    * Inactive peers.
    * 
    * Purge peers from database if they have not been in contact within the last
    * 30 minutes.
    *
    * @return void
    */
    public function inactivePeers()
    {
        $sql = e107::getDb();
        $sql->delete( 'peers', 
                      'UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(updated) >= 1800' );
    }

    /**
    * Inactive files.
    *
    * Set torrents as inactive, if they have not had any peer activity within
    * one hour.
    *
    * @return void
    */ 
    public function inactiveFiles()
    {
        $sql = e107::getDb();
        $sql->update( 'torrents', [
          'active' => 0,
          'WHERE'  => 'active = 1 AND UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(updated) >= 3600'
        ] );
    } 
}