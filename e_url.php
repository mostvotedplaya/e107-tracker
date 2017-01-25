<?php
/**
* e107 Tracker
*
* @author Lee Howarth <mostvotedplayer@gmail.com>
*/

/**
* Plugin urls.
*/
class tracker_url
{
    /**
    * Configure SEO friendly urls.
    *
    * @return array
    */
    public function config()
    {
        $config[] = [
          'regex' => '^torrents(?:/([0-9]+))?$',    'redirect' => '{e_PLUGIN}tracker/torrents.php?page=$1'
        ];
        
        $config[] = [
          'regex' => '^torrent/download/([0-9]+)$', 'redirect' => '{e_PLUGIN}tracker/download.php?tid=$1'
        ];

        $config[] = [
          'regex' => '^torrent/details/([0-9]+)$',  'redirect' => '{e_PLUGIN}tracker/details.php?tid=$1'
        ];

        $config[] = [
          'regex' => '^torrent/edit/([0-9]+)$',     'redirect' => '{e_PLUGIN}tracker/edit.php?tid=$1'
        ];

        $config[] = [
          'regex' => '^(announce|scrape)',          'redirect' => '{e_PLUGIN}tracker/$1.php'
        ];
        
        $config[] = [
          'regex' => '^torrent/upload$',            'redirect' => '{e_PLUGIN}tracker/upload.php'
        ];
        return $config;
    }
}
