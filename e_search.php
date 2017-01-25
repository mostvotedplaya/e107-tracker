<?php
/**
* e107 Tracker
*
* @author Lee Howarth <mostvotedplayer@gmail.com>
*/

/**
* Plugin search.
*/
class tracker_search extends e_search
{
    /**
    * Register search config.
    * 
    * @return array
    */
    public function config()
    {
        $config = [
           'name'  => 'Torrents',
           'table' => 'torrents',
             'return_fields' => ['name', 'tid', 'added', 'category'],
             'search_fields' => ['name' => 100],
           'order'   => ['added' => DESC],
           'refpage' => 'torrents'
        ];
        return $config;
    }
    
    /**
    * Format search results.
    * 
    * @param  array $item
    * @return array
    */
    public function compile( array $item )
    {
        $data['link']        = '/torrent/details/' . $item['tid'];
        $data['pre_title']   = '';
        $data['title']       = htmlspecialchars( $item['name'] );
        $data['pre_summary'] = '';
        $data['detail']      = '';
        return $data;
    }
    
    /**
    * Build where clause.
    * 
    * @param  array $item
    * @return string
    */
    public function where( array $item )
    {
        $where = 'active = 1 AND banned = 0 AND';
        return $where;
    }
}
