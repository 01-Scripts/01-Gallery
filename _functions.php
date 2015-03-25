<?PHP
/* 
	01-Artikelsystem V3 - Copyright 2006-2015 by Michael Lorer - 01-Scripts.de
	Lizenz: Creative-Commons: Namensnennung-Keine kommerzielle Nutzung-Weitergabe unter gleichen Bedingungen 3.0 Deutschland
	Weitere Lizenzinformationen unter: http://www.01-scripts.de/lizenz.php
	
	Modul:		01gallery
	Dateiinfo: 	Modulspezifische Funktionen
	#fv.212#
*/

/* SYNTAKTISCHER AUFBAU VON FUNKTIONSNAMEN BEACHTEN!!!
	_ModulName_beliebigerFunktionsname()
	Beispiel: 
	if(!function_exists("_example_TolleFunktion")){
		_example_TolleFunktion($parameter){ ... }
		}
*/

// Funktion wird zentral aufgerufen, wenn ein Benutzer gelöscht wird.
/*$userid			UserID des gelöschten Benutzers
  $username			Username des gelöschten Benutzers
  $mail				E-Mail-Adresse des gelöschten Benutzers

RETURN: TRUE/FALSE
*/
if(!function_exists("_01gallery_DeleteUser")){
function _01gallery_DeleteUser($userid,$username,$mail){
global $mysqli,$mysql_tables;

$mysqli->query("UPDATE ".$mysql_tables['gallery']." SET uid='0' WHERE uid='".$mysqli->escape_string($userid)."'");
$mysqli->query("UPDATE ".$mysql_tables['pics']." SET uid='0' WHERE uid='".$mysqli->escape_string($userid)."'");

return TRUE;
}
}

// Funktion wird zentral aufgerufen, wenn das Modul gelöscht werden soll
/*
RETURN: TRUE
*/
if(!function_exists("_01gallery_DeleteModul")){
function _01gallery_DeleteModul(){
global $mysqli,$mysql_tables,$modul;

$modul = $mysqli->escape_string($modul);

// MySQL-Tabellen löschen
$mysqli->query("DROP TABLE `".$mysql_tables['gallery']."`");
$mysqli->query("DROP TABLE `".$mysql_tables['pics']."`");

// Rechte entfernen
$mysqli->query("ALTER TABLE `".$mysql_tables['user']."` DROP `".$modul."_editgal`");
$mysqli->query("ALTER TABLE `".$mysql_tables['user']."` DROP `".$modul."_newgal`");
$mysqli->query("ALTER TABLE `".$mysql_tables['user']."` DROP `".$modul."_uploadpics`");

return TRUE;
}
}


// String der Galerie, dem der übergebene IdentifizierungsID zugeordnet ist
/*$postid			Beitrags-ID

RETURN: String mit dem entsprechenden Text
*/
if(!function_exists("_01gallery_getCommentParentTitle")){
function _01gallery_getCommentParentTitle($postid){
global $mysqli,$mysql_tables,$htmlent_flags,$htmlent_encoding_acp;

$list = $mysqli->query("SELECT galeriename FROM ".$mysql_tables['gallery']." WHERE id='".$mysqli->escape_string($postid)."' LIMIT 1");
while($row = $list->fetch_assoc()){
	return htmlentities(stripslashes($row['galeriename']),$htmlent_flags,$htmlent_encoding_acp);
	}
}
}

