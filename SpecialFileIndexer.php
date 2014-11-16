<?php

class SpecialFileIndexer extends SpecialPage {

	var $insertEditTools = true;

	/**
	 * Konstruktor der Spezialseite.
	 */
	function __construct() {
		parent::__construct('FileIndexer');
	}

	/**
	 * Systempruefung eines Kommandos
	 *
	 * @param $sCommand STRING Systemkommando
	 * @return STRING | TRUE Fehlermeldung oder TRUE fuer Erfolgreiche Pruefung
	 */
	static private function isCommandPresent( $sCommandKey, $sCommandPath ) {
		global $wgFiCommandPaths;

		if ( $sCommandPath === false ) {
			return "* ERROR (" . $sCommandKey . "): " . wfMessage('fileindexer_sp_msg_response_systemcheck_command_missing') . "\n";
		}

		if ( file_exists( $sCommandPath) === false ) {
			$sCommand = (strrpos( $sCommandPath, '/') !== false) ? substr( $sCommandPath, strrpos( $sCommandPath, '/') + 1) : $sCommandPath;
			exec("which " . $sCommand, $aWhichReply);
			if ( empty( $aWhichReply) ) {
				$wgFiCommandPaths[$sCommand] = false;
				return "* ERROR (" . $sCommand . "): " . wfMessage('fileindexer_sp_msg_response_systemcheck_command_missing') . "\n";
			} else {
				$wgFiCommandPaths[$sCommand] = $aWhichReply[0];
				return "* WARNING (" . $sCommand . "): " . wfMessage('fileindexer_sp_msg_response_systemcheck_command_wrong_path') . $aWhichReply[0] . "\n";
			}

		}

		return true;
	}

	/**
	 * Systemvoraussetzungen werden geprueft.
	 *
	 * @return STRING | TRUE Fehlermeldung oder TRUE fuer Erfolgreiche Pruefung
	 */
	static function checkNecessaryCommands() {
		global $wgFiCheckSystem, $wgFiCommandPaths;

		$sAnswer = "";
		// Testen, ob alle benoetigten Tools auf dem System vorhanden sind.
		if ( $wgFiCheckSystem === true ) {
			foreach ( $wgFiCommandPaths as $sCommandKey => $sPath ) {
				$xCheck = self::isCommandPresent( $sCommandKey, $sPath);
				if ( $xCheck !== true ) {
					$sAnswer .= $xCheck;
				}
			}
		}

		return ( $sAnswer == "") ? true : $sAnswer;
	}

	static function getCommandLine( $sFile ) {
		global $wgFiCommandCalls, $wgFiCommandPaths;

		$sFileExtension = strtolower(substr(strrchr( $sFile, '.'),1));
		$sCommandLine = $wgFiCommandCalls[$sFileExtension];

		while(strpos( $sCommandLine, WC_FI_COMMAND) !== false ) {
			$iSignStart = strpos( $sCommandLine, WC_FI_COMMAND);
			$iSignEnd = strlen(WC_FI_COMMAND) + $iSignStart;
			$iOpenSign = strpos( $sCommandLine, '[', $iSignEnd);
			$iCloseSign = strpos( $sCommandLine, ']', $iSignEnd + 1);
			if ( $iOpenSign === false || $iCloseSign === false || $iOpenSign > $iCloseSign ) {
				return false;
			}

			$sCommand = trim(substr( $sCommandLine, $iOpenSign + 1, $iCloseSign - $iOpenSign - 1));
			$sCommandLine = substr( $sCommandLine, 0, $iSignStart) . $wgFiCommandPaths[$sCommand] . substr( $sCommandLine, $iCloseSign + 1);
		}
		return str_replace(WC_FI_FILEPATH, $sFile, $sCommandLine);
	}

	/**
	 * Prueft, ob der Dateityp der angegebenen Datei vom FileIndexer geprueft wird und liefert
	 * das die Tatsache zurueck.
	 *
	 * @param $sFilename STRING Dateiname
	 * @return BOOL Pruefergebnis
	 */
	public static function checkFileType( $sFilename ) {
		global $wgFiCommandCalls;

		return isset( $wgFiCommandCalls[strtolower(substr(strrchr( $sFilename, '.'),1))]);
	}

