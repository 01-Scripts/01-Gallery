<?PHP
// 2.1.0 --> 2.1.1
if(isset($_REQUEST['update']) && $_REQUEST['update'] == "210_zu_211"){

	// Update CSS-Code in settings
	$list = $mysqli->query("SELECT id,wert FROM ".$mysql_tables['settings']." WHERE modul = '01gallery' AND idname = 'csscode'");
	while($row = $list->fetch_assoc()){
		$wert = str_replace(".cssgallery{",".cssgallery{\r\n    margin-top: 10px !important;",stripslashes($wert));

		$mysqli->query("UPDATE ".$mysql_tables['settings']." SET `wert` = '".$mysqli->escape_string($wert)."' WHERE `id` = '".$row['id']."' LIMIT 1");
	}

	// Spaltenname 'password' umbenennen:
	$mysqli->query("ALTER TABLE ".$mysql_tables['gallery']." CHANGE `password` `galpassword` VARCHAR( 40 ) NULL DEFAULT NULL");
	// Spaltenname 'timestamp' umbenennen:
	$mysqli->query("ALTER TABLE ".$mysql_tables['gallery']." CHANGE `timestamp` `galtimestamp` INT( 15 ) NULL DEFAULT NULL");
	$mysqli->query("ALTER TABLE ".$mysql_tables['pics']." CHANGE `timestamp` `pictimestamp` INT( 15 ) NULL DEFAULT NULL");
	// Spaltenname 'text' umbenennen:
	$mysqli->query("ALTER TABLE ".$mysql_tables['pics']." CHANGE `text` `pictext` TEXT NULL DEFAULT NULL");

	// Versionsnummer aktualisieren
	$mysqli->query("UPDATE ".$mysql_tables['module']." SET version = '2.1.1' WHERE idname = '".$mysqli->escape_string($modul)."' LIMIT 1");
?>
<h2>Update Version 2.1.0 nach 2.1.1</h2>

<p class="meldung_erfolg">
	Das Update von Version 2.1.0 auf Version 2.1.1 wurde erfolgreich durchgef&uuml;hrt.
</p>

<p class="meldung_hinweis">
	<b>Achtung: &Uuml;berarbeitung von CSS-Eigenschaften:</b><br />
	Mit diesem Update wurde eine &Auml;nderungen am CSS-Code vorgenommen.
	Sollten Sie den CSS-Code in eine externe .css-Datei ausgelagert haben, m&uuml;ssen Sie folgende
	<b>Folgende CSS-Klasse bitte manuell aktualisieren</b>:<br />
	<br />
<code>
.cssgallery{<br />
	margin:0; padding:0;					/* NICHT VERÄNDERN!!! */<br />
    margin-top:10px;<br />
    overflow:hidden; 						/* NICHT VERÄNDERN!!! - Clears the floats */<br />
	width:100%; 							/* NICHT VERÄNDERN!!! - IE and older Opera fix for clearing, they need a dimension */<br />
	list-style:none;						/* NICHT VERÄNDERN!!! */<br />
}<br />
</code><br /><br />
</p>

<div class="meldung_erfolg">
	<b>Mit dem Update wurde unter anderem folgendes verbessert:</b>
	<ul>
		<li>Sch&ouml;nere Thumbnail-Auflistung mit schmaleren Abst&auml;nden</li>
		<li>Direkte Verwendung von Bildern aus der Galerie innerhalb des <a href="http://www.01-scripts.de/01article.php" target="_blank">01-Artikelsystems</a> möglich.</li>
		<li>Diverse weitere Bugfixes. Siehe <a href="http://www.01-scripts.de/down/01gallery_changelog.txt" target="_blank">changelog.txt</a></li>
	</ul>
	<p><a href="module.php">Zur&uuml;ck zur Modul-&Uuml;bersicht &raquo;</a></p>
</div>
<?PHP
}
if(isset($_REQUEST['update']) && $_REQUEST['update'] == "2001_zu_210"){
	// Neue Settings hinzufügen
	$sql_insert = "INSERT INTO ".$mysql_tables['settings']." (modul,is_cat,catid,sortid,idname,name,exp,formename,formwerte,input_exp,standardwert,wert,nodelete,hide) VALUES
	('".$mysqli->escape_string($modul)."', 0, 1, 9, 'tb_size', 'Maximale Thumbnail-Gr&ouml;&szlig;e', 'Breite und H&ouml;he in Pixeln ohne Einheit angeben.', 'text', '10', 'Pixel (Breite x H&ouml;he)', 'x', '100x75', 0, 0),
	('".$mysqli->escape_string($modul)."', 0, 2, 4, 'thumbnail_type','Thumbnails','<a href=\"javascript:modulpopup(''".$mysqli->escape_string($modul)."'',''recreate_thumbnails'','''','''','''',510,450);\">Thumbnails neu generieren</a>','Dynamische Gr&ouml;&szlig;e (Seitenverh&auml;ltnis beibehalten)|Feste Gr&ouml;&szlig;e (Bilder werden zugeschnitten)','dyn|fix','','fix','dyn',0,0);";
	$mysqli->query($sql_insert) OR die($mysqli->error);
	
	// Kategorie ID 2++
	$mysqli->query("UPDATE ".$mysql_tables['settings']." SET `catid` = '3' WHERE `catid` = '2' AND `modul` = '".$mysqli->escape_string($modul)."';");
	$mysqli->query("UPDATE ".$mysql_tables['settings']." SET `sortid` = '3' WHERE `idname` = 'csssettings' AND `modul` = '".$mysqli->escape_string($modul)."' AND `is_cat` = '1';");
	
	// Zusätzliche Einstellungs-Kategorie hinzufügen
	$sql_insert = "INSERT INTO ".$mysql_tables['settings']." (modul,is_cat,catid,sortid,idname,name,exp,formename,formwerte,input_exp,standardwert,wert,nodelete,hide) VALUES
	('".$mysqli->escape_string($modul)."', 1, 2, 2, 'bildeinstellungen', 'Bildeinstellungen', '', '', '', '', '', '', 1, 0),
	('".$mysqli->escape_string($modul)."', 0, 2, 1, 'resize_pics_on_upload', 'Bilder beim Hochladen verkleinern?', 'Eine Version mit der vollen Bildaufl&ouml;sung bleibt erhalten.', 'Ja|Nein' ,'1|0', '', '0', '0', 0, 0),
	('".$mysqli->escape_string($modul)."', 0, 2, 2, 'resize_maxpicsize', 'Maximale Bildaufl&ouml;sung', 'Bilder mit h&ouml;herer Aufl&ouml;sung werden ggf. verkleinert.', 'text', '10', 'Pixel (Breite x H&ouml;he)', '1024x768', '1024x768', 0, 0),
	('".$mysqli->escape_string($modul)."', 0, 1, 0, 'gals_listtype', 'Bilderalben ausgeben...', '', 'Klassische Darstellung (Albentitel + Beschreibung)|Kompakte Darstellung (nur Ausgabe des Titels)', '1|2', '', '1', '1', 0, 0);";
	$mysqli->query($sql_insert) OR die($mysqli->error);
	
	// Einträge neuer Kategorie zuweisen
	$mysqli->query("UPDATE ".$mysql_tables['settings']." SET `catid` = '2' WHERE (`idname` = 'galpic_size' OR `idname` = 'thumbnail_type' OR `idname` = 'thumbwidth' OR `idname` = 'tb_size') AND `modul` = '".$mysqli->escape_string($modul)."';");
	
	// Reihenfolge ändern
	$mysqli->query("UPDATE ".$mysql_tables['settings']." SET `sortid` = '3', `name` = 'Maximale Dateigr&ouml;&szlig;e' WHERE `idname` = 'galpic_size' AND `modul` = '".$mysqli->escape_string($modul)."' LIMIT 1;");
	$mysqli->query("UPDATE ".$mysql_tables['settings']." SET `sortid` = '5' WHERE `idname` = 'tb_size' AND `modul` = '".$mysqli->escape_string($modul)."' LIMIT 1;");
	
	$mysqli->query("DELETE FROM ".$mysql_tables['settings']." WHERE `idname` = 'thumbwidth' AND `modul` = '".$mysqli->escape_string($modul)."' LIMIT 1;");
	
	// Update CSS-Code in settings
	$list = $mysqli->query("SELECT id,wert FROM ".$mysql_tables['settings']." WHERE modul = '".$mysqli->escape_string($modul)."' AND idname = 'csscode'");
	while($row = $list->fetch_assoc()){
		$wert = str_replace("width: 800px;","width: 100%;",stripslashes($row['wert']));
		$wert = str_replace("div.picstream {","div.picstream {\r\n    clear: both;\r\n    float: left;\r\n    width: 100%;\r\n    overflow: hidden;\r\n}\r\ndiv.picstream ul.cssgallery {\r\n    width: auto;\r\n    float: left;\r\n    position: relative;\r\n    left: 50%;\r\n    margin: 0 auto;\r\n    padding: 0;\r\n    list-style-type: none;\r\n    overflow: visible;\r\n}\r\ndiv.picstream ul.cssgallery li.stream {\r\n    float: left;\r\n    position: relative;\r\n    margin:0;\r\n    right: 50%;",stripslashes($wert));
	
	    $mysqli->query("UPDATE ".$mysql_tables['settings']." SET `wert` = '".$mysqli->escape_string($wert)."' WHERE `id` = '".$row['id']."' LIMIT 1");
		}
	
	$mysqli->query("UPDATE ".$mysql_tables['module']." SET version = '2.1.0' WHERE idname = '".$mysqli->escape_string($modul)."' LIMIT 1");
?>
<h2>Update Version 2.0.0.1 nach 2.1.0</h2>

<p class="meldung_erfolg">
	Das Update von Version 2.0.0.1 auf Version 2.1.0 wurde erfolgreich durchgef&uuml;hrt.
</p>

<p class="meldung_hinweis">
	<b>Achtung: &Uuml;berarbeitung von CSS-Eigenschaften:</b><br />
	Mit diesem Update wurden einige &Auml;nderungen an den Standard-CSS-Definitionen vorgenommen.
	Sollten Sie den CSS-Code in eine externe .css-Datei ausgelagert haben, m&uuml;ssen Sie folgende
	<b>CSS-Klassen manuell bearbeiten und zwei neue Klassen hinzuf&uuml;gen</b>:<br />
	<br />
<code>
div.picstream {<br />
    clear: both;<br />
    float: left;<br />
    width: 100%;<br />
    overflow: hidden;<br />
}<br />
<br />
div.picstream ul.cssgallery {<br />
    width: auto;<br />
    float: left;<br />
    position: relative;<br />
    left: 50%;<br />
    margin: 0 auto;<br />
    padding: 0;<br />
    list-style-type: none;<br />
    overflow: visible;<br />
}<br />
<br />
div.picstream ul.cssgallery li.stream {<br />
    float: left;<br />
    position: relative;<br />
    margin:0;<br />
    right: 50%;<br />
}</code><br /><br />
	Der f&uuml;r die Version 2.1.0 aktuelle CSS-Code kann
	<a href="https://gist.github.com/3129112/1d0e6b8809d0fd643ce88e2c80a09982983b74b9" target="_blank">hier eingesehen werden</a>.
</p>

<div class="meldung_erfolg">
	<b>Unter anderem sind mit dem Update folgende neue Features hinzugekommen:</b>
	<ul>
		<li>Automatische Bildverkleinerung auf eine festgelegte Gr&ouml;&szlig;e beim Upload<br />
			Die original Bilddateien bleiben vorhanden und k&ouml;nnen heruntergeladen werden</li>
		<li>Generierung von Thumbnails mit fester Kantengr&ouml;&szlig;e</li>
		<li>Verbesserte Lightbox-Ansicht</li>
		<li>Ausgabe von Thumbnails aus untergeordneten Alben</li>
		<li>Schnelleres L&ouml;schen von mehreren Bildern per Checkbox-Auswahl</li>
		<li>&Uuml;bernahme von Titel &amp; Beschreibung gleichzeitig f&uuml;r mehrere Bilder</li>
	</ul>
	<p><a href="module.php">Zur&uuml;ck zur Modul-&Uuml;bersicht &raquo;</a></p>
</div>
<?PHP
	}
elseif(isset($_REQUEST['update']) && $_REQUEST['update'] == "2000_zu_2001"){

$mysqli->query("UPDATE ".$mysql_tables['module']." SET version = '2.0.0.1' WHERE idname = '".$mysqli->escape_string($modul)."' LIMIT 1");
?>
<h2>Update Version 2.0.0.0 nach 2.0.0.1</h2>

<p class="meldung_erfolg">
	Das Update von Version 2.0.0.0 auf Version 2.0.0.1 wurde erfolgreich durchgef&uuml;hrt.<br />
	<br />
	<a href="module.php">Zur&uuml;ck zur Modul-&Uuml;bersicht &raquo;</a>
</p>
<?PHP
	}
?>