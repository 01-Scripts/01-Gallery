<?php

$datei = $moduldir.$_REQUEST['modul']."/galerien/";

clearstatcache();
@chmod ($datei, 0777);
$chmod = decoct(fileperms($datei));
$cchmod = strchr($chmod, "777");

if($cchmod != 777){
	echo "<p class=\"meldung_hinweis\"><b>Bitte vergeben Sie mit Ihrem FTP-Programm f&uuml;r das Verzeichnis
	".$datei." die Chmod-Rechte 0777!</b></p>";
	}

?>