	/**
	 * Ausfuehrung nach Aufruf und nach Formularsubmit. Startet den Indexerstellungsprozess.
	 */
	function execute( $par ) {
		global $wgRequest, $wgOut;

		if ( !is_null( $wgRequest->getVal('wpSubmitButton')) && !is_null( $wgRequest->getVal('wpFileMatches')) ) {
			$this->processIndex();
		}

		$this->showForm();
	}

	/**
	 * Fuehrt den Prozess der Indexerstellung durch.
	 */
	function processIndex() {
		global $wgRequest, $wgOut, $wgDBtype, $wgDBprefix, $wgFiCreateIndexThisTime, $wgContLang, $wgFiPrefix, $wgFiPostfix;

		wfProfileIn('SpecialFileIndexer::processIndex');

		if ( $wgRequest->getVal('wpFileMatches') == "" ) {
			$wgOut->addHTML('<h3 style="color:red;">' . wfMessage('fileindexer_sp_msg_response_no_params') . '</h3><br /><br /><hr /><br /><br />');
			return;
		}

		// Feststellen, ob Indexerstellung durchgefuehrt oder Liste nicht indexierter Dateien erzeugt werden soll
		$bCreateMode = ( $wgRequest->getVal('wpSubmitButton') == wfMessage('fileindexer_sp_label_submit_create'));

		$bSpecialPageNoUpdates = is_null( $wgRequest->getVal('wpNoUpdates'));
		$sNamespaceDestination = $wgContLang->getNsText( $wgRequest->getVal('wpNamespace'));
		$sNamespaceFile = $wgContLang->getNsText(NS_IMAGE);
		$xWildcardSign = is_null( $wgRequest->getVal('wpWildcardSign')) ? false : $wgRequest->getVal('wpWildcardSign');
		$aTitleMatches = explode("\n", str_replace("\r", "\n", str_replace("\r\n", "\n", $wgRequest->getVal('wpFileMatches'))));
		$aTmp = array();
		foreach ( $aTitleMatches as $sTitleMatch ) {
			if ( trim( $sTitleMatch) != "" ) {
				$aTmp[] = $sTitleMatch;
			}
		}
		$aTitleMatches = $aTmp;
		$dbConnection = wfGetDB( DB_SLAVE );

		$aTitles = array();
		foreach ( $aTitleMatches as $sTitleMatch ) {
			$sTitleMatch = trim( $sTitleMatch);
			$oTitleFile = Title::makeTitleSafe(NS_IMAGE, $sTitleMatch);

			/*
			 * Title::makeTitleSafe(NS_IMAGE, $sTitleMatch) liefert komischerweise fuer %f und %pdf ein Objekt,
			 * %df hingegen nicht. Weitere Analysen notwendig
			 */
			$sTitleAsKey = is_null( $oTitleFile) ? $sTitleMatch : $oTitleFile->getDBKey();
			if ( $xWildcardSign && strpos( $sTitleMatch, $xWildcardSign) === false || !$xWildcardSign ) {
				$oTitleDestination = Title::makeTitleSafe( $wgRequest->getVal('wpNamespace'), $sTitleMatch);
				$aTitles[$sTitleAsKey] = array('file' => $oTitleFile, 'destination' => $oTitleDestination);
			} else {
				$aWhere = array( "page_namespace = " . NS_IMAGE, "page_title LIKE '" . str_replace( $xWildcardSign, "%", $sTitleAsKey) . "'" );
				$dbRessource = $dbConnection->select( 'page', 'page_title', $aWhere, __METHOD__, array('ORDER BY' => 'page_title ASC'));
				$aTitleNames = array();
				while( $aRow = $dbConnection->fetchObject( $dbRessource)) {
					$aTitleNames[] = $aRow->page_title;
				}
				$dbRessource->free();
				foreach ( $aTitleNames as $sTitlename ) {
					$oTitleFile = Title::makeTitleSafe(NS_IMAGE, $sTitlename);
					$sTitleAsKey = is_null( $oTitleFile) ? $sTitleMatch : $oTitleFile->getDBKey();
					$oTitleDestination = Title::makeTitleSafe( $wgRequest->getVal('wpNamespace'), $sTitlename);
					$aTitles[$sTitleAsKey] = array('file' => $oTitleFile, 'destination' => $oTitleDestination);
				}
			}
		}

		$aNotFoundTitles = array();
		$aNewlyIndexedTitles = array();
		$aUpdatelyIndexedTitles = array();
		$aUnsupportedTypeTitles = array();

		// Unbekannte Dateitypen rausfiltern
		$aTmp = array();
		foreach ( array_keys( $aTitles) as $sTitle ) {
			if ( SpecialFileIndexer::checkFileType( $sTitle) ) {
				$aTmp[$sTitle] = $aTitles[$sTitle];
			} else {
				$aUnsupportedTypeTitles[] = $sTitle;
			}
		}
		$aTitles = $aTmp;

		foreach ( $aTitles as $sTitle => $aTitleObjects ) {
			$iFileArticleId = $aTitleObjects['file']->getArticleId();
			if ( $iFileArticleId == 0 ) {
				$aNotFoundTitles[] = $sTitle;
				continue;
			}
			$iDestionationArticleId = $aTitleObjects['destination']->getArticleId();
			if ( $iDestionationArticleId != 0 ) {
				// Zielartikel existiert
				$oArticle = Article::newFromId( $iDestionationArticleId);
				$oArticle->loadContent();

				// Check, ob Index vorhanden
				$aFragments = FileIndexer::getIndexFragments( $oArticle->mContent);
				if ( $aFragments === false ) {
					$aNewlyIndexedTitles[] = $sTitle;
				} else {
					$aUpdatelyIndexedTitles[] = $sTitle;
				}

				if ( $bCreateMode && (!$bSpecialPageNoUpdates || $aFragments === false) ) {
					// Index soll erzeugt/aktualisiert werden
					$wgFiCreateIndexThisTime = true;
					$oArticle->doEdit( $oArticle->mContent, wfMessage('fileindexer_sp_index_creation_comment'));
					$wgFiCreateIndexThisTime = false;
				}
			} else {
				// Zielartikel ist/waere neu
				$aNewlyIndexedTitles[] = $sTitle;

				if ( $bCreateMode ) {
					// Index soll erzeugt werden
					$oArticle = new Article( $aTitleObjects['destination']);
					$wgFiCreateIndexThisTime = true;
					$oArticle->doEdit("", wfMessage('fileindexer_sp_index_creation_comment'));
					$wgFiCreateIndexThisTime = false;
				}
			}
		}

		if ( $bCreateMode ) {
			if ( !empty( $aNewlyIndexedTitles) ) {
				sort( $aNewlyIndexedTitles);
				$wgOut->addHTML('<h4>' . wfMessage('fileindexer_sp_msg_response_files_index_created') . '</h4>');
				foreach ( $aNewlyIndexedTitles as $sTitlename ) {
					$wgOut->addWikiText("* [[:" . $sNamespaceDestination . ":" . $sTitlename . "]]<br />");
				}
			}
			if ( !empty( $aUpdatelyIndexedTitles) ) {
				sort( $aUpdatelyIndexedTitles);
				$wgOut->addHTML('<h4>' . ((!$bSpecialPageNoUpdates) ? wfMessage('fileindexer_sp_msg_response_files_index_updated') : wfMessage('fileindexer_sp_msg_response_files_index_update_suppressed')) . '</h4>');
				foreach ( $aUpdatelyIndexedTitles as $sTitlename ) {
					$wgOut->addWikiText("* [[:" . $sNamespaceDestination . ":" . $sTitlename . "]]<br />");
				}
			}
		} else {
			$aList = (!$bSpecialPageNoUpdates) ? array_merge( $aNewlyIndexedTitles, $aUpdatelyIndexedTitles) : $aNewlyIndexedTitles;
			if ( !empty( $aList) ) {
				sort( $aList);
				$wgOut->addHTML( '<h4>' . wfMessage('fileindexer_sp_msg_response_files_to_be_indexed') . '</h4>' );
				$wgOut->addHTML( '<ul>' );
				foreach ( $aList as $sTitlename ) {
					$wgOut->addHTML( '<li>' . $sTitlename . '</li>' );
				}
				$wgOut->addHTML( '</ul>' );
			}
		}
		if ( !empty( $aNotFoundTitles) ) {
			sort( $aNotFoundTitles);
			$wgOut->addHTML('<h4>' . wfMessage('fileindexer_sp_msg_response_files_not_found') . '</h4>');
			foreach ( $aNotFoundTitles as $sTitlename ) {
				$wgOut->addWikiText("* [[:" . $sNamespaceFile . ":" . $sTitlename . "]]<br />");
			}
		}
		if ( !empty( $aUnsupportedTypeTitles) ) {
			sort( $aUnsupportedTypeTitles);
			$wgOut->addHTML('<h4>' . wfMessage('fileindexer_sp_msg_response_files_with_unsupported_filetypes') . '</h4>');
			foreach ( $aUnsupportedTypeTitles as $sTitlename ) {
				$wgOut->addWikiText("* [[:" . $sNamespaceDestination . ":" . $sTitlename . "]]<br />");
			}
		}
		$wgOut->addHTML("<br /><hr /><br /");

		wfProfileOut('SpecialFileIndexer::processIndex');
	}

