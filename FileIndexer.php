<? php
/**
 * TITLE: Extension: FileIndexer
 * DATE OF CREATION: 05/15/2008
 * AUTHOR: Ramon Dohle aka 'raZe'
 * ORGANIZATION: GRASS-MERKUR GmbH & Co. KG & raZe.it
 * VERSION: 0.4.6.00 07/30/2014
 * REVISION:
 * 05/15/2008 0.1.0.00 raZe * Initial version
 * 26.06.2008 0.2.0.00 raZe * Complete revision
 * 29.06.2009 0.2.1.00 raZe * Further open points processed:
 * * Use $ wgFiArticleNamespace also for uploads.
 * * Make $ wgFiAutoIndexMark usable automatically when uploading with an index creation request in the article.
 * New option: $ wgFiSetAutoIndexMark
 * * Use $ wgFiCheckSystem to check the system requirements with every call
 * New option: $ wgFiCheckSystem
 * * Temporary file must be clearly assigned to a session ...
 * 07/01/2009 0.2.2.00 raZe * Bug fixed that $ wgFiAutoIndexMark was not taken into account when uploading files
 * 08/26/2010 0.3.0.00 raZe * BUGFIX: variable error in wfFiCheckIndexcreation () fixed
 * * New configuration parameters built in (see description under CONFIGURATION)
 * * New file types (Office 2007) incorporated
 * * UTF-8 entered for antiword calls
 * 27.08.2010 0.3.1.00 raZe * Index creation per special page now also implemented for several pages
 * 08/28/2010 0.3.1.01 raZe * Revision header (descriptions ...) updated
 * 0.3.2.00 raZe * New function wfFiGetIndexFragments () to split an article text into an array (pre, index, post).
 * * Control via index update regulation outsourced in special page class
 * 29.08.2010 0.3.3.00 raZe * MediaWiki 1.16 has rebuilt the upload object and declared many parts as protected so that
 * It was necessary to use the $ wgRequest object in order to use the hook function wfFiBeforeProcessing ()
 * to be able to access the summary or description / source entered.
 * * The same applied to the image object in the hook function wfFiUploadComplete ().
 * * Comment manipulation no longer possible in the UploadForm hook: BeforeProcessing, therefore relocation to ArticleSave
 * * Temporary compatibility with 1.15 maintained
 * 30.08.2010 0.4.0.00 raZe * Several changes to form adaptations in the page edit and upload area:
 * * Configuration parameters and functions slimmed down / changed
 * * Forms are now controlled via a checkbox for creating the index
 * * Automatic index creation is no longer determined by an extra feature, but
 * controlled as a suggestion for each change in the form.
 * 05.09.2010 0.4.0.01 raZe * Version information corrected
 * 23.09.2010 0.4.1.00 raZe * BUGFIX: Incorrect array check when determining commands for index formation corrected
 * 25.09.2010 0.4.2.00 raZe * Default value for the wildcard character changed to *
 * * Default value for the namespace changed to NS_IMAGE
 * * New configuration parameters $ wgFiSpWildcardSignChangeable, $ wgFiSpNamespaceChangeable, $ wgFiLowercaseIndex,
 * $ wgFiCreateOnUploadByDefault, $ wgFiUpdateOnEditArticleByDefault (see below for descriptions)
 * * The following configuration parameters have been removed: $ wgFiCreateIndexByDefault
 * * BUGFIX: Fixed a bug that prevented the removal of the tags from, for example, office documents
 * * Configuration moved to new file FileIndexer_cfg.php.
 * * Unused function wfFiCheckNamespace () removed
 * 09/26/2010 0.4.3.00 raZe * Variable $ wgFiFilenamePlaceholder replaced by constant WC_FI_FILEPATH
 * * Variable $ wgFiTempFilePrefix replaced by constant WC_FI_TMPFILE
 * * INTERFACE: The wfFiGetIndex () function now returns an error code (INT) in the event of an error
 * * Errors in the index creation are now also indicated in the summary when saving the article
 * 27.09.2010 0.4.4.00 raZe * New constant: WC_FI_COMMAND for configuration parameter $ wgFiCommandCalls
 * 0.4.4.01 raZe * version corrected
 * 09/28/2010 0.4.5.00 raZe * BUGFIX: wfFiAddCheckboxToEditForm () use of the global $ wgFiCreateOnUploadByDefault and
 * $ wgFiUpdateOnEditArticleByDefault corrected
 * 09/30/2010 0.4.5.01 raZe * Default configuration changed
 * * Revision head tidied up
 *
 * DESCRIPTION:
 * This extension is based on the wiki extension 'FileIndexer' from 05/15/2008.
 * Like its original, it should index files in order to capture the content of these files by search engines.
 *
 * OPEN POINTS:
 * 5 TODO: [] Implement better filter operations.
 * 9 TODO: [] Provide temporary files with a date, so that they can be deleted when they expire.
 *
 * DESCRIPTION:
  * This extension is based on the wiki extension 'FileIndexer' from 05/15/2008.
  * Like its original, it should index files in order to capture the content of these files by search engines.
  *
  * OPEN POINTS:
  * 5 TODO: [] Implement better filter operations.
  * 9 TODO: [] Provide temporary files with a date, so that they can be deleted when they expire.
  *
  * LEGALDE:
  * 		[ ]: Open
  * [B]: Decided on the upcoming release
  * [I]: Inactive but reserved / scheduled
  * [A]: Active in progress
  * [T]: Realized, but untested
  * [P]: To be re-examined and assessed, to be canceled if necessary
  * [D]: Realized and tested, but not yet documented
  * [X]: Temporary mark for completion for patch notes
  * /

$wgExtensionCredits['other'][] = array(
	'name' => 'FileIndexer',
	'version' => '0.6',
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
