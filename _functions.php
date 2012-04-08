<?PHP
/* 
	01-Artikelsystem V3 - Copyright 2006-2012 by Michael Lorer - 01-Scripts.de
	Lizenz: Creative-Commons: Namensnennung-Keine kommerzielle Nutzung-Weitergabe unter gleichen Bedingungen 3.0 Deutschland
	Weitere Lizenzinformationen unter: http://www.01-scripts.de/lizenz.php
	
	Modul:		01gallery
	Dateiinfo: 	Modulspezifische Funktionen
	#fv.210#
*/

/* SYNTAKTISCHER AUFBAU VON FUNKTIONSNAMEN BEACHTEN!!!
	_ModulName_beliebigerFunktionsname()
	Beispiel: 
	if(!function_exists("_example_TolleFunktion")){
		_example_TolleFunktion($parameter){ ... }
		}
*/

// Globale Funktionen - nötig!

// Funktion wird zentral aufgerufen, wenn ein Benutzer gelöscht wird.
/*$userid			UserID des gelöschten Benutzers
  $username			Username des gelöschten Benutzers
  $mail				E-Mail-Adresse des gelöschten Benutzers

RETURN: TRUE/FALSE
*/
if(!function_exists("_01gallery_DeleteUser")){
function _01gallery_DeleteUser($userid,$username,$mail){
global $mysql_tables;

mysql_query("UPDATE ".$mysql_tables['gallery']." SET uid='0' WHERE uid='".mysql_real_escape_string($userid)."'");
mysql_query("UPDATE ".$mysql_tables['pics']." SET uid='0' WHERE uid='".mysql_real_escape_string($userid)."'");

return TRUE;
}
}

// Funktion wird zentral aufgerufen, wenn das Modul gelöscht werden soll
/*
RETURN: TRUE
*/
if(!function_exists("_01gallery_DeleteModul")){
function _01gallery_DeleteModul(){
global $mysql_tables,$modul;

$modul = mysql_real_escape_string($modul);

// MySQL-Tabellen löschen
mysql_query("DROP TABLE `".$mysql_tables['gallery']."`");
mysql_query("DROP TABLE `".$mysql_tables['pics']."`");

// Rechte entfernen
mysql_query("ALTER TABLE `".$mysql_tables['user']."` DROP `".$modul."_editgal`");
mysql_query("ALTER TABLE `".$mysql_tables['user']."` DROP `".$modul."_newgal`");
mysql_query("ALTER TABLE `".$mysql_tables['user']."` DROP `".$modul."_uploadpics`");


return TRUE;
}
}









// String der Galerie, dem der übergebene IdentifizierungsID zugeordnet ist
/*$postid			Beitrags-ID

RETURN: String mit dem entsprechenden Text
*/
if(!function_exists("_01gallery_getCommentParentTitle")){
function _01gallery_getCommentParentTitle($postid){
global $mysql_tables;

$list = mysql_query("SELECT galeriename FROM ".$mysql_tables['gallery']." WHERE id='".mysql_real_escape_string($postid)."' LIMIT 1");
while($row = mysql_fetch_array($list)){
	return htmlentities(stripslashes($row['galeriename']));
	}
}
}








// String des Bildes, dem der übergebene IdentifizierungsID zugeordnet ist
/*$postid			Beitrags-ID

RETURN: String mit dem entsprechenden Text
*/
if(!function_exists("_01gallery_getCommentChildTitle")){
function _01gallery_getCommentChildTitle($postid){
global $mysql_tables;

$list = mysql_query("SELECT orgname FROM ".$mysql_tables['pics']." WHERE id='".mysql_real_escape_string($postid)."' LIMIT 1");
while($row = mysql_fetch_array($list)){
	return stripslashes($row['orgname']);
	}
}
}







// Userstatistiken holen
/*$userid			UserID, zu der die Infos geholt werden sollen

RETURN: Array(
			statcat[x] 		=> "Statistikbezeichnung für Frontend-Ausgabe"
			statvalue[x] 	=> "Auszugebender Wert"
			)
  */
