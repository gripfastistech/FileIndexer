# FileIndexer

FileIndexer is a [MediaWiki depecrecated extension](https://www.mediawiki.org/wiki/Extension_talk:FileIndexer#Security) that allows users to search within uploaded text files, such as PDFs, DOCs, TXTs, etc. This version is modified for use with ***internal-network/intranet use cases*** based on Mediawiki v1.35. Do NOT use on public wikis.

## Installation

To install FileIndexer, clone this repo to the extensions/ directory and add the following line to LocalSettings.php:

Mediawiki v1.35+: ```wfLoadExtension( 'FileIndexer' );```
	
Original [old] method: ```require_once "$IP/extensions/FileIndexer/FileIndexer.php";```

Install the required dependencies listed at FileIndexer.config.php (lines 59-66).

## Required Dependencies (per FileIndexer.config.php lines 59-66):

 - convPDFtoTXT
 - pdftotext
 - iconv
 - antiword
 - xls2csv
 - catppt
 - strings
 - unzip
 
## Ubuntu (20.04):
 
  - convPDFtoTXT: ```(?)```
 - pdftotext: ```sudo apt-get install poppler-utils```
 - iconv: ```sudo apt install libc6```
 - antiword: ```sudo apt install antiword```
 - xls2csv: ```sudo apt install xls2csv```
 - catppt: ```sudo apt install catdoc```
 - strings: ```apt-get install binutils```
 - unzip: ```apt-get install unzip```
 
## OTHER LINUX DISTROS
 - Search here for dependencies install method/packages: https://command-not-found.com/
 
## Configuration

Documentation missing, check the FileIndexer.config.php file directly.

By default, FileIndexer will try to use the command line tool "pdftotext" to index PDF files. However, by setting `$wgFileIndexer_OCRisActive = true;` in your LocalSettings *before* requiring the extension, the "convPDFtoTXT" tool will be used, which uses optical character recognition (OCR) to scan and index PDF files.
 
## Configuration

Documentation missing, check the FileIndexer.config.php file directly.

By default, FileIndexer will try to use the command line tool "pdftotext" to index PDF files. However, by setting `$wgFileIndexer_OCRisActive = true;` in your LocalSettings *before* requiring the extension, the "convPDFtoTXT" tool will be used, which uses optical character recognition (OCR) to scan and index PDF files.

## Usage

Once the extension has been installed and configured, log in as an admin and go to Special:FileIndexer to generate the index of the uploaded text files. After doing that the files will be searchable from the regular search box. New files are automatically indexed (?).
