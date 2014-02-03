<?PHP
/*
	01-Gallery - Copyright 2003-2014 by Michael Lorer - 01-Scripts.de
	Lizenz: Creative-Commons: Namensnennung-Keine kommerzielle Nutzung-Weitergabe unter gleichen Bedingungen 3.0 Deutschland
	Weitere Lizenzinformationen unter: http://www.01-scripts.de/lizenz.php

	Modul:		01gallery
	Dateiinfo: 	Modulspezifische Popup-Fenster
	#fv.211#
*/

// Alle existierenden Thumbnails neu generieren (Abfrage)
if(isset($_REQUEST['action']) && $_REQUEST['action'] == "recreate_thumbnails"){
?>
<h1>Thumbnails neu generieren</i></h1>

<p>Thumbnails mit folgenden Einstellungen neu generieren:<br />
<b>Art der Thumbnails:</b>
    <?php if($settings['thumbnail_type'] == "dyn")
        echo "Dynamische Gr&ouml;&szlig;e (Seitenverh&auml;ltnis beibehalten)";
    else
        echo "Feste Gr&ouml;&szlig;e (Bilder werden zugeschnitten)"; ?>
    <br /><b>Thumbnail-Größe:</b> <?php echo $settings['tb_size']; ?> Pixel
</p>
<p class="meldung_hinweis">Abh&auml;ngig von der Anzahl an Bilderalben kann der folgende Prozess einige Zeit in Anspruch nehmen.</p>
<p><b>Klicken Sie auf Start um nun alle existierenden Thumbnails neu zu generieren:</b></p>

<p style="text-align: center;"><a href="popups.php?modul=<?php echo $modul; ?>&amp;action=do_recreate_thumbnails&amp;limit=0&amp;sublimit=0">Vorgang starten</a></p>

<?PHP
	}
// Alle existierenden Thumbnails neu generieren (tun)
if(isset($_GET['action']) && $_GET['action'] == "do_recreate_thumbnails" && isset($_GET['limit']) && is_numeric($_GET['limit']) && isset($_GET['sublimit']) && is_numeric($_GET['sublimit'])){
    $result = $mysqli->query("SELECT id FROM ".$mysql_tables['gallery']."");
    $num_gals = $result->num_rows;
    
    $query = "SELECT id,galpassword FROM ".$mysql_tables['gallery']." ORDER BY id LIMIT ".$mysqli->escape_string($_GET['limit']).",".$mysqli->escape_string($_GET['limit']+1)."";
    $list = $mysqli->query($query);
	$gal = $list->fetch_assoc();
	
	if(isset($gal['id'])){
        $_GET['limit']++;
        echo "<p style=\"text-align: center;\">Galerie <b>".$_GET['limit']."</b> von ".$num_gals."...<br />Bitte warten...</p>";
        $dir = _01gallery_getGalDir($gal['id'],$gal['galpassword']);
	   
        $query = "SELECT filename FROM ".$mysql_tables['pics']." WHERE galid = '".$gal['id']."' ORDER BY id LIMIT ".$mysqli->escape_string($_GET['sublimit']).",".$mysqli->escape_string($_GET['sublimit']+$gen_thumbs_max)."";
		$list = $mysqli->query($query);
        $num_pics = $list->num_rows;
        while($row = $list->fetch_assoc()){
			_01gallery_makeThumbs($modulpath.$galdir.$dir."/",$row['filename'],TRUE);
            }
            
        if($num_pics == $gen_thumbs_max){
            $_GET['sublimit'] = $_GET['sublimit']+$gen_thumbs_max;
            $_GET['limit']--;
            }
        else $_GET['sublimit'] = 0;
	   
        echo "<p style=\"text-align: center;\"><a href=\"popups.php?modul=".$modul."&amp;action=do_recreate_thumbnails&amp;limit=".$_GET['limit']."&amp;sublimit=".$_GET['sublimit']."\" class=\"small\">Automatische Weiterleitung funktioniert nicht? Weiter...</a></p>";
        echo "<script type=\"text/javascript\">redirect(\"popups.php?modul=".$modul."&action=do_recreate_thumbnails&limit=".$_GET['limit']."&sublimit=".$_GET['sublimit']."\");</script>";
        }
    else
        echo "<p class=\"meldung_erfolg\"><b>Vorgang beendet</b></p><p style=\"text-align: center;\"><a href=\"javascript:window.close();\">Fenster schlie&szlig;en</a></p>";
    
    }
?>