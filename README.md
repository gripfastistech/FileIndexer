FileIndexer
===========

FileIndexer is a MediaWiki extension that allows the software to search within uploaded text files, such as PDFs, DOCs, TXTs, etc.

The extension has been remvoed from MediaWiki.org for security reasons, but no one explained what those reasons are. Therefore I upload it to my GitHub to make it available for those who want to download this very useful extension.

Installation
------------
To install FileIndexer, simply add the following to your LocalSettings.php:

require_once "$IP/extensions/FileIndexer/FileIndexer.php";

Usage
-----
Once the extension has been install, go to Special:FileIndexer to generate the index of uploaded text files. After this the files will be searchable from the regular search box.

Changelog
---------
/**
 * TITEL:				Extension: FileIndexer
 * ERSTELLDATUM:		15.05.2008
 * AUTHOR:				Ramon Dohle aka 'raZe'
 * ORGANISATION:		GRASS-MERKUR GmbH & Co. KG & raZe.it
 * VERSION:				0.4.6.00	30.07.2014
 * REVISION:
 * 		15.05.2008	0.1.0.00	raZe		*	Initiale Version
 * 		26.06.2008	0.2.0.00	raZe		*	Komplettueberarbeitung
 * 		29.06.2009	0.2.1.00	raZe		*	Weitere Offene Punkte abgearbeitet:
 * 												*	$wgFiArticleNamespace auch bei Uploads nutzen.
 * 												*	$wgFiAutoIndexMark automatisch beim Upload mit Indexerstellungs-Aufforderung im Artikel einsetzbar machen.
 * 													Neue Option: $wgFiSetAutoIndexMark
 * 												*	$wgFiCheckSystem nutzen um Systemvoraussetzung bei jedem Aufruf zu pruefen
 * 													Neue Option: $wgFiCheckSystem
 * 												*	Temporaere Datei muss eindeutig einer Session zugeordnet werden koennen...
 * 		01.07.2009	0.2.2.00	raZe		*	Bug beseitigt, dass $wgFiAutoIndexMark beim Dateiupload nicht beruecksichtigt wurde
 * 		26.08.2010	0.3.0.00	raZe		*	BUGFIX: Variablenfehler in wfFiCheckIndexcreation() behoben
 * 											*	Neue Konfigurationsparameter eingebaut (siehe Beschreibung unter KONFIGURATION)
 * 											*	Neue Dateitypen (Office 2007) eingearbeitet
 * 											*	UTF-8 bei antiword Aufrufen eingetragen
 * 		27.08.2010	0.3.1.00	raZe		*	Indexerstellung per Spezialseite nun auch fuer mehrere Seiten eingebaut
 * 		28.08.2010	0.3.1.01	raZe		*	Revisionskopf (Beschreibungen...) aktualisiert
 * 					0.3.2.00	raZe		*	Neue Funktion wfFiGetIndexFragments() zur Aufteilung eines Artikeltextes in ein Array (pre, index, post).
 * 											*	Steuerung ueber Indexupdateregelung in Spezialseitenklasse ausgelagert
 * 		29.08.2010	0.3.3.00	raZe		*	MediaWiki 1.16 hat das Upload-Objekt umgebaut und viele Bestandteile als protected deklariert, sodass
 * 												ein Ausweichen auf das $wgRequest-Objekt notwendig wurde um in Hookfunktion wfFiBeforeProcessing() auf
 * 												den eingegebenen Summary bzw. Beschreibung/Quelle zugreifen zu koennen.
 * 											*	Gleiches galt fuer das Image-Objekt in Hookfunktion wfFiUploadComplete().
 * 											*	Kommentarmanipulation nicht mehr im Hook UploadForm:BeforeProcessing moeglich, daher Verlagerung nach ArticleSave
 * 											*	Voruebergehend Kompatibilitaet zu 1.15 aufrecht erhalten
 * 		30.08.2010	0.4.0.00	raZe		*	Mehrere Umstellungen auf Formularanpassungen im Page edit und Upload Bereich:
 * 												*	Konfigurationsparameter und Funktionen abgespeckt / veraendert
 * 												*	Formulare werden nun fuer die Indexerstellung ueber eine Checkbox gesteuert
 * 												*	Automatische Indexerstellung wird nun nicht mehr durch ein extra Merkmal festgemacht, sondern
 * 													als Vorschlag je Veraenderung im Formular gesteuert.
 * 		05.09.2010	0.4.0.01	raZe		*	Versionsangabe korrigiert
 * 		23.09.2010	0.4.1.00	raZe		*	BUGFIX: Falsche Arraypruefung bei der Kommandoermittlung zur Indexbildung korrigiert
 * 		25.09.2010	0.4.2.00	raZe		*	Defaultwert fuer das Wildcard Zeichen auf * geaendert
 * 											*	Defaultwert fuer den Namensraum auf NS_IMAGE geaendert
 * 											*	Neue Konfigurationsparameter $wgFiSpWildcardSignChangeable, $wgFiSpNamespaceChangeable, $wgFiLowercaseIndex,
 * 												$wgFiCreateOnUploadByDefault, $wgFiUpdateOnEditArticleByDefault (Beschreibungen s.u.)
 * 											*	Folgende Konfigurationsparameter wurden entfernt: $wgFiCreateIndexByDefault
 * 											*	BUGFIX: Fehler behoben, der bei bspw. Officedokumenten das entfernen der Tags verhinderte
 * 											*	Konfiguration in neue Datei FileIndexer_cfg.php ausgelagert.
 * 											*	Ungenutzte Funktion wfFiCheckNamespace() entfernt
 *		26.09.2010	0.4.3.00	raZe		*	Variable $wgFiFilenamePlaceholder durch Konstante WC_FI_FILEPATH ersetzt
 *											*	Variable $wgFiTempFilePrefix durch Konstante WC_FI_TMPFILE ersetzt
 *											*	SCHNITTSTELLE: Funktion wfFiGetIndex() liefert nun im Fehlerfall einen Fehlercode (INT) zurueck
 *											*	Fehler bei der Indexerstellung werden nun auch in der Summary beim Artikelspeichern angegeben
 *		27.09.2010	0.4.4.00	raZe		*	Neue Konstante: WC_FI_COMMAND fuer Konfigurationsparameter $wgFiCommandCalls
 *					0.4.4.01	raZe		*	Version korrigiert
 *		28.09.2010	0.4.5.00	raZe		*	BUGFIX: wfFiAddCheckboxToEditForm() Verwendung der globalen $wgFiCreateOnUploadByDefault und
 *												$wgFiUpdateOnEditArticleByDefault korrigiert
 *		30.09.2010	0.4.5.01	raZe		*	Defaultkonfiguration veraendert
 *											*	Revisionskopf aufgeraeumt
 *		30.07.2014	0.4.6.00	lfschenone	*	Updates to make it work in MediaWiki 1.23.1
 *
 * BESCHREIBUNG:
 * 		Diese Erweiterung basiert auf der Wiki-Erweiterung 'FileIndexer' vom Stand 15.05.2008.
 * 		Wie sein Original soll sie Dateien Indexieren um auch den Inhalt dieser Dateien durch Suchmaschienen zu erfassen.
 *
 * OFFENE PUNKTE:
 * 	5	TODO:	[ ]	Bessere Filter-Operationen einbauen.
 * 	9	TODO:	[ ]	Temporaere Dateien mit Datum versehen, sodass sie ggf. per Verfallsdatum geloescht werden koennen.
 *
 * LEGELDE:
 * 		[ ]: Offen
 * 		[B]: Beschlossen zum kommenden Release
 * 		[I]: Inaktiv aber reserviert / zeitlich geplant
 * 		[A]: Aktiv in Bearbeitung
 * 		[T]: Realisiert, aber ungetestet
 * 		[P]: Neu zu pruefen und zu bewerten, ggf. abzusagen
 * 		[D]: Realisiert und getestet, aber noch nicht dokumentiert
 * 		[X]: Temporaere Markierung fuer die Fertigstellung fuer Patchnotes
 */

