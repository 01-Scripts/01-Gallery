<?PHP
/* 
	01-Gallery V2 - Copyright 2003-2010 by Michael Lorer - 01-Scripts.de
	Lizenz: Creative-Commons: Namensnennung-Keine kommerzielle Nutzung-Weitergabe unter gleichen Bedingungen 3.0 Deutschland
	Weitere Lizenzinformationen unter: http://www.01-scripts.de/lizenz.php
	
	Modul:		01gallery
	Dateiinfo: 	Upload und Import von Bildern in Galerien
	#fv.2002#
*/

if($userdata['uploadpics'] >= 1){


// Bilder importieren
if(isset($_GET['action']) && $_GET['action'] == "import" && isset($_GET['galid']) && !empty($_GET['galid']) && is_numeric($_GET['galid']) &&
   isset($_GET['send']) && !empty($_GET['send'])){
	$list = mysql_query("SELECT password,uid FROM ".$mysql_tables['gallery']." WHERE id = '".mysql_real_escape_string($_GET['galid'])."' LIMIT 1");
	$statrow = mysql_fetch_assoc($list);
	$dir = _01gallery_getGalDir($_GET['galid'],$statrow['password']);

	// Zugriffsberechtigung?
	if($userdata['uploadpics'] == 2 || $userdata['uploadpics'] == 1 && $statrow['uid'] == $userdata['id']){
		
		echo "<h2>Bilder importieren</h2>";
		
		// Vorhandene Bildnamen in Array einlesen
		$pictures = array();
		$x = 0;
		$list = mysql_query("SELECT sortorder,filename FROM ".$mysql_tables['pics']." WHERE galid = '".mysql_real_escape_string($_GET['galid'])."' ORDER BY sortorder DESC");
		while($row = mysql_fetch_assoc($list)){
			if($x == 0) $new_sortid = ($row['sortorder']+1);
			$pictures[]	= stripslashes($row['filename']);
			$split = split('[.]', stripslashes($row['filename']));
			$pictures[]	= stripslashes($split[0]."_tb.".$split[1]);
			$pictures[]	= stripslashes($split[0]."_acptb.".$split[1]);
			$x = 1;
			}
		
		// Verzeichnisinhalt auflisten und alle Dateien, die nicht in $pictures enthalen sind umbennnen und aufnehmen
		$verz = opendir($modulpath.$galdir._01gallery_getGalDir($_GET['galid'],$statrow['password']));

		$cup = 0;
		$imported = "";
		$not_imported = "";
		$imported_files = array();
		while($file = readdir($verz)){
			if($file != "." && $file != ".."){
				// Überprüfen ob Bild schon in DB ist und ob die Dateiendung zulässig ist
				if(!in_array($file,$pictures) && in_array(getEndung($file),$supported_pictypes)){
				// Endlosschleife, die durch die Erzeugung von Thumbnails entstand, beheben (Thumbnails nicht importieren)
					@clearstatcache();
					@chmod($modulpath.$galdir.$dir."/".$file, 0777);
					
					$newname = _01gallery_makeFilename($file,$cup).".".getEndung($file);
					if(rename($modulpath.$galdir.$dir."/".$file,$modulpath.$galdir.$dir."/".$newname)){
						$imported .= $file."<br />\n";
						$imported_files[] = $newname;
						
						// ggf. eigenen Bildtitel berücksichtigen
						if(isset($_GET['bildtitle']) && $_GET['bildtitle'] == "own" && isset($_GET['owntitle']) && !empty($_GET['owntitle']))
							$title = mysql_real_escape_string($_GET['owntitle']);
						else
							$title = mysql_real_escape_string($file);
						
						//Eintragung in Datenbank vornehmen:
                        $sql_insert = "INSERT INTO ".$mysql_tables['pics']." (galid,sortorder,timestamp,orgname,filename,title,text,uid) VALUES (
							'".mysql_real_escape_string($_GET['galid'])."',
							'".$new_sortid."',
							'".time()."',
							'".mysql_real_escape_string($file)."',
							'".mysql_real_escape_string($newname)."',
							'".$title."', 
							'', 
							'".$userdata['id']."'
							)";
						mysql_query($sql_insert) OR die(mysql_error());
						$cup++;
						$new_sortid++;
						
						_01gallery_makeThumbs($modulpath.$galdir.$dir."/",$newname,true,"_tb",$settings['thumbwidth']);
						_01gallery_makeThumbs($modulpath.$galdir.$dir."/",$newname,false,"_acptb",ACP_GAL_TB_WIDTH,"dyn");
						
						// Soeben umbenannte und generierte Bilder in den Array einfügen, damit die Bilder nicht auch gleich importiert werden
						$pictures[]	= stripslashes($newname);
						$split = split('[.]', stripslashes($newname));
						$pictures[]	= stripslashes($split[0]."_tb.".$split[1]);
						$pictures[]	= stripslashes($split[0]."_acptb.".$split[1]);
						}
					else{
						echo "<p class=\"meldung_error\"><b>Fehler:</b><br />
							Bilddatei ".$file." konnte zum importieren nicht umbenannt werden.<br />
							Bitte stellen Sie sicher, dass die Datei die Chmod-Rechte 0777 besitzt.</p>";
						break;
						}
					}
				elseif(!in_array($file,$pictures))
					$not_imported .= $file."<br />\n";
				}
				
			// Nach $import_max_count reload der Seite verlangen
			if($cup == $import_max_count){
				echo "<p class=\"meldung_erfolg\"><b>".$cup." Bilder wurden erfolgreich importiert</b>.<br />
					Es werden <b>automatisch</b> alle weiteren Bilder importiert. Wenn nicht, klicken Sie <a href=\"javascript:location.reload();\">hier</a>.</p>";
				echo "<script type=\"text/javascript\">location.reload();</script>";
				echo "<p class=\"meldung_hinweis\">Folgende Bilder wurden importiert:<br />
					".$imported."</p>";
				
				break;
				}
			}
			
		if($cup > 0 && $cup < $import_max_count || $cup == 0){
			echo "<p class=\"meldung_erfolg\"><b>Alle Bilder wurden in die gew&auml;hlte Galerie importiert!</b><br />
            <br />
            <a href=\"_loader.php?modul=".$modul."&amp;loadpage=showpics&amp;action=show_pics&amp;galid=".$_GET['galid']."\">Bilder verwalten &raquo;</a>
            </p>";
			_01gallery_countPics($_GET['galid']);
			
			// Importierte Bilder sortieren
			mysql_query("SET @pos=0");
			mysql_query("UPDATE ".$mysql_tables['pics']." SET sortorder= ( SELECT @pos := @pos +1 ) WHERE galid = '".mysql_real_escape_string($_GET['galid'])."' ORDER BY orgname DESC");
			}
				
		if(!empty($not_imported))
			echo "<p class=\"meldung_error\"><b>Folgende Dateien konnte nicht importiert werden:</b><br />
			".$not_imported."
			Dies kann unter anderem an nicht unterstützen Dateiendungen oder fehlenden Chmod-Rechten (0777) liegen.</p>";
	
		}
		else
			$flag_loginerror = true;
	}


