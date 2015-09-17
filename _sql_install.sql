-- 01-Gallery - Copyright 2003-2015 by Michael Lorer - 01-Scripts.de
-- Lizenz: Creative-Commons: Namensnennung-Keine kommerzielle Nutzung-Weitergabe unter gleichen Bedingungen 3.0 Deutschland
-- Weitere Lizenzinformationen unter: http://www.01-scripts.de/lizenz.php

-- Modul:		01gallery
-- Dateiinfo:	SQL-Befehle für die Erstinstallation der 01-Gallery
-- #fv.212#
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
('#modul_idname#', 0, 2, 5, 'tb_size','Maximale Thumbnail-Gr&ouml;&szlig;e','Breite und H&ouml;he in Pixeln ohne Einheit angeben.','text','10','Pixel (Breite x H&ouml;he)','150x100','150x100',0,0),
('#modul_idname#', 1, 3, 3, 'csssettings', 'CSS-Einstellungen', '', '', '', '', '', '', 0, 0),
('#modul_idname#', 0, 3, 1, 'extern_css', 'Externe CSS-Datei', 'Geben Sie einen absoluten Pfad inkl. <b>http://</b> zu einer externen CSS-Datei an.\nIst dieses Feld leer, wird die Datei templates/style.css aus dem Modulverzeichnis verwendet.', 'text', '50', '', '', '', 0, 0),
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
  `subof` int(10) NOT NULL DEFAULT '0',
  `sortid` int(10) NOT NULL DEFAULT '0',
  `galtimestamp` int(15) NOT NULL DEFAULT '0',
  `galpassword` varchar(40) NULL DEFAULT NULL,
  `galeriename` varchar(255) DEFAULT NULL,
  `beschreibung` text NULL DEFAULT NULL,
  `galpic` int(10) NULL DEFAULT NULL,
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
  `pictimestamp` int(15) NOT NULL DEFAULT '0',
  `orgname` varchar(100) NOT NULL DEFAULT '0',
  `filename` varchar(25) NOT NULL DEFAULT '0',
  `title` varchar(100) NULL DEFAULT NULL,
  `pictext` text NULL DEFAULT NULL,
  `uid` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

COMMIT;