/**
 * TITEL:				Extension: FileIndexer / FileIndexer.body.php
 * ERSTELLDATUM:		26.06.2008
 * AUTHOR:				Ramon Dohle aka 'raZe'
 * ORGANISATION:		GRASS-MERKUR GmbH & Co. KG & raZe.it
 * VERSION:				0.4.2.00	30.07.2014
 * REVISION:
 * 		26.06.2008	0.1.0.00	raZe		*	Initiale Version
 * 		29.06.2009	0.1.1.00	raZe		*	Weitere Offene Punkte abgearbeitet:
 * 												*	$wgFiArticleNamespace auch bei Uploads nutzen.
 * 												*	$wgFiAutoIndexMark automatisch beim Upload mit Indexerstellungs-Aufforderung im Artikel einsetzbar machen.
 * 													Neue Option: $wgFiSetAutoIndexMark
 * 												*	$wgFiCheckSystem nutzen um Systemvoraussetzung bei jedem Aufruf zu pruefen
 * 													Neue Option: $wgFiCheckSystem
 * 												*	Temporaere Datei muss eindeutig einer Session zugeordnet werden koennen...
 * 		01.07.2009	0.1.2.00	raZe		*	An neue Funktionen in FileIndexer.php angeglichen
 * 		26.08.2010	0.2.0.00	raZe		*	Formular an neue Funktionsweise angepasst
 * 		27.08.2010	0.2.1.00	raZe		*	Logik nach neuem Formular umgesetzt
 * 		28.08.2010	0.2.2.00	raZe		*	Formular mit Hilfeinformationen verbessert
 * 											*	Ergebnisinformationsausgabe verbessert
 * 											*	Check Funktionalitaet hinzugefuegt
 * 											*	Formularreset abgestellt
 * 		29.08.2010	0.2.2.01	raZe		*	Revisionskopf korrigiert
 * 		30.08.2010	0.3.0.00	raZe		*	Mehrere Umstellungen auf Formularanpassungen im Page edit und Upload Bereich
 * 		23.09.2010	0.3.1.00	raZe		*	BUGFIX: Fehler entfernt, der bei Leerzeilen in der Spezialseite zum Abbruch fuehrte
 * 		25.09.2010	0.3.2.00	raZe		*	Reihenfolge der Felder in der Spezialseite umgestellt
 * 											*	Anzeige einiger Eingabefelder (Namespace und WildcardSign) optional gemacht.
 * 		26.09.2010	0.3.3.00	raZe		*	Trennung zwischen "nicht gefundenen Dateien" und "nicht unterstuetzten Dateitypen" bei der Ergebnisausgabe
 * 												herbeigefuehrt
 * 											*	Systempruefung eines Kommandos ist nun auch mehrsprachig
 * 		27.09.2010	0.4.0.00	raZe		*	SCHNITTSTELLE: neue, statische Funktion getCommandLine() zur endgueltigen Bestimmung des Kommandoaufrufes
 * 											*	BUGFIX: Funktion checkNecessaryCommands() ueberarbeitet un dFehler behoben
 * 												+	Ausgabe verbesssert
 * 												+	'whereis' durch 'which' abgeloest
 * 												+	wenn Pfad inkorrekt, aber Erfolg mit which => kein Fehler, sondern Warnung (Spezialseite), aber
 * 													Indexierung wird mit gefundenem Pfad trotzdem durchgefuehrt.
 * 		28.09.2010	0.4.1.00	raZe		*	BUGFIX: In processIndex() Links korrigiert, sodass Bilder nicht mehr als Bild aufgelistet werden
 * 											*	Wildcard Zeichen Text in Spezialzeichenhilfe besser hervorgehoben fuer die Konfiguration
 * 												$wgFiSpWildcardSignChangeable = false
 * 		30.07.2014	0.4.2.00	lfschenone	*	Updates to make it work in MediaWiki 1.23.1
 *
 *
 * BESCHREIBUNG:
 * 		Diese Erweiterung basiert auf der Wiki-Erweiterung 'FileIndexer' vom Stand 15.05.2008.
 * 		Wie sein Original soll sie Dateien Indexieren um auch den Inhalt dieser Dateien durch Suchmaschienen zu erfassen.
 *
 * 		Hier wird die Spezialseitenklasse zur Verfuegung gestellt.
 */