if(!function_exists("_01gallery_getUserstats")){
function _01gallery_getUserstats($userid){
global $mysql_tables,$modul,$module;

if(isset($userid) && is_integer(intval($userid))){
	$galmenge = 0;
	list($galmenge) = mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM ".$mysql_tables['gallery']." WHERE hide = '0' AND uid = '".mysql_real_escape_string($userid)."'"));
	$picmenge = 0;
	list($picmenge) = mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM ".$mysql_tables['pics']." WHERE uid = '".mysql_real_escape_string($userid)."'"));

	$ustats[] = array("statcat"	=> "Angelegte Bilderalben (".$module[$modul]['instname']."):",
					  "statvalue"	=> $galmenge);
	$ustats[] = array("statcat"	=> "Hochgeladene Galeriebilder (".$module[$modul]['instname']."):",
					  "statvalue"	=> $picmenge);
	return $ustats;
	}
else
	return false;
}
}








// Zugriffsberechtigung eines Benutzers auf eine Galerie überprüfen
/*$galid			ID der Galerie

RETURN: true/false
  */
if(!function_exists("_01gallery_checkUserright")){
function _01gallery_checkUserright($galid){
global $mysql_tables,$userdata;

$list = mysql_query("SELECT uid FROM ".$mysql_tables['gallery']." WHERE id = '".mysql_real_escape_string($galid)."'");
while($row = mysql_fetch_array($list)){
	$gal_uid = $row['uid'];
	}

if($userdata['editgal'] == 2 || $userdata['editgal'] == 1 && $gal_uid == $userdata['id'])
	return true;
else return false;

}
}








// Vorhandene Galerien für Drop-Down auflisten
/*$selected			Vorausgewählter Wert

RETURN: <option>-Fields
  */
if(!function_exists("_01gallery_getGalleryDropDown")){
function _01gallery_getGalleryDropDown($selected=0){
global $mysql_tables;

$list = mysql_query("SELECT uid FROM ".$mysql_tables['gallery']." WHERE id = '".mysql_real_escape_string($galid)."'");
while($row = mysql_fetch_array($list)){
	$gal_uid = $row['uid'];
	}



}
}











// Rekursiv alle Galerien auflisten
/* $parentid			GalID der Parent-Galerie
   $deep				Aktuelle Tiefe
   $maxdeep				Maximale Tiefe (int)
   $callfunction		Name der Funktion, die zur sichtbaren Ausgabe von Daten aufgerufen werden soll
						An die Funktion wird $row als 1. Parameter übergeben
   $givedeeperparam		Weiterer Parameter, der als 3. Parameter an die in $callfunction angegebene Funktion weitergereicht wird
   $excludebranch		Übergebene ID und alle Subgalerien der ID werden nicht aufgelistet

RETURN: true
  */
if(!function_exists("_01gallery_getGallerysRek")){
function _01gallery_getGallerysRek($parentid,$deep=0,$maxdeep=-1,$callfunction="",$givedeeperparam="",$excludebranch=""){
global $mysql_tables;

$return_ids = "";

// Abbruch, falls $deep = 0 erreicht wurde
if($maxdeep == 0) return true;

if($excludebranch != "" && is_numeric($excludebranch) && $excludebranch > 0)
	$exclude = " AND id != '".mysql_real_escape_string($excludebranch)."' AND subof != '".mysql_real_escape_string($excludebranch)."'";
else
	$exclude = "";

$list = mysql_query("SELECT * FROM ".$mysql_tables['gallery']." WHERE subof = '".mysql_real_escape_string($parentid)."'".$exclude." ORDER BY sortid DESC");
while($row = mysql_fetch_assoc($list)){
	if(!empty($callfunction) && function_exists($callfunction) && $callfunction != "echoSubIDs") call_user_func($callfunction,$row,$deep,$givedeeperparam);

    // Rekursion
    if($callfunction == "echoSubIDs")
        $return_ids .= _01gallery_getGallerysRek($row['id'],($deep+1),($maxdeep-1),$callfunction,$givedeeperparam,$excludebranch)."|".$row['id'];
    else
        _01gallery_getGallerysRek($row['id'],($deep+1),($maxdeep-1),$callfunction,$givedeeperparam,$excludebranch);
	}
	
if($callfunction == "echoSubIDs" && !empty($return_ids))
    return $return_ids."|";
elseif($callfunction == "echoSubIDs")
    return "";
else
    return true;

}
}









