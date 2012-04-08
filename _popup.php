<?PHP
/*
	01-Gallery - Copyright 2003-2012 by Michael Lorer - 01-Scripts.de
	Lizenz: Creative-Commons: Namensnennung-Keine kommerzielle Nutzung-Weitergabe unter gleichen Bedingungen 3.0 Deutschland
	Weitere Lizenzinformationen unter: http://www.01-scripts.de/lizenz.php

	Modul:		01gallery
	Dateiinfo: 	Modulspezifische Popup-Fenster
	#fv.210#
*/

// Alle existierenden Thumbnails neu generieren (Abfrage)
if(isset($_REQUEST['action']) && $_REQUEST['action'] == "recreate_thumbnails"){
?>
<h1>Thumbnails neu generieren</i></h1>

<p>Thumbnails mit folgenden Einstellungen neu generieren:<br />
<b>Art der Thumbnails:</b>
    <?php if($settings['thumbnail_type'] == "dyn")
        echo "Dynamische Gr&ouml;&szlig;e (Seitenverh&auml;ltnis beibehalten)<br /><b>Max. Kantenlänge für dynam. Thumbnails:</b> ".$settings['thumbwidth']." Pixel";
    else
        echo "Feste Gr&ouml;&szlig;e (Bilder werden zugeschnitten)<br /><b>Thumbnail-Größe:</b> ".$settings['fix_tb_size']." Pixel (Breite x Höhe)"; ?>

</p>
<p class="meldung_hinweis">Abh&auml;ngig von der Anzahl an Bilderalben kann der folgende Prozess einige Zeit in Anspruch nehmen.</p>
<p><b>Klicken Sie auf Start um nun alle existierenden Thumbnails neu zu generieren:</b></p>

<p style="text-align: center;"><a href="popups.php?modul=<?php echo $modul; ?>&amp;action=do_recreate_thumbnails&amp;limit=0&amp;sublimit=0">Vorgang starten</a></p>

<?PHP
	}
// Alle existierenden Thumbnails neu generieren (tun)
if(isset($_GET['action']) && $_GET['action'] == "do_recreate_thumbnails" && isset($_GET['limit']) && is_numeric($_GET['limit']) && isset($_GET['sublimit']) && is_numeric($_GET['sublimit'])){
    $result = mysql_query("SELECT id FROM ".$mysql_tables['gallery']."");
    $num_gals = mysql_num_rows($result);
    
    $query = "SELECT id,password FROM ".$mysql_tables['gallery']." ORDER BY id LIMIT ".mysql_real_escape_string($_GET['limit']).",".mysql_real_escape_string($_GET['limit']+1)."";
    $list = mysql_query($query);
	$gal = mysql_fetch_array($list);
	
	if(isset($gal['id'])){
        $_GET['limit']++;
        echo "<p style=\"text-align: center;\">Galerie <b>".$_GET['limit']."</b> von ".$num_gals."...<br />Bitte warten...</p>";
        $dir = _01gallery_getGalDir($gal['id'],$gal['password']);
	   
        $query = "SELECT filename FROM ".$mysql_tables['pics']." WHERE galid = '".$gal['id']."' ORDER BY id LIMIT ".mysql_real_escape_string($_GET['sublimit']).",".mysql_real_escape_string($_GET['sublimit']+$gen_thumbs_max)."";
        $list = mysql_query($query);
        $num_pics = mysql_num_rows($list);
        while($row = mysql_fetch_assoc($list)){
            _01gallery_getThumb($modulpath.$galdir.$dir."/",$row['filename'],"_tb",FALSE,TRUE);
            }
            
        if($num_pics == $gen_thumbs_max){
            $_GET['sublimit'] = $_GET['sublimit']+$gen_thumbs_max;
            $_GET['limit']--;
            }
	   
        echo "<p style=\"text-align: center;\"><a href=\"popups.php?modul=".$modul."&amp;action=do_recreate_thumbnails&amp;limit=".$_GET['limit']."&amp;sublimit=".$_GET['sublimit']."\" class=\"small\">Automatische Weiterleitung funktioniert nicht? Weiter...</a></p>";
        echo "<script type=\"text/javascript\">redirect(\"popups.php?modul=".$modul."&action=do_recreate_thumbnails&limit=".$_GET['limit']."&sublimit=".$_GET['sublimit']."\");</script>";
        }
    else
        echo "<p class=\"meldung_erfolg\"><b>Vorgang beendet</b></p><p style=\"text-align: center;\"><a href=\"javascript:window.close();\">Fenster schlie&szlig;en</a></p>";
    
    }
?>