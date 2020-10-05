# CHANGELOG

## Mediawiki v1.35

 - Adding apostrophes to clear: Warning: Use of undefined constant NS_IMAGE - assumed 'NS_IMAGE' (this will throw an Error in a future version of PHP) in /var/www/html/bkwiki/extensions/FileIndexer/FileIndexer.config.php on line 165
 - Changed ``` $wgOut->addWikiText ``` to ```$output->addWikiTextAsInterface``` 12 line locations: 233, 240, 259, 266, 295, 304, 307, 313, 320, 325, 343, 367 in SpecialFileIndexer.php
  - Adding apostrophes to clear: Notice: Undefined variable and Notice: Undefined property NS_IMAGE 6 occurances in SpecialFileIndexer.php