// Ausgabe der Galeriedaten (Aufruf über Rekursive Funktion) für SELECT-Felder
/*$row				Array mit allen MySQL-Feldern aus der Galerie-Tabelle zur entsprechenden Galerie-ID
  $deep				Aktuelle "Tiefe"
  $selected			Vorselektierter Wert

RETURN: <option>-Fields
  */
if(!function_exists("_01gallery_echoGalinfo_select")){
function _01gallery_echoGalinfo_select($row,$deep,$selected=""){

$return = "";
$tab = "";
for($x=0;$x<($deep*2);$x++){
	$tab .= "-";
	}

if($row['id'] == $selected) $sel = " selected=\"selected\"";
else $sel ="";

$return .= "<option value=\"".$row['id']."\"".$sel.">".$tab.htmlentities(stripslashes($row['galeriename']))."</option>\n";

echo $return;

}
}









// Liefert den Verzeichnisnamen zu einer übergebenen GalerieID zurück
/*$galid			Galerie-ID für die das Verzeichnis zurückgeliefert werden soll
  $passw			Ggf. das Passwort der Galerie (es erfolgt keine Überprüfung ob das Passwort korrekt ist)

RETURN: Verzeichnisname der gewünschten Galerie
  */
if(!function_exists("_01gallery_getGalDir")){
function _01gallery_getGalDir($galid,$passw=""){
global $instnr;

if(empty($galid) || !is_numeric($galid)) return false;

$galdir = "gal_".$galid;
if(!empty($passw)){
	$galdir .= "_".substr(md5($instnr.$galid.$passw),0,10);
	}

return $galdir;
}
}









// Funktion erstellt einen neuen, eindeutigen Dateinamen
/*$filename			Dateiname der hochgeladenen, original-Datei. Die ersten 10 Zeichen werden davon übernommen
  $param			$param fließt in die md5()-Funktion ein

RETURN: 15-stelliger Unique-Dateiname ohen Dateiendung
  */
if(!function_exists("_01gallery_makeFilename")){
function _01gallery_makeFilename($filename="",$param=""){
global $instnr,$oldfilename_length;

if(!empty($filename)){
	$split = explode('.',$filename,2);
	$filename = $split[0];

	$array_entfernen= array('ä','Ä','ö','Ö','ü','Ü','ß','?','\'','`','´','.','#','\\','/','&',' ','\"','§','\$','=','(',')');

	foreach($array_entfernen as $delete){
		$filename = str_replace($delete, "", $filename);
		}
	$filename = substr($filename,0,$oldfilename_length)."_";
	$filename = strtolower($filename);
	}

mt_srand((double)microtime()*1000000); 

$filename .= substr(md5($instnr.time().microtime().$param.mt_rand(10000,99999999)),0,10);

return $filename;
}
}









// Aktualisiert die Bildanzahl in Bildergalerien
/*$galid			GalerieID

RETURN: Anzahl an Bildern bzw. 0 bei Fehler
  */