// Neue Bilder hochladen / importieren (Formular)
elseif(isset($_GET['action']) && $_GET['action'] == "upload_pic" && isset($_GET['galid']) && !empty($_GET['galid']) && is_numeric($_GET['galid'])){
	$list = mysql_query("SELECT password,galeriename,uid FROM ".$mysql_tables['gallery']." WHERE id = '".mysql_real_escape_string($_GET['galid'])."' LIMIT 1");
	$row = mysql_fetch_assoc($list);
	$dir = _01gallery_getGalDir($_GET['galid'],$row['password']);

	// Zugriffsberechtigung?
	if($userdata['uploadpics'] == 2 || $userdata['uploadpics'] == 1 && $row['uid'] == $userdata['id']){
	
	// Bilder hochladen (classic Uploader)
	if(isset($_POST['send']) && !empty($_POST['send'])){
		$count_erfolg = 0;
		$count_error  = 0;
		for($x=0;$x<$max_uploads;$x++){
			if(isset($_FILES['file_'.$x]['name']) && !empty($_FILES['file_'.$x]['name'])){
				$upload_info = _01gallery_upload_2Gallery($_GET['galid'],"file_".$x,$_POST['title_'.$x],$_POST['beschreibung_'.$x]);
				
				if($upload_info['status']){
					$count_erfolg++;
					$uploaded_pics[] = $upload_info['filename'];
					}
				else{
					$count_error++;
					$sammel_errors[] = $upload_info;
					}
				}
			}
		_01gallery_countPics($_GET['galid']);
		
		// Bilder sortieren
		mysql_query("SET @pos=0");
		mysql_query("UPDATE ".$mysql_tables['pics']." SET sortorder= ( SELECT @pos := @pos +1 ) WHERE galid = '".mysql_real_escape_string($_GET['galid'])."' ORDER BY orgname DESC");
		}
?>
<h1><?PHP echo stripslashes($row['galeriename']); ?> &raquo; Bilder hochladen</h1>

<?PHP echo  _01gallery_echoActionButtons_Gal(); ?>

<h2><input type="radio" name="type" value="upload" onclick="hide_always('import1');hide_always('import2');show_always('upload1');" checked="checked" /> Bilder per Web-Upload hochladen</h2>

<?PHP
// Fehler- und Erfolgsmeldungen vom Upload ausgeben
if(isset($count_error) && $count_error > 0){
	echo "<p class=\"meldung_error\"><b>Beim Upload von ".($count_erfolg+$count_error)." Bildern traten ".$count_error." Fehlermeldungen auf:</b><br />";
	foreach($sammel_errors as $error){
		echo "&bull; ".$error['message']." <i>(Bild: ".$error['orgname'].")</i><br />";
		}
	echo "</p>";
	}

if(isset($count_erfolg) && $count_erfolg > 0){
	echo "<p class=\"meldung_erfolg\"><b>Es wurden ".$count_erfolg." Bilder erfolgreich in die Galerie
		<i>".stripslashes($row['galeriename'])."</i> hochgeladen!</b><br />
		Sie k&ouml;nnen jetzt weitere Bilder hochladen.<br />
        <br />
        <a href=\"_loader.php?modul=".$modul."&amp;loadpage=showpics&amp;action=show_pics&amp;galid=".$_GET['galid']."\">Bilder verwalten &raquo;</a>
        </p>";

	echo "<ul class=\"cssgallery\">";
	foreach($uploaded_pics as $pic){
		$img = false;

		// Thumbnails generieren:
		_01gallery_makeThumbs($modulpath.$galdir.$dir."/",$pic,true,"_tb",$settings['thumbwidth']);
		$img = _01gallery_makeThumbs($modulpath.$galdir.$dir."/",$pic,true,"_acptb",ACP_GAL_TB_WIDTH,"dyn");		// ACP-Thumbnail
		if($img && !empty($img) && file_exists($img))
			echo "<li><a href=\"".$modulpath.$galdir.$dir."/".$pic."\" class=\"lightbox\"><img src=\"".$img."\" alt=\"hochgeladenes Bild\" /></a></li>\n";
		elseif(getEndung($pic) == "gif" && isset($pic) && file_exists($modulpath.$galdir.$dir."/".$pic)){
			echo "<li><a href=\"".$modulpath.$galdir.$dir."/".$pic."\" class=\"lightbox\"><img src=\"".$modulpath.$galdir.$dir."/".$pic."\" width=\"".ACP_GAL_TB_WIDTH."\" alt=\"hochgeladenes Bild\" /></a></li>\n";
			}
		}
	echo "</ul>\n<br />";
	}

// Thumbnails der hochgeladenen Bilder ausgeben
?>

<div id="upload1" style="display:block;">

<p class="meldung_hinweis">
	<b>Bitte nutzen Sie nachfolgendes Upload-Formular</b> und w&auml;hlen Sie nach 
	einem Klick auf '<b>Bilder ausw&auml;hlen</b>' eine oder mehrere Bilddateien aus!<br />
	<br />
	Mit einem Klick auf '<b>Bilder jetzt hochladen</b>' werden alle Bilder in der Warteschlange
	automatisch nacheinander hochgeladen.<br />
	Die Statusbalken geben Ihnen Auskunft &uuml;ber den Fortschritt.
</p>

<?php if(!strchr($_SERVER['HTTP_USER_AGENT'],"MSIE 6.0")){ ?>
<form action="_ajaxloader.php?SID=<?php echo htmlspecialchars(session_id()); ?>&amp;modul=<?php echo $modul; ?>&amp;ajaxaction=fancyupload&amp;galid=<?PHP echo $_GET['galid']; ?>" method="post" enctype="multipart/form-data" id="fancy-form">

<div id="fancy-status" class="hide">
<p>
<a href="#" id="fancy-browse">Bilder ausw&auml;hlen</a> |
<a href="#" id="fancy-clear">Warteschlange l&ouml;schen</a> |
<a href="#" id="fancy-upload">Bilder jetzt hochladen</a>
</p>
<div>
<strong class="overall-title"></strong><br />
<img src="images/fancy/bar.gif" class="progress overall-progress" />
</div>
<div>
<strong class="current-title"></strong><br />
<img src="images/fancy/bar.gif" class="progress current-progress" />
</div>
<div class="current-text"></div>
</div>

<ul id="fancy-list"></ul>

</form>
<?php } ?>

<!-- Fallback-Lösung! -->
<div id="fancy-fallback">
<p class="meldung_hinweis">
	Bitte beachten Sie:<br />
	<b>Bei dem nachfolgenden Upload-Formular handelt es sich lediglich um eine Notl&ouml;sung</b>,
	die f&uuml;r den Bildupload genutzt werden kann, wenn der Flash-Uploader oberhalb dieser Meldung
	auf Ihrem PC nicht genutzt werden kann.<br />
	Bitte nutzen Sie wenn m&ouml;glich den Flash-Uploader und aktivieren Sie daf&uuml;r JavaScript und
	installieren Sie einen geeigneten Flash-Player f&uuml;r Ihren Browser.
</p>

<form action="<?PHP echo $filename; ?>&amp;action=upload_pic&amp;galid=<?PHP echo $_GET['galid']; ?>" enctype="multipart/form-data" method="post">
<table border="0" align="center" width="100%" cellpadding="3" cellspacing="5" class="rundrahmen">

	<tr>
		<td class="tra" valign="top"><b>Bild ausw&auml;hlen:</b></td>
		<td class="tra"><b>Bildtitel</b><br /><span class="small">Leer lassen um Dateinamen als Titel zu &uuml;bernehmen</span></td>
		<td class="tra" valign="top"><b>Bildbeschreibung:</b></td>
	</tr>

<?PHP
// $max_uploads Dateifelder ausgeben
$count = 0;
for($x=0;$x<$max_uploads;$x++){
	if($count == 1){ $class = "tra"; $count--; }else{ $class = "trb"; $count++; }

	echo "    <tr>
		<td class=\"".$class."\"><input type=\"file\" name=\"file_".$x."\" /></td>
		<td class=\"".$class."\"><input type=\"text\" name=\"title_".$x."\" size=\"25\" maxlength=\"100\" /></td>
		<td class=\"".$class."\"><textarea name=\"beschreibung_".$x."\" rows=\"2\" cols=\"45\" class=\"input_textarea\" style=\"font-size:9px;\"></textarea></td>
	</tr>\n";


	}
if($count == 1){ $class = "tra"; $count--; }else{ $class = "trb"; $count++; }
?>

	<tr>
		<td class="<?PHP echo $class; ?>" colspan="2">Unterst&uuml;tzte Dateiendungen: <b><?PHP echo implode(",",$supported_pictypes); ?></b></td>
		<td class="<?PHP echo $class; ?>" align="right"><input type="submit" name="send" value="Bilder jetzt hochladen &raquo" onclick="hide_unhide('loading');" class="input" /></td>
	</tr>

</table>
</form>
</div>

</div>

<div id="loading" style="display:none; text-align:center;">
	<img src="images/icons/loading.gif" alt="Lade-Animation" title="Dateien werden zum Server &uuml;bertragen - bitte warten..." /><br />
	<span class="small">Bilder werden &uuml;betragen...</span>
</div>

<h2><input type="radio" name="type" value="import" onclick="show_always('import1');show_always('import2');hide_always('upload1');" /> Bilder per FTP hochladen und importieren</h2>

<form action="<?PHP echo $filename; ?>" method="get">
<table border="0" align="center" width="100%" cellpadding="3" cellspacing="5" class="rundrahmen" id="import1" style="display:none;">

	<tr>
		<td class="tra" colspan="3"><b class="bigger">1.</b> Laden Sie die gew&uuml;nschten Bilddateien (Thumbnails werden automatisch generiert!) 
			per FTP-Programm in folgendes Verzeichnis hoch:</td>
	</tr>
	
	<tr>
		<td class="tra"></td>
		<td class="tra" colspan="2"><b>Verzeichnis:</b> <?PHP echo "01scripts/01module/".$modul."/galerien/<b>"._01gallery_getGalDir($_GET['galid'],$row['password'])."</b>/"; ?></td>
	</tr>
	
	<tr>
		<td class="tra" colspan="3"><b class="bigger">2.</b></td>
	</tr>
	
	<tr>
		<td class="trb" align="center" width="25"><input type="radio" name="bildtitle" value="auto" checked="checked" /></td>
		<td class="trb"><b>Jeweils Dateiname als Bildtitel verwenden</b></td>
		<td class="trb">&nbsp;</td>
	</tr>
	
	<tr>
		<td class="tra" align="center"><input type="radio" name="bildtitle" value="own" /></td>
		<td class="tra"><b>Eigenen Bildtitel für alle verwenden:</b></td>
		<td class="tra"><input type="text" name="owntitle" size="25" maxlength="100" /> <span class="small">(sp&auml;ter &auml;ndern)</span></td>
	</tr>
	
	<tr>
		<td class="tra" colspan="3"><b class="bigger">3.</b> Klicken Sie auf <i>"Bilder importieren &raquo;"</i> um den Import zu starten.</td>
	</tr>
	
	<tr>
		<td class="trb" align="right" colspan="3"><input type="submit" name="send" value="Bilder importieren &raquo" class="input" /></td>
	</tr>
	
</table>
<input type="hidden" name="action" value="import" />
<input type="hidden" name="galid" value="<?PHP echo $_GET['galid']; ?>" />
<input type="hidden" name="loadpage" value="upload" />
<input type="hidden" name="modul" value="<?PHP echo $modul; ?>" />
</form>

<p class="meldung_hinweis" id="import2" style="display:none;"><b>Hinweis:</b><br />
	&bull; Die hochgeladenen Bilder ben&ouml;tigen die <b>Chmod-Rechte 0777</b>!<br />
	&bull; Es werden nur Bilder importiert, die noch nicht im Album enthalten sind.<br />
	&bull; <b>Starten Sie den Import-Vorgang erst, wenn der FTP-Upload komplett abgeschlossen ist!</b>
</p>

<?PHP
		}
	else
		$flag_loginerror = true;
	}











}
else $flag_loginerror = true;

// 01-Gallery V2 Copyright 2006-2010 by Michael Lorer - 01-Scripts.de
?>