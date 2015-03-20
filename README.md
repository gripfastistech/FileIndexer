FileIndexer
===========

FileIndexer is a MediaWiki extension that allows MediaWiki to search within uploaded text files, such as PDFs, DOCs, TXTs, etc.

The extension has been removed from MediaWiki.org for security reasons, but no one explained what those reasons are. Therefore I upload it to my GitHub to make it available for those who want to download this very useful extension.

Installation
------------
To install FileIndexer, simply clone this repo to your extensions/ directory and add the following line to your LocalSettings.php:

require_once "$IP/extensions/FileIndexer/FileIndexer.php";

Usage
-----
Once the extension has been installed, go to Special:FileIndexer to generate the index of the uploaded text files. After doing that the files will be searchable from the regular search box.