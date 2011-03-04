<?PHP
/*
	01-Gallery V2 - Copyright 2003-2011 by Michael Lorer - 01-Scripts.de
	Lizenz: Creative-Commons: Namensnennung-Keine kommerzielle Nutzung-Weitergabe unter gleichen Bedingungen 3.0 Deutschland
	Weitere Lizenzinformationen unter: http://www.01-scripts.de/lizenz.php

	Modul:		01gallery
	Dateiinfo: 	Frontend-Ausgabe
	#fv.202#
*/

//Hinweis zum Einbinden des Artikelsystems per include();
/*Folgender PHP-Code n�tig:

<?PHP
$subfolder 		= "01scripts/";
$modul			= "01gallery/";

include($subfolder."01module/".$modul."01gallery.php");
?>

*/

$frontp		= 1;
$flag_acp	= FALSE;
if(!isset($flag_nocss))				$flag_nocss = FALSE;
if(!isset($flag_second))			$flag_second = FALSE;
if(!isset($flag_breadcrumps))		$flag_breadcrumps = TRUE;
if(!isset($flag_smallstream))		$flag_smallstream = FALSE;

if(isset($subfolder) && !empty($subfolder)){
    if(substr_count($subfolder, "/") < 1){ $subfolder .= "/"; }
	}
else
	$subfolder = "";

// Globale Config-Datei einbinden
include_once($subfolder."01_config.php");
include_once($subfolder."01acp/system/headinclude.php");
if(!$flag_second) include_once($subfolder."01acp/system/functions.php");

$modulvz = $modul."/";
// Modul-Config-Dateien einbinden
include_once($moduldir.$modulvz."_headinclude.php");
include_once($moduldir.$modulvz."_functions.php");

// Variablen
$imagepf 	= $moduldir.$modulvz.$imagepf;			// Verzeichnis mit Bild-Dateien
$tempdir	= $moduldir.$modulvz.$tempdir;			// Template-Verzeichnis
$galdir		= $moduldir.$modulvz.$galdir;			// Bilderalben-Verzeichnis
$sites		= 0;
$sites2		= 0;
$errormsg	= "";

// Notice: Undefined index: ... beheben
if(!isset($_REQUEST[$names['galid']]))	$_REQUEST[$names['galid']]	= "";
if(!isset($_REQUEST[$names['galpage']]))$_REQUEST[$names['galpage']]= "";
if(!isset($_REQUEST[$names['picid']]))	$_REQUEST[$names['picid']]	= "";
if(!isset($_REQUEST[$names['picpage']]))$_REQUEST[$names['picpage']]= "";
if(!isset($_REQUEST[$names['action']])) $_REQUEST[$names['action']]	= "";

if(isset($galid) && !empty($galid)){
    $gals_allids = "";
    $gals_allids = _01gallery_getGallerysRek($galid,0,-1,"echoSubIDs").$galid;
    $gals_allids = explode("|",$gals_allids);
    array_splice($gals_allids,0,1);
    }
if(!isset($galid) || empty($galid) || (isset($gals_allids) && is_array($gals_allids) && !empty($_REQUEST[$names['galid']]) && in_array($_REQUEST[$names['galid']],$gals_allids))) $galid = $_REQUEST[$names['galid']];
$galpage=	$_REQUEST[$names['galpage']];
if(!isset($picid) || empty($picid) || isset($_REQUEST[$names['picid']]) && !empty($_REQUEST[$names['picid']]) && is_numeric($_REQUEST[$names['picid']]) && $_REQUEST[$names['picid']] > 0)
	$picid	=	$_REQUEST[$names['picid']];
$picpage=	$_REQUEST[$names['picpage']];

$filename = $_SERVER['PHP_SELF'];
$system_link_gal = $filename."?".$names['galid']."=".$galid."&amp;".$names['galpage']."=".$galpage;
$system_link_pic = $system_link_gal."&amp;".$names['picpage']."=".$picpage."&amp;".$names['picid']."=".$_REQUEST[$names['picid']];

