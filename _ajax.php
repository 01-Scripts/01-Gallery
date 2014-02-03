<?PHP
/* 
	01-Gallery - Copyright 2003-2014 by Michael Lorer - 01-Scripts.de
	Lizenz: Creative-Commons: Namensnennung-Keine kommerzielle Nutzung-Weitergabe unter gleichen Bedingungen 3.0 Deutschland
	Weitere Lizenzinformationen unter: http://www.01-scripts.de/lizenz.php
	
	Modul:		01gallery
	Dateiinfo: 	Bearbeitung von eingehenden Ajax-Requests:
	               - Bild-Upload (using Fancy-Upload9
	               - Bilder in Galerien neu zählen
	               - Bilder-Sortierung speichern
	               - Coverbild speichern
	               - Bilddaten speichern
	               - Einzelbild löschen
	#fv.211#
*/

// Security: Only allow calls from _ajaxloader.php!
if(basename($_SERVER['SCRIPT_FILENAME']) != "_ajaxloader.php") exit;

// Fancy-Upload (Bilder hochladen)
if(isset($_GET['ajaxaction']) && $_GET['ajaxaction'] == "fancyupload" && isset($_GET['galid']) && !empty($_GET['galid']) && is_numeric($_GET['galid'])){
	$list = $mysqli->query("SELECT galpassword,galeriename,uid FROM ".$mysql_tables['gallery']." WHERE id = '".$mysqli->escape_string($_GET['galid'])."' LIMIT 1");
	$row = $list->fetch_assoc();
	$dir = _01gallery_getGalDir($_GET['galid'],$row['galpassword']);

	// Zugriffsberechtigung?
	if($userdata['uploadpics'] == 2 || $userdata['uploadpics'] == 1 && $row['uid'] == $userdata['id']){
		$error = false;
	
		if(!isset($_FILES['Filedata']) || !is_uploaded_file($_FILES['Filedata']['tmp_name']))
			$error = 'Invalid Upload';
		else{
			// Our processing, we get a hash value from the file
			$return['hash'] = md5_file($_FILES['Filedata']['tmp_name']);

			$return = _01gallery_upload_2Gallery($_GET['galid'],"Filedata","","");
	
			_01gallery_countPics($_GET['galid']);
			
			if(isset($_REQUEST['response']) && $_REQUEST['response'] == 'xml'){
				echo "<response>";
				foreach ($return as $key => $value){
					echo "<$key><![CDATA[$value]]></$key>";
					}
				echo "</response>";
				}
			else
				echo json_encode($return);

			}
		}
	}
// Anzahl der Bilder in Galerien neu zählen
elseif(isset($_REQUEST['ajaxaction']) && $_REQUEST['ajaxaction'] == "countgalpics"){
	$list = $mysqli->query("SELECT id FROM ".$mysql_tables['gallery']."");
	while($row = $list->fetch_assoc()){
		_01gallery_countPics($row['id']);
		}
	
	echo "
<script type=\"text/javascript\">
location.reload(true);
Success_standard();
</script>";
	}
// Individuelle Bilder-Sortierung speichern
elseif(isset($_REQUEST['ajaxaction']) && $_REQUEST['ajaxaction'] == "savesortorder" &&
    isset($_REQUEST['id']) && !empty($_REQUEST['id']) && is_numeric($_REQUEST['id']) &&
    isset($_REQUEST['sortdatafield']) && !empty($_REQUEST['sortdatafield'])){

	if(_01gallery_checkUserright($_REQUEST['id'])){
	
		$sortarray = explode(",",$_REQUEST['sortdatafield']);
		$sortstartid = count($sortarray);
		$sortstartid++;
		
		if($sortstartid > 1){
			foreach($sortarray as $picid){
				$mysqli->query("UPDATE ".$mysql_tables['pics']." SET sortorder='".$sortstartid."' WHERE id = '".$mysqli->escape_string($picid)."' AND galid = '".$mysqli->escape_string($_REQUEST['id'])."'");
				$sortstartid--;
				}
			echo "
<script type=\"text/javascript\">
Stop_Loading_standard();
Success_standard();
</script>";
			}
		else
			echo "
<script type=\"text/javascript\">
Stop_Loading_standard();
Failed_delfade();
</script>";
		}
	}
