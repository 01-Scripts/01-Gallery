<?PHP
/* 
	01-Gallery V2 - Copyright 2003-2010 by Michael Lorer - 01-Scripts.de
	Lizenz: Creative-Commons: Namensnennung-Keine kommerzielle Nutzung-Weitergabe unter gleichen Bedingungen 3.0 Deutschland
	Weitere Lizenzinformationen unter: http://www.01-scripts.de/lizenz.php
	
	Modul:		01gallery
	Dateiinfo: 	Bildergalerie ACP-Startseite = Galerien-Übersicht & Galerien anlegen
	#fv.2002#
*/


// Neue Galerie anlegen (Datenbankeintrag)
if(isset($_POST['do']) && $_POST['do'] == "add_gal" &&
   isset($_POST['galeriename']) && !empty($_POST['galeriename']) && $userdata['newgal'] == 1){
    // Höchste momentane Sortorder bestimmen:
	$list = mysql_query("SELECT sortid FROM ".$mysql_tables['gallery']." WHERE subof = '".mysql_real_escape_string($_POST['subof'])."' ORDER BY sortid DESC LIMIT 1");
	while($row = mysql_fetch_array($list)){
		$new_sortid = ($row['sortid']+1);
		}
	
	if(isset($_POST['comments']) && $_POST['comments'] == 1) $comments = 0;
	else $comments = 1;
	if(!isset($_POST['hide']) || isset($_POST['hide']) && empty($_POST['hide'])) $_POST['hide'] = 0;
	if(!isset($_POST['galeriename']) || isset($_POST['galeriename']) && empty($_POST['galeriename'])) $_POST['galeriename'] = "";
	
    //Eintragung in Datenbank vornehmen:
	$sql_insert = "INSERT INTO ".$mysql_tables['gallery']." (subof,sortid,timestamp,password,galeriename,beschreibung,galpic,anzahl_pics,uid,comments,hide) VALUES (
				'".mysql_real_escape_string($_POST['subof'])."',
				'".$new_sortid."',
				'".time()."',
				'".mysql_real_escape_string($_POST['password'])."',
				'".mysql_real_escape_string($_POST['galeriename'])."',
				'".mysql_real_escape_string(stripslashes($_POST['textfeld']))."', 
				'', 
				'0', 
				'".$userdata['id']."',
				'".$comments."',
				'".mysql_real_escape_string($_POST['hide'])."'
				)";
	mysql_query($sql_insert) OR die(mysql_error());
	$galid = mysql_insert_id();
	
	if(isset($_POST['password']) && !empty($_POST['password']))
		$newgaldir = _01gallery_getGalDir($galid,$_POST['password']);
	else
		$newgaldir = _01gallery_getGalDir($galid);
	
	// Verzeichnis generieren
	if(isset($galid) && $galid > 0){
		//umask(2);					// ist nötig, damit aus 0777 letztlich 0775 wird
		if(mkdir($modulpath.$galdir.$newgaldir, 0777)){
			@clearstatcache();
        	@chmod($modulpath.$galdir.$newgaldir, 0777);
			echo "<p class=\"meldung_erfolg\">Das neue Bilderalbum <b>".stripslashes($_POST['galeriename'])."</b> wurde erfolgreich angelegt.<br />
					<a href=\"_loader.php?modul=".$modul."&amp;loadpage=upload&amp;action=upload_pic&amp;galid=".$galid."\">Jetzt Bilder hochladen oder importieren &raquo;</a></p>";
			}
		}
	}
elseif(isset($_POST['do']) && $_POST['do'] == "add_gal" &&
   isset($_POST['galeriename']) && !empty($_POST['galeriename']))
	$flag_loginerror = true;
elseif(isset($_POST['do']) && $_POST['do'] == "add_gal")
	echo "<p class=\"meldung_error\"><b>Fehler:</b> Sie haben nicht alle ben&ouml;tigten Felder ausgef&uuml;llt<br />
			Bitte gehen Sie <a href=\"javascript:history.back();\">zur&uuml;ck</a>.</p>";


	