	/**
	 * Baut das Formular der Spezialseite auf.
	 */
	function showForm() {
		global $wgOut, $wgUser, $wgRequest, $wgContLang, $wgScriptPath, $wgUseAjax, $wgJsMimeType, $wgFiSpDefaultWildcardSign, $wgFiArticleNamespace, $wgFiSpNamespaceChangeable, $wgFiSpWildcardSignChangeable, $wgFiCommandCalls;

		$wgOut->setPagetitle(wfMessage('fileindexer_sp_title'));
		$oTitle = Title::makeTitle(NS_SPECIAL, 'FileIndexer');
		$sAction = $oTitle->getLocalURL('');

		$sDescription = wfMessage('fileindexer_sp_description');
		$sLabelNoUpdates = wfMessage('fileindexer_sp_label_no_updates');
		$sLabelWildcardSign = wfMessage('fileindexer_sp_label_wildcard_sign');
		$sLabelFileMatches = wfMessage('fileindexer_sp_label_file_matches');
		$sLabelNamespace = wfMessage('fileindexer_sp_label_destination_namespace');
		$sLabelSubmitCheck = wfMessage('fileindexer_sp_label_submit_check');
		$sLabelSubmitCreate = wfMessage('fileindexer_sp_label_submit_create');

		$xCheck = self::checkNecessaryCommands();
		if ( $xCheck !== true ) {
			$wgOut->addHTML('<h3 style="color:red;">' . wfMessage('fileindexer_msg_missing_dependencies') . '</h3>');
			$wgOut->addWikiText( $xCheck );
			$wgOut->addHTML( '<br /><hr /><br />' );
		}

		$sDefaultWildcardSign = is_null( $wgRequest->getVal('wpWildcardSign')) ? $wgFiSpDefaultWildcardSign : $wgRequest->getVal('wpWildcardSign');
		$bDefaultNoUpdates = is_null( $wgRequest->getVal('wpSubmitButton')) ? false : !is_null( $wgRequest->getVal('wpNoUpdates'));
		$sDefaultFileMatches = is_null( $wgRequest->getVal('wpFileMatches')) ? "" : $wgRequest->getVal('wpFileMatches');
		$iDefaultNamespace = is_null( $wgRequest->getVal('wpNamespace')) ? (( $wgFiArticleNamespace >= 0) ? $wgFiArticleNamespace : NS_IMAGE) : $wgRequest->getVal('wpNamespace');

		$wgOut->addWikiText( $sDescription );
		$supportedFormats = array_keys( $wgFiCommandCalls );
		$supportedFormats = implode( ', ', $supportedFormats );
		$wgOut->addWikiText( $supportedFormats );
		$wgOut->addHTML( '<br /><hr /><br />' );

		$wgOut->addHTML( '<form id="FileIndexer" method="post" action="' . $sAction . '">');

		if ( $wgFiSpWildcardSignChangeable ) {
			$wgOut->addWikiText( wfMessage('fileindexer_sp_help_wildcard_sign') );
			$wgOut->addHTML( '<p><label>' . $sLabelWildcardSign . ' <input type="text" name="wpWildcardSign" value="' . $sDefaultWildcardSign . '" /></label></p>' );
			$wgOut->addHTML( '<br /><hr /><br />' );
		} else {
			$wgOut->addHTML( '<input type="hidden" name="wpWildcardSign" value="' . $sDefaultWildcardSign . '" />' );
		}

		$wgOut->addWikiText( wfMessage('fileindexer_sp_help_no_updates') );
		$wgOut->addHTML( '<p><label><input type="checkbox" ' . ( $bDefaultNoUpdates ? "checked" : "" ) . ' name="wpNoUpdates" value="true" /> ' . $sLabelNoUpdates . '</label></p>' );
		$wgOut->addHTML( '<br /><hr /><br />' );

		if ( $wgFiSpNamespaceChangeable ) {
			$wgOut->addWikiText( wfMessage('fileindexer_sp_help_destination_namespace') );
			$wgOut->addHTML( '<p><label>' . $sLabelNamespace . ' <select name="wpNamespace">' );
			$aNamespaces = $wgContLang->getFormattedNamespaces();
			foreach ( $aNamespaces as $iNamespaceID => $sNamespaceLabel ) {
				if ( $iNamespaceID >= 0 ) {
					if ( $iNamespaceID == 0 ) {
						$sNamespaceLabel = wfMessage('fileindexer_sp_main_ns');
					}
					$wgOut->addHTML( '<option value="' . $iNamespaceID . '"' . ( ( $iDefaultNamespace == $iNamespaceID ) ? " selected" : "" ) . '>' . $sNamespaceLabel . '</option>' );
				}
			}
			$wgOut->addHTML( '</select></label></p>' );
			$wgOut->addHTML( '<br /><hr /><br />' );
		} else {
			$wgOut->addHTML( '<input type="hidden" name="wpNamespace" value="' . $iDefaultNamespace . '" />' );
		}


		$wgOut->addWikiText( wfMessage('fileindexer_sp_help_file_matches') );
		$wgOut->addHTML( '<br />' );


		$wgOut->addHTML( '<textarea name="wpFileMatches" rows="20">' . $sDefaultFileMatches . '</textarea>' );


		$wgOut->addHTML( '<br />' );
		$wgOut->addHTML( '<input type="submit" name="wpSubmitButton" value="' . $sLabelSubmitCheck . '" />' );
		$wgOut->addHTML( '<br /><br />' );
		$wgOut->addHTML( '<input type="submit" name="wpSubmitButton" value="' . $sLabelSubmitCreate . '" />' );
		$wgOut->addHTML( '</form>' );

		if ( $this->insertEditTools == true && $wgUseAjax == true && function_exists('charInsert') ) {
			$currentPath = str_replace( '\\', '/', __DIR__ );
			$curServerPath = substr( $currentPath, stripos( $currentPath, $wgScriptPath . '/' ) );
			$wgOut->addScript( "<script type=\"{$wgJsMimeType}\" src=\"{$curServerPath}/edittools.js\"></script>" );

			$filename = __DIR__ . '/EditTools.htm';
			$handle = fopen( $filename, 'rb' );
			$contents = fread( $handle, filesize( $filename ) );
			fclose( $handle );

			$wgOut->addHtml( '<div class="mw-editTools">' );
			$wgOut->addWikiText( $contents );
			$wgOut->addHtml( '</div>' );
		}
	}
}