if(!function_exists("_01gallery_countPics")){
function _01gallery_countPics($galid){
global $mysql_tables;

if(isset($galid) && !empty($galid) && is_numeric($galid)){
	list($picmenge) = mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM ".$mysql_tables['pics']." WHERE galid = '".mysql_real_escape_string($galid)."'")); 
	mysql_query("UPDATE ".$mysql_tables['gallery']." SET 
							anzahl_pics		= '".mysql_real_escape_string($picmenge)."'
							WHERE id = '".mysql_real_escape_string($galid)."' LIMIT 1");

	return $picmenge;
	}
else return 0;

}
}









// Bilder in eine Galerie hochladen
/*$galid			GalerieID
  $filefieldname	Name des Formularfelds, über das das Bild hochgeladen wird
  $title			ggf. Bildtitel, der in der DB gespeichert werden soll
  $beschreibung		ggf. Beschreibungstext, der in der DB gespeichert werden soll

RETURN: Array("status","message","filename","orgname","size","imgid");
  */
if(!function_exists("_01gallery_upload_2Gallery")){
function _01gallery_upload_2Gallery($galid,$filefieldname,$title="",$beschreibung=""){
global $mysql_tables,$_FILES,$settings,$supported_pictypes,$galdir,$modulpath,$userdata;

$return = array("status"	=> false,
				"message"	=> "Beim Hochladen der Datei trat ein unvorhergesehener Fehler auf.",
				"filename"	=> "",
				"orgname"	=> "",
				"size"		=> 0,
				"imgid"		=> 0);

if(isset($galid) && !empty($galid) && is_numeric($galid)){
	if(isset($_FILES[$filefieldname]['name']) && !empty($_FILES[$filefieldname]['name'])){
		$return['orgname'] = $_FILES[$filefieldname]['name'];
		
		// Endungen überprüfen
		$endung = getEndung($_FILES[$filefieldname]['name']);
		
		if(in_array($endung,$supported_pictypes)){
			// Dateigröße überprüfen
			if(($settings['galpic_size']*1000) > $_FILES[$filefieldname]['size']){
				$list = mysql_query("SELECT password,uid FROM ".$mysql_tables['gallery']." WHERE id = '".mysql_real_escape_string($galid)."' LIMIT 1");
				$statrow = mysql_fetch_assoc($list);
				$dir = _01gallery_getGalDir($galid,$statrow['password']);
				
				// Temporäre Datei in richtigen Ordner verschieben
				$newname = _01gallery_makeFilename($_FILES[$filefieldname]['name'],$filefieldname);
				$new_filename = $newname.".".$endung;
				if(move_uploaded_file($_FILES[$filefieldname]['tmp_name'],$modulpath.$galdir.$dir."/".$new_filename)){
					$return = array("status"	=> true,
									"message"	=> "Datei wurde erfolgreich in die Galerie hochgeladen.",
									"filename"	=> $new_filename,
									"orgname"	=> $_FILES[$filefieldname]['name'],
									"size"		=> $_FILES[$filefieldname]['size']);

	                //Chmod-Rechte für Dateien ggf setzen (644):
	                @clearstatcache();
	                @chmod($modulpath.$galdir.$dir."/".$new_filename, 0777);
					
	                // Sortorder des neuen Bildes ermitteln
					$list = mysql_query("SELECT sortorder FROM ".$mysql_tables['pics']." WHERE galid = '".mysql_real_escape_string($galid)."' ORDER BY sortorder DESC LIMIT 1");
					$row = mysql_fetch_assoc($list);
					$new_sortid = ($row['sortorder']+1);
					
					// ggf. eigenen Bildtitel berücksichtigen
					if(isset($title) && !empty($title))
						$title = mysql_real_escape_string($title);
					else
						$title = mysql_real_escape_string($_FILES[$filefieldname]['name']);
						
					// ggf. eine Beschreibung berücksichtigen
					if(isset($beschreibung) && !empty($beschreibung))
						$mysql_beschreibung = mysql_real_escape_string($beschreibung);
					else
						$mysql_beschreibung = "";
			
					//Eintragung in Datenbank vornehmen:
					$sql_insert = "INSERT INTO ".$mysql_tables['pics']." (galid,sortorder,timestamp,orgname,filename,title,text,uid) VALUES (
						'".mysql_real_escape_string($galid)."',
						'".$new_sortid."',
						'".time()."',
						'".mysql_real_escape_string($_FILES[$filefieldname]['name'])."',
						'".mysql_real_escape_string($new_filename)."',
						'".$title."', 
						'".$mysql_beschreibung."', 
						'".$userdata['id']."'
						)";
					mysql_query($sql_insert) OR die(mysql_error());
					$return['imgid'] = mysql_insert_id();
	                }
				else{
					$fupload['msg'] = "Ein unbekannter Fehler ist aufgetreten oder es wurde keine Datei hochgeladen.";
					}
				}
			else $return['message'] = "Fehler beim Upload: Die gew&auml;hlte Datei ist zu gro&szlig;.<br />Maximale Dateigr&ouml;&szlig;e: ".$settings['galpic_size']."KB";
			}
		else $return['message'] = "Fehler beim Upload: Die Dateiendung <i>".getEndung($_FILES[$filefieldname]['name'])."</i> wird nicht unterst&uuml;tzt.<br />Bitte nutzen Sie eine der unterst&uuml;tzten Dateiendungen: ".implode(",",$supported_pictypes)."";
		}
	}
else $return['message'] = "Fehler beim Upload: Keine Galerie gew&auml;hlt.";

return $return;
}
}









