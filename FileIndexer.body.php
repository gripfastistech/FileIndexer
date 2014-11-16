<?php

/**
 * FileIndexer extends FileRepo only to be able to call a protected static method of FileRepo
 */
class FileIndexer extends FileRepo {

	public static function addCheckboxToEditForm(&$oEditPage){
		global $wgRequest, $wgFiUpdateOnEditArticleByDefault, $wgFiCreateOnUploadByDefault, $wgFiArticleNamespace;

		/*
		 * Formular abgeschickt?
		 * Ja => Harken setzen, wie vor dem Abschicken
		 * Nein => Datei mit Artikeltitel im Namensraum NS_IMAGE existiert?
		 * 		Ja => Artikel existiert?
		 * 			Ja => Harken setzen, wenn darin Index gefunden wird und $wgFiUpdateOnEditArticleByDefault == true
		 * 			Nein => Harken nach globalen $wgFiCreateOnUploadByDefault setzen
		 * 		Nein => return
		 */
		$bCheckboxChecked = true;
		if ( !is_null($wgRequest->getVal('wpSave')) || !is_null($wgRequest->getVal('wpPreview')) || !is_null($wgRequest->getVal('wpDiff'))){
			$bCheckboxChecked = (!is_null($wgRequest->getVal('wpProcessIndex')) && $wgRequest->getVal('wpProcessIndex') == "true");
		} else {
			$iIndexArticleNamespace = ($wgFiArticleNamespace > -1) ? $wgFiArticleNamespace : NS_IMAGE;
			$oFileTitle = Title::makeTitleSafe(NS_IMAGE, $oEditPage->mTitle->getDBkey());
			$oFileArticle = new Article($oFileTitle);
			if ( $oFileArticle->exists()){
				$oArticle = new Article($oEditPage->mTitle);
				if ( $oArticle->exists()){
					$oArticle->loadContent();
					$xFragments = self::getIndexFragments($oArticle->mContent);
					$bCheckboxChecked = $xFragments !== false && $wgFiUpdateOnEditArticleByDefault || $xFragments === false && $wgFiCreateOnUploadByDefault;
				} else {
					$bCheckboxChecked = $wgFiCreateOnUploadByDefault;
				}
			} else {
				return true;
			}
		}

		$oEditPage->editFormTextAfterWarn .= "<b>FileIndexer:</b> <input type='checkbox' name='wpProcessIndex' value='true' " . ($bCheckboxChecked ? "checked" : "") . "> " . wfMessage('fileindexer_form_label_create_index') . "\n";

		return true;
	}

	public static function addCheckboxToUploadForm(&$oUploadForm){
		global $wgRequest, $wgFiCreateOnUploadByDefault, $wgFiArticleNamespace;

		/*
		 * Formular abeschickt?
		 * Ja => Harken setzen, wie vor dem Abschicken
		 * Nein => Artikel zur Datei aus dem Namensraum $wgFiArticleNamespace existiert?
		 * 		Ja => Harken setzen, wenn darin Index gefunden wird
		 * 		Nein => Harken nach globalen $wgFiCreateOnUploadByDefault setzen
		 */
		$bCheckboxChecked = true;
		if ( !is_null($wgRequest->getVal('wpUpload'))){
			$bCheckboxChecked = (!is_null($wgRequest->getVal('wpProcessIndex')) && $wgRequest->getVal('wpProcessIndex') == "true");
		} else {
			$iIndexArticleNamespace = ($wgFiArticleNamespace > -1) ? $wgFiArticleNamespace : NS_IMAGE;
			if ( $oUploadForm->mDesiredDestName != ""){
				$oTitle = Title::makeTitleSafe($iIndexArticleNamespace, $oUploadForm->mDesiredDestName);
				$oArticle = new Article($oTitle);
				if ( $oArticle->exists()){
					$oArticle->loadContent();
					$bCheckboxChecked = !(self::getIndexFragments($oArticle->mContent) === false);
				} else {
					$bCheckboxChecked = $wgFiCreateOnUploadByDefault;
				}
			} else {
				$bCheckboxChecked = $wgFiCreateOnUploadByDefault;
			}
		}
		$oUploadForm->uploadFormTextAfterSummary .= "</td></tr><tr><td align=right>FileIndexer:</td><td><input type='checkbox' name='wpProcessIndex' value='true' " . ($bCheckboxChecked ? "checked" : "") . "> " . wfMessage('fileindexer_form_label_create_index') . "\n";

		return true;
	}