// Galerie bearbeiten
if(isset($_POST['do']) && $_POST['do'] == "do_edit" &&
   isset($_POST['galid']) && !empty($_POST['galid']) && is_numeric($_POST['galid']) && 
   isset($_POST['galeriename']) && !empty($_POST['galeriename']) && $userdata['editgal'] > 0){
	// Zugriffsberechtigung und ggf. Passwortänderung überprüfen
	$list = mysql_query("SELECT password,uid FROM ".$mysql_tables['gallery']." WHERE id = '".mysql_real_escape_string($_POST['galid'])."' LIMIT 1");
	$row = mysql_fetch_assoc($list);
	$oldpassword = stripslashes($row['password']);
	
	if($userdata['editgal'] == 2 || $userdata['editgal'] == 1 && $row['uid'] == $userdata['id']){
		if(isset($_POST['comments']) && $_POST['comments'] == 1) $comments = 0;
		else $comments = 1;
		if(!isset($_POST['hide']) || isset($_POST['hide']) && empty($_POST['hide'])) $_POST['hide'] = 0;
		
		if($userdata['editgal'] == 2 && isset($_POST['owner_uid']) && !empty($_POST['owner_uid']) && is_numeric($_POST['owner_uid']) && $row['uid'] != $_POST['owner_uid']){
			$new_uid = "uid = '".mysql_real_escape_string($_POST['owner_uid'])."',";
			}
		else $new_uid = "";
		
		$flag_renameok = true;
		// Passwort geändert?
		if(isset($_POST['password']) && !empty($_POST['password']) && $_POST['password'] != stripslashes($row['password'])){
			$oldgaldir = _01gallery_getGalDir($_POST['galid'],stripslashes($row['password']));
			$newgaldir = _01gallery_getGalDir($_POST['galid'],$_POST['password']);
			$flag_renameok = rename($modulpath.$galdir.$oldgaldir,$modulpath.$galdir.$newgaldir);
			
			$changepw = "password = '".mysql_real_escape_string($_POST['password'])."',";
			}
		// Kein Passwort mehr
		elseif((isset($_POST['password']) && empty($_POST['password']) || !isset($_POST['password'])) && !empty($oldpassword)){
			$oldgaldir = _01gallery_getGalDir($_POST['galid'],stripslashes($row['password']));
			$newgaldir = _01gallery_getGalDir($_POST['galid']);
			$flag_renameok = rename($modulpath.$galdir.$oldgaldir,$modulpath.$galdir.$newgaldir);
			
			$changepw = "password = '',";
			}
		// Keine Änderung
		else{
			$changepw = "";
			}
		
		if($flag_renameok){
			// Datenbankeintrag aktualisieren
			mysql_query("UPDATE ".$mysql_tables['gallery']." SET 
							subof			= '".mysql_real_escape_string($_POST['subof'])."',
							".$changepw."
							galeriename		= '".mysql_real_escape_string($_POST['galeriename'])."',
							beschreibung	= '".mysql_real_escape_string(stripslashes($_POST['textfeld']))."',
							".$new_uid."
							comments		= '".$comments."',
							hide			= '".mysql_real_escape_string($_POST['hide'])."'
							WHERE id = '".mysql_real_escape_string($_POST['galid'])."' LIMIT 1");
			
			echo "<p class=\"meldung_erfolg\"><b>Bilderalbum wurde erfolgreich bearbeitet</b><br />
                <br />
                <a href=\"_loader.php?modul=".$modul."&amp;loadpage=showpics&amp;action=show_pics&amp;galid=".$_POST['galid']."\">Bilder verwalten &raquo;</a>
                </p>";
			}
		else
			echo "<p class=\"meldung_error\"><b>Fehler: Das Verzeichnis ".$modulpath.$galdir.$oldgaldir." konnte nicht umbenannt werden!</b></p>";
		
		}
	else $flag_loginerror = true;
	
	}
elseif(isset($_POST['do']) && $_POST['do'] == "do_edit")
	echo "<p class=\"meldung_error\"><b>Fehler:</b> Sie haben nicht alle ben&ouml;tigten Felder ausgef&uuml;llt<br />
			Bitte gehen Sie <a href=\"javascript:history.back();\">zur&uuml;ck</a>.</p>";
	
	
	



	
	
	


// 	Neue Galerie anlegen / Galerie bearbeiten (Formular)
if(isset($_REQUEST['action']) && $_REQUEST['action'] == "new_gal" && $userdata['newgal'] == 1 ||
   (isset($_GET['action']) && $_GET['action'] == "edit_gal" && 
   isset($_GET['galid']) && !empty($_GET['galid']) && is_numeric($_GET['galid']) && $userdata['editgal'] > 0)
   ){
	echo loadTinyMCE("none","bold,italic,underline,strikethrough,|,link,unlink,|,bullist,numlist,","","","top");

	// Beim Bearbeiten Daten aus DB holen und Zugriffsberechtigung überprüfen
	if(isset($_GET['action']) && $_GET['action'] == "edit_gal"){
		$list = mysql_query("SELECT * FROM ".$mysql_tables['gallery']." WHERE id = '".mysql_real_escape_string($_GET['galid'])."'");
		while($row = mysql_fetch_array($list)){
			// Zugriffsberechtigt?
			if($userdata['editgal'] == 2 || $userdata['editgal'] == 1 && $row['uid'] == $userdata['id']){
				$form_data = array(	"section"			=> "update",
									"title"				=> "Bilderalbum bearbeiten",
									"button"			=> "Bearbeiten",
									"do"				=> "do_edit",
									"galname"			=> htmlentities(stripslashes($row['galeriename'])),
									"beschreibung"		=> stripslashes($row['beschreibung']),
									"password"			=> stripslashes($row['password']),
									"uid"				=> $row['uid'],
									"comments"			=> $row['comments'],
									"hide"				=> $row['hide'],
									"id"				=> $row['id'],
									"subof"				=> $row['subof']
								);
				}
			else $flag_loginerror = true;
			}
		}
	else{
		$form_data = array(	"section"			=> "new",
							"title"				=> "Neues Bilderalbum anlegen",
							"button"			=> "Anlegen &raquo;",
							"do"				=> "add_gal",
							"galname"			=> "",
							"beschreibung"		=> "",
							"password"			=> "",
							"comments"			=> 1,
							"hide"				=> 0,
							"id"				=> 0,
							"subof"				=> 0
						);
		}
		
		if(!$flag_loginerror){
?>

<h1><?PHP echo $form_data['title']; ?></h1>

<?php if(isset($_GET['action']) && $_GET['action'] == "edit_gal") echo  _01gallery_echoActionButtons_Gal(); ?>

<form action="<?PHP echo $filename; ?>" method="post" name="post">
<table border="0" align="left" width="70%" cellpadding="3" cellspacing="5" class="rundrahmen" style="float:none;">

    <tr>
        <td class="trb"><b>Name des Albums:</b></td>
        <td class="trb"><input type="text" name="galeriename" value="<?PHP echo $form_data['galname']; ?>" size="50" /></td>
    </tr>
	
    <tr>
        <td class="trb"><b>Anderem Bilderalbum unterordnen:</b></td>
        <td class="trb">
			<select name="subof" size="1">
				<option value="0">Keinem Album untergeordnet</option>
				<?PHP _01gallery_getGallerysRek(0,0,-1,"_01gallery_echoGalinfo_select",$form_data['subof'],$form_data['id']); ?>
			</select>
		</td>
    </tr>
	
    <tr>
        <td class="tra"><b>Kurzbeschreibung:</b></td>
        <td class="tra"><textarea name="textfeld" rows="6" cols="60" style="font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 12px; font-style: normal;"><?PHP echo $form_data['beschreibung']; ?></textarea></td>
    </tr>
	
    <tr>
        <td class="trb"><b>Passwort verwenden?</b></td>
        <td class="trb"><input type="text" name="password" value="<?PHP echo $form_data['password']; ?>" size="15" /> [ <a href="javascript:hide_unhide('pwinfo');">?</a> ]</td>
    </tr>

    <tr>
        <td class="tra"><b>Kommentare deaktivieren</b></td>
        <td class="tra"><input type="checkbox" name="comments" value="1"<?PHP if($form_data['comments'] == 0) echo " checked=\"checked\""; ?> /></td>
    </tr>
	
    <tr>
        <td class="trb"><b>Album verstecken</b></td>
        <td class="trb"><input type="checkbox" name="hide" value="1"<?PHP if($form_data['hide'] == 1) echo " checked=\"checked\""; ?> /></td>
    </tr>
	<?PHP if($userdata['editgal'] == 2 && isset($form_data['uid']) && !empty($form_data['uid']) && is_numeric($form_data['uid'])) { ?>	
    
	<tr>
        <td class="tra"><b>Eigent&uuml;mer &auml;ndern:</b></td>
        <td class="tra">
			<select name="owner_uid" size="1" class="input_select">
				<?PHP echo create_UserDropDown(TRUE,$form_data['uid']); ?>
			</select>
		</td>
    </tr>
	
	<?PHP 
	$followclass = "trb";
	}else $followclass ="tra"; ?>
    <tr>
        <td class="<?PHP echo $followclass; ?>"><input type="reset" value="Reset" class="input" /></td>
        <td class="<?PHP echo $followclass; ?>" align="right">
			<input type="hidden" name="galid" value="<?PHP echo $form_data['id']; ?>" />
			<input type="hidden" name="do" value="<?PHP echo $form_data['do']; ?>" />
			<input type="submit" name="submit" value="<?PHP echo $form_data['button']; ?>" class="input" />
		</td>
    </tr>
</table>
</form>

<p class="meldung_hinweis" id="pwinfo" style="display:none;">
	<b>Informationen zum Passwortschutz von Bilderalben:</b><br />
	&bull; Die Passw&ouml;rter der Bilderalben werden im Gegensatz zu allen anderen Passw&ouml;rtern 
	in der Datenbank <b>unverschl&uuml;sselt</b> gespeichert, damit sie auch nach dem 
	ersten Anlegen noch angezeigt werden k&ouml;nnen.<br />
	&bull; Die Bilder selber werden nicht verschl&uuml;sselt oder &auml;hnliches. Benutzer, die einen
	direkten Link auf Bilddateien besitzen, k&ouml;nnen ggf. trotz Passwortschutz Zugriff auf Bilddateien erhalten.<br />
	&bull; Der Passwortschutz hat im Administrationsbereich keine Auswirkungen
</p>

<?PHP
		} // Ende: if-Abfrage: flag-Loginerror
	}
elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == "new_gal")
	$flag_loginerror = true;