// Thumbnail generieren
/*$path					Finaler Pfad (ggf. zum Aufruf passender relativer Pfad)
  $sourcefilename		Dateiname des Bildes (mit Endung aber ohne Suffixe)
  $replace				true/false	true: vorhandenes Thumbnail wird ggf. ersetzt
  $suffix				Suffix für das generierte Thumbnail (Standard: "_tb")
  $resize				Max. Kantenlänge, auf die das Bild gerisized werden soll

RETURN: path+filename(inkl. Suffix)+Endung des generierten Thumbnails / false
  */
if(!function_exists("_01gallery_makeThumbs")){
function _01gallery_makeThumbs($path,$sourcefilename,$replace=false,$suffix="_tb",$resize="",$tb_type=""){
global $settings;

if(empty($tb_type) || ($tb_type != "fix" && $tb_type != "dyn")) $tb_type = $settings['thumbnail_type'];

$sourcefile = $path.$sourcefilename;

// Dynamische Thumbnail-Größe (Seitenverhältnis beibehalten)
// oder Feste Thumbnail-Größe (Ausschnitt bilden)
if($tb_type == "fix" && (!empty($settings['fix_tb_size']) && (strchr($settings['fix_tb_size'],"x") || strchr($settings['fix_tb_size'],"X")) || !empty($resize) && (strchr($resize,"x") || strchr($resize,"X")))){
	$rez_type = "fix";

	if(!empty($resize) && (strchr($resize,"x") || strchr($resize,"X"))) $temp_string = $resize;
	elseif(!empty($settings['fix_tb_size']) && (strchr($settings['fix_tb_size'],"x") || strchr($settings['fix_tb_size'],"X"))) $temp_string = $settings['fix_tb_size'];
	else return false;
	
	$rez_size = explode("x",strtolower($temp_string));
	$picwidth = trim($rez_size[0]);
	$picheight = trim($rez_size[1]);
	
	if(!is_numeric($picwidth) || !is_numeric($picheight))
		return false;
	}
elseif($tb_type == "dyn" && (isset($settings['thumbwidth']) && !empty($settings['thumbwidth']) && is_numeric($settings['thumbwidth']) || isset($resize) && !empty($resize) && is_numeric($resize))){
	$rez_type = "dyn";

	if((empty($resize) || !is_numeric($resize)) && !empty($settings['thumbwidth']) && is_numeric($settings['thumbwidth'])) $resize = $settings['thumbwidth'];
	}
else
	return false;
	

$split = explode('.', strtolower($sourcefilename));
$filename = $split[0];
if(isset($split[1])) $fileType = $split[1];
else $fileType = "";
if($fileType == "jpeg") $fileType = "jpg";

// Überprüfen ob Quelldatei überhaupt exisiert
if(file_exists($path.$sourcefilename)){

	if($fileType == "jpg" || $fileType == "png"){
		list($source_width, $source_height) = getimagesize($sourcefile);
		$source_height_org = $source_height;
		$source_width_org = $source_width;

		if($source_width >= $source_height) $bigside = $source_width;
		else $bigside = $source_height;

		// Resize images
		if($rez_type == "dyn"){
			if($bigside > $resize){
				$k = $bigside/$resize;
				$picwidth = $source_width/$k;
				$picheight = $source_height/$k;
				}
			else{
				$picwidth = $source_width;
				$picheight = $source_height;
				}
			$c1 = array("x"=>0, "y"=>0);
			}
		elseif($rez_type == "fix"){
			if($source_width <= $source_height){
				$k = $source_width/$picwidth;
				$source_height = $k*$picheight;
				$c1['x'] = 0;
				$c1['y'] = ($source_height_org-$source_height)/2;
				}
			else{
				$k = $source_height/$picheight;
				$source_width = $k*$picwidth;
				$c1['x'] = ($source_width_org-$source_width)/2;
				$c1['y'] = 0;
				}
			//$c1 = array("x"=>($source_width-$picwidth)/2, "y"=>($source_height-$picheight)/2);
			}
			

		$echofile_id = imagecreatetruecolor($picwidth, $picheight);

		switch($fileType){
		  case('png'):
			$sourcefile_id = imagecreatefrompng($sourcefile);
			
			imagealphablending($echofile_id, false);
			$colorTransparent = imagecolorallocatealpha($echofile_id, 0, 0, 0, 127);
			imagefill($echofile_id, 0, 0, $colorTransparent);
			imagesavealpha($echofile_id, true);
		  break;
		  default:
			$sourcefile_id = imagecreatefromjpeg($sourcefile);
		  }

		// Get the sizes of pic
		//$sourcefile_width = imageSX($sourcefile_id);
		//$sourcefile_height = imageSY($sourcefile_id);

		// Create a jpeg out of the modified picture
		imagecopyresampled($echofile_id, $sourcefile_id, 0, 0, $c1['x'], $c1['y'], $picwidth, $picheight, $source_width, $source_height);
		
		// Vorhandenes Thumbnail überschreiben oder nicht?
		if(!$replace && file_exists($path.$filename.$suffix.".".$fileType))
			return $path.$filename.$suffix.".".$fileType;
		elseif($replace && file_exists($path.$filename.$suffix.".".$fileType)){
			@clearstatcache();
			@chmod($path.$filename.$suffix.".".$fileType, 0777);
			@unlink($path.$filename.$suffix.".".$fileType);
			}		
		
		switch($fileType){
		  case('png'):
			imagepng($echofile_id,$path.$filename.$suffix.".".$fileType);
		  break;
		  default:
			imagejpeg($echofile_id,$path.$filename.$suffix.".".$fileType,75);
		  }

		@imagedestroy($sourcefile_id);
		@imagedestroy($echofile_id);

		return $path.$filename.$suffix.".".$fileType;
		}
	else // falls Bild ein gif ist
		return false;

	}
else
	return false;

}
}