	/**
	 * Sucht im Kommentar nach dem Zeichen zur Indexerzeugung und erstellt unter Einsatz externer Programme den Index. Dieser wird zunaechst
	 * in einer globalen Variable abgelegt um spaeter von anderen Funktionen verarbeitet zu werden.
	 *
	 * @param $oUploadForm OBJECT Alle Informationen aus dem Uploadformular
	 * @return BOOL TRUE
	 */
	public static function beforeProcessing(&$oUploadForm){
		global $wgFiRequestIndexCreationFile, $wgUploadDirectory, $wgHashedUploadDirectory, $wgRequest;

		// Im Kommentar zum Upload wird geschaut, ob der Index erzeugt werden soll. Wenn ja, dann entferne das Zeichen aus dem Kommentar. Ansonsten gehe ohne getane Arbeit raus.
		if ( is_null( $wgRequest->getVal('wpProcessIndex')) || $wgRequest->getVal('wpProcessIndex') != "true" ) {
			return true;
		}

		// Im Falle einer gueltigen Dateiendung => Endgueltigen Pfad in temporaere Datei schreiben
		if ( isset( $wgFiCommandCalls[ strtolower( substr( strrchr( $oUploadForm->mDesiredDestName, '.' ), 1 ) ) ] ) ) {
			exec( "echo \"" . $wgUploadDirectory . "/" . self::getHashPathForLevel( $oUploadForm->mDesiredDestName, $wgHashedUploadDirectory ? 2 : 0 ) . $oUploadForm->mDesiredDestName . "\" > " . $wgFiRequestIndexCreationFile . WC_FI_TMPFILE . session_id() );
		}

		return true;
	}

	/**
	 * Diese Hook-Funktion wird nach dem erfolgreichen Upload einer Datei aufgerufen und stoesst das Update des zur Datei
	 * gehoerigen Artikels an, sollte die Indexerstellung gefordert sein.
	 *
	 * @param $oImage OBJECT Die Uploadinformationen
	 * @return BOOL TRUE
	 */
	public static function uploadComplete(&$oImage){
		global $wgFiCreateIndexThisTime, $wgFiRequestIndexCreationFile, $wgFiArticleNamespace, $wgVersion;

		$aVersion = explode(".", $wgVersion);
		$iVersionRange = ($aVersion[0] == 1 && $aVersion[1] < 16) ? 1 : 2;

		// Pruefen, ob diesmalig zur Indexierung aufgefordert....
		$sUploadedFilepath = false;
		if ( $iVersionRange == 1){
			$sUploadedFilepath = $oImage->mLocalFile->repo->directory . "/" . $oImage->mLocalFile->hashPath . $oImage->mLocalFile->name;
		} else {
			$sUploadedFilepath = $oImage->getLocalFile()->repo->directory . "/" . $oImage->getLocalFile()->hashPath . $oImage->getLocalFile()->name;
		}
		$sCreateIndexFilepath = self::readFilepath();

		if ( $sCreateIndexFilepath !== false && $sCreateIndexFilepath == $sUploadedFilepath){
			// Zunaechst wird der Artikel gesucht und ggf. geladen, in den der Index abgelegt wuerde...
			$oArticle = false; // Bekannt machen...
			$iIndexArticleNamespace = ($wgFiArticleNamespace > -1) ? $wgFiArticleNamespace : NS_IMAGE;
			$oTitle = Title::makeTitleSafe($iIndexArticleNamespace, ($iVersionRange == 1) ? $oImage->mLocalFile->getTitle()->getDBkey() : $oImage->getTitle()->getDBkey());
			if ( $oTitle !== NULL){
				$oArticle = new Article($oTitle);

				$wgFiCreateIndexThisTime = true;
				$oArticle->doEdit($oArticle->mContent, wfMessage('fileindexer_upl_index_creation_comment'), 0);
				$wgFiCreateIndexThisTime = false;
			}
		}

		// Alles in Ausgangslage zuruecksetzen (temporaere Datei loeschen und Flag zuruecknehmen)...
		if ( file_exists($wgFiRequestIndexCreationFile . WC_FI_TMPFILE . session_id())){
			unlink($wgFiRequestIndexCreationFile . WC_FI_TMPFILE . session_id());
		}

		return true;
	}

