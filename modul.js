/*!
	01-Gallery - Copyright 2003-2013 by Michael Lorer - 01-Scripts.de
	Lizenz: Creative-Commons: Namensnennung-Keine kommerzielle Nutzung-Weitergabe unter gleichen Bedingungen 3.0 Deutschland
	Weitere Lizenzinformationen unter: http://www.01-scripts.de/lizenz.php

	Modul:		01gallery
	Dateiinfo:	JavaScript-Funktionen
	Unkomprimierte Version der Datei: https://github.com/01-Scripts/01-Gallery/blob/V2.1.1/01gallery/modul.js
	#fv.211#
*/

/* Funktion erzeugt einen pro Bild eindeutigen Ajax-Request um Titel/Beschreibung zu aktualisieren */
function SendPicFormData(id, data) {

MyAjaxRequest = new Request({
	method: 'post',
	url: '_ajaxloader.php',
	evalScripts: true,
	onComplete: function(response) {
		$('hide_show_'+id).set('html', response);
		}
	}).send(data);
	
}


/* Wählt alle in 'what' angegebenen Checkboxen an/ab 
 * (Abhängig vom Zustand der Selector-Checkbox */ 
function SelectAll(selector,what) {

if($(selector).checked == true){
	var change = true;
}else{
	var change = false;
}
		
$$(what).each(function(el,c){

 el.checked = change;
 });
}