else
	{
	
	
	
	
	
	
	
	
	
	
	
	
	// Galerien auflisten
?>

<h1>Bilderalben</h1>

<?PHP
	// Sortieren
	if(isset($_POST['sort']) && !empty($_POST['sort'])){
	$list = mysql_query("SELECT id,sortid FROM ".$mysql_tables['gallery']."");
	while($row = mysql_fetch_array($list)){
		if(isset($_POST['sortid_'.$row['id']]) && !empty($_POST['sortid_'.$row['id']]) && is_numeric($_POST['sortid_'.$row['id']]))
			mysql_query("UPDATE ".$mysql_tables['gallery']." SET sortid='".mysql_real_escape_string($_POST['sortid_'.$row['id']])."' WHERE id='".$row['id']."' LIMIT 1");
		}
	}
	
	
	
	// Galerie löschen? (Abfrage)
	if(isset($_GET['action']) && $_GET['action'] == "ask_del" &&
	   isset($_GET['galid']) && !empty($_GET['galid']) && is_numeric($_GET['galid']) && $userdata['editgal'] > 0){
		echo "<p class=\"meldung_frage\">Möchten Sie wirklich das Album <b>".stripslashes($_GET['galeriename'])."</b> 
				inkl. <b>aller hochgeladenen Bilder, Beschreibungstexte und Kommentare</b> löschen?<br />
				Untergeordnete Alben werden hierbei <b>nicht</b> gel&ouml;scht, sondern auf die oberste Ebene verschoben!<br />
				<br />
				<a href=\"".$filename."&amp;action=do_del&amp;galid=".$_GET['galid']."\">JA</a> | <a href=\"javascript:history.back();\">Nein</a></p>";
		}
	
	
	// Galerie löschen! (tun)
	if(isset($_GET['action']) && $_GET['action'] == "do_del" &&
	   isset($_GET['galid']) && !empty($_GET['galid']) && is_numeric($_GET['galid']) && $userdata['editgal'] > 0){
		
		// User-Berechtigung überprüfen
		if(_01gallery_checkUserright($_GET['galid'])){
			// Galerie-Verzeichnisinhalte löschen
			
			// Passwort holen
			$list = mysql_query("SELECT password FROM ".$mysql_tables['gallery']." WHERE id = '".mysql_real_escape_string($_GET['galid'])."' LIMIT 1");
			$row = mysql_fetch_assoc($list);
			
			$dir = _01gallery_getGalDir($_GET['galid'],stripslashes($row['password']));
			$verz = opendir($modulpath.$galdir.$dir);

			$flag_error = false;
			while($file = readdir($verz)){
			    if($file != "." && $file != "..")
			        if(!@unlink($modulpath.$galdir.$dir."/".$file))
						$flag_error = true;
			    }
			
			if(!$flag_error){
				if(!@unlink($modulpath.$galdir.$dir."/"))
					$flag_error = true;
				}
			
			// Kommentare löschen:
			delComments($_GET['galid']);
			
			// Bildeinträge aus Datenbank löschen
			mysql_query("DELETE FROM ".$mysql_tables['pics']." WHERE galid='".mysql_real_escape_string($_GET['galid'])."'");
			
			// Sub-Galerien auf oberste Ebene verschieben
			mysql_query("UPDATE ".$mysql_tables['gallery']." SET subof='0' WHERE subof='".mysql_real_escape_string($_GET['galid'])."'");
			
			// Galerie-Eintrag aus Datenbank löschen:
			mysql_query("DELETE FROM ".$mysql_tables['gallery']." WHERE id='".mysql_real_escape_string($_GET['galid'])."' LIMIT 1");
			
			echo "<p class=\"meldung_erfolg\"><b>Das Album wurde erfolgreich gelöscht!</b></p>";
			if($flag_error)
				echo "<p class=\"meldung_error\">Das Album wurde aus der Datenbank entfernt.
						Das FTP-Verzeichnis konnten jedoch leider nicht automatisch gelöscht werden.<br />
						<b>Bitte veranlassen Sie eine Löschung des folgenden Verzeichnisses inkl. des Inhalts:<br />
						".$modulpath.$galdir.$dir."/</b></p>";
			}
		}
	
	
	
	
	
	// GALERIE-AUFLISTUNG
	
	// undefined index...
	if(!isset($_GET['orderby']))	$_GET['orderby'] = "";
	if(!isset($_GET['site']))		$_GET['site'] = "";
	if(!isset($_GET['parentids']))	$_GET['parentids'] = "";
	if(!isset($_GET['sort']))		$_GET['sort'] = "";
	
	// Order by 
	if((!isset($_GET['orderby']) || isset($_GET['orderby']) && empty($_GET['orderby'])) && (!isset($_GET['sort']) || isset($_GET['sort']) && empty($_GET['sort'])))
		$sortorder = "DESC";
	elseif(isset($_GET['sort']) && $_GET['sort'] == "desc") $sortorder = "DESC";
	else $sortorder = "ASC";
	
	// Sub-Gal anzeigen?
	if(!isset($_GET['subid']) || isset($_GET['subid']) && (empty($_GET['subid']) || !is_numeric($_GET['subid']))) $_GET['subid'] = 0;
	
	$where = " WHERE subof = '".mysql_real_escape_string($_GET['subid'])."' ";

	switch($_GET['orderby']){
	  case "position":
	    $orderby = "sortid";
	  break;
	  case "timestamp":
	    $orderby = "timestamp";
	  break;
	  case "titel":
	    $orderby = "galeriename";
	  break;
	  default:
	    $orderby = "sortid";
		$sortorder = "DESC";
	  break;
	  }

	$sites = 0;
	$query = "SELECT * FROM ".$mysql_tables['gallery']."".$where." ORDER BY ".$orderby." ".$sortorder;
	$query = makepages($query,$sites,"site",ACP_PER_PAGE);
	
	$filename2 = $filename."&amp;site=".$_GET['site']."&amp;parentids=".$_GET['parentids']."";
	$filename3 = $filename."&amp;sort=".$_GET['sort']."&amp;orderby=".$_GET['orderby']."&amp;parentids=".$_GET['parentids']."";
	$filename4 = $filename2."&amp;subid=".$_GET['subid']."";
?>
	<?PHP if($userdata['newgal'] == 1){ ?>
	<p><a href="<?PHP echo $filename; ?>&amp;action=new_gal" class="actionbutton"><img src="images/icons/add.gif" alt="Plus-Zeichen" title="Neues Album hinzuf&uuml;gen" style="border:0; margin-right:10px;" />Neues Album hinzuf&uuml;gen</a></p>
	<?PHP } ?>
	
	<?PHP
	// Breadcrumps
	if($_GET['subid'] > 0){
		echo "<h3><a href=\"".$filename."&amp;sort=".$_GET['sort']."&amp;orderby=".$_GET['orderby']."&amp;\">Galerien</a>";
		if(isset($_GET['parentids']) && !empty($_GET['parentids']) && $_GET['parentids'] != "0"){
			$parentids = explode(",",$_GET['parentids']);
			if(is_array($parentids)){
				$preparentids = "0";
				foreach($parentids as $parentid){
					$list = mysql_query("SELECT galeriename FROM ".$mysql_tables['gallery']." WHERE id = '".$parentid."' LIMIT 1");
					while($row = mysql_fetch_assoc($list)){
						$preparentids .= ",".$parentid;
						echo " &raquo; <a href=\"".$filename."&amp;subid=".$parentid."&amp;parentids=".$preparentids."&amp;sort=".$_GET['sort']."&amp;orderby=".$_GET['orderby']."\">".htmlentities(stripslashes($row['galeriename']))."</a>";
						}
					}
				}
			}
		else
			$_GET['parentids'] = "0";
		echo "</h3>";
		}else $_GET['parentids'] = "0";
	?>

	<form action="<?PHP echo $filename3; ?>&amp;subid=<?PHP echo $_GET['subid']; ?>" method="post">
	<table border="0" align="center" width="100%" cellpadding="3" cellspacing="5" class="rundrahmen">
    <tr>
		<td class="tra" width="115"><b>Pos. (99 -&gt; 0)</b>
			<a href="<?PHP echo $filename4; ?>&amp;sort=asc&amp;orderby=position"><img src="images/icons/sort_asc.gif" alt="Icon: Pfeil nach oben" title="Aufsteigend sortieren" /></a>
			<a href="<?PHP echo $filename4; ?>&amp;sort=desc&amp;orderby=position"><img src="images/icons/sort_desc.gif" alt="Icon: Pfeil nach unten" title="Absteigend sortieren (DESC)" /></a>
		</td>
		<td class="tra" width="30" align="center"><b>ID</b></td>
        <td class="tra" width="110"><b>Datum</b>
			<a href="<?PHP echo $filename4; ?>&amp;sort=asc&amp;orderby=timestamp"><img src="images/icons/sort_asc.gif" alt="Icon: Pfeil nach oben" title="Aufsteigend sortieren" /></a>
			<a href="<?PHP echo $filename4; ?>&amp;sort=desc&amp;orderby=timestamp"><img src="images/icons/sort_desc.gif" alt="Icon: Pfeil nach unten" title="Absteigend sortieren (DESC)" /></a>
		</td>
		<td class="tra" width="275"><b>Titel</b>			
			<a href="<?PHP echo $filename4; ?>&amp;sort=asc&amp;orderby=titel"><img src="images/icons/sort_asc.gif" alt="Icon: Pfeil nach oben" title="Aufsteigend sortieren" /></a>
			<a href="<?PHP echo $filename4; ?>&amp;sort=desc&amp;orderby=titel"><img src="images/icons/sort_desc.gif" alt="Icon: Pfeil nach unten" title="Absteigend sortieren (DESC)" /></a>
		</td>
		<td class="tra"><b>Benutzer</b></td>
		<td class="tra" width="25">&nbsp;<!--Hochladen--></td>
		<td class="tra" width="25">&nbsp;<!--Bearbeiten--></td>
		<td class="tra" width="25" align="center"><!--Löschen--><img src="images/icons/icon_trash.gif" alt="M&uuml;lleimer" title="Datei l&ouml;schen" /></td>
    </tr>
<?PHP
	if($userdata['editgal'] == 1)
		$artuserdata[$userdata['id']] = $userdata;
	else
		$artuserdata = getUserdatafields_Queryless("username");
	
	// Ausgabe der Datensätze (Liste) aus DB
	$count = 0;
	$somethinghidden = false;
	$list = mysql_query($query);
	while($row = mysql_fetch_array($list)){
		if($count == 1){ $class = "tra"; $count--; }else{ $class = "trb"; $count++; }
		
		// Sub-Galerien vorhanden?
		$submenge = 0;
		$countlist = mysql_query("SELECT * FROM ".$mysql_tables['gallery']." WHERE subof = '".$row['id']."'");
		$submenge = mysql_num_rows($countlist);
		if($submenge > 0)
			$showsubtext = "<br /><span class=\"small\"><a href=\"".$filename3."&amp;subid=".$row['id']."&amp;parentids=".$_GET['parentids'].",".$row['id']."\">".$submenge." untergeordnete Alben betrachten &raquo;</a></span>";
		else $showsubtext = "";
		
		// Status-Bestimmung
		if($row['hide'] == 1){
			$class = "tr_red";
			$somethinghidden = true;
			}
		
		if($userdata['editgal'] == 2)
			$showsort = "<input type=\"text\" name=\"sortid_".$row['id']."\" onkeyup=\"IsZahl('sortid_".$row['id']."')\" size=\"5\" value=\"".$row['sortid']."\" style=\"text-align:center;\" class=\"input_text\" />";
		else
			$showsort = $row['sortid'];
		
		if(stripslashes($row['password']) != "" && ($userdata['editgal'] == 2 || $userdata['editgal'] == 1 && $row['uid'] == $userdata['id'])){
			$pwicon = "<img src=\"images/icons/icon_gesperrt.gif\" alt=\"gif: Schlo&szlig;\" title=\"Passwort anzeigen\" onclick=\"fade_element('showpw_".$row['id']."')\" style=\"cursor: pointer;\" /> ";
			$pwtext = "<div class=\"moo_inlinehide\" id=\"showpw_".$row['id']."\"> <i>Passwort: ".stripslashes($row['password'])."</i></div>";
			}
		elseif(stripslashes($row['password']) != ""){
			$pwicon = "<img src=\"images/icons/icon_gesperrt.gif\" alt=\"gif: Schlo&szlig;\" title=\"Passwort anzeigen\" onclick=\"fade_element('showpw_".$row['id']."')\" style=\"cursor: pointer;\" /> ";
			$pwtext = "<div class=\"moo_inlinehide\" id=\"showpw_".$row['id']."\"> <i>Passwort: *****</i></div>";
			}
		else{
			$pwicon = "";
			$pwtext = "";
			}
		
		echo "    <tr>
		<td class=\"".$class."\" align=\"center\">".$showsort."</td>
		<td class=\"".$class."\" align=\"center\">".$row['id']."</td>
		<td class=\"".$class."\">".date("d.m.Y - G:i",$row['timestamp'])."</td>
		<td class=\"".$class."\">".$pwicon."<a href=\"_loader.php?modul=".$modul."&amp;loadpage=showpics&amp;action=show_pics&amp;galid=".$row['id']."\">".htmlentities(stripslashes($row['galeriename']))."</a> (".$row['anzahl_pics'].")".$pwtext.$showsubtext."</td>
		<td class=\"".$class."\">".$artuserdata[$row['uid']]['username']."</td>";
		
		if($userdata['editgal'] == 2 || $userdata['editgal'] == 1 && $row['uid'] == $userdata['id'])
			echo "<td class=\"".$class."\" align=\"center\"><a href=\"_loader.php?modul=".$modul."&amp;loadpage=upload&amp;action=upload_pic&amp;galid=".$row['id']."\"><img src=\"images/icons/icon_upload.gif\" alt=\"Hochladen\" title=\"Bilder hochladen\" /></a></td>
		<td class=\"".$class."\" align=\"center\"><a href=\"".$filename."&amp;action=edit_gal&amp;galid=".$row['id']."\"><img src=\"images/icons/icon_edit.gif\" alt=\"Bearbeiten - Stift\" title=\"Galerie bearbeiten\" /></a></td>
		<td class=\"".$class."\" align=\"center\"><a href=\"".$filename."&amp;action=ask_del&amp;galid=".$row['id']."&amp;galeriename=".htmlentities(stripslashes($row['galeriename']))."\"><img src=\"images/icons/icon_delete.gif\" alt=\"L&ouml;schen - rotes X\" title=\"Galerie l&ouml;schen\" /></a></td>
	</tr>";		
		else
			echo "<td class=\"".$class."\" align=\"center\">-</td>
		<td class=\"".$class."\" align=\"center\">-</td>
		<td class=\"".$class."\" align=\"center\">-</td>
	</tr>";		
		}
	
	// Sortier-Button anzeigen
	if($userdata['editgal'] == 2){
		if($count == 1){ $class = "tra"; $count--; }else{ $class = "trb"; $count++; }
		
		echo "    <tr>
		<td class=\"".$class."\" align=\"center\"><input type=\"submit\" name=\"sort\" value=\"Sortieren\" class=\"input\" /></td>
		<td colspan=\"7\" class=\"".$class."\">&nbsp;</td>
	</tr>";
		}
	
	echo "</table>\n</form>";
	
	if($somethinghidden) echo "<p class=\"tr_red\">Album ist versteckt und wird momentan nicht &ouml;ffentlich angezeigt</p>";
	
	echo "<br />";
	echo echopages($sites,"80%","site","&amp;sort=".$_GET['sort']."&amp;orderby=".$_GET['orderby']."&amp;subid=".$_GET['subid']."&amp;parentids=".$_GET['parentids']."");	

	if($userdata['settings'] == 1)
		echo "<button type=\"button\" onclick=\"AjaxRequest.send('modul=".$modul."&ajaxaction=countgalpics');\" class=\"input\">Bilder in Alben neu z&auml;hlen</button>";

	}
	
// 01-Gallery V2 Copyright 2006-2010 by Michael Lorer - 01-Scripts.de
?>