// Cookie auslesen
if(isset($_COOKIE["c_".$modul.'_galpwcookie']) && !empty($_COOKIE["c_".$modul.'_galpwcookie']))
	$pwcookie = explode("|",$_COOKIE["c_".$modul.'_galpwcookie']);
else
	$pwcookie = array();
	
// Wenn $_GET['picfilename'] vorhanden, $galid �berschreiben
if(isset($_GET[$names['picfilename']]) && !empty($_GET[$names['picfilename']])){
	$list = mysql_query("SELECT id FROM ".$mysql_tables['pics']." WHERE filename = '".mysql_real_escape_string($_GET[$names['picfilename']])."' LIMIT 1");
	$singlepicinfo = mysql_fetch_assoc($list);
	if(isset($singlepicinfo['id']) && !empty($singlepicinfo['id']) && is_numeric($singlepicinfo['id']) && $singlepicinfo['id'] > 0)
		$picid = $singlepicinfo['id'];
	}




// externe CSS-Datei / CSS-Eigenschaften?
if(isset($settings['extern_css']) && !empty($settings['extern_css']) && $settings['extern_css'] != "http://" && !$flag_nocss)
	$echo_css = "<link rel=\"stylesheet\" type=\"text/css\" href=\"".$settings['extern_css']."\" />";
elseif(isset($settings['csscode']) && !empty($settings['csscode']) && !$flag_nocss)
	$echo_css = "<style type=\"text/css\">
".$settings['csscode']."
</style>";
else $echo_css = "";


	$echo_css .= "\n<style type=\"text/css\">
.cssgallery li{
	width:".$settings['pics_per_line'].";
	height:".($settings['thumbwidth']+25)."px;
}

.cssgallery li.stream{
	width:".($settings['thumbwidth']+10)."px;
	height:".($settings['thumbwidth']+25)."px;
}

.cssgallery li.smallstream{
	width:".($smallstreampicsize+10)."px;
	height:".($smallstreampicsize+27)."px;
}

.cssgallery a:link,
.cssgallery a:visited,
.cssgallery a:focus,
.cssgallery a:hover,
.cssgallery a:active{
	width:".$settings['thumbwidth']."px;
	height:".$settings['thumbwidth']."px;
	position:absolute; top:50%; left:50%; 	/* NICHT VER�NDERN!!! - position it so that image's top left corner is in the center of the list item */
	margin:-".round($settings['thumbwidth']/2)."px 0 0 -".round($settings['thumbwidth']/2)."px; 				/* NICHT VER�NDERN!!! - Pull the image into position with negative margins (margins value is half of the width of the image) */
}

.cssgallery li.smallstream a:link,
.cssgallery li.smallstream a:visited,
.cssgallery li.smallstream a:focus,
.cssgallery li.smallstream a:hover,
.cssgallery li.smallstream a:active{
	width:".$smallstreampicsize."px;
	height:".$smallstreampicsize."px;
	position:absolute; top:50%; left:50%; 	/* NICHT VER�NDERN!!! - position it so that image's top left corner is in the center of the list item */
	margin:-".round(($smallstreampicsize/2)+10)."px 0 0 -".round($smallstreampicsize/2)."px; 				/* NICHT VER�NDERN!!! - Pull the image into position with negative margins (margins value is half of the width of the image) */
}

</style>";

// Head einf�gen
include($tempdir."main_top.html");






