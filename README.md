# FileIndexer

FileIndexer is a MediaWiki extension that allows users to search within uploaded text files, such as PDFs, DOCs, TXTs, etc.

This extension has been removed from MediaWiki.org due to security reasons (https://www.mediawiki.org/wiki/Extension:FileIndexer). Basically, the problem is that the extension uses the "exec" command several times, without properly escaping the variables, so it's possible for a malicious user to upload a file with a name like "nothing | rm *.xls" and do nasty stuff.

If anyone wants to fix this problem, everyone would be very grateful.

## Installation

To install FileIndexer, clone this repo to the extensions/ directory and add the following line to LocalSettings.php:

	require_once "$IP/extensions/FileIndexer/FileIndexer.php";

Then install the relevant dependencies listed at FileIndexer.config.php (lines 59-66).

## Configuration

Documentation missing, check the FileIndexer.config.php file directly.

## Usage

Once the extension has been installed and configured, log in as an admin and go to Special:FileIndexer to generate the index of the uploaded text files. After doing that the files will be searchable from the regular search box. New files are automatically indexed.

## Tasks

* Translate all comments in German to English.
* Fix the security issue.
* Document the config options.