// Thumbnail ausgeben und davor, wenn nötig generieren
/*$path						Pfad zum entsprechenden Galerieordner
  $sourcefilename			Dateiname (des normalen Bildes)
  $suffix					Gewünschtes Thumbnail-Suffix (Standard = "_tb")

RETURN: fertigen HTML <img>-Tag
  */
if(!function_exists("_01gallery_getThumb")){
function _01gallery_getThumb($path,$sourcefilename,$suffix="_tb",$smallstream=FALSE,$recreate=FALSE){
global $settings,$picuploaddir,$smallstreampicsize;



$split = explode('.',$sourcefilename);
$filename = $split[0];
if(isset($split[1])) $endung = $split[1];
else $endung = "";

if($smallstream){
	$w = $smallstreampicsize;
	$tb_type = $settings['thumbnail_type'];
	}
elseif($suffix == "_tb"){
	$w = $settings['thumbwidth'];
	$tb_type = $settings['thumbnail_type'];
	}
else{
	$w = ACP_GAL_TB_WIDTH;
	$tb_type = "dyn";
	}
	
if($smallstream && isset($smallstreampicsize) && $smallstreampicsize > 0) $style = " style=\"width: ".$smallstreampicsize."px;\"";
else $style = "";

if($endung == "gif" && file_exists($path.$sourcefilename)){
	$info = getimagesize($path.$sourcefilename);
	if($info[0] <= $w)
		return "<img src=\"".$path.$sourcefilename."\" alt=\"Bild-Thumbnail (gif)\" />";
	else
		return "<img src=\"".$path.$sourcefilename."\" alt=\"Bild-Thumbnail (gif)\" width=\"".$w."\" />";
	}
else{
	if($recreate && file_exists($path.$filename.$suffix.".".$endung))
	   @unlink($path.$filename.$suffix.".".$endung);    
        
    if(file_exists($path.$filename.$suffix.".".$endung))
		return "<img src=\"".$path.$filename.$suffix.".".$endung."\" alt=\"Bild-Thumbnail\" />";
	else{
		$img = _01gallery_makeThumbs($path,$sourcefilename,FALSE,$suffix,$w,$tb_type);
		if(isset($img) && !empty($img))
			return "<img src=\"".$img."\" alt=\"Bild-Thumbnail\"".$style." />";
		else
			return "<img src=\"".$picuploaddir.FILE_NO_THUMBS."\" alt=\"Keine Thumbnails vorhanden\"".$style." />";
		}
	}

}
}