// Auto-Bilder-Sortierung speichern
elseif(isset($_REQUEST['ajaxaction']) && $_REQUEST['ajaxaction'] == "saveautosortorder" &&
    isset($_REQUEST['id']) && !empty($_REQUEST['id']) && is_numeric($_REQUEST['id']) &&
    isset($_REQUEST['sortorder']) && !empty($_REQUEST['sortorder'])){

	if(_01gallery_checkUserright($_REQUEST['id'])){

		/* Sortierung gerade "umgedreht", da die Ausgabe von 99->0 erfolgt. Es muss also beim "hochzählen" mit dem letzten Bild begonnen werden
		Deshalb alle Query-Befehle vom DESC-Argument her grad umgekehrt... */
		$query = "UPDATE ".$mysql_tables['pics']." SET sortorder= ( SELECT @pos := @pos +1 ) WHERE galid = '".$mysqli->escape_string($_REQUEST['id'])."'";
		switch($_REQUEST['sortorder']){
		  case "az":
		  default:
			$mysqli->query("SET @pos=0");
			$mysqli->query($query." ORDER BY orgname DESC");
		  break;
		  case "za":
			$mysqli->query("SET @pos=0");
			$mysqli->query($query." ORDER BY orgname");
		  break;
		  case "taz":
			$mysqli->query("SET @pos=0");
			$mysqli->query($query." ORDER BY title DESC");
		  break;
		  case "tza":
			$mysqli->query("SET @pos=0");
			$mysqli->query($query." ORDER BY title");
		  break;
		  case "timeup":
			$mysqli->query("SET @pos=0");
			$mysqli->query($query." ORDER BY pictimestamp");
		  break;
		  case "timedown":
			$mysqli->query("SET @pos=0");
			$mysqli->query($query." ORDER BY pictimestamp DESC");
		  break;
		  }
		echo "
<script type=\"text/javascript\">
location.reload(true);
Stop_Loading_standard();
Success_standard();
</script>";
		}
	}
// Fehler abfangen, falls Sortierung gespeichert werden soll; Speicherfeld aber leer ist
elseif(isset($_REQUEST['ajaxaction']) && $_REQUEST['ajaxaction'] == "savesortorder" &&
    isset($_REQUEST['id']) && !empty($_REQUEST['id']) && is_numeric($_REQUEST['id'])){

	echo "
<script type=\"text/javascript\">
Stop_Loading_standard();
</script>";
	}
// Neues Coverbild für Galerie setzen
elseif(isset($_REQUEST['ajaxaction']) && $_REQUEST['ajaxaction'] == "setnewcover" &&
    isset($_REQUEST['id']) && !empty($_REQUEST['id']) && is_numeric($_REQUEST['id']) &&
    isset($_REQUEST['galid']) && !empty($_REQUEST['galid']) && is_numeric($_REQUEST['galid'])){
	
	if(_01gallery_checkUserright($_REQUEST['id'])){
		$mysqli->query("UPDATE ".$mysql_tables['gallery']." SET galpic='".$mysqli->escape_string($_REQUEST['id'])."' WHERE id = '".$mysqli->escape_string($_REQUEST['galid'])."'");

		echo "
<script type=\"text/javascript\">
Success_standard();
</script>";
		}
	}
