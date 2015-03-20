<?php
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

define( "WC_FI_TMPFILE", "/fi.requested.article." );
define( "WC_FI_FILEPATH", "[=-FILE_NAME-=]" );
define( "WC_FI_COMMAND", "[=-COMMAND_PATH-=]" );
define( "WC_FI_ERR_MISSING_SYSTEMCOMMAND", -1 );
define( "WC_FI_ERR_UNKNOWN_FILETYPE", -2 );

// *** INTERN GENUTZTE VARIABLEN
$wgFiCreateIndexThisTime = false; // Temporaerer Schalter fuer die Erstellung eines Indexes beim speichern eines Artikels


// *** SYSTEMNAHE KONFIGURATIONSPARAMETER

/*
 * TYP: BOOL
 * ENTWICKLER DEFAULT = FALSE
 * BESCHREIBUNG:
 * 		Wenn TRUE, dann wird vor jeder Indexerstellung geprueft, ob alle Hilfprogramme erreichbar sind.
 */
$wgFiCheckSystem = false;

/*
 * TYP: ARRAY
 * BESCHREIBUNG:
 * 		Verwaltet alle vorausgesetzten System-Kommandozeilen-Hilfsprogramme mit deren Aufrufpfaden.
 */
$wgFiCommandPaths = array(
	'convPDFtoTXT' => "/usr/bin/convPDFtoTXT",
	'pdftotext' => "/usr/bin/pdftotext",
	'iconv' => "/usr/bin/iconv",
	'antiword' => "/usr/bin/antiword",
	'xls2csv' => "/usr/bin/xls2csv",
	'catppt' => "/usr/bin/catppt",
	'strings' => "/usr/bin/strings",
	'unzip' => "/usr/bin/unzip",
);

/*
 * TYP: ARRAY
 * BESCHREIBUNG:
 * 		Verwaltet die Templates der Kommandoaufrufe je eingestelltem Dateityp (Dateiendung).
 * 		ACHTUNG: Bitte die Konstante WC_FI_FILEPATH dort einsetzen, wo spaeter jeweils der Dateipfad ersetzt werden soll!
 */
$wgFiCommandCalls = array(
	'pdf' => WC_FI_COMMAND . "[convPDFtoTXT] \"" . WC_FI_FILEPATH . "\"",
//	'pdf' => WC_FI_COMMAND . "[pdftotext] -raw -nopgbrk \"" . WC_FI_FILEPATH . "\" -| " . WC_FI_COMMAND . "[iconv] -f ISO-8859-1 -t UTF-8",
	'dot' => WC_FI_COMMAND . "[antiword] -m UTF-8.txt -s \"" . WC_FI_FILEPATH . "\"",
	'doc' => WC_FI_COMMAND . "[antiword] -m UTF-8.txt -s \"" . WC_FI_FILEPATH . "\"",
	'xls' => WC_FI_COMMAND . "[xls2csv] \"" . WC_FI_FILEPATH . "\"",
	'ppt' => WC_FI_COMMAND . "[catppt] \"" . WC_FI_FILEPATH . "\"",
	'rtf' => WC_FI_COMMAND . "[strings] \"" . WC_FI_FILEPATH . "\"",
	'ods' => WC_FI_COMMAND . "[unzip] -p \"" . WC_FI_FILEPATH . "\" content.xml",
	'odp' => WC_FI_COMMAND . "[unzip] -p \"" . WC_FI_FILEPATH . "\" content.xml",
	'odg' => WC_FI_COMMAND . "[unzip] -p \"" . WC_FI_FILEPATH . "\" content.xml",
	'odt' => WC_FI_COMMAND . "[unzip] -p \"" . WC_FI_FILEPATH . "\" content.xml",
	'docx' => WC_FI_COMMAND . "[unzip] -p \"" . WC_FI_FILEPATH . "\" word/document.xml",
	'xlsx' => WC_FI_COMMAND . "[unzip] -p \"" . WC_FI_FILEPATH . "\" xl/sharedStrings.xml",
	'pptx' => WC_FI_COMMAND . "[unzip] -p \"" . WC_FI_FILEPATH . "\" ppt/slides/slide*.xml",
	'ppsx' => WC_FI_COMMAND . "[unzip] -p \"" . WC_FI_FILEPATH . "\" ppt/slides/slide*.xml",
	'dotx' => WC_FI_COMMAND . "[unzip] -p \"" . WC_FI_FILEPATH . "\" word/document.xml",
	'dotm' => WC_FI_COMMAND . "[unzip] -p \"" . WC_FI_FILEPATH . "\" word/document.xml",
	'docm' => WC_FI_COMMAND . "[unzip] -p \"" . WC_FI_FILEPATH . "\" word/document.xml",
	'xlsx' => WC_FI_COMMAND . "[unzip] -p \"" . WC_FI_FILEPATH . "\" xl/sharedStrings.xml",
	'xlam' => WC_FI_COMMAND . "[unzip] -p \"" . WC_FI_FILEPATH . "\" xl/sharedStrings.xml",
	'xslx' => WC_FI_COMMAND . "[unzip] -p \"" . WC_FI_FILEPATH . "\" xl/sharedStrings.xml",
	'xlsm' => WC_FI_COMMAND . "[unzip] -p \"" . WC_FI_FILEPATH . "\" xl/sharedStrings.xml",
);

/*
 * TYP: ARRAY
 * BESCHREIBUNG:
 * 		Liste aller Dateitypen (Dateiendungen) auf, deren Ausgabe nach Ausfuehrung das entsprechenden Kommandos aus $wgFiCommandCalls noch von Tags zu befreien sind.
 */