// Aktualisiert die Bildanzahl in Bildergalerien
/*

RETURN: Anzahl an Bildern bzw. 0 bei Fehler
  */
if(!function_exists("_01gallery_echoActionButtons_Gal")){
function _01gallery_echoActionButtons_Gal(){
global $modul,$_REQUEST,$moduldir;

return "<p>
	<a href=\"_loader.php?modul=".$modul."&amp;loadpage=upload&amp;action=upload_pic&amp;galid=".$_REQUEST['galid']."\" class=\"actionbutton\"><img src=\"images/icons/icon_upload.gif\" alt=\"Hochladen\" title=\"Bilder hochladen\" style=\"border:0; margin-right:10px;\" />Bilder hochladen</a>
	<a href=\"_loader.php?modul=".$modul."&amp;loadpage=galerien&amp;action=edit_gal&amp;galid=".$_REQUEST['galid']."\" class=\"actionbutton\"><img src=\"".$moduldir.$modul."/images/gallery.gif\" height=\"20\" alt=\"Bearbeiten - Stift\" title=\"Bilderalbum bearbeiten\" style=\"border:0; margin-right:10px;\" />Album bearbeiten</a>
	<a href=\"_loader.php?modul=".$modul."&amp;loadpage=showpics&amp;action=show_pics&amp;galid=".$_REQUEST['galid']."\" class=\"actionbutton\"><img src=\"images/icons/icon_edit.gif\" alt=\"Bearbeiten - Stift\" title=\"Bilder bearbeiten\" style=\"border:0; margin-right:10px;\" />Bilder bearbeiten</a>
	<a href=\"_loader.php?modul=".$modul."&amp;loadpage=showpics&amp;action=sort_pics&amp;galid=".$_REQUEST['galid']."\" class=\"actionbutton\"><img src=\"images/icons/sort_desc.gif\" alt=\"Pfeil nach unten\" title=\"Bilder sortieren\" style=\"border:0;\" /><img src=\"images/icons/sort_asc.gif\" alt=\"Pfeil nach oben\" title=\"Bilder sortieren\" style=\"border:0; margin-right:10px;\" />Bilder sortieren</a>
</p>";

}
}









// Frontend: Galerien auflisten (Untereinander / Nebeneinander)
/*$fgalid			GalerieID (Standard = 0)

RETURN: true/false (Ausgabe erfolgt per echo)
  */
