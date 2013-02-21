<?PHP
/* 
	01-Gallery - Copyright 2003-2013 by Michael Lorer - 01-Scripts.de
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
	$list = mysql_query("SELECT password,galeriename,uid FROM ".$mysql_tables['gallery']." WHERE id = '".mysql_real_escape_string($_GET['galid'])."' LIMIT 1");
	$row = mysql_fetch_assoc($list);
	$dir = _01gallery_getGalDir($_GET['galid'],$row['password']);

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
	$list = mysql_query("SELECT id FROM ".$mysql_tables['gallery']."");
	while($row = mysql_fetch_assoc($list)){
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
				mysql_query("UPDATE ".$mysql_tables['pics']." SET sortorder='".$sortstartid."' WHERE id = '".mysql_real_escape_string($picid)."' AND galid = '".mysql_real_escape_string($_REQUEST['id'])."'");
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
		switch($_REQUEST['sortorder']){
		  case "az":
		  default:
			mysql_query("SET @pos=0");
			mysql_query("UPDATE ".$mysql_tables['pics']." SET sortorder= ( SELECT @pos := @pos +1 ) WHERE galid = '".mysql_real_escape_string($_REQUEST['id'])."' ORDER BY orgname DESC");
		  break;
		  case "za":
			mysql_query("SET @pos=0");
			mysql_query("UPDATE ".$mysql_tables['pics']." SET sortorder= ( SELECT @pos := @pos +1 ) WHERE galid = '".mysql_real_escape_string($_REQUEST['id'])."' ORDER BY orgname");
		  break;
		  case "taz":
			mysql_query("SET @pos=0");
			mysql_query("UPDATE ".$mysql_tables['pics']." SET sortorder= ( SELECT @pos := @pos +1 ) WHERE galid = '".mysql_real_escape_string($_REQUEST['id'])."' ORDER BY title DESC");
		  break;
		  case "tza":
			mysql_query("SET @pos=0");
			mysql_query("UPDATE ".$mysql_tables['pics']." SET sortorder= ( SELECT @pos := @pos +1 ) WHERE galid = '".mysql_real_escape_string($_REQUEST['id'])."' ORDER BY title");
		  break;
		  case "timeup":
			mysql_query("SET @pos=0");
			mysql_query("UPDATE ".$mysql_tables['pics']." SET sortorder= ( SELECT @pos := @pos +1 ) WHERE galid = '".mysql_real_escape_string($_REQUEST['id'])."' ORDER BY timestamp");
		  break;
		  case "timedown":
			mysql_query("SET @pos=0");
			mysql_query("UPDATE ".$mysql_tables['pics']." SET sortorder= ( SELECT @pos := @pos +1 ) WHERE galid = '".mysql_real_escape_string($_REQUEST['id'])."' ORDER BY timestamp DESC");
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
		mysql_query("UPDATE ".$mysql_tables['gallery']." SET galpic='".mysql_real_escape_string($_REQUEST['id'])."' WHERE id = '".mysql_real_escape_string($_REQUEST['galid'])."'");

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
	        $title = "title = '".mysql_real_escape_string(utf8_decode($_REQUEST['title']))."' ";
	        $echotitle = htmlentities(utf8_decode(stripslashes($_REQUEST['title'])),$htmlent_flags,$htmlent_encoding_acp);
	        }
		else{ $title = "title = '' "; $echotitle = ""; }
		
		if(isset($_REQUEST['beschreibung']) && !empty($_REQUEST['beschreibung'])){
	        $beschreibung = "text = '".mysql_real_escape_string(utf8_decode($_REQUEST['beschreibung']))."' ";
	        $echobeschreibung = "<br />".substr(htmlentities(utf8_decode(stripslashes($_REQUEST['beschreibung'])),$htmlent_flags,$htmlent_encoding_acp),0,100);
	        }
		else{ $beschreibung = "text = '' "; $echobeschreibung = ""; }
	    
	    mysql_query("UPDATE ".$mysql_tables['pics']." SET ".$title.", ".$beschreibung." WHERE id = '".mysql_real_escape_string($_REQUEST['id'])."'");
	
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

    $list = mysql_query("SELECT galid,filename,uid FROM ".$mysql_tables['pics']." WHERE id = '".mysql_real_escape_string($_REQUEST['id'])."' LIMIT 1");
	$statrow = mysql_fetch_assoc($list);
	
	// Berechtigung abfragen
	if($userdata['editgal'] == 2 || $userdata['editgal'] == 1 && $statrow['uid'] == $userdata['id']){
        $list = mysql_query("SELECT password FROM ".$mysql_tables['gallery']." WHERE id = '".mysql_real_escape_string($statrow['galid'])."' LIMIT 1");
	    $galstatrow = mysql_fetch_assoc($list);
	    
        $dir = _01gallery_getGalDir($statrow['galid'],stripslashes($galstatrow['password']));
        $split = pathinfo($statrow['filename']);
        
        @unlink($modulpath.$galdir.$dir."/".$statrow['filename']);
        @unlink($modulpath.$galdir.$dir."/".$split['filename']."_big.".$split['extension']);
        @unlink($modulpath.$galdir.$dir."/".$split['filename']."_tb.".$split['extension']);
	    @unlink($modulpath.$galdir.$dir."/".$split['filename']."_acptb.".$split['extension']);
        
        mysql_query("DELETE FROM ".$mysql_tables['pics']." WHERE id='".mysql_real_escape_string($_REQUEST['id'])."'");
        mysql_query("UPDATE ".$mysql_tables['gallery']." SET galpic = 0 WHERE galpic='".mysql_real_escape_string($_REQUEST['id'])."' LIMIT 1");
        
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