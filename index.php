<?PHP
/* 
	01-Gallery V2 - Copyright 2003-2009 by Michael Lorer - 01-Scripts.de
	Lizenz: Creative-Commons: Namensnennung-Keine kommerzielle Nutzung-Weitergabe unter gleichen Bedingungen 3.0 Deutschland
	Weitere Lizenzinformationen unter: http://www.01-scripts.de/lizenz.php
	
	Modul:		01gallery
	Dateiinfo: 	Bildergalerie ACP-Startseite = Galerien-�bersicht & Galerien anlegen
	#fv.2000#
*/
?>
<div class="acp_startbox">
<p align="center"><b class="yellow"><?PHP echo $module[$modul]['instname']; ?></b></p>

<div class="acp_innerbox">
	<h4>Informationen</h4>

	<p>
	<b>Modul-Version:</b> <?PHP echo $module[$modul]['version']; ?><br /><br />
	
	<b>Bilderalben:</b> <?PHP list($pmenge) = mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM ".$mysql_tables['gallery']."")); echo $pmenge; ?><br />
	<b>Bilder:</b> <?PHP list($pmenge) = mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM ".$mysql_tables['pics']."")); echo $pmenge; ?><br />
	<br />
	<?PHP if($settings['comments']){ ?><b>Kommentare:</b> <?PHP list($commentsmenge) = mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM ".$mysql_tables['comments']." WHERE frei = '1' AND modul = '".$modul."'")); echo $commentsmenge; ?><br /><?PHP } ?>
	<?PHP if($settings['comments'] && $userdata['editcomments'] == 1){ ?><a href="comments.php?modul=<?PHP echo $modul; ?>">&raquo; <?PHP list($commentsmenge) = mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM ".$mysql_tables['comments']." WHERE frei = '0' AND modul = '".$modul."'")); echo $commentsmenge; ?> Kommentare freischalten</a><?PHP } ?>
	</p>
</div>

<div class="acp_innerbox" style="overflow: hidden;">
	<h4>Die 4 neueste Bilder</h4>

	<?PHP
	echo "<ul class=\"cssgallerystream\">\n";
    $list = mysql_query("SELECT id,galid,filename FROM ".$mysql_tables['pics']." ORDER BY timestamp DESC LIMIT 4");
	while($row = mysql_fetch_assoc($list)){
		$listgal = mysql_query("SELECT password FROM ".$mysql_tables['gallery']." WHERE id = '".mysql_real_escape_string($row['galid'])."' LIMIT 1");
		$statrow = mysql_fetch_assoc($listgal);
		$dir = _01gallery_getGalDir($row['galid'],$statrow['password']);
	
        echo "<li><a href=\"".$modulpath.$galdir.$dir."/".stripslashes($row['filename'])."\" class=\"lightbox\">"._01gallery_getThumb($modulpath.$galdir.$dir."/",stripslashes($row['filename']),"_acptb")."</a></li>\n";
        }
    echo "</ul>";
	?>
</div>

<br />

</div>

<?PHP
// 01-Gallery V2 Copyright 2006-2009 by Michael Lorer - 01-Scripts.de
?>