// Bilddaten (Titel und Beschreibung) speichern
elseif(isset($_REQUEST['ajaxaction']) && $_REQUEST['ajaxaction'] == "savepicdata" &&
    isset($_REQUEST['id']) && !empty($_REQUEST['id']) && is_numeric($_REQUEST['id'])){

	if(_01gallery_checkUserright($_REQUEST['id'])){
		if(isset($_REQUEST['title']) && !empty($_REQUEST['title'])){
	        $title = "title = '".$mysqli->escape_string(iconv("UTF-8", "ISO-8859-1//TRANSLIT", strip_tags($_REQUEST['title'])))."' ";
	        $echotitle = strip_tags($_REQUEST['title']);
	        }
		else{ $title = "title = '' "; $echotitle = ""; }
		
		if(isset($_REQUEST['beschreibung']) && !empty($_REQUEST['beschreibung'])){
	        $beschreibung = "pictext = '".$mysqli->escape_string(iconv("UTF-8", "ISO-8859-1//TRANSLIT", strip_tags($_REQUEST['beschreibung'])))."' ";
	        $echobeschreibung = "<br />".strip_tags($_REQUEST['beschreibung']);
	        }
		else{ $beschreibung = "pictext = '' "; $echobeschreibung = ""; }
	    
	    $mysqli->query("UPDATE ".$mysql_tables['pics']." SET ".$title.", ".$beschreibung." WHERE id = '".$mysqli->escape_string($_REQUEST['id'])."'");
	
	    echo "
<script type=\"text/javascript\">
Stop_Loading_standard();
Success_standard();
hide_unhide('hide_show_".$_REQUEST['id']."');
hide_unhide('hide_edit_".$_REQUEST['id']."');
</script>";

    	echo "<b>".$echotitle."</b>".$echobeschreibung."";
    	}

	}
// Einzelnes Bild aus einer Galerie löschen
elseif(isset($_REQUEST['ajaxaction']) && $_REQUEST['ajaxaction'] == "delpic" &&
	isset($_REQUEST['id']) && !empty($_REQUEST['id']) && is_numeric($_REQUEST['id'])){

    $list = $mysqli->query("SELECT galid,filename,uid FROM ".$mysql_tables['pics']." WHERE id = '".$mysqli->escape_string($_REQUEST['id'])."' LIMIT 1");
	$statrow = $list->fetch_assoc();
	
	// Berechtigung abfragen
	if($userdata['editgal'] == 2 || $userdata['editgal'] == 1 && $statrow['uid'] == $userdata['id']){
        $list = $mysqli->query("SELECT galpassword FROM ".$mysql_tables['gallery']." WHERE id = '".$mysqli->escape_string($statrow['galid'])."' LIMIT 1");
	    $galstatrow = $list->fetch_assoc();
	    
        $dir = _01gallery_getGalDir($statrow['galid'],stripslashes($galstatrow['galpassword']));
        $split = pathinfo($statrow['filename']);
        
        @unlink($modulpath.$galdir.$dir."/".$statrow['filename']);
        @unlink($modulpath.$galdir.$dir."/".$split['filename']."_big.".$split['extension']);
        @unlink($modulpath.$galdir.$dir."/".$split['filename']."_tb.".$split['extension']);
	    @unlink($modulpath.$galdir.$dir."/".$split['filename']."_acptb.".$split['extension']);
        
        $mysqli->query("DELETE FROM ".$mysql_tables['pics']." WHERE id='".$mysqli->escape_string($_REQUEST['id'])."'");
        $mysqli->query("UPDATE ".$mysql_tables['gallery']." SET galpic = 0 WHERE galpic='".$mysqli->escape_string($_REQUEST['id'])."' LIMIT 1");
        
        _01gallery_countPics($statrow['galid']);
        
        echo "<script type=\"text/javascript\"> Success_delfade('id".$_REQUEST['id']."'); </script>";
        }
    else{
        echo "<script type=\"text/javascript\"> Failed_delfade(); </script>";
        }
    }
elseif(isset($_REQUEST['ajaxaction']) && $_REQUEST['ajaxaction'] == "test"){
	echo $_REQUEST['id'];
	echo "<script type=\"text/javascript\"> Stop_Loading_standard(); </script>";
	}
else
	echo "<script type=\"text/javascript\"> Stop_Loading_standard(); Failed_delfade(); </script>";

?>