$wgFiTypesToRemoveTags = array(
	'ods',
	'odp',
	'odg',
	'odt',
	'docx',
	'xlsx',
	'pptx',
	'dotx',
	'dotm',
	'docm',
	'xlam',
	'xslx',
	'xlsm',
	'pptx',
	'ppsx',
);

/*
 * TYP: STRING
 * ENTWICKLER DEFAULT: "/tmp"
 * BESCHREIBUNG:
 * 		Verzeichnispfad zur Erstellung temporaeren Dateien. Diese werden benoetigt um bei Warnmeldungen waehrend des Fileuploads die Aufforderung
 * 		zur Indexerstellung nicht zu vergessen (Problematik: neuer Request)
 * 		ACHTUNG: In diesem Verzeichnis benoetigt der Systembenutzer, unter dem der Webserver ausgefuehrt wird, Schreibrechte!
 */
$wgFiRequestIndexCreationFile = "/tmp";



// *** AUSGABE KONFIGURATIONSPARAMETER

/*
 * TYP: STRING
 * ENTWICKLER DEFAULT: "<!-- FI:INDEX-START -->{{FileIndex |index="
 * BESCHREIBUNG:
 * 		Ein eindeutiges Praefix vor dem Index-Block, welches bei einer Aktualisierung genutzt wird um den Anfang des Blocks zu erkennen.
 * 		TIP: Dieser kann genutzt werden um die Ausgabe zu formatieren. Dabei ist zu beachten, wie die eingesetzten SeachEngines arbeiten!
 */
$wgFiPrefix = "<!-- FI:INDEX-START -->{{FileIndex |index=";

/*
 * TYP: STRING
 * ENTWICKLER DEFAULT: " }}<!-- FI:INDEX-START -->"
 * BESCHREIBUNG:
 * 		Ein eindeutiges Postfix hinter dem Index-Block, welches bei einer Aktualisierung genutzt wird um das Ende des Blocks zu erkennen.
 * 		TIP: Dieser kann genutzt werden um die Ausgabe zu formatieren. Dabei ist zu beachten, wie die eingesetzten SeachEngines arbeiten!
 */
$wgFiPostfix = " }}<!-- FI:INDEX-ENDE -->";

/*
 * TYP: INT
 * ENTWICKLER DEFAULT: NS_IMAGE
 * BESCHREIBUNG:
 * 		Dieses Feld legt fest, ob das Erstellen eines Indexes ausschliesslich in einem bestimmten Namensraum ermoeglicht sein soll.
 * 		Hier ist die Nummer des Namensraumes anzugeben. Sollte ein anderer Namensraum als NS_IMAGE gewaehlt werden, wird beim Upload
 * 		von Dateien im Artikel des Namensraums NS_IMAGE kein Index erstellt, hingegen wird im gleichnamigen Artikel des eingestellten
 * 		Namensraums der Index hinterlegt.
 */
$wgFiArticleNamespace = NS_IMAGE;

/*
 * TYP: INT
 * ENTWICKLER DEFAULT: 3
 * BESCHREIBUNG:
 * 		Mindestlaenge der Indexworte (bei der Angabe von Werte kleiner eins wird automatisch der Wert 3 verwendet).
 * 		Zeichenfolgen mit weniger als dieser Anzahl von Zeichen werden nicht im Index beruecksichtigt.
 */
$wgFiMinWordLen = 3;

/*
 * TYP: BOOL
 * ENTWICKLER DEFAULT: TRUE
 * BESCHREIBUNG:
 * 		Legt fest, ob der Index komplett in Kleinbuchstaben umgewandelt werden soll.
 */
$wgFiLowercaseIndex = false;



// *** SPEZIALSEITEN KONFIGURATIONSPARAMETER

/*
 * TYP: CHAR
 * ENTWICKLER DEFAULT: '*'
 * BESCHREIBUNG:
 * 		Voreingestelltes Zeichen als Wildcard fuer die Spezialseite zur gezielten Indexierung
 */
$wgFiSpDefaultWildcardSign = "*";

/*
 * TYP: BOOL
 * ENTWICKLER DEFAULT: TRUE
 * BESCHREIBUNG:
 * 		Legt fest, ob auf der Spezialseite das Wildcard Zeichen fuer die Bestimmung der Artikel waehlbar ist.
 */
$wgFiSpWildcardSignChangeable = false;

/*
 * TYP: BOOL
 * ENTWICKLER DEFAULT: TRUE
 * BESCHREIBUNG:
 * 		Legt fest, ob auf der Spezialseite der Namesraum fuer die Index-Zielartikel waehlbar ist.
 */
$wgFiSpNamespaceChangeable = false;



// *** ALLGEMEINE STEUERUNGS KONFIGURATIONSPARAMETER

/*
 * TYP: BOOL
 * ENTWICKLER DEFAULT: TRUE
 * BESCHREIBUNG:
 * 		Schalter fuer das standardmaessige Erstellen eines Indexes, wann immer eine Datei hochgeladen wird. Er erzwingt zwar nicht die
 * 		Erstellung, aber durch setzen auf TRUE, wird bei jedem Aufruf der entsprechenden des Uploadformulars die Checkbox zur
 * 		Indexanforderung gesetzt.
 */
$wgFiCreateOnUploadByDefault = true;

/*
 * TYP: BOOL
 * ENTWICKLER DEFAULT: FALSE
 * BESCHREIBUNG:
 * 		Schalter fuer das standardmaessige Aktualisieren des Indexes, wann immer ein Artikel, der einen Index zu einer Datei beinhaltet,
 * 		veraendert wird. Er erzwingt zwar nicht die Aktualisierung, aber durch setzen auf TRUE, wird im Formular die Checkbox zur
 * 		Indexanforderung gesetzt.
 */
$wgFiUpdateOnEditArticleByDefault = false;