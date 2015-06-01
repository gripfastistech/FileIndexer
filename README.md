FileIndexer
===========
FileIndexer is a MediaWiki extension that allows users to search within uploaded text files, such as PDFs, DOCs, TXTs, etc.

This extension has been removed from MediaWiki.org due to security reasons (https://www.mediawiki.org/wiki/Extension:FileIndexer). However, no one cares to explain what those reasons are, so I uploaded it here to make it available for those who want it.

Installation
------------
To install FileIndexer, clone this repo to your extensions/ directory and add the following line to your LocalSettings.php:

	require_once "$IP/extensions/FileIndexer/FileIndexer.php";

Then install all dependencies listed on FilIndexer.config.php (lines 59-66).

Configuration
-------------
Documentation missing, check the FileIndexer.config.php file!

Usage
-----
Once the extension has been installed and configured, go to Special:FileIndexer to generate the index of the uploaded text files. After doing that the files will be searchable from the regular search box.

Tasks
-----
* Configuration documentation.
* Translate all messages in German to English.
* Fix the security issues that no one cares to point out.