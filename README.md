# FileIndexer

FileIndexer is a MediaWiki extension that allows users to search within uploaded text files, such as PDFs, DOCs, TXTs, etc.

This extension has been removed from MediaWiki.org due to security reasons (https://www.mediawiki.org/wiki/Extension:FileIndexer). Basically, the problem is that the extension uses the "exec" command several times, without properly escaping the variables, so it's possible for a malicious user to upload a file with a name like "nothing | rm *.xls" and do nasty stuff.

If anyone wants to fix this problem, everyone would be grateful.

## Installation

To install FileIndexer, clone this repo to the extensions/ directory and add the following line to LocalSettings.php:

	require_once "$IP/extensions/FileIndexer/FileIndexer.php";

Then install the relevant dependencies listed at FileIndexer.config.php (lines 59-66).

## Configuration

Documentation missing, check the FileIndexer.config.php file directly.

By default, FileIndexer will try to use the command line tool "pdftotext" to index PDF files. However, by setting `$wgFileIndexer_OCRisActive = true;` in your LocalSettings *before* requiring the extension, the "convPDFtoTXT" tool will be used, which uses optical character recognition (OCR) to scan and index PDF files.

## Usage

Once the extension has been installed and configured, log in as an admin and go to Special:FileIndexer to generate the index of the uploaded text files. After doing that the files will be searchable from the regular search box. New files are automatically indexed.

## Tasks

* Fix the security issue.
* Translate all code comments in German to English.
* Document the config options.