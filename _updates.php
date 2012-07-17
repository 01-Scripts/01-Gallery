<?PHP
if(isset($_REQUEST['update']) && $_REQUEST['update'] == "2001_zu_210"){
	// Neue Settings hinzufügen
	$sql_insert = "INSERT INTO ".$mysql_tables['settings']." (modul,is_cat,catid,sortid,idname,name,exp,formename,formwerte,input_exp,standardwert,wert,nodelete,hide) VALUES
	('".mysql_real_escape_string($modul)."', 0, 1, 9, 'tb_size', 'Maximale Thumbnail-Gr&ouml;&szlig;e', 'Breite und H&ouml;he in Pixeln ohne Einheit angeben.', 'text', '10', 'Pixel (Breite x H&ouml;he)', 'x', '100x75', 0, 0),
	('".mysql_real_escape_string($modul)."', 0, 2, 4, 'thumbnail_type','Thumbnails','<a href=\"javascript:modulpopup(''".mysql_real_escape_string($modul)."'',''recreate_thumbnails'','''','''','''',510,450);\">Thumbnails neu generieren</a>','Dynamische Gr&ouml;&szlig;e (Seitenverh&auml;ltnis beibehalten)|Feste Gr&ouml;&szlig;e (Bilder werden zugeschnitten)','dyn|fix','','fix','dyn',0,0);";
	mysql_query($sql_insert) OR die(mysql_error());
	
	// Kategorie ID 2++
	mysql_query("UPDATE ".$mysql_tables['settings']." SET `catid` = '3' WHERE `catid` = '2' AND `modul` = '".mysql_real_escape_string($modul)."';");
	mysql_query("UPDATE ".$mysql_tables['settings']." SET `sortid` = '3' WHERE `idname` = 'csssettings' AND `modul` = '".mysql_real_escape_string($modul)."' AND `is_cat` = '1';");
	
	// Zusätzliche Einstellungs-Kategorie hinzufügen
	$sql_insert = "INSERT INTO ".$mysql_tables['settings']." (modul,is_cat,catid,sortid,idname,name,exp,formename,formwerte,input_exp,standardwert,wert,nodelete,hide) VALUES
	('".mysql_real_escape_string($modul)."', 1, 2, 2, 'bildeinstellungen', 'Bildeinstellungen', '', '', '', '', '', '', 1, 0),
	('".mysql_real_escape_string($modul)."', 0, 2, 1, 'resize_pics_on_upload', 'Bilder beim Hochladen verkleinern?', 'Eine Version mit der vollen Bildaufl&ouml;sung bleibt erhalten.', 'Ja|Nein' ,'1|0', '', '0', '0', 0, 0),
	('".mysql_real_escape_string($modul)."', 0, 2, 2, 'resize_maxpicsize', 'Maximale Bildaufl&ouml;sung', 'Bilder mit h&ouml;herer Aufl&ouml;sung werden ggf. verkleinert.', 'text', '10', 'Pixel (Breite x H&ouml;he)', '1024x768', '1024x768', 0, 0),
	('".mysql_real_escape_string($modul)."', 0, 1, 0, 'gals_listtype', 'Bilderalben ausgeben...', '', 'Klassische Darstellung (Albentitel + Beschreibung)|Kompakte Darstellung (nur Ausgabe des Titels)', '1|2', '', '1', '1', 0, 0);";
	mysql_query($sql_insert) OR die(mysql_error());
	
	// Einträge neuer Kategorie zuweisen
	mysql_query("UPDATE ".$mysql_tables['settings']." SET `catid` = '2' WHERE (`idname` = 'galpic_size' OR `idname` = 'thumbnail_type' OR `idname` = 'thumbwidth' OR `idname` = 'tb_size') AND `modul` = '".mysql_real_escape_string($modul)."';");
	
	// Reihenfolge ändern
	mysql_query("UPDATE ".$mysql_tables['settings']." SET `sortid` = '3', `name` = 'Maximale Dateigr&ouml;&szlig;e' WHERE `idname` = 'galpic_size' AND `modul` = '".mysql_real_escape_string($modul)."' LIMIT 1;");
	mysql_query("UPDATE ".$mysql_tables['settings']." SET `sortid` = '5' WHERE `idname` = 'tb_size' AND `modul` = '".mysql_real_escape_string($modul)."' LIMIT 1;");
	
	mysql_query("DELETE FROM ".$mysql_tables['settings']." WHERE `idname` = 'thumbwidth' AND `modul` = '".mysql_real_escape_string($modul)."' LIMIT 1;");
	
	// Update CSS-Code in settings
	$list = mysql_query("SELECT id,wert FROM ".$mysql_tables['settings']." WHERE modul = '".mysql_real_escape_string($modul)."' AND idname = 'csscode'");
	while($row = mysql_fetch_array($list)){
		$wert = str_replace("width: 800px;","width: 100%;",stripslashes($row['wert']));
		$wert = str_replace("div.picstream {","div.picstream {\r\n    clear: both;\r\n    float: left;\r\n    width: 100%;\r\n    overflow: hidden;\r\n}\r\ndiv.picstream ul.cssgallery {\r\n    width: auto;\r\n    float: left;\r\n    position: relative;\r\n    left: 50%;\r\n    margin: 0 auto;\r\n    padding: 0;\r\n    list-style-type: none;\r\n    overflow: visible;\r\n}\r\ndiv.picstream ul.cssgallery li.stream {\r\n    float: left;\r\n    position: relative;\r\n    margin:0;\r\n    right: 50%;",stripslashes($wert));
	
	    mysql_query("UPDATE ".$mysql_tables['settings']." SET `wert` = '".mysql_real_escape_string($wert)."' WHERE `id` = '".$row['id']."' LIMIT 1");
		}
	
	mysql_query("UPDATE ".$mysql_tables['module']." SET version = '2.1.0' WHERE idname = '".mysql_real_escape_string($modul)."' LIMIT 1");
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

mysql_query("UPDATE ".$mysql_tables['module']." SET version = '2.0.0.1' WHERE idname = '".mysql_real_escape_string($modul)."' LIMIT 1");
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