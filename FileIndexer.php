<?php

$wgExtensionCredits['other'][] = array(
	'name' => 'FileIndexer',
	'version' => '0.5',
	'author' => array( '[https://www.mediawiki.org/wiki/User:RaZe RaZe]', '[https://www.mediawiki.org/wiki/User:SmartK SmartK]' ),
	'description' => 'Creates an index that makes uploaded text files searchable',
	'descriptionmsg' => 'fileindexer-desc',
	'url' => 'https://www.mediawiki.org/wiki/Extension:FileIndexer',
);

include_once "FileIndexer.config.php";

$wgAutoloadClasses['FileIndexer'] = __DIR__ . '/FileIndexer.body.php';
$wgAutoloadClasses['SpecialFileIndexer'] = __DIR__ . '/SpecialFileIndexer.php';

$wgSpecialPages['FileIndexer'] = 'SpecialFileIndexer';

$wgExtensionMessagesFiles['FileIndexer'] = __DIR__ . '/FileIndexer.i18n.php';
$wgMessagesDirs['FileIndexer'] = __DIR__ . '/i18n';

$wgHooks['EditPage::showEditForm:initial'][] = 'FileIndexer::addCheckboxToEditForm';
$wgHooks['UploadForm:initial'][] = 'FileIndexer::addCheckboxToUploadForm';
$wgHooks['UploadForm:BeforeProcessing'][] = 'FileIndexer::beforeProcessing';
$wgHooks['UploadComplete'][] = 'FileIndexer::uploadComplete';
$wgHooks['ArticleSave'][] = 'FileIndexer::articleSave';