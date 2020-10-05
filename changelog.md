# CHANGELOG

## Mediawiki v1.35

 - Adding apostrophes to clear: Warning: Use of undefined constant NS_IMAGE - assumed 'NS_IMAGE' (this will throw an Error in a future version of PHP) in /var/www/html/bkwiki/extensions/FileIndexer/FileIndexer.config.php on line 165
 - Create extension.json file for new extension call include method: wfLoadExtension( 'FileIndexer' );
 - Changed ``` $wgOut->addWikiText ``` to ```$output->addWikiTextAsInterface``` in 12 locations
