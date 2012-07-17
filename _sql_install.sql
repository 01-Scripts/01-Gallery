-- 01-Gallery - Copyright 2003-2012 by Michael Lorer - 01-Scripts.de
-- Lizenz: Creative-Commons: Namensnennung-Keine kommerzielle Nutzung-Weitergabe unter gleichen Bedingungen 3.0 Deutschland
-- Weitere Lizenzinformationen unter: http://www.01-scripts.de/lizenz.php

-- Modul:		01gallery
-- Dateiinfo:	SQL-Befehle für die Erstinstallation der 01-Gallery
-- #fv.210#
--  **  **  **  **  **  **  **  **  **  **  **  **  **  **  **  **  *  *

-- --------------------------------------------------------

SET AUTOCOMMIT=0;
START TRANSACTION;

-- --------------------------------------------------------

-- 
-- Neue Einstellungs-Kategorie für Modul anlegen
-- Einstellungen importieren
-- 
	
INSERT INTO 01prefix_settings (modul,is_cat,catid,sortid,idname,name,exp,formename,formwerte,input_exp,standardwert,wert,nodelete,hide) VALUES
('#modul_idname#', 1, 1, 1, 'gallerysettings', 'Einstellungen', NULL, '', '', NULL, NULL, NULL, 0, 0),
('#modul_idname#', 1, 2, 2, 'bildeinstellungen', 'Bildeinstellungen', '', '', '', '', '', '', 1, 0),
('#modul_idname#', 0, 2, 1, 'resize_pics_on_upload', 'Bilder beim Hochladen verkleinern?', 'Eine Version mit der vollen Bildaufl&ouml;sung bleibt erhalten.', 'Ja|Nein' ,'1|0', '', '0', '0', 0, 0),
('#modul_idname#', 0, 2, 2, 'resize_maxpicsize', 'Maximale Bildaufl&ouml;sung','Bilder mit h&ouml;herer Aufl&ouml;sung werden ggf. verkleinert.','text','10','Pixel (Breite x H&ouml;he)','1024x768','1024x768', 0, 0),
('#modul_idname#', 0, 2, 3, 'galpic_size', 'Maximale Dateigr&ouml;&szlig;e (Galeriebilder)', '', 'text', '5', 'KB', '1024', '2000', 0, 0),
('#modul_idname#', 0, 2, 4, 'thumbnail_type','Thumbnails','<a href=\"javascript:modulpopup(''#modul_idname#'',''recreate_thumbnails'','''','''','''',510,450);\">Thumbnails neu generieren</a>','Dynamische Gr&ouml;&szlig;e (Seitenverh&auml;ltnis beibehalten)|Feste Gr&ouml;&szlig;e (Bilder werden zugeschnitten)','dyn|fix','','fix','fix',0,0),
('#modul_idname#', 0, 2, 5, 'tb_size','Maximale Thumbnail-Gr&ouml;&szlig;e','Breite und H&ouml;he in Pixeln ohne Einheit angeben.','text','10','Pixel (Breite x H&ouml;he)','100x75','100x75',0,0),
('#modul_idname#', 1, 3, 3, 'csssettings', 'CSS-Einstellungen', '', '', '', '', '', '', 0, 0),
('#modul_idname#', 0, 3, 1, 'extern_css', 'Externe CSS-Datei', 'Geben Sie einen absoluten Pfad inkl. <b>http://</b> zu einer externen CSS-Datei an.\r\nLassen Sie dieses Feld leer um die nachfolgend definierten CSS-Eigenschaften zu verwenden.', 'text', '50', '', '', '', 0, 0),
('#modul_idname#', 0, 3, 2, 'csscode', 'CSS-Eigenschaften', 'Nachfolgende CSS-Definitionen werden nur ber&uuml;cksichtigt, wenn <b>keine</b> URL zu einer externen CSS-Datei eingegeben wurde!', 'textarea', '18|100', '', '', '/* Äußere Box für den gesamten Bildergalerie-Bereich - DIV selber (id = _01gallery) */\r\n#_01gallery{\r\n	text-align:left;\r\n	}\r\n\r\n.box_out{\r\n	width: 100%;\r\n	margin: 0 auto;\r\n	color:#000;\r\n	text-align:left;\r\n	font-family: Verdana, Arial, Helvetica, sans-serif;\r\n	font-size:10pt;\r\n	}\r\n\r\n/* Link-Definitionen (box_out) */\r\n.box_out a:link,.box_out a:visited  {\r\n	text-decoration: underline;\r\n	color: #000;\r\n}\r\n.box_out a:hover  {\r\n	text-decoration: none;\r\n	color: #000;\r\n}\r\n\r\n/* Textattribute für Fehlermeldungen */\r\np.errormsg {\r\n	color:red;\r\n}\r\n\r\n/* Formatierung der Bilderalben Breadcrumps */\r\nh2.breadcrumps {\r\n	font-size:21px;\r\n}\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n/* Galerie-Auflistung */\r\n/* Allgemeine Formatierung des Beschreibungstextes */\r\n.gallist_u_beschreibung p {\r\n	margin:0;\r\n	margin-top:5px;\r\n}\r\n\r\n/* Aussehen der Passwort-Box, die angezeigt wird, wenn eine Galerie mit einem Passwort geschützt ist */\r\n.galpwbox {\r\n	width:400px;\r\n	height:125px;\r\n	margin: 0 auto;\r\n\r\n	padding:5px;\r\n	border:1px solid #eee;\r\n\r\n	text-align:center;\r\n}\r\n\r\n\r\n/* Auflistung untereinander */\r\n/* Formatierung des Galerienamens */\r\nh3.gal_title {\r\n	margin-top:0;\r\n	margin-bottom:5px;\r\n	font-weight:normal;\r\n}\r\n\r\n/* Formatierung des Hinweistextes, wenn ein Bilderalbum mit einem Passwort geschützt wrde */\r\n.gallist_u_beschreibung p.gal_password {\r\n	font-size:8pt;\r\n}\r\n\r\n/* Rahmen für die Tabellenzellen */\r\ntd.gallist_u_beschreibung {\r\n	border-bottom:0px solid #000;\r\n}\r\n\r\n/* Rahmen für Galerie-Thubnails */\r\ntd.gallist_u_thumbnail img {\r\n	border:1px solid #000;\r\n	padding:5px;\r\n}\r\n\r\n/* Bildergalerien auflisten (nebeneinander), DIV-Box */\r\ndiv.gallistbox {\r\n	height: 140px;									/* Höhe der Box, ggf. an Thumbnail-Größe anpassen */\r\n	float:left;\r\n	margin:4px;\r\n	padding:3px;\r\n	width:180px;\r\n	text-align:center;\r\n	}\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n/* CSS-Definitionen für die Thumbnail-Auflistung */\r\n/* CSS-Gallery by dnevnikeklektika.com\r\n-	http://web.archive.org/web/20070410114605/http://dnevnikeklektika.com/en/css-gallery-layout-en\r\n-	http://dnevnikeklektika.com/en/css-gallery-layout-en\r\n*/\r\n.cssgallery{\r\n	margin:0; padding:0;					/* NICHT VERÄNDERN!!! */\r\n	overflow:hidden; 						/* NICHT VERÄNDERN!!! - Clears the floats */\r\n	width:100%; 							/* NICHT VERÄNDERN!!! - IE and older Opera fix for clearing, they need a dimension */\r\n	list-style:none;						/* NICHT VERÄNDERN!!! */\r\n}\r\n\r\n.cssgallery li{\r\n	float:left;								/* NICHT VERÄNDERN!!! */\r\n	display:inline; 						/* NICHT VERÄNDERN!!! - For IE so it doesn''t double the 1% left margin */\r\n	margin:0 0 0 1%; padding:0 0;	/* Bestimmt den Abstand der einzelnen Bilder zueinander */\r\n	position:relative; 						/* NICHT VERÄNDERN!!! - This is the key */\r\n	text-align:center;\r\n}\r\n\r\n.cssgallery a{\r\n	display:block;\r\n	margin:0 auto;\r\n}\r\n\r\n.cssgallery img{					/* Hier kann ein Rahmen um die Thumbnails angelegt werden */\r\n	padding:3px;\r\n	border:1px solid #000;\r\n}\r\n\r\na img{ border:none; } 						/* NICHT VERÄNDERN!!! - A small fix */\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n/* DIV für die Detailansicht eines einzelnen Bildes */\r\ndiv.picdetailview {\r\n	width:95%;\r\n	margin:0 auto;\r\n	padding:10px;\r\n\r\n	text-align:center;\r\n}\r\n\r\n/* CSS-Classe des Detailbildes */\r\nimg.picdetailimg {\r\n	width:auto !important;\r\n	width:100%;\r\n	max-width:100%;\r\n	border:0;\r\n}\r\n\r\n/* CSS-Classe für Bildtitel in der Detailansicht */\r\nh3.picdetailh3 {\r\n	margin-top:5px;\r\n	margin-bottom:10px;\r\n}\r\n\r\n/* Beschreibungstext in der Detailansicht */\r\ndiv.picdetailview p {\r\n\r\n}\r\n\r\n/* DIV-Box für die Thumbnails unterhalb der Detailansicht (Smallstream) */\r\ndiv.picstream {\r\n    clear: both;\r\n    float: left;\r\n    width: 100%;\r\n    overflow: hidden;\r\n}\r\ndiv.picstream ul.cssgallery {\r\n    width: auto;\r\n    float: left;\r\n    position: relative;\r\n    left: 50%;\r\n    margin: 0 auto;\r\n    padding: 0;\r\n    list-style-type: none;\r\n    overflow: visible;\r\n}\r\ndiv.picstream ul.cssgallery li.stream {\r\n    float: left;\r\n    position: relative;\r\n    margin:0;\r\n    right: 50%;\r\n}\r\n\r\n\r\n\r\n\r\n\r\n/* Definition für Kommentar-Box (Anzeige von Kommentaren) */\r\n.commentbitbox {\r\n	width:80%;\r\n	text-align:left;\r\n	border: 1px dotted #999;\r\n	padding:8px;\r\n	margin:0 auto;\r\n	}\r\n\r\n.comment_text {\r\n	font-size:12px;\r\n	text-decoration:none;\r\n	}\r\n\r\n/* Definition für Kommentar-Hinzufügen-Tabelle */\r\n.commentaddbox {\r\n	width:82%;					/* 2% größer als width von .commentbitbox wählen */\r\n	text-align:left;\r\n	border: 1px dotted #999;\r\n	padding:8px;\r\n	}\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n/* Formatierung der Tabelle mit Weiter- und Zurück-Links bei mehrseitigen Auflistungen */\r\n.galpagestable,\r\n.picpagestable {\r\n	width:100%;\r\n	margin-top:25px;\r\n}\r\n\r\n\r\n\r\n\r\n\r\n/* Aussehen von kleinem Text */\r\n.small01acp, .small01acp a:link,.small01acp a:visited {\r\n	font-size:10px;\r\n	text-decoration:none;\r\n	text-transform: uppercase;\r\n	font-family: Arial, Helvetica, sans-serif;\r\n	}\r\n.small01acp a:link,.small01acp a:visited {\r\n	text-decoration:underline;\r\n	}\r\n.box_out a:hover  {\r\n	text-decoration: none;\r\n}\r\n\r\n/* Hervorgehobener, wichtiger Text */\r\n.highlight {\r\n	font-weight:bold;\r\n	color:red;\r\n	}\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n/* Formular-Elemente */\r\n/* Normales Textfeld */\r\n.input_field {\r\n\r\n	}\r\n\r\n/* CSS-Klasse für Mehrzeilige Eingabefelder (textareas) */\r\n.textareafeld {\r\n	font-size: 10pt;\r\n	font-family: Verdana, Arial, Helvetica, sans-serif;\r\n}\r\n\r\n/* Formular-Buttons */\r\n.input_button {\r\n\r\n	}\r\n\r\n/* Dropdown-Boxen */\r\n.input_selectfield {\r\n\r\n	}\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n/* SLIMBOX */\r\n\r\n#lbOverlay {\r\n	position: fixed;\r\n	z-index: 9999;\r\n	left: 0;\r\n	top: 0;\r\n	width: 100%;\r\n	height: 100%;\r\n	background-color: #000;				/* Overlay-Hintergrundfarbe der Lightbox-Abdunklung */\r\n	cursor: pointer;\r\n}\r\n\r\n#lbCenter, #lbBottomContainer {\r\n	position: absolute;\r\n	z-index: 9999;\r\n	overflow: hidden;\r\n	background-color: #fff;				/* Hintergrundfarbe des Untertitel-Bereichs */\r\n}\r\n\r\n#lbImage {\r\n	position: absolute;\r\n	left: 0;\r\n	top: 0;\r\n	border: 10px solid #fff;			/* Bildrahmenfarbe um das in der Lightbox geöffnete Bild herum */\r\n	background-repeat: no-repeat;\r\n}\r\n\r\n#lbPrevLink, #lbNextLink {\r\n	display: block;\r\n	position: absolute;\r\n	top: 0;\r\n	width: 50%;\r\n	outline: none;\r\n}\r\n\r\n#lbPrevLink {\r\n	left: 0;\r\n}\r\n#lbNextLink {\r\n	right: 0;\r\n}\r\n\r\n/* Untertitel-Textdefinition */\r\n#lbBottom {\r\n	font-family: Verdana, Arial, Geneva, Helvetica, sans-serif;\r\n	font-size: 10px;\r\n	color: #666;\r\n	line-height: 1.4em;\r\n	text-align: left;\r\n	border: 10px solid #fff;\r\n	border-top-style: none;\r\n}\r\n\r\n#lbCloseLink {\r\n	display: block;\r\n	float: right;\r\n	width: 66px;\r\n	height: 22px;\r\n	margin: 5px 0;\r\n	outline: none;\r\n}\r\n\r\n#lbCaption, #lbNumber {\r\n	margin-right: 71px;\r\n}\r\n#lbCaption {\r\n	font-weight: bold;\r\n}\r\n\r\n\r\n\r\n/* Rahmen um Bilder standardmäßig entfernen */\r\nimg *,\r\nimg.noborder,\r\ntd.gallist_u_thumbnail img.noborder {\r\n	border:0;\r\n}\r\n\r\n\r\n\r\n\r\n/* Copyright-Hinweis */\r\n/* Sichtbare Hinweis darf ohne eine entsprechende Lizenz NICHT entfernt werden! */\r\n.copyright {\r\n	padding-top:15px;\r\n	font-size:11px;\r\n	text-decoration:none;\r\n	}', 0, 0),
('#modul_idname#', 0, 1, 4, 'use_lightbox', 'Lightbox für die Ausgabe aktivieren?', '', 'Lightbox auf der Detailseite|Lightbox statt Detailseite (keine Kommentare)|Keine Lightbox', '1|2|0', '', '1', '1', 0, 0),
('#modul_idname#', 0, 1, 1, 'gals_per_page', 'Anzahl Bilderalben pro Seite', '', 'text', '5', 'Alben', '20', '20', 0, 0),
('#modul_idname#', 0, 1, 0, 'gals_listtype', 'Bilderalben ausgeben...','','Klassische Darstellung (Albentitel + Beschreibung)|Kompakte Darstellung (nur Ausgabe des Titels)','1|2','','1','1',0,0),
('#modul_idname#', 0, 1, 5, 'gal_comments', 'Bilderkommentare aktivieren?', '', 'Ja|Nein', '1|0', '', '1', '1', 0, 0),
('#modul_idname#', 0, 1, 2, 'pics_per_line', 'Anzahl Thumbnails pro Zeile', '', '3|4|5|6|7|8|9|10|automatisch', '30.0%|20.0%|17.0%|15.0%|13.0%|11.0%|10.0%|9.0%|auto', '', '17.0%', 'auto', 0, 0),
('#modul_idname#', 0, 1, 3, 'thumbs_per_page', 'Anzahl Thumbnails pro Seite', '', 'text', '5', 'Thumbnails', '25', '25', 0, 0);


-- --------------------------------------------------------

-- 
-- Menüeinträge anlegen
-- 

INSERT INTO 01prefix_menue (name,link,modul,sicherheitslevel,rightname,rightvalue,sortorder,subof,hide) VALUES 
('<b>Bilderalben</b>', '_loader.php?modul=#modul_idname#&amp;loadpage=galerien', '#modul_idname#', 1, 'editgal', '1', 2, 0, 0),
('Neues Album anlegen', '_loader.php?modul=#modul_idname#&amp;action=new_gal&amp;loadpage=galerien', '#modul_idname#', 1, 'newgal', '1', 1, 0, 0),
('<b>Bilderalben</b>', '_loader.php?modul=#modul_idname#&amp;loadpage=galerien', '#modul_idname#', 1, 'editgal', '2', 2, 0, 0),
('Kommentare verwalten', 'comments.php?modul=#modul_idname#', '#modul_idname#', 1, 'editcomments', '1', 3, 0, 0);



-- --------------------------------------------------------

-- 
-- Benutzerrechte und Rechte-Kategorien anlegen
-- 

INSERT INTO 01prefix_rights (modul,is_cat,catid,sortid,idname,name,exp,formename,formwerte,input_exp,standardwert,nodelete,hide,in_profile) VALUES
('#modul_idname#', 1, 1, 1, '01gallery_userrights', 'Benutzerrechte', NULL, '', '', NULL, NULL, 0, 0, 0),
('#modul_idname#', 0, 1, 2, 'editgal', 'Bildergalerien bearbeiten', '', 'Alle Galerien bearbeiten|Nur eigene Galerien bearbeiten', '2|1', '', '1', 0, 0, 0),
('#modul_idname#', 0, 1, 1, 'newgal', 'Neue Galerie anlegen', '', 'Ja|Nein', '1|0', '', '1', 0, 0, 0),
('#modul_idname#', 0, 1, 3, 'uploadpics', 'Kann Bilder hochladen?', '', 'Darf keine Bilder hochladen|Nur in eigene Galerien hochladen|Kann in alle Galerien hochladen', '0|1|2', '', '1', 0, 0, 0);


ALTER TABLE `01prefix_user` ADD `#modul_idname#_editgal` tinyint( 1 ) NOT NULL DEFAULT '1';
ALTER TABLE `01prefix_user` ADD `#modul_idname#_newgal` tinyint( 1 ) NOT NULL DEFAULT '1';
ALTER TABLE `01prefix_user` ADD `#modul_idname#_uploadpics` tinyint( 1 ) NOT NULL DEFAULT '1';

-- 
-- Dem Benutzer, der das Modul installiert hat die entsprechenden Rechte zuweisen
-- 

UPDATE `01prefix_user` SET `#modul_idname#_editgal` = '2' WHERE `01prefix_user`.`id` = #UID_ADMIN_AKT# LIMIT 1;
UPDATE `01prefix_user` SET `#modul_idname#_uploadpics` = '2' WHERE `01prefix_user`.`id` = #UID_ADMIN_AKT# LIMIT 1;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `01modulprefix_galerien`
-- 

CREATE TABLE IF NOT EXISTS `01modulprefix_galerien` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `subof` int(10) DEFAULT NULL,
  `sortid` int(10) NOT NULL DEFAULT '0',
  `timestamp` int(15) NOT NULL DEFAULT '0',
  `password` varchar(32) DEFAULT NULL,
  `galeriename` varchar(255) NOT NULL DEFAULT '0',
  `beschreibung` text DEFAULT NULL,
  `galpic` int(10) DEFAULT NULL,
  `anzahl_pics` int(5) NOT NULL DEFAULT '0',
  `uid` int(10) NOT NULL DEFAULT '0',
  `comments` tinyint(1) NOT NULL DEFAULT '0',
  `hide` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `01modulprefix_pictures`
-- 

CREATE TABLE IF NOT EXISTS `01modulprefix_pictures` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `galid` int(10) NOT NULL DEFAULT '0',
  `sortorder` int(5) NOT NULL DEFAULT '0',
  `timestamp` int(15) NOT NULL DEFAULT '0',
  `orgname` varchar(100) NOT NULL DEFAULT '0',
  `filename` varchar(25) NOT NULL DEFAULT '0',
  `title` varchar(100) DEFAULT NULL,
  `text` text DEFAULT NULL,
  `uid` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

COMMIT;