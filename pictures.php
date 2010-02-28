<?PHP
/* 
	01-Gallery V2 - Copyright 2003-2009 by Michael Lorer - 01-Scripts.de
	Lizenz: Creative-Commons: Namensnennung-Keine kommerzielle Nutzung-Weitergabe unter gleichen Bedingungen 3.0 Deutschland
	Weitere Lizenzinformationen unter: http://www.01-scripts.de/lizenz.php
	
	Modul:		01gallery
	Dateiinfo: 	Bilder einer Galerie auflisten, bearbeiten und sortieren
	#fv.2001#
*/



// Bilder sortieren
if(isset($_GET['action']) && $_GET['action'] == "sort_pics" &&
    isset($_GET['galid']) && !empty($_GET['galid']) && is_numeric($_GET['galid']) && $userdata['editgal'] > 0){
	// Zugriffsberechtigung und ggf. Passwortänderung überprüfen
	$list = mysql_query("SELECT password,galeriename,uid FROM ".$mysql_tables['gallery']." WHERE id = '".mysql_real_escape_string($_GET['galid'])."' LIMIT 1");
	$statrow = mysql_fetch_assoc($list);

	if($userdata['editgal'] == 2 || $userdata['editgal'] == 1 && $statrow['uid'] == $userdata['id']){
	   $dir = _01gallery_getGalDir($_GET['galid'],$statrow['password']);
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

		<p class="meldung_hinweis">
			Klicken und ziehen Sie die Bilder in die gew&uuml;nschte Reihenfolge. Klicken Sie anschlie&szlig;end auf <i>Speichern</i>.<br />
			<b>F&uuml;r Internet Explorer 6:</b> Halten Sie die Maustaste gedr&uuml;ckt und ziehen Sie das Bild kurz. Lassen Sie dann die Maustaste los.<br />
			Sie k&ouml;nnen nun das Bild an die gew&uuml;nschte Position bewegen. Klicken Sie 1x um das Bild dort zu platzieren.
		</p>
		
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
        $list = mysql_query("SELECT id,sortorder,filename FROM ".$mysql_tables['pics']." WHERE galid = '".mysql_real_escape_string($_GET['galid'])."' ORDER BY sortorder DESC");
		while($row = mysql_fetch_assoc($list)){
            echo "<li id=\"".$row['id']."\"><a href=\"#foo\">"._01gallery_getThumb($modulpath.$galdir.$dir."/",stripslashes($row['filename']),"_acptb")."</a></li>\n";
            }
        echo "</ul>";
        }
    }


	
	
	
	
	
	
	