	/**
	 * Diese Hook-Funktion aktualisiert die Index-Sektion, sollte es sich um einen FileUpload handeln
	 * und ein neuer Inhalt fuer diese Sektion vorbereitet worden sein.
	 * In jedem Fall wird die global abgelegte Index-Sektions-Inhalts-Variable wieder geleert.
	 *
	 * @param $oArticle OBJECT Der Artikel
	 * @param $oUser OBJECT Der Benutzer
	 * @param $sContent STRING Inhalt des Artikels
	 * @param $sSummary STRING Zusammenfassung fuer das Update
	 * @param $minor SIEHE WIKIDOKU
	 * @param $watch SIEHE WIKIDOKU
	 * @param $sectionanchor SIEHE WIKIDOKU
	 * @param $flags SIEHE WIKIDOKU
	 * @return BOOL TRUE
	 */
	public static function articleSave(&$oArticle, &$oUser, &$sContent, &$sSummary, $minor, $watch, $sectionanchor, &$flags){
		global $wgFiPrefix, $wgFiPostfix, $wgUploadDirectory, $wgHashedUploadDirectory, $wgRequest, $wgFiCreateIndexThisTime;

		// Spezialseite und UploadFormular setzen $wgFiCreateIndexThisTime auf true zur Indexerstellung
		if ( $wgFiCreateIndexThisTime === true || !is_null($wgRequest->getVal('wpProcessIndex')) && $wgRequest->getVal('wpProcessIndex') == "true"){
			// Datei holen und Index erstellen
			$sFilepath = $wgUploadDirectory . "/" . self::getHashPathForLevel( $oArticle->mTitle->mDbkeyform , $wgHashedUploadDirectory ? 2 : 0 ) . $oArticle->mTitle->mDbkeyform;
			$sIndex = self::getIndex( $sFilepath );
			if ( is_numeric($sIndex)){
				// kein Index aus Datei erzeugt
				switch ($sIndex){
					case WC_FI_ERR_MISSING_SYSTEMCOMMAND:
						$sReason = wfMessage('fileindexer_index_creation_failed_comment_missing_systemcommand');
						break;
					case WC_FI_ERR_UNKNOWN_FILETYPE:
						$sReason = wfMessage('fileindexer_index_creation_failed_comment_unknown_filetype');
						break;
					default:
						$sReason = wfMessage('fileindexer_index_creation_failed_comment_unknown_reason');
				}

				$sSummary .= ((substr($sSummary, strlen($sSummary) - 1, 1) == "\n") ? "" : "\n") . wfMessage('fileindexer_index_creation_failed_comment') . $sReason;

				return true;
			}

			// Index suchen und Text in Fragmente splitten
			$aFragments = self::getIndexFragments($sContent);
			if ( $aFragments === false){
				// kein Index gefunden
				if ( substr($sContent, strlen($sContent) - 1, 1) != "\n"){
					$sContent .= "\n";
				}
				$sContent .= $sIndex;
				$sSummary .= ((substr($sSummary, strlen($sSummary) - 1, 1) == "\n") ? "" : "\n") . wfMessage('fileindexer_index_creation_complete_comment');

				return true;
			} else {
				// Index gefunden
				$sContent = $aFragments['pre'] . $sIndex . $aFragments['post'];
				$sSummary .= ((substr($sSummary, strlen($sSummary) - 1, 1) == "\n") ? "" : "\n") . wfMessage('fileindexer_index_update_complete_comment');

				return true;
			}
		}

		return true;
	}

	/*
	 * **********************************************************************************************
	 * *********************************** Hilfsfunktionen ******************************************
	 * **********************************************************************************************
	 */

	/**
	 * Liefert ein Array, dass eine Trennung von den Teilen vor dem Index ('pre'), dem Index selbst ('index') und dem Teil nach dem Index ('post')
	 * oder FALSE, wenn kein Index lokalisiert werden konnte.
	 *
	 * @param $sText STRING zu durchsuchender Text
	 * @return ARRAY | FALSE Fragmente oder FALSE
	 */
	public static function getIndexFragments($sText){
		global $wgFiPrefix, $wgFiPostfix;

		$aFragments = false;

		$iPostFileIndexPos = false;
		$iFileIndexPos = strpos($sText, $wgFiPrefix);
		if ( $iFileIndexPos === false){
			return false;
		} else {
			$aFragments['pre'] = substr($sText, 0, $iFileIndexPos);

			$iPostFileIndexPos = strpos($sText, $wgFiPostfix, $iFileIndexPos);
			if ( $iPostFileIndexPos !== false){
				$aFragments['index'] = substr($sText, $iFileIndexPos, $iPostFileIndexPos - $iFileIndexPos);
				$aFragments['post'] = substr($sText, $iPostFileIndexPos + strlen($wgFiPostfix));
			} else {
				return false;
			}
		}

		return $aFragments;
	}

	/**
	 * Prueft, ob der Artikel zur Erstellung eines Indexes gemaess der Namensraumkonfigurationen valide ist.
	 *
	 * @param $oTitle OBJECT Der Artikel
	 * @return BOOL Erfolg
	 */
	public static function checkNamespace($oTitle){
		global $wgFiArticleNamespace;

		return !($wgFiArticleNamespace > -1 && $wgFiArticleNamespace != $oTitle->mNamespace);
	}