// String des Bildes, dem der übergebene IdentifizierungsID zugeordnet ist
/*$postid			Beitrags-ID

RETURN: String mit dem entsprechenden Text
*/
if(!function_exists("_01gallery_getCommentChildTitle")){
function _01gallery_getCommentChildTitle($postid){
global $mysqli,$mysql_tables;

$list = $mysqli->query("SELECT orgname FROM ".$mysql_tables['pics']." WHERE id='".$mysqli->escape_string($postid)."' LIMIT 1");
while($row = $list->fetch_assoc()){
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
global $mysqli,$mysql_tables,$modul,$module;

if(isset($userid) && is_integer(intval($userid))){
	$galmenge = 0;
	list($galmenge) = $mysqli->query("SELECT COUNT(*) FROM ".$mysql_tables['gallery']." WHERE hide = '0' AND uid = '".$mysqli->escape_string($userid)."'")->fetch_array(MYSQLI_NUM);
	$picmenge = 0;
	list($picmenge) = $mysqli->query("SELECT COUNT(*) FROM ".$mysql_tables['pics']." WHERE uid = '".$mysqli->escape_string($userid)."'")->fetch_array(MYSQLI_NUM);

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
global $mysqli,$mysql_tables,$userdata;

$list = $mysqli->query("SELECT uid FROM ".$mysql_tables['gallery']." WHERE id = '".$mysqli->escape_string($galid)."'");
while($row = $list->fetch_assoc()){
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
global $mysqli,$mysql_tables;

$list = $mysqli->query("SELECT uid FROM ".$mysql_tables['gallery']." WHERE id = '".$mysqli->escape_string($galid)."'");
while($row = $list->fetch_assoc()){
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
   $showhidden			Versteckte Alben auflisten? (Default: TRUE)

RETURN: true
  */
if(!function_exists("_01gallery_getGallerysRek")){
function _01gallery_getGallerysRek($parentid,$deep=0,$maxdeep=-1,$callfunction="",$givedeeperparam="",$excludebranch="",$showhidden=TRUE){
global $mysqli,$mysql_tables;

$return_ids = "";

// Abbruch, falls $deep = 0 erreicht wurde
if($maxdeep == 0) return true;

if($excludebranch != "" && is_numeric($excludebranch) && $excludebranch > 0)
	$exclude = " AND id != '".$mysqli->escape_string($excludebranch)."' AND subof != '".$mysqli->escape_string($excludebranch)."'";
else
	$exclude = "";

// Versteckte Alben auflisten?
if(!$showhidden)
	$exclude2 = " AND hide = '0'";
else
	$exclude2 = "";

$list = $mysqli->query("SELECT * FROM ".$mysql_tables['gallery']." WHERE subof = '".$mysqli->escape_string($parentid)."'".$exclude.$exclude2." ORDER BY sortid DESC");
while($row = $list->fetch_assoc()){
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
global $htmlent_flags,$htmlent_encoding_acp;

$return = "";
$tab = "";
for($x=0;$x<($deep*2);$x++){
	$tab .= "-";
	}

if($row['id'] == $selected) $sel = " selected=\"selected\"";
else $sel ="";

$return .= "<option value=\"".$row['id']."\"".$sel.">".$tab.htmlentities(stripslashes($row['galeriename']),$htmlent_flags,$htmlent_encoding_acp)."</option>\n";

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
global $mysqli,$mysql_tables;

if(isset($galid) && !empty($galid) && is_numeric($galid)){
	list($picmenge) = $mysqli->query("SELECT COUNT(*) FROM ".$mysql_tables['pics']." WHERE galid = '".$mysqli->escape_string($galid)."'")->fetch_array(MYSQLI_NUM); 
	$mysqli->query("UPDATE ".$mysql_tables['gallery']." SET 
							anzahl_pics		= '".$mysqli->escape_string($picmenge)."'
							WHERE id = '".$mysqli->escape_string($galid)."' LIMIT 1");

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

RETURN: Array("status","message","error","filename","name","size","imgid"[,"width","height"]);
  */
if(!function_exists("_01gallery_upload_2Gallery")){
function _01gallery_upload_2Gallery($galid,$filefieldname,$title="",$beschreibung=""){
global $mysqli,$mysql_tables,$_FILES,$settings,$supported_pictypes,$galdir,$modulpath,$userdata;

$return = array("status"	=> 0,
				"message"	=> "",
				"error"     => "Beim Hochladen der Datei trat ein unvorhergesehener Fehler auf.",
				"filename"	=> "",
				"name"      => "",
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
				$list = $mysqli->query("SELECT galpassword,uid FROM ".$mysql_tables['gallery']." WHERE id = '".$mysqli->escape_string($galid)."' LIMIT 1");
				$statrow = $list->fetch_assoc();
				$dir = _01gallery_getGalDir($galid,$statrow['galpassword']);
				
				// Temporäre Datei in richtigen Ordner verschieben
				$newname = _01gallery_makeFilename($_FILES[$filefieldname]['name'],$filefieldname);
				$new_filename = $newname.".".$endung;
				if(move_uploaded_file($_FILES[$filefieldname]['tmp_name'],$modulpath.$galdir.$dir."/".$new_filename)){
					$info = @getimagesize($modulpath.$galdir.$dir."/".$new_filename);
                    $return = array("status"    => 1,
									"message"   => "Datei wurde erfolgreich in die Galerie hochgeladen.",
									"filename"  => $new_filename,
									"name"      => $_FILES[$filefieldname]['name'],
									"size"		=> $_FILES[$filefieldname]['size'],
                                    "width"     => $info[0],
                                    "height"    => $info[1]);

	                //Chmod-Rechte für Dateien ggf setzen (644):
	                @clearstatcache();
	                @chmod($modulpath.$galdir.$dir."/".$new_filename, 0777);
	                
    				// Image-Resize?
                    if(_01gallery_checkResize($info)){
                        // Filename als zu Filename_big umbenennen
                        copy($modulpath.$galdir.$dir."/".$new_filename,$modulpath.$galdir.$dir."/".$newname."_big.".$endung);
    
                        _01gallery_makeThumbs($modulpath.$galdir.$dir."/",$new_filename,true,"",$settings['resize_maxpicsize'],"dyn");
                        }
    
                    _01gallery_makeThumbs($modulpath.$galdir.$dir."/",$new_filename);   // Standard-Thumbnail _tb
    				_01gallery_makeThumbs($modulpath.$galdir.$dir."/",$new_filename,false,"_acptb",ACP_GAL_TB_WIDTH,"dyn");		// ACP-Thumbnail
					
	                // Sortorder des neuen Bildes ermitteln
					$list = $mysqli->query("SELECT sortorder FROM ".$mysql_tables['pics']." WHERE galid = '".$mysqli->escape_string($galid)."' ORDER BY sortorder DESC LIMIT 1");
					$row = $list->fetch_assoc();
					$new_sortid = ($row['sortorder']+1);
					
					// ggf. eigenen Bildtitel berücksichtigen
					if(isset($title) && !empty($title))
						$title = $mysqli->escape_string($title);
					else
						$title = $mysqli->escape_string($_FILES[$filefieldname]['name']);
						
					// ggf. eine Beschreibung berücksichtigen
					if(isset($beschreibung) && !empty($beschreibung))
						$mysql_beschreibung = $mysqli->escape_string($beschreibung);
					else
						$mysql_beschreibung = "";
			
					//Eintragung in Datenbank vornehmen:
					$sql_insert = "INSERT INTO ".$mysql_tables['pics']." (galid,sortorder,pictimestamp,orgname,filename,title,pictext,uid) VALUES (
						'".$mysqli->escape_string($galid)."',
						'".$new_sortid."',
						'".time()."',
						'".$mysqli->escape_string($_FILES[$filefieldname]['name'])."',
						'".$mysqli->escape_string($new_filename)."',
						'".$title."', 
						'".$mysql_beschreibung."', 
						'".$userdata['id']."'
						)";
					$mysqli->query($sql_insert) OR die($mysqli->error);
					$return['imgid'] = $mysqli->insert_id;
	                }
				else{
					$return['error'] = "Ein unbekannter Fehler ist aufgetreten oder es wurde keine Datei hochgeladen.";
					}
				}
			else $return['error'] = "Fehler beim Upload: Die gew&auml;hlte Datei ist zu gro&szlig;.<br />Maximale Dateigr&ouml;&szlig;e: ".$settings['galpic_size']."KB";
			}
		else $return['error'] = "Fehler beim Upload: Die Dateiendung <i>".getEndung($_FILES[$filefieldname]['name'])."</i> wird nicht unterst&uuml;tzt.<br />Bitte nutzen Sie eine der unterst&uuml;tzten Dateiendungen: ".implode(",",$supported_pictypes)."";
		}
	}
else $return['error'] = "Fehler beim Upload: Keine Galerie gew&auml;hlt.";

return $return;
}
}


// Thumbnail generieren
/*$path					Finaler Pfad (ggf. zum Aufruf passender relativer Pfad)
  $sourcefilename		Dateiname des Bildes (mit Endung aber ohne Suffixe)
  $replace				true/false	true: vorhandenes Thumbnail wird ggf. ersetzt
  $suffix				Suffix für das generierte Thumbnail (Standard: "_tb")
  $resize				Größenangabe (B x H), auf die das Bild verkleinert werden soll (Standard: $settings['tb_size'])
  $rez_type             Resize-Art dyn|fix (dynamisch oder fest) (Standard: $settings['thumbnail_type'])

RETURN: path+filename(inkl. Suffix)+Endung des generierten Thumbnails / false
  */
if(!function_exists("_01gallery_makeThumbs")){
function _01gallery_makeThumbs($path,$sourcefilename,$replace=false,$suffix="_tb",$resize="",$rez_type=""){
global $settings;

if(empty($rez_type) || ($rez_type != "fix" && $rez_type != "dyn")) $rez_type = $settings['thumbnail_type'];

$sourcefile = $path.$sourcefilename;

if(empty($resize))
    $rez_size = _01gallery_ParseWxH($settings['tb_size']);
elseif(stripos($resize,"x"))
    $rez_size = _01gallery_ParseWxH($resize); 
elseif(is_numeric($resize)){
    $rez_size[0] = $rez_size[1] = $rez_size['width'] = $rez_size['height'] = $resize;
    }

if(!$rez_size) return false;	

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
		$source_width_org  = $source_width;
		$c1 = array("x"=>0, "y"=>0);

		// Resize images
        switch($rez_type){
          case "dyn":
			if($source_width > $rez_size['width'] && ($source_width/$rez_size['width']) >= ($source_height/$rez_size['height']))
                $k = $source_width/$rez_size['width'];
			elseif($source_height > $rez_size['height'] && ($source_width/$rez_size['width']) < ($source_height/$rez_size['height']))
                $k = $source_height/$rez_size['height'];
			else
		        $k = 1;
			
			$rez_size['width']  = $source_width/$k;
			$rez_size['height'] = $source_height/$k;
          break;
          case "fix":
            if(($source_width/$rez_size['width']) <= ($source_height/$rez_size['height'])){
				$k = $source_width/$rez_size['width'];
				$source_height = $k*$rez_size['height'];
				$c1['y'] = ($source_height_org-$source_height)/2;
				}
			else{
				$k = $source_height/$rez_size['height'];
				$source_width = $k*$rez_size['width'];
				$c1['x'] = ($source_width_org-$source_width)/2;
				}
          break;
        }

		$echofile_id = imagecreatetruecolor($rez_size['width'], $rez_size['height']);

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

		// Create a jpeg out of the modified picture
		imagecopyresampled($echofile_id, $sourcefile_id, 0, 0, $c1['x'], $c1['y'], $rez_size['width'], $rez_size['height'], $source_width, $source_height);
		
		// Vorhandenes Thumbnail überschreiben oder nicht?
		if(!$replace && file_exists($path.$filename.$suffix.".".$fileType))
			return $path.$filename.$suffix.".".$fileType;
		elseif($replace && file_exists($path.$filename.$suffix.".".$fileType)){
			clearstatcache();
			chmod($path.$filename.$suffix.".".$fileType, 0777);
			unlink($path.$filename.$suffix.".".$fileType);
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
  $smallstream              Thumbnails für die Ansicht im Smallstream generieren?

RETURN: fertigen HTML <img>-Tag
  */
if(!function_exists("_01gallery_getThumb")){
function _01gallery_getThumb($path,$sourcefilename,$suffix="_tb",$smallstream=FALSE,$alt="Bild-Thumbnail"){
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
	$w = $settings['tb_size'];
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
	if(file_exists($path.$filename.$suffix.".".$endung))
		return "<img src=\"".$path.$filename.$suffix.".".$endung."\" alt=\"".$alt."\" />";
	else{
		$img = _01gallery_makeThumbs($path,$sourcefilename,false,$suffix,$w,$tb_type);
		if(isset($img) && !empty($img))
			return "<img src=\"".$img."\" alt=\"".$alt."\"".$style." />";
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
global $mysqli,$filename,$salt,$settings,$tempdir,$mysql_tables,$imagepf,$galdir,$names,$sites,$pwcookie,$galid,$picuploaddir,$htmlent_flags,$htmlent_encoding_acp;

// Auflistung Untereinander
if($settings['gals_listtype'] == 2)
	include($tempdir."gallist2_u_top.html");
else
	include($tempdir."gallist_u_top.html");

$query = "SELECT id,galtimestamp,galpassword,galeriename,beschreibung,galpic,anzahl_pics FROM ".$mysql_tables['gallery']." WHERE subof = '".$mysqli->escape_string($fgalid)."' AND hide='0' ORDER BY sortid DESC";
makepages($query,$sites,$names['galpage'],$settings['gals_per_page']);

$list = $mysqli->query($query);
while($gal = $list->fetch_assoc()){
	$anz_subgals = 0;
	list($anz_subgals) = $mysqli->query("SELECT COUNT(*) FROM ".$mysql_tables['gallery']." WHERE subof ='".$gal['id']."'")->fetch_array(MYSQLI_NUM);
	
	if(!empty($gal['galpassword']) && (!isset($pwcookie) || isset($pwcookie) && !in_array(pwhashing($gal['galpassword'].$salt),$pwcookie)))
		$gal['pic'] = "<img src=\"".$imagepf."lock.png\" alt=\"Symbol: Schlo&szlig;\" title=\"Diese Galerie ist durch ein Passwort gesch&uuml;tzt\" class=\"noborder\" />";
	elseif(!empty($gal['galpic'])){
		$galpiclist = $mysqli->query("SELECT filename FROM ".$mysql_tables['pics']." WHERE id = '".$mysqli->escape_string($gal['galpic'])."' LIMIT 1");
		$gpic = $galpiclist->fetch_assoc();
		$gal['pic'] = _01gallery_getThumb($galdir._01gallery_getGalDir($gal['id'],$gal['galpassword'])."/",$gpic['filename'],"_tb");
		}
	else{
		$gal['pic'] = _01gallery_collectThumbnail($gal['id']);
		if(empty($gal['pic'])) $gal['pic'] = "<img src=\"".$picuploaddir.FILE_NO_THUMBS."\" alt=\"Keine Thumbnails vorhanden\" />";
		}


	$gal['link'] = addParameter2Link($filename,$names['galid']."=".$gal['id']);
	
	$gal['galeriename'] = htmlentities(($gal['galeriename']),$htmlent_flags,$htmlent_encoding_acp);
	if(!empty($gal['galpassword']) && (!isset($pwcookie) || isset($pwcookie) && !in_array(pwhashing($gal['galpassword'].$salt),$pwcookie))) $gal['beschreibung'] = "<p class=\"gal_password\">Zum Betrachten dieses Bilderalbums ben&ouml;tigen Sie ein Passwort.</p>";
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
global $mysqli,$filename,$names,$_REQUEST,$mysql_tables,$flag_breadcrumps,$text_bilderlaben,$pwcookie,$salt,$htmlent_flags,$htmlent_encoding_acp;

$stop = false;
$errorid = 0;

// Alle vorhandenen Galerien in einen Array einlesen
$gals = array();
$list = $mysqli->query("SELECT id,subof,galpassword,galeriename FROM ".$mysql_tables['gallery']."");
while($row = $list->fetch_assoc()){
	$gals[$row['id']]['id']			= $row['id'];
	$gals[$row['id']]['subof']		= $row['subof'];
	$gals[$row['id']]['name']		= htmlentities(stripslashes($row['galeriename']),$htmlent_flags,$htmlent_encoding_acp);
	if(!empty($row['galpassword']))
		$gals[$row['id']]['galpassword']	= $row['galpassword'];
	else 
		$gals[$row['id']]['galpassword']	= "";
	}
	
$runid = $aktgalid;
$crumps = "";
$c = 0;
while(!$stop){
	if(empty($gals[$runid]['galpassword']) || is_array($pwcookie) && in_array(pwhashing($gals[$runid]['galpassword'].$salt),$pwcookie)){
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


// Überprüfen ob für das Bild beim Upload ein Resize vorgenommen werden soll/muss
/*$source_properties		Array(0=width,1=height) des Ausgangsbildes
  $max_properties			Array(0=width,1=height) mit den maximalen Größenangaben

RETURN: TRUE / FALSE
  */
if(!function_exists("_01gallery_checkResize")){
function _01gallery_checkResize($source_properties, $max_properties=""){
global $settings;

if(empty($max_properties)) $max_properties = _01gallery_ParseWxH($settings['resize_maxpicsize']);

// Check Parameter:
if($settings['resize_pics_on_upload'] == 0 || !isset($source_properties) || !is_array($source_properties) || !isset($max_properties) || !is_array($source_properties)) return false;
if(!is_numeric($source_properties['0']) || !is_numeric($source_properties['1']) || !is_numeric($max_properties['0']) || !is_numeric($max_properties['1'])) return false;

if($source_properties['0'] > $max_properties['0'] || $source_properties['1'] > $max_properties['1']){
    return true;
    }

return false;

}
}


// Extrahiert Breite und Höhe aus einer mit x getrennten Größenangabe
/*$string       String, der eine mit x (oder X) getrennte Größenangabe enthält

RETURN: Array mit Index 0,1,width,height
  */
if(!function_exists("_01gallery_ParseWxH")){
function _01gallery_ParseWxH($string){

if(!stripos($string,"x")) return false;

$rez_size = explode("x",strtolower($string));

if(!is_numeric($rez_size[0]) || !is_numeric($rez_size[1]))
	return false;

$rez_size[0] = trim($rez_size[0]);
$rez_size[1] = trim($rez_size[1]);
$rez_size['width'] =  $rez_size[0];
$rez_size['height'] = $rez_size[1];

return $rez_size;

}
}


// Sucht das passende Thumbnail für die Galerie (auch aus beliebig vielen Sub-Galerien)
/*$galid       Galerie-ID, bei der mit der Thubnail-Suche begonnen werden soll

RETURN: Pic-Filename
  */
if(!function_exists("_01gallery_collectThumbnail")){
function _01gallery_collectThumbnail($galid){
global $mysqli,$mysql_tables,$galdir;

if(isset($galid) && is_numeric($galid) && !empty($galid)){
	$gallist = $mysqli->query("SELECT galpassword,galpic FROM ".$mysql_tables['gallery']." WHERE id = '".$mysqli->escape_string($galid)."' LIMIT 1");
	$gal = $gallist->fetch_assoc();
	
	if(isset($gal['galpic']) && is_numeric($gal['galpic']) && $gal['galpic'] > 0)
	    $query = "SELECT filename FROM ".$mysql_tables['pics']." WHERE id = '".$mysqli->escape_string($gal['galpic'])."' LIMIT 1";
	else
		$query = "SELECT filename FROM ".$mysql_tables['pics']." WHERE galid = '".$mysqli->escape_string($galid)."' ORDER BY sortorder DESC LIMIT 1";  

	$galpiclist = $mysqli->query($query);
	if($galpiclist->num_rows == 1){
		$gpic = $galpiclist->fetch_assoc();
	
		return _01gallery_getThumb($galdir._01gallery_getGalDir($galid,$gal['galpassword'])."/",$gpic['filename'],"_tb");
		}
	else{
		// Alle Subgaleries der Reihe nach durchgehen
		// IN allen Subgalerien wird wiederum alle Tiefen durchgegangen, bis auf ein Bild gestoßen wird
		$subgals = $mysqli->query("SELECT id FROM ".$mysql_tables['gallery']." WHERE subof = '".$mysqli->escape_string($galid)."' ORDER BY sortid DESC");
		if($subgals->num_rows >= 1){
			while($row = $subgals->fetch_assoc()){
				$thumb = _01gallery_collectThumbnail($row['id']);
				
				if(!empty($thumb)) return $thumb;
				}
			}
		
		
		return "";
		}

	}
else return "";

}
}

?>