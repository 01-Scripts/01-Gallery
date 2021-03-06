<?PHP
/* 
	01-Gallery - Copyright 2003-2014 by Michael Lorer - 01-Scripts.de
	Lizenz: Creative-Commons: Namensnennung-Keine kommerzielle Nutzung-Weitergabe unter gleichen Bedingungen 3.0 Deutschland
	Weitere Lizenzinformationen unter: http://www.01-scripts.de/lizenz.php
	
	Modul:		01gallery
	Dateiinfo: 	Bilder einer Galerie auflisten, bearbeiten und sortieren
	#fv.212#
*/



// Bilder sortieren
if(isset($_GET['action']) && $_GET['action'] == "sort_pics" &&
    isset($_GET['galid']) && !empty($_GET['galid']) && is_numeric($_GET['galid']) && $userdata['editgal'] > 0){
	// Zugriffsberechtigung und ggf. Passwort�nderung �berpr�fen
	$list = $mysqli->query("SELECT galpassword,galeriename,uid FROM ".$mysql_tables['gallery']." WHERE id = '".$mysqli->escape_string($_GET['galid'])."' LIMIT 1");
	$statrow = $list->fetch_assoc();

	if($userdata['editgal'] == 2 || $userdata['editgal'] == 1 && $statrow['uid'] == $userdata['id']){
	   $dir = _01gallery_getGalDir($_GET['galid'],$statrow['galpassword']);
?>
        <h2><?PHP echo stripslashes($statrow['galeriename']); ?> &raquo; Bilder sortieren</h2>

        <?PHP echo  _01gallery_echoActionButtons_Gal(); ?>
        
        <h3>Automatische Sortierung</h3>
        
        <form id="saveautosortform" action="_ajaxloader.php?modul=<?PHP echo $modul; ?>&ajaxaction=saveautosortorder&id=<?PHP echo $_GET['galid']; ?>" method="post">
        <select name="sortorder" size="1" class="input_select">
			<option value="az">Nach Dateinamen (A-&gt;Z) sortieren</option>
			<option value="za">Nach Dateinamen (Z-&gt;A) sortieren</option>
			<option value="taz">Nach Titel (A-&gt;Z) sortieren</option>
			<option value="tza">Nach Titel (Z-&gt;A) sortieren</option>
			<option value="timeup">Nach Upload-Datum (jung nach alt) sortieren</option>
			<option value="timedown">Nach Upload-Datum (alt nach jung) sortieren</option>
		</select>
        <input type="submit" value="Bilder jetzt sortieren" class="input" />
        </form>
        
        <script type="text/javascript">
        $('saveautosortform').addEvent('submit', function(e) {
        e.stop();

        this.set('send', {onComplete: function(response) {
            $('answer').set('html', response);
            }, evalScripts: true});
        Start_Loading_standard();
        this.send();
        });
        </script>
        
        <h3>Individuelle Sortierung</h3>
		
		<form id="savesortform" action="_ajaxloader.php?modul=<?PHP echo $modul; ?>&ajaxaction=savesortorder&id=<?PHP echo $_GET['galid']; ?>" method="post">
        <input type="hidden" value="" name="sortdatafield" id="sortdatafield" />
        <input type="submit" value="Eigene Reihenfolge Speichern" class="input" />
        </form>

        <div id="answer"></div>

        <script type="text/javascript">
        $('savesortform').addEvent('submit', function(e) {
        e.stop();

        this.set('send', {onComplete: function(response) {
            $('answer').set('html', response);
            }, evalScripts: true});
        Start_Loading_standard();
        this.send();
        });
        </script>

<?PHP   
        echo "<ul class=\"cssgallery\" id=\"sortliste\">\n";
        $list = $mysqli->query("SELECT id,sortorder,filename FROM ".$mysql_tables['pics']." WHERE galid = '".$mysqli->escape_string($_GET['galid'])."' ORDER BY sortorder DESC");
		while($row = $list->fetch_assoc()){
            echo "<li id=\"".$row['id']."\"><a href=\"#foo\">"._01gallery_getThumb($modulpath.$galdir.$dir."/",stripslashes($row['filename']),"_acptb")."</a></li>\n";
            }
        echo "</ul>";
        }
    }


	
	
	
	
	
	
	