// In Galerie enthaltene Bilder auflisten
elseif(isset($_GET['action']) && $_GET['action'] == "show_pics" &&
    isset($_GET['galid']) && !empty($_GET['galid']) && is_numeric($_GET['galid']) && $userdata['editgal'] > 0){
	// Zugriffsberechtigung und ggf. Passwortänderung überprüfen
	$list = mysql_query("SELECT password,galeriename,galpic,uid FROM ".$mysql_tables['gallery']." WHERE id = '".mysql_real_escape_string($_GET['galid'])."' LIMIT 1");
	$statrow = mysql_fetch_assoc($list);
	
	if($userdata['editgal'] == 2 || $userdata['editgal'] == 1 && $statrow['uid'] == $userdata['id']){
	
	$dir = _01gallery_getGalDir($_GET['galid'],$statrow['password']);
	$artuserdata = getUserdatafields_Queryless("username");
	
	// Formular abgesende? --> Speichern
	if(isset($_POST['send']) && $_POST['send'] == 1){
        if(isset($_POST['cover']) && !empty($_POST['cover']) && is_numeric($_POST['cover'])){
            mysql_query("UPDATE ".$mysql_tables['gallery']." SET galpic='".mysql_real_escape_string($_POST['cover'])."' WHERE id = '".mysql_real_escape_string($_GET['galid'])."'");
            $statrow['galpic'] = $_POST['cover'];
            }

	    }
?>
		<h2><?PHP echo stripslashes($statrow['galeriename']); ?> &raquo; Bilder bearbeiten</h2>

        <?PHP echo  _01gallery_echoActionButtons_Gal(); ?>

        <table border="0" align="center" width="100%" cellpadding="3" cellspacing="5" class="rundrahmen">
	    <tr>
			<td class="tra" width="100" colspan="2"><b>Coverbild</b><!--Thumbnail--></td>
	        <td class="tra" width="110"><b>Datum</b></td>
			<td class="tra"><b>Bildtitel / Beschreibung</b></td>
			<td class="tra"><b>original Dateiname / aktueller Dateiname</b></td>
			<td class="tra"><b>Benutzer</b></td>
			<td class="tra" width="25" align="center"><!--Löschen--><img src="images/icons/icon_trash.gif" alt="M&uuml;lleimer" title="Datei l&ouml;schen" /></td>
	    </tr>
<?PHP
		$sites = 0;
		$query = "SELECT id,sortorder,timestamp,orgname,filename,title,text,uid FROM ".$mysql_tables['pics']." WHERE galid = '".mysql_real_escape_string($_GET['galid'])."' ORDER BY sortorder DESC";
		$query = makepages($query,$sites,"site",ACP_PER_PAGE2);
	
		$count = 0;
		$list = mysql_query($query);
		while($row = mysql_fetch_assoc($list)){
			if($count == 1){ $class = "tra"; $count--; }else{ $class = "trb"; $count++; }
			
			if(!empty($row['text'])) $text = "<br />".substr(htmlentities(stripslashes($row['text'])),0,100);
			else $text = "";
			
			// Coverbild?
			if($row['id'] == $statrow['galpic']) $checked = " checked=\"checked\"";
			else $checked = "";
			
			echo "
		<tr id=\"id".$row['id']."\">
			<td class=\"".$class."\" align=\"center\"><input type=\"radio\" name=\"cover\" value=\"".$row['id']."\"".$checked." onclick=\"AjaxRequest.send('modul=".$modul."&ajaxaction=setnewcover&id=".$row['id']."&galid=".$_GET['galid']."');\" />
            <td class=\"".$class."\" align=\"center\"><a href=\"".$modulpath.$galdir.$dir."/".stripslashes($row['filename'])."\" class=\"lightbox\">"._01gallery_getThumb($modulpath.$galdir.$dir."/",stripslashes($row['filename']),"_acptb")."</a></td>
			<td class=\"".$class."\">".date("d.m.Y - G:i",$row['timestamp'])."</td>
			<td class=\"".$class."\">
            <div style=\"float:right;\">
                <a href=\"javascript:hide_unhide('hide_show_".$row['id']."'); hide_unhide('hide_edit_".$row['id']."');\"><img src=\"images/icons/icon_edit.gif\" alt=\"Bearbeiten - Stift\" title=\"Bild bearbeiten\" /></a>
            </div>
			<form id=\"editform_".$row['id']."\" action=\"_ajaxloader.php?modul=".$modul."&ajaxaction=savepicdata&id=".$row['id']."\" method=\"post\">
            <div id=\"hide_show_".$row['id']."\" style=\"display:block;\">
                <b>".stripslashes($row['title'])."</b>".$text."
            </div>
			<div id=\"hide_edit_".$row['id']."\" style=\"display:none;\">
                <input type=\"text\" size=\"26\" name=\"title\" value=\"".stripslashes($row['title'])."\" /><br />
                <textarea name=\"beschreibung\" cols=\"25\" rows=\"3\" class=\"input_textarea\" style=\"font-size: 11px;\">".stripslashes($row['text'])."</textarea><br />
                <input type=\"reset\" value=\"Zur&uuml;cksetzen\" class=\"input\" /> <input type=\"submit\" value=\"Speichern\" class=\"input\" />
            </div>
            </form>

            <script type=\"text/javascript\">
            $('editform_".$row['id']."').addEvent('submit', function(e) {
        		e.stop();
        		
                this.set('send', {onComplete: function(response) {
        			$('hide_show_".$row['id']."').set('html', response);
        		}, evalScripts: true});
        		Start_Loading_standard();
        		this.send();
        	});
            </script>
            </td>
			<td class=\"".$class."\">".stripslashes($row['orgname'])."<br />".stripslashes($row['filename'])."</td>
			<td class=\"".$class."\">".$artuserdata[$row['uid']]['username']."</td>
			<td class=\"".$class."\" align=\"center\"><img src=\"images/icons/icon_delete.gif\" alt=\"L&ouml;schen - rotes X\" title=\"Eintrag l&ouml;schen\" class=\"fx_opener\" style=\"border:0; float:left;\" align=\"left\" /><div class=\"fx_content tr_red\" style=\"width:60px; display:none;\"><a href=\"#foo\" onclick=\"AjaxRequest.send('modul=".$modul."&ajaxaction=delpic&id=".$row['id']."');\">Ja</a> - <a href=\"#foo\">Nein</a></div></td>
		</tr>			
			";
			}
		echo "</table>";
		
		echo echopages($sites,"80%","site","action=show_pics&amp;galid=".$_GET['galid']);	
		}
	else $flag_loginerror = true;
	
	}
//elseif...
















// 01-Gallery V2 Copyright 2006-2009 by Michael Lorer - 01-Scripts.de
?>