// Display: Detailansicht (einzelnes Bild)
if(is_numeric($picid) && $picid > 0){
	
	$system_link_form = $system_link_pic."&amp;".$names['picid']."=".$picid;
	
	// Keine $galid vorhanden? (Aus picid holen)
	if(!isset($galid) || isset($galid) && (empty($galid) || $galid == 0 || !is_numeric($galid))){
		$list = mysql_query("SELECT galid FROM ".$mysql_tables['pics']." WHERE id = '".mysql_real_escape_string($picid)."' LIMIT 1");
		$galinfo = mysql_fetch_assoc($list);
		$galid = $galinfo['galid'];
		}

	// Galerie-Infos aus Datenbank holen
	$list = mysql_query("SELECT id,timestamp,password,galeriename,beschreibung,galpic,anzahl_pics,comments FROM ".$mysql_tables['gallery']." WHERE id = '".mysql_real_escape_string($galid)."' AND hide='0' LIMIT 1");
	$galinfo = mysql_fetch_assoc($list);
	$galverz = $galdir._01gallery_getGalDir($galinfo['id'],$galinfo['password'])."/";

	// Login-Formular abgeschickt?
	if(!empty($galinfo['password']) && isset($_POST['login2gal']) && isset($_POST['galpw']) && !empty($_POST['galpw'])){
		if($_POST['galpw'] == $galinfo['password'] && !in_array(pwhashing($galinfo['password'].$salt),$pwcookie)){
			$pwcookie[] = pwhashing($galinfo['password'].$salt);
			echo set_a_cookie("c_".$modul."_galpwcookie", implode("|",$pwcookie),time()+60*60*24);
			}
		elseif($_POST['galpw'] != $galinfo['password']){
			$errormsg = "<p class=\"errormsg\">Das eingegebene Passwort ist leider falsch.</p>";
			include_once($tempdir."passwordbox.html");
			}
		}

	// Bild-Detailansicht anzeigen (Cookie vorhanden?)
	if(!empty($galinfo['password']) && isset($pwcookie) && is_array($pwcookie) && in_array(pwhashing($galinfo['password'].$salt),$pwcookie) ||
	   empty($galinfo['password'])){

	    // Detailansicht anzeigen
	    $lightbox_arels1 = "";
	    $lightbox_arels2 = "";
	    $lightbox_second = false;
	    $picnr = 1;
    	$list = mysql_query("SELECT id,sortorder,filename,title,text FROM ".$mysql_tables['pics']." WHERE galid = '".mysql_real_escape_string($galid)."' ORDER BY sortorder DESC");
		while($row = mysql_fetch_assoc($list)){
			if($row['id'] == $picid){
				$pic['title']	= htmlentities(stripslashes($row['title']));
				$pic['text']	= htmlentities(stripslashes($row['text']));
				$pic['filename']= $row['filename'];
				$pic['sortorder']=$row['sortorder'];
				$pic['id']		= $row['id'];
				$lightbox_second = true;
				$picid_nr = $picnr;
				}
			$pics[$picnr]['filename']= $row['filename'];
			$pics[$picnr]['id']		 = $row['id'];
				
			if(!$lightbox_second && $settings['use_lightbox'] >= 1)
				$lightbox_arels1 .= "<a href=\"".$galverz.$row['filename']."\" rel=\"lightbox-gal".$galid."set\" title=\"".stripslashes($row['title'])." - ".stripslashes($row['text'])."\"></a>";
			elseif($settings['use_lightbox'] >= 1 && $row['id'] != $picid)
				$lightbox_arels2 .= "<a href=\"".$galverz.$row['filename']."\" rel=\"lightbox-gal".$galid."set\" title=\"".stripslashes($row['title'])." - ".stripslashes($row['text'])."\"></a>";
				
			$picnr++;
			}
		$pics_gesamt = ($picnr-1);
			
		// Breadcrumps ausgeben UND Zugangsberechtigung �berpr�fen
		$accesserror = _01gallery_echoBreadcrumps($galid);
		if($accesserror == 0){
			// Weitere Thumbnails im Stream zeigen:
			
			if($flag_smallstream && isset($smallstreampicsize) && $smallstreampicsize > 0) $cssclass = "smallstream";
			else $cssclass = "stream";
			
			$tempcache = array();				// Workaround um Reihenfolge der Bilder zu erhalten
			$picstream = "\n\n<ul class=\"cssgallery\">\n";
		    
			// Picstream VOR dem aktuellen Bild
			for($x=($picid_nr-$anz_streampics);$x<$picid_nr;$x++){
				if($x > 0)
					$picstream .= "<li class=\"".$cssclass."\"><a href=\"".addParameter2Link($system_link_pic,$names['picid']."=".$pics[$x]['id'])."\">"._01gallery_getThumb($galverz,stripslashes($pics[$x]['filename']),"_tb",$flag_smallstream)."</a></li>\n";
				}
		    
			// Aktuelles Bild im Picstream
			$picstream .= "<li class=\"".$cssclass."\"><a href=\"#01jump2top\" class=\"stream\">"._01gallery_getThumb($galverz,stripslashes($pic['filename']),"_tb",$flag_smallstream)."</a></li>\n";
			
			// Picstream NACH dem aktuellen Bild
			for($x=($picid_nr+1);$x<=($picid_nr+$anz_streampics);$x++){
				if($x < ($pics_gesamt+1))
					$picstream .= "<li class=\"".$cssclass."\"><a href=\"".addParameter2Link($system_link_pic,$names['picid']."=".$pics[$x]['id'])."\">"._01gallery_getThumb($galverz,stripslashes($pics[$x]['filename']),"_tb",$flag_smallstream)."</a></li>\n";
				}
			
			$picstream .= "</ul>\n\n";
			
			echo $lightbox_arels1;
			include($tempdir."picdetailview.html");
			echo $lightbox_arels2;
			
			// KOMMENTAR-AUSGABE & FORMULAR, etc.
			if(isset($picid) && !empty($picid) && $picid > 0 && is_numeric($picid) &&
			   isset($galid) && !empty($galid) && $galid > 0 && is_numeric($galid) &&
			   $settings['comments'] == 1 && $settings['gal_comments'] == 1 && $galinfo['comments'] == 1){
	            //Template einbinden
	            include($tempdir."comments_head.html");
	
	            $comment_sites = 0;
				$message = 0;
	            // Neuen Kommentar hinzuf�gen
	            if(isset($_POST['send_comment']) && $_POST['send_comment'] == 1 &&
				   isset($_POST['modul_comment']) && $_POST['modul_comment'] == $modul)
					$message = insert_Comment($_POST['autor'],$_POST['email'],$_POST['url'],$_POST['comment'],$_POST['antispam'],$_POST['deaktiv_bbc'],$galid,$_POST['uid'],$picid);
	
	            // KOMMENTARE AUSGEBEN
	            $nr = 1;
	            $comment_query = "SELECT * FROM ".$mysql_tables['comments']." WHERE modul='".$modul."' AND postid='".$galid."' AND subpostid='".$picid."' AND frei='1' ORDER BY timestamp ".mysql_real_escape_string($comment_desc)."";
	
				// Seiten-Funktion
	            if($settings['comments_perpage'] > 0){
	                $comment_sc = mysql_num_rows(mysql_query($comment_query));
	                $comment_sites = ceil($comment_sc/$settings['comments_perpage']);    //=Anzahl an Seiten
	
	                if(isset($_GET[$names['cpage']]) && $_GET[$names['cpage']] == "last" && $comment_sites > 1){
	                    $_GET[$names['cpage']] = $comment_sites;
	                    $commentsstart = $comment_sites*$settings['comments_perpage']-$settings['comments_perpage'];
	                    $comment_query .= " LIMIT ".mysql_real_escape_string($commentsstart).",".mysql_real_escape_string($settings['comments_perpage'])."";
	                    $nr = $commentsstart+1;
	                    }
	                elseif(isset($_GET[$names['cpage']]) && !empty($_GET[$names['cpage']]) && $_GET[$names['cpage']] <= $comment_sites && $comment_sites > 1){
	                    $commentsstart = $_GET[$names['cpage']]*$settings['comments_perpage']-$settings['comments_perpage'];
	                    $comment_query .= " LIMIT ".mysql_real_escape_string($commentsstart).",".mysql_real_escape_string($settings['comments_perpage'])."";
	                    $nr = $commentsstart+1;
	                    }
	                else
	                    $comment_query .= " LIMIT ".mysql_real_escape_string($settings['comments_perpage'])."";
	                }
	
	            $clist = mysql_query($comment_query);
	            while($crow = mysql_fetch_array($clist)){
	
					// URL
	                if(!empty($crow['url']) && $crow['url'] != "http://"){
	                    if(substr_count($crow['url'], "http://") < 1)
							$url = "http://".stripslashes($crow['url']);
						else
							$url = stripslashes($crow['url']);
	                    }
	                else $url = "";
	
	                // Weitere Variablen f�r die Template-Ausgabe aufbereiten
	                $datum = date("d.m.y - G:i",$crow['timestamp']);
	                $autorenname = stripslashes($crow['autor']);
	                $comment_id = $crow['id'];
	
	                // BB-Code & Smilies
	                $comment = stripslashes($crow['comment']);
	                if($crow['bbc'] == 1 && $settings['comments_bbc'] == 1 && $crow['smilies'] == 1 && $settings['comments_smilies'] == 1)
						$comment = bb_code_comment($comment,1,1,1);
					elseif($crow['bbc'] == 1 && $settings['comments_bbc'] == 1 && ($crow['smilies'] == 0 || $settings['comments_smilies'] == 0))
						$comment = bb_code_comment($comment,1,1,0);
					elseif(($crow['bbc'] == 0 || $settings['comments_bbc'] == 0) && $crow['smilies'] == 1 && $settings['comments_smilies'] == 1)
						$comment = bb_code_comment($comment,1,0,1);
					else
						$comment = nl2br($comment);
	
	                // Template ausgeben
	                include($tempdir."commentbit.html");
	
	                $nr++;
	                }
	
	            //Seiten (Kommentare) ausgeben
	            if($comment_sites > 1 && $settings['comments_perpage'] > 0){
	                if(isset($_GET[$names['cpage']]) && $_GET[$names['cpage']] > 1){
	                    $c_sz = $_GET[$names['cpage']]-1;
	                    if($c_sz > 1)
							$c_szl1 = $system_link_pic."&amp;".$names['picid']."=".$picid."&amp;".$names['cpage']."=1#01jumpcomments";
	                    $c_szl2 = $system_link_pic."&amp;".$names['picid']."=".$picid."&amp;".$names['cpage']."=".$c_sz."#01jumpcomments";
	                    }
	                else{ $c_szl1 = ""; $c_szl2 = ""; }
	
					if(!isset($_GET[$names['cpage']]) || isset($_GET[$names['cpage']]) && empty($_GET[$names['cpage']]))
	                    {
	                    $comment_current = 1;
	                    if($comment_sites > 1) $c_sv = 2;
	                    }
	                else{
	                    $comment_current = $_GET[$names['cpage']];
	                    $c_sv = $_GET[$names['cpage']]+1;
	                    }
	
					if(isset($_GET[$names['cpage']]) && $_GET[$names['cpage']] < $comment_sites || !isset($_GET[$names['cpage']]) && $comment_sites > 1){
	                    $c_svl1 = $system_link_pic."&amp;".$names['picid']."=".$picid."&amp;".$names['cpage']."=".$c_sv."#01jumpcomments";
	                    if($c_sv != $comment_sites)
							$c_svl2 = $system_link_pic."&amp;".$names['picid']."=".$picid."&amp;".$names['cpage']."=".$comment_sites."#01jumpcomments";
	                    }
	                else{ $c_svl1 = ""; $c_svl2 = ""; }
	                }
	
	            //Template ausgeben
	            include($tempdir."comments_end.html");
	
	            //Unterschiedliche Sprungmarken nach Absenden des Kommentarformulars
	            if(!isset($jumpto_id)) $jumpto_id = "";
				if($settings['commentfreischaltung'] == 0) $jumpto = "01comment".$jumpto_id; else $jumpto = "01jumpcomments";
	            if($comment_desc == "") $jumpto_csite = "last"; else $jumpto_csite = "1";
	
	            $system_link_form = $system_link_pic."&amp;".$names['picid']."=".$picid."&amp;".$names['cpage']."=".$c_sz."&amp;".$names['cpage']."=".$jumpto_csite."#01jumpcomments_add";
	
	
	            if($galinfo['comments'] == 1 && $settings['comments'] == 1){
	                mt_srand((double)microtime()*1000000);
					$zahl = mt_rand(1, 9999999999999);
					$uid = md5(time().$_SERVER['REMOTE_ADDR'].$zahl.$galid.$picid);
					//Template ausgeben
	                include($tempdir."comments_add.html");
	                }
	            }
			}
		else
			include_once($tempdir."passwordbox.html");
			
	    }
	else{ // Passwort-Box anzeigen
		_01gallery_echoBreadcrumps($galid);
		include_once($tempdir."passwordbox.html");
		}
}