/**
 * TITEL:				Extension: FileIndexer / FileIndexer.config.php
 * ERSTELLDATUM:		25.09.2010
 * AUTHOR:				Ramon Dohle aka 'raZe'
 * ORGANISATION:		GRASS-MERKUR GmbH & Co. KG & raZe.it
 * VERSION:				0.1.4.00	30.09.2010
 * REVISION:
 * 		25.09.2010	0.1.0.00	raZe		*	Initiale Version
 * 											*	Ausgelagert aus FileIndexer.php
 * 		26.09.2010	0.1.1.00	raZe		*	Variable $wgFiFilenamePlaceholder durch Konstante WC_FI_FILEPATH ersetzt
 * 		27.09.2010	0.1.2.00	raZe		*	$wgFiCommandCalls unter Verwendung der Konstante WC_FI_COMMAND neu gesetzt
 * 		28.09.2010	0.1.3.00	raZe		*	Weitere Office Dokumenttypen eingetragen
 * 											*	BUGFIX: Index iconv in $wgFiCommandPaths korrigiert
 * 		30.09.2010	0.1.4.00	raZe		*	Defaultwert von $wgFiCommandPaths geaendert - Pfrade auf /usr/bin/ vereinheitlicht
 * 											*	Defaultwert von $wgFiCommandCalls geaendert - hochgeladene Datei in Anfuehrungszeichen gesetzt
 *
 * OFFENE PUNKTE:
 * 		@SYSTEMADMINISTRATOREN:
 * 			Die folgenden Konfigurationsparameter muessen noch geprueft werden:
 * 				$wgFiCommandPaths
 * 				$wgFiCommandCalls
 * 				$wgFiTypesToRemoveTags
 * 			Die
 *
 * BESCHREIBUNG:
 * 		Diese Erweiterung basiert auf der Wiki-Erweiterung 'FileIndexer' vom Stand 15.05.2008.
 * 		Wie sein Original soll sie Dateien Indexieren um auch den Inhalt dieser Dateien durch Suchmaschienen zu erfassen.
 *
 * 		Hier wird die Konfiguration der Erweiterung extrahiert.
 */