// In Galerie enthaltene Bilder auflisten
elseif(isset($_GET['action']) && $_GET['action'] == "show_pics" &&
    isset($_GET['galid']) && !empty($_GET['galid']) && is_numeric($_GET['galid']) && $userdata['editgal'] > 0){
	// Zugriffsberechtigung und ggf. Passwort�nderung �berpr�fen
	$list = $mysqli->query("SELECT galpassword,galeriename,galpic,uid FROM ".$mysql_tables['gallery']." WHERE id = '".$mysqli->escape_string($_GET['galid'])."' LIMIT 1");
	$statrow = $list->fetch_assoc();
	
	if($userdata['editgal'] == 2 || $userdata['editgal'] == 1 && $statrow['uid'] == $userdata['id']){
	
	$dir = _01gallery_getGalDir($_GET['galid'],$statrow['galpassword']);
	$artuserdata = getUserdatafields_Queryless("username");
	
	if(!isset($_POST['title_all'])) $_POST['title_all'] = "";
	if(!isset($_POST['beschreibung_all'])) $_POST['beschreibung_all'] = "";
	
	// Beschreibung selektierter Bilder bearbeiten:
	if(isset($_POST['selectids']) && !empty($_POST['selectids']) &&
	   isset($_POST['massedit']) && $_POST['massedit'] == 1){
		
		if(!empty($_POST['title_all']))
	        $title = "title = '".$mysqli->escape_string($_POST['title_all'])."'";
		else
			$title = ""; 

		if(!empty($_POST['beschreibung_all']))
	        $beschreibung = "pictext = '".$mysqli->escape_string($_POST['beschreibung_all'])."'";
		else
			$beschreibung = "";
			
		if(!empty($_POST['title_all']) && !empty($_POST['beschreibung_all']))
		    $seperator = ", ";
		else
			$seperator = "";

		$mysqli->query("UPDATE ".$mysql_tables['pics']." SET ".$title.$seperator.$beschreibung." WHERE id IN (".$mysqli->escape_string(implode(",",$_POST['selectids'])).")");

		echo "<p class=\"meldung_erfolg\">Bilder wurden bearbeitet</p>";
		}	
	// Selektierte Bilder l�schen:
	elseif(isset($_POST['selectids']) && !empty($_POST['selectids']) &&
	   isset($_POST['delselected']) && $_POST['delselected'] == 1){
		$cup = 0;
		$list = $mysqli->query("SELECT id,filename FROM ".$mysql_tables['pics']." WHERE id IN (".$mysqli->escape_string(implode(",",$_POST['selectids'])).")");
		while($drow = $list->fetch_assoc()){
			
			$dir = _01gallery_getGalDir($_GET['galid'],stripslashes($statrow['galpassword']));
	        $split = pathinfo($drow['filename']);
	
	        @unlink($modulpath.$galdir.$dir."/".$drow['filename']);
	        @unlink($modulpath.$galdir.$dir."/".$split['filename']."_big.".$split['extension']);
	        @unlink($modulpath.$galdir.$dir."/".$split['filename']."_tb.".$split['extension']);
	        @unlink($modulpath.$galdir.$dir."/".$split['filename']."_acptb.".$split['extension']);
	
	        $mysqli->query("DELETE FROM ".$mysql_tables['pics']." WHERE id='".$mysqli->escape_string($drow['id'])."'");
	
			$cup++;
			}
		
		$mysqli->query("UPDATE ".$mysql_tables['gallery']." SET galpic = 0 WHERE id='".$mysqli->escape_string($_GET['galid'])."' AND galpic IN (".$mysqli->escape_string(implode(",",$_POST['selectids'])).")");
		_01gallery_countPics($_GET['galid']);
		echo "<p class=\"meldung_erfolg\">Es wurden ".$cup." Bilder gel&ouml;scht</p>";
		}
?>
		<h2><?PHP echo stripslashes($statrow['galeriename']); ?> &raquo; Bilder bearbeiten</h2>

        <?PHP echo  _01gallery_echoActionButtons_Gal(); ?>

        <form action="<?php echo addParameter2Link($filename,"action=show_pics&galid=".$_GET['galid']); ?>" method="post">
		<table border="0" align="center" width="100%" cellpadding="3" cellspacing="5" class="rundrahmen">
	    <tr>
			<td class="tra" style="width:25px;" align="center"><input type="checkbox" name="select" id="selector" onclick="SelectAll('selector','input.dcb');" /><!--L�schung--></td>
			<td class="tra" style="width:100px;" colspan="2"><b>Coverbild</b><!--Thumbnail--></td>
	        <td class="tra" style="width:110px;"><b>Datum</b></td>
			<td class="tra"><b>Bildtitel / Beschreibung</b></td>
			<td class="tra"><b>original Dateiname / aktueller Dateiname</b></td>
			<td class="tra"><b>Benutzer</b></td>
			<td class="tra" style="width:25px;" align="center"><!--L�schen--><img src="images/icons/icon_trash.gif" alt="M&uuml;lleimer" title="Datei l&ouml;schen" /></td>
	    </tr>
<?PHP
		$sites = 0;
		$query = "SELECT id,sortorder,pictimestamp,orgname,filename,title,pictext,uid FROM ".$mysql_tables['pics']." WHERE galid = '".$mysqli->escape_string($_GET['galid'])."' ORDER BY sortorder DESC";
		$query = makepages($query,$sites,"site",ACP_PER_PAGE2);
	
		$count = 0;
		$list = $mysqli->query($query);
		while($row = $list->fetch_assoc()){
			if($count == 1){ $class = "tra"; $count--; }else{ $class = "trb"; $count++; }
			
			if(!empty($row['pictext'])) $text = "<br />".substr(htmlentities(stripslashes($row['pictext']),$htmlent_flags,$htmlent_encoding_acp),0,100);
			else $text = "";
			
			// Coverbild?
			if($row['id'] == $statrow['galpic']) $checked = " checked=\"checked\"";
			else $checked = "";
			
			// Bigpicture?
			$split = explode(".",stripslashes($row['filename']));
            if(file_exists($modulpath.$galdir.$dir."/".$split[0]."_big.".$split[1])) $big_link = "<a href=\"".$modulpath.$galdir.$dir."/".$split[0]."_big.".$split[1]."\" title=\"Unkomprimierte Original-Datei\" target=\"_blank\">".stripslashes($row['orgname'])."</a>";
            else $big_link = stripslashes($row['orgname']);
			
			echo "
		<tr id=\"id".$row['id']."\">
			<td class=\"".$class."\" align=\"center\"><input type=\"checkbox\" name=\"selectids[]\" value=\"".$row['id']."\" class=\"dcb\" /></td>
			<td class=\"".$class."\" align=\"center\"><input type=\"radio\" name=\"cover\" value=\"".$row['id']."\"".$checked." onclick=\"AjaxRequest.send('modul=".$modul."&ajaxaction=setnewcover&id=".$row['id']."&galid=".$_GET['galid']."');\" /></td>
            <td class=\"".$class."\" align=\"center\"><a href=\"".$modulpath.$galdir.$dir."/".stripslashes($row['filename'])."\" class=\"lightbox\">"._01gallery_getThumb($modulpath.$galdir.$dir."/",stripslashes($row['filename']),"_acptb")."</a></td>
			<td class=\"".$class."\">".date("d.m.Y - G:i",$row['pictimestamp'])."</td>
			<td class=\"".$class."\">
            <div style=\"float:right;\">
                <a href=\"javascript:hide_unhide('hide_show_".$row['id']."'); hide_unhide('hide_edit_".$row['id']."');\"><img src=\"images/icons/icon_edit.gif\" alt=\"Bearbeiten - Stift\" title=\"Bild bearbeiten\" /></a>
            </div>

            <div id=\"hide_show_".$row['id']."\" style=\"display:block;\">
                <b>".stripslashes($row['title'])."</b>".$text."
            </div>
			<div id=\"hide_edit_".$row['id']."\" style=\"display:none;\">
                <input type=\"text\" size=\"26\" name=\"title_".$row['id']."\" id=\"title_".$row['id']."\" value=\"".stripslashes($row['title'])."\" class=\"pic_title\" /><br />
                <textarea name=\"beschreibung_".$row['id']."\" id=\"beschreibung_".$row['id']."\" cols=\"25\" rows=\"3\" class=\"input_textarea pic_descr\">".stripslashes($row['pictext'])."</textarea><br />
                <button type=\"button\" class=\"input\" onclick=\"SendPicFormData('".$row['id']."','modul=".$modul."&ajaxaction=savepicdata&id=".$row['id']."&title='+document.id('title_".$row['id']."').get('value')+'&beschreibung='+document.id('beschreibung_".$row['id']."').get('value'));\">Speichern</button>
            </div>

            </td>
			<td class=\"".$class."\">".$big_link."<br />".stripslashes($row['filename'])."</td>
			<td class=\"".$class."\">".$artuserdata[$row['uid']]['username']."</td>
			<td class=\"".$class."\" align=\"center\"><img src=\"images/icons/icon_delete.gif\" alt=\"L&ouml;schen - rotes X\" title=\"Eintrag l&ouml;schen\" class=\"fx_opener\" style=\"border:0; float:left;\" align=\"left\" /><div class=\"fx_content tr_red\" style=\"width:60px; display:none;\"><a href=\"#foo\" onclick=\"AjaxRequest.send('modul=".$modul."&ajaxaction=delpic&id=".$row['id']."');\">Ja</a> - <a href=\"#foo\">Nein</a></div></td>
		</tr>			
			";
			}
		if($count == 1){ $class = "tra"; $count--; }else{ $class = "trb"; $count++; }
		echo "
		<tr id=\"massedit_tr\">
			<td class=\"".$class."\" align=\"center\"><input type=\"checkbox\" name=\"massedit\" value=\"1\" onclick=\"hide_unhide('hide_massedit');hide_unhide_tr('massdel_tr');\" /></td>
			<td class=\"".$class."\" align=\"center\"><img src=\"images/icons/icon_edit.gif\" alt=\"Bearbeiten - Stift\" title=\"Bitte klicken Sie auf die Checkbox um die gew&auml;hlten Bilder zu bearbeiten\" /></td>
			<td class=\"".$class."\" colspan=\"6\">
				<b>Titel &amp; Beschreibung der gew&auml;hlten Bilder &auml;ndern</b><br /> 
				<div id=\"hide_massedit\" style=\"display:none;\">
				<b>Titel:</b> <input type=\"text\" size=\"26\" name=\"title_all\" value=\"".$_POST['title_all']."\" style=\"width: 265px;\" /><br />
                <b>Beschreibung:</b><br /><textarea name=\"beschreibung_all\" cols=\"30\" rows=\"3\" class=\"input_textarea\" style=\"font-size: 11px; width:300px;\">".$_POST['beschreibung_all']."</textarea><br />
				<br />
				<input type=\"submit\" value=\"Text f&uuml;r gew&auml;hlte Bilder aktualisieren\" class=\"input\" />
				</div>
			</td>
		</tr>";
		if($count == 1){ $class = "tra"; $count--; }else{ $class = "trb"; $count++; }
		echo "
		<tr id=\"massdel_tr\">
			<td class=\"".$class."\" align=\"center\"><input type=\"checkbox\" name=\"delselected\" value=\"1\" onclick=\"hide_unhide('hide_massdel');hide_unhide_tr('massedit_tr');\" /></td>
			<td class=\"".$class."\" align=\"center\"><img src=\"images/icons/icon_trash.gif\" alt=\"M&uuml;lleimer\" title=\"Bitte klicken Sie auf die Checkbox um die gew&auml;hlten Bilder zu l&ouml;schen\" /></td>
			<td class=\"".$class."\" colspan=\"6\">
				<b>Gew&auml;hlte Bilder l&ouml;schen</b><br />
				<div id=\"hide_massdel\" style=\"display:none;\">
				<input type=\"submit\" value=\"Gew&auml;hlte Bilder l&ouml;schen\" class=\"input\" />
				Es erfolgt <b>keine</b> weitere Abfrage!
				</div>
			</td>
		</tr>";
		 
		echo "</table>\n</form>";
		
		echo echopages($sites,"80%","site","action=show_pics&amp;galid=".$_GET['galid']);	
		}
	else $flag_loginerror = true;
	
	}

?>