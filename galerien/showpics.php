<?PHP
/* 
	01-Gallery V2 - Copyright 2003-2009 by Michael Lorer - 01-Scripts.de
	Lizenz: Creative-Commons: Namensnennung-Keine kommerzielle Nutzung-Weitergabe unter gleichen Bedingungen 3.0 Deutschland
	Weitere Lizenzinformationen unter: http://www.01-scripts.de/lizenz.php
	
	Modul:		01gallery
	Dateiinfo: 	Dynaisches Generieren von Thumbnails
	#fv.2000#
*/


//include("../../../01_config.php");
$subfolder = "../../../";
include("../../../01acp/system/headinclude.php");
//include("../../../01acp/system/functions.php");
include("../_functions.php");
include("../_headinclude.php");
define('ACP_TB_WIDTH', 40);
define('FILE_404_THUMB', '../../../01pics/404thumb.gif');
define('FILE_GIF_THUMB', '../../../01pics/gifthumb.gif');
print_r($mysql_tables);
//Bild-Ausgabe
if(isset($_GET['imgid']) && !empty($_GET['imgid']) && is_numeric($_GET['imgid'])){
	$listpic = mysql_query("SELECT galid,filename FROM ".$mysql_tables['pics']." WHERE id = '".mysql_real_escape_string($_GET['imgid'])."' LIMIT 1");
	$picrow = mysql_fetch_assoc($listpic);
	//print_r($picrow);
	if(isset($picrow['galid']) && !empty($picrow['galid']) && isset($picrow['filename']) && !empty($picrow['filename'])){
		$list = mysql_query("SELECT password,uid FROM ".$mysql_tables['gallery']." WHERE id = '".mysql_real_escape_string($picrow['galid'])."' LIMIT 1");
		$statrow = mysql_fetch_assoc($list);
		$dir = _01gallery_getGalDir($picrow['galid'],$statrow['password']);

		if(isset($dir) && !empty($dir)){
			if(getEndung($picrow['filename']) == "gif"){
				$file = fread(fopen($dir."/".$picrow['filename'], "r"), filesize($dir."/".$picrow['filename']));
				header("Content-type: image/gif");
				echo $file; 
				fclose($file);
				}
			elseif(isset($_GET['size']) && !empty($_GET['size']))
			    showpic($dir."/".$picrow['filename'],$_GET['size']);
			else
			    showpic($dir."/".$picrow['filename']);
			}
		}
	}
else{
	$file = fread(fopen(FILE_404_THUMB, "r"), filesize(FILE_404_THUMB));
	header("Content-type: image/gif");
	echo $file; 
	fclose($file);
	}

// 01-Gallery V2 Copyright 2006-2009 by Michael Lorer - 01-Scripts.de
?>