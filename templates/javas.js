/*! 
	01-Gallery V2 - Copyright 2003-2012 by Michael Lorer - 01-Scripts.de
	Lizenz: Creative-Commons: Namensnennung-Keine kommerzielle Nutzung-Weitergabe unter gleichen Bedingungen 3.0 Deutschland
	Weitere Lizenzinformationen unter: http://www.01-scripts.de/lizenz.php
		
	Modul:		01gallery
	Dateiinfo: 	Frontend-BB-Code-JavaScript-Funktion
	Unkomprimierte Version der Datei: https://gist.github.com/627847/fd7bc0ee379135780b05fbbe55680fb05681007d#file_javas.js
	#fv.210#
*/


/*BB-Code-Funktion (c) by http://aktuell.de.selfhtml.org/artikel/javascript/bbcode/index.htm */
function bbcinsert(aTag, eTag) {
  var input = document.forms['post'].elements['comment'];
  input.focus();
  /* f�r Internet Explorer */
  if(typeof document.selection != 'undefined') {
    /* Einf�gen des Formatierungscodes */
    var range = document.selection.createRange();
    var insText = range.text;
    range.text = aTag + insText + eTag;
    /* Anpassen der Cursorposition */
    range = document.selection.createRange();
    if (insText.length == 0) {
      range.move('character', -eTag.length);
    } else {
      range.moveStart('character', aTag.length + insText.length + eTag.length);      
    }
    range.select();
  }
  /* f�r neuere auf Gecko basierende Browser */
  else if(typeof input.selectionStart != 'undefined')
  {
    /* Einf�gen des Formatierungscodes */
    var start = input.selectionStart;
    var end = input.selectionEnd;
    var insText = input.value.substring(start, end);
    input.value = input.value.substr(0, start) + aTag + insText + eTag + input.value.substr(end);
    /* Anpassen der Cursorposition */
    var pos;
    if (insText.length == 0) {
      pos = start + aTag.length;
    } else {
      pos = start + aTag.length + insText.length + eTag.length;
    }
    input.selectionStart = pos;
    input.selectionEnd = pos;
  }
  /* f�r die �brigen Browser */
  else
  {
    /* Abfrage der Einf�geposition */
    var pos;
    var re = new RegExp('^[0-9]{0,3}$');
    while(!re.test(pos)) {
      pos = prompt("Einf�gen an Position (0.." + input.value.length + "):", "0");
    }
    if(pos > input.value.length) {
      pos = input.value.length;
    }
    /* Einf�gen des Formatierungscodes */
    var insText = prompt("Bitte geben Sie den zu formatierenden Text ein:");
    input.value = input.value.substr(0, pos) + aTag + insText + eTag + input.value.substr(pos);
  }
}

/* 01-Gallery V2 Copyright 2003-2008 by Michael Lorer - 01-Scripts.de */