if(!function_exists("_01gallery_echoGalList")){
function _01gallery_echoGalList($fgalid=0){
global $filename,$salt,$settings,$tempdir,$mysql_tables,$imagepf,$galdir,$names,$sites,$pwcookie,$galid;

// Auflistung Untereinander
if($settings['gals_listtype'] == 2)
	include($tempdir."gallist2_u_top.html");
else
	include($tempdir."gallist_u_top.html");

$query = "SELECT id,timestamp,password,galeriename,beschreibung,galpic,anzahl_pics FROM ".$mysql_tables['gallery']." WHERE subof = '".mysql_real_escape_string($fgalid)."' AND hide='0' ORDER BY sortid DESC";
makepages($query,$sites,$names['galpage'],$settings['gals_per_page']);

$list = mysql_query($query);
while($gal = mysql_fetch_assoc($list)){
	$anz_subgals = 0;
	list($anz_subgals) = mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM ".$mysql_tables['gallery']." WHERE subof ='".$gal['id']."'"));
	
	if(!empty($gal['password']) && (!isset($pwcookie) || isset($pwcookie) && !in_array(pwhashing($gal['password'].$salt),$pwcookie)))
		$gal['pic'] = "<img src=\"".$imagepf."lock.png\" alt=\"Symbol: Schlo&szlig;\" title=\"Diese Galerie ist durch ein Passwort gesch&uuml;tzt\" class=\"noborder\" />";
	elseif(!empty($gal['galpic'])){
		$galpiclist = mysql_query("SELECT filename FROM ".$mysql_tables['pics']." WHERE id = '".mysql_real_escape_string($gal['galpic'])."' LIMIT 1");
		$gpic = mysql_fetch_assoc($galpiclist);
		$gal['pic'] = _01gallery_getThumb($galdir._01gallery_getGalDir($gal['id'],$gal['password'])."/",$gpic['filename'],"_tb");
		}
	else{
		$galpiclist = mysql_query("SELECT filename FROM ".$mysql_tables['pics']." WHERE galid = '".mysql_real_escape_string($gal['id'])."' ORDER BY sortorder DESC LIMIT 1");
		$gpic = mysql_fetch_assoc($galpiclist);
		$gal['pic'] = _01gallery_getThumb($galdir._01gallery_getGalDir($gal['id'],$gal['password'])."/",$gpic['filename'],"_tb");
		}


	$gal['link'] = addParameter2Link($filename,$names['galid']."=".$gal['id']);
	
	$gal['galeriename'] = htmlentities(($gal['galeriename']));
	if(!empty($gal['password']) && (!isset($pwcookie) || isset($pwcookie) && !in_array(pwhashing($gal['password'].$salt),$pwcookie))) $gal['beschreibung'] = "<p class=\"gal_password\">Zum Betrachten dieses Bilderalbums ben&ouml;tigen Sie ein Passwort.</p>";
	else $gal['beschreibung'] = stripslashes($gal['beschreibung']);
	
	if($settings['gals_listtype'] == 2)
		include($tempdir."gallist2_u_bit.html");
	else
		include($tempdir."gallist_u_bit.html");
	}

if($settings['gals_listtype'] == 2)
	include($tempdir."gallist2_u_bottom.html");
else
	include($tempdir."gallist_u_bottom.html");

echo echopages($sites,"",$names['galpage'],$names['galid']."=".$galid,"galpagestable");
}
}









// Gibt Breadcumps auf dem Frontpanel aus und überprüft gleichzeitig ob ggf. ein Passwort vorhanden ist
/*$aktgalid			Aktuelle GalerieID von der aus die Breadcurmps ausgegeben werden sollen

RETURN: $errorgalid (ID der Galerie für die als erstes das Passwort fehlt)
  */
if(!function_exists("_01gallery_echoBreadcrumps")){
function _01gallery_echoBreadcrumps($aktgalid){
global $filename,$names,$_REQUEST,$mysql_tables,$flag_breadcrumps,$text_bilderlaben,$pwcookie,$salt;

$stop = false;
$errorid = 0;

// Alle vorhandenen Galerien in einen Array einlesen
$gals = array();
$list = mysql_query("SELECT id,subof,password,galeriename FROM ".$mysql_tables['gallery']."");
while($row = mysql_fetch_assoc($list)){
	$gals[$row['id']]['id']			= $row['id'];
	$gals[$row['id']]['subof']		= $row['subof'];
	$gals[$row['id']]['name']		= htmlentities(stripslashes($row['galeriename']));
	if(!empty($row['password']))
		$gals[$row['id']]['password']	= $row['password'];
	else 
		$gals[$row['id']]['password']	= "";
	}
	
$runid = $aktgalid;
$crumps = "";
$c = 0;
while(!$stop){
	if(empty($gals[$runid]['password']) || is_array($pwcookie) && in_array(pwhashing($gals[$runid]['password'].$salt),$pwcookie)){
		if($c > 0) $crumps = " &raquo; ".$crumps;
		
		$crumps = "<a href=\"".addParameter2Link($filename,$names['galid']."=".$runid)."\">".$gals[$runid]['name']."</a>".$crumps;
		}
	else{
		$errorid = $runid;
		$crumps = "<a href=\"".addParameter2Link($filename,$names['galid']."=".$runid)."\">".$gals[$runid]['name']."</a>";
		}
	
	if($gals[$runid]['subof'] == 0)
		$stop = true;
	else
		$runid = $gals[$runid]['subof'];
		
	$c++;
	}
if($flag_breadcrumps)
	echo "<h2 class=\"breadcrumps\"><a href=\"".$filename."#\">".$text_bilderlaben."</a> &raquo; ".$crumps."</h2>";

return $errorid;	
}
}

?>