	/**
	 * Versucht die temporaere Datei auszulesen und prueft, ob der Inhalt eine existierende Datei darstellt.
	 *
	 * @return STRING | FALSE Pfad oder Fehler
	 */
	public static function readFilepath(){
		global $wgFiRequestIndexCreationFile;

		if ( file_exists($wgFiRequestIndexCreationFile . WC_FI_TMPFILE . session_id())){
			exec ("cat \"" . $wgFiRequestIndexCreationFile . WC_FI_TMPFILE . session_id() . "\"", $aReturn);
			$sFileHashPath = $aReturn[0];
			if ( $sFileHashPath != ""){
				if ( !file_exists($sFileHashPath)){
					// Datei konnte nicht gefunden werden...
					return false;
				} else {
					return $sFileHashPath;
				}
			} else {
				// Es ist kein Dateipfad zur Indexerstellung hinterlegt worden...
				return false;
			}
		} else {
			// Es ist keine Datei zur Indexerstellung hinterlegt worden.
			return false;
		}

	}

	/**
	 * Sucht im Kommentar nach dem Zeichen zur Indexerzeugung und erstellt unter Einsatz externer Programme den Index. Dieser wird zunaechst
	 * in einer globalen Variable abgelegt um spaeter von anderen Funktionen verarbeitet zu werden.
	 *
	 * @param $sFileHashPath STRING Dateipfad
	 * @return STRING Index
	 */
	public static function getIndex($sFileHashPath){
		global $wgFiPrefix, $wgFiPostfix, $wgFiMinWordLen, $wgFiCommandCalls, $wgFiCommandPaths, $wgFiTypesToRemoveTags, $wgFiLowercaseIndex;

		$sReturn = "";

		// Systemvoraussetzungen checken...
		SpecialFileIndexer::checkNecessaryCommands();
		if ( in_array(false, $wgFiCommandPaths)){
			return WC_FI_ERR_MISSING_SYSTEMCOMMAND;
		}

		$sFileExtension = strtolower(substr(strrchr($sFileHashPath, '.'),1));
		if ( !isset($wgFiCommandCalls[$sFileExtension])){
			// Unbekannter Dateityp => Abbruch
			return WC_FI_ERR_UNKNOWN_FILETYPE;
		}

		// ExecutionCommand ermitteln
		$sExecutionCommand = isset($wgFiCommandCalls[$sFileExtension]) ? SpecialFileIndexer::getCommandLine($sFileHashPath) : "";

		if ($sExecutionCommand != ""){
			exec($sExecutionCommand, $sDocText);

			$sReturn = $wgFiPrefix;
			$aIndex = array();

			// Feststellung der Mindest-Wortlaenge fuer ein Indexwort
			$wgFiMinWordLen = ($wgFiMinWordLen > 0) ? $wgFiMinWordLen : 3;

			foreach ($sDocText as $sDocLine){
				if ( in_array($sFileExtension, $wgFiTypesToRemoveTags)){
					// Tags entfernen... Vorher vor jedem "<" Leerzeichen einfuegen, damit keine Worte zusammenfallen!
					$sDocLine = strip_tags(str_replace("<", " <", $sDocLine));
				}

				// Sonderzeichen entfernen...
				// ATTENTION: German only! Umlaute werden durch strtolower nicht in Kleinbuchstaben gewandelt...
				if ( $wgFiLowercaseIndex){
					$sDocLine = strtolower(preg_replace("/[[:punct:]][[:space:]]|[[:space:]][[:punct:]]|[[:punct:]][[:punct:]]/", " ", str_replace("Ä", "ä", str_replace("Ö", "ö", str_replace("Ü", "ü", $sDocLine)))));
				} else {
					$sDocLine = preg_replace("/[[:punct:]][[:space:]]|[[:space:]][[:punct:]]|[[:punct:]][[:punct:]]/", " ", $sDocLine);
				}

				// Worte filtern und in Index packen...
				$aSplit = explode(" ", $sDocLine);
				foreach ( $aSplit as $sWord){
					if ( $sWord != "" && !is_numeric($sWord) && strlen($sWord) >= $wgFiMinWordLen){
						$aIndex[$sWord] = true;
					}
				}
			}

			// Index global setzen...
			foreach ( array_keys($aIndex) as $skeyword){
				$sReturn .= $skeyword . " ";
			}

			$sReturn .= $wgFiPostfix;
		}

		return $sReturn;
	}
}
