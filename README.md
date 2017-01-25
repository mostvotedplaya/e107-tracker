# e107 Tracker

This project is a bittorrent tracker plugin for e107.

### Installation

Download files to your computer via the command line / sourcetree, create a directory called
tracker inside of e107_plugins and upload the files in this repo to that directory.

After this you should modify the plugin.xml to change the announceUrl value to your domain for example: ```http://domain.tld/announce```

Then you can proceed to installing the plugin via the e107 plugins dashboard.

### User groups

The plugin adds two new user groups:

###### MODERATOR

The moderator class can edit / delete .torrents

###### UPLOADER

Uploaders can upload .torrents and edit but not delete.

Admins of e107 have permissions to moderate and upload.

### Features Lacking

No administration interface.

### Requirements

File_Bittorrent2 -> https://pear.php.net/package/File_Bittorrent2

### Thanks

Big thanks to Worldwide7477 he contributed alot to making this project.