// Display: Thumbnail-Ansicht
elseif(is_numeric($galid) && $galid > 0){
	
	$system_link_form = $system_link_gal;
	
	// Galerie-Infos aus Datenbank holen
	$list = mysql_query("SELECT id,timestamp,password,galeriename,beschreibung,galpic,anzahl_pics FROM ".$mysql_tables['gallery']." WHERE id = '".mysql_real_escape_string($galid)."' AND hide='0' LIMIT 1");
	$galinfo = mysql_fetch_assoc($list);
	$galverz = $galdir._01gallery_getGalDir($galinfo['id'],$galinfo['password'])."/";
	
	// Login-Formular abgeschickt?
	if(!empty($galinfo['password']) && isset($_POST['login2gal']) && isset($_POST['galpw']) && !empty($_POST['galpw'])){
		if($_POST['galpw'] == $galinfo['password'] && !in_array(pwhashing($galinfo['password'].$salt),$pwcookie)){
			$pwcookie[] = pwhashing($galinfo['password'].$salt);
			echo set_a_cookie("c_".$modul."_galpwcookie", implode("|",$pwcookie),time()+60*60*24);
			}
		elseif($_POST['galpw'] != $galinfo['password']){
			$errormsg = "<p class=\"errormsg\">Das eingegebene Passwort ist leider falsch.</p>";
			include_once($tempdir."passwordbox.html");
			}
		}
	
	// Inhalt der Galerie ausgeben (Passwort vorhanden?)
	if(!empty($galinfo['password']) && isset($pwcookie) && is_array($pwcookie) && in_array(pwhashing($galinfo['password'].$salt),$pwcookie) ||
	   empty($galinfo['password'])){
	    
		$accesserror = _01gallery_echoBreadcrumps($galid);
		if($accesserror == 0){
		
			// Subgalerien anzeigen
		    _01gallery_echoGalList($galid);
		    
		    // Thumbnails auflisten
		    $query = "SELECT id,filename,title,text FROM ".$mysql_tables['pics']." WHERE galid = '".mysql_real_escape_string($galid)."' ORDER BY sortorder DESC";
			makepages($query,$sites2,$names['picpage'],$settings['thumbs_per_page']);
	
			if($settings['pics_per_line'] == "auto") $class = " class=\"stream\"";
			else $class = "";
			
			echo "\n\n<ul class=\"cssgallery\">\n";
		    $list = mysql_query($query);
			while($pics = mysql_fetch_assoc($list)){
				if($settings['use_lightbox'] == 2)
					echo "<li".$class."><a href=\"".$galverz.$pics['filename']."\" rel=\"lightbox-gal".$galid."set\" title=\"".strip_tags(stripslashes($pics['title']))." - ".strip_tags(stripslashes($pics['text']))."\">"._01gallery_getThumb($galverz,stripslashes($pics['filename']),"_tb")."</a></li>\n";
				else
					echo "<li".$class."><a href=\"".addParameter2Link($system_link_pic,$names['picid']."=".$pics['id'])."\">"._01gallery_getThumb($galverz,stripslashes($pics['filename']),"_tb")."</a></li>\n";
				}
			echo "</ul>\n\n";
			
			echo echopages($sites2,"",$names['picpage'],$names['galid']."=".$galid."&amp;".$names['galpage']."=".$galpage,"picpagestable");
			}
		else{
			$system_link_form = addParameter2Link($filename,$names['galid']."=".$accesserror);
			include_once($tempdir."passwordbox.html");
			}
	    }
	else{ // Passwort-Box anzeigen
		_01gallery_echoBreadcrumps($galid);
		include_once($tempdir."passwordbox.html");
		}
}










// Display: Auflistung der Galerien
else{

	_01gallery_echoGalList(0);

	}







// Main_bottom einf�gen
include($tempdir."main_bottom.html");

// 01-Gallery V2 Copyright 2003-2008 by Michael Lorer - 01-Scripts.de
?>