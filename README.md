FileIndexer
===========

FileIndexer is a MediaWiki extension that allows users to search within uploaded text files, such as PDFs, DOCs, TXTs, etc.

This extension has been removed from MediaWiki.org for security reasons (https://www.mediawiki.org/wiki/Extension:FileIndexer), but no one cares to explain what those reasons are, so I uploaded it here to make it available for those who want to download this very useful extension.

Installation
------------
To install FileIndexer, simply clone this repo to your extensions/ directory and add the following line to your LocalSettings.php:

	require_once "$IP/extensions/FileIndexer/FileIndexer.php";

Configuration
-------------
Documentation missing

Usage
-----
Once the extension has been installed, go to Special:FileIndexer to generate the index of the uploaded text files. After doing that the files will be searchable from the regular search box.

Tasks
-----
* Fill in the configuration section.
* Translate all messages in German to English.
* Fix the security issues that no one cares to point out.