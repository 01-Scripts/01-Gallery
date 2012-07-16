<?PHP
/* 
	01-Gallery - Copyright 2003-2012 by Michael Lorer - 01-Scripts.de
	Lizenz: Creative-Commons: Namensnennung-Keine kommerzielle Nutzung-Weitergabe unter gleichen Bedingungen 3.0 Deutschland
	Weitere Lizenzinformationen unter: http://www.01-scripts.de/lizenz.php
	
	Modul:		01gallery
	Dateiinfo: 	Modulspezifische Grundeinstellungen, Variablendefinitionen etc.
				Wird automatisch am Anfang jeden Modulaufrufs automatisch includiert.
	#fv.210#
*/

// Modul-Spezifische MySQL-Tabellen
$mysql_tables['gallery'] 	= "01_".$instnr."_".$module[$modul]['nr']."_galerien";
$mysql_tables['pics'] 		= "01_".$instnr."_".$module[$modul]['nr']."_pictures";

$addJSFile 	= "javas.js";			// Zusätzliche modulspezifische JS-Datei (im Modulverzeichnis!)
$addCSSFile = "modul.css";			// Zusätzliche modulspezifische CSS-Datei (im Modulverzeichnis!)
$mootools_use = array("moo_core","moo_more","moo_remooz","moo_slideh","moo_request","moo_sortable");
	
if(isset($_REQUEST['loadpage']) && $_REQUEST['loadpage'] == "upload" && !strchr($_SERVER['HTTP_USER_AGENT'],"MSIE 6.0"))
	$mootools_use[] = "moo_fancyup";

// Welche PHP-Seiten sollen abhängig von $_REQUEST['loadpage'] includiert werden?
$loadfile['index'] 		= "index.php";			// Standardseite, falls loadpage invalid ist
$loadfile['galerien']	= "galerien.php";		// Galerie-Übersicht (Galerien bearbeiten und anlegen)
$loadfile['upload']		= "upload.php";			// Bilder hochladen oder importieren
$loadfile['showpics']	= "pictures.php";		// Bilder einer Galerie auflisten, bearbeiten und sortieren

// Weitere Pfadangaben
$imagepf 	= "images/";			// Verzeichnis zum Bild-Verzeichnis
$tempdir	= "templates/";			// Template-Verzeichnis
$galdir		= "galerien/";			// Verzeichnis mit Bildergalerien

// Weitere Variablen
define('ACP_PER_PAGE2', 20);		// Einträge pro Seite im ACP
$allow_big_download = true;         // Download von unverkleinerten Originaldateien erlauben?
$comment_desc       = "DESC";		// Sortierreihenfolge der Kommentare
$max_uploads		= 10;			// Anzahl an Datei-Upload-Feldern
$gen_thumbs_max     = 50;           // Max. Anzahl an Thumbnails, die gleichzeitig generiert werden dürfen
$oldfilename_length = 10;			// Länge des Anteils des original Dateinamens nach dem Upload
$anz_streampics		= 2;			// Anzahl Bilder im Thumbnail-Stream unterhalb der Detailansicht (Jeweils $anz_streampics vor und nach dem aktuellen Bild) -> Gesamtzahl = $anz_streampics*2+1
$smallstreampicsize = 40;			// Max. Kantenlänge (px) der Bilder für den kleinen Bilderstream ($flag_smallstream)
$text_bilderlaben	= "Bilderalben";		// Text für die Übersichtsseite in den Breacrumps

// Variablennamen-Deklaration
$names['galid']		= "galid";
$names['galpage']	= "galpage";
$names['picid']		= "galpicid";
$names['picpage']	= "picpage";
$names['picfilename'] = "picfilename";
$names['action']	= "galdisplay";
$names['cpage']		= "galcompage";











// System-Variablen (Änderungen nur vornehmen, wenn Sie wissen, was Sie tun!)
define('ACP_GAL_TB_WIDTH', 75); 	// Max. Kantenlänge von Bildern im Galerie-ACP
$import_max_count	= 10;			// Maximalanzahl an Bildern, die in einem Schritt importiert werden.
$supported_pictypes = array("jpg","jpeg","png","gif");		// Unterstützte Dateitypen

?>