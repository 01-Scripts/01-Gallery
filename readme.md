# 01-Gallery

Die **01-Gallery** ist als Modul in das [01ACP](https://github.com/01-Scripts/01ACP) als zentralen Administrationsbereich integriert und mit vielen innovativen Features ausgestattet:  
Die Web-Verwaltung der Bildergalerien ermöglicht den Upload und die Verkleinerung von Bildern direkt aus dem Browser heraus (Upload ganzer Verzeichnisse mit nur einem Klick möglich). Ein FTP-Import für große Datenmengen steht ebenfalls zur Verfügung.  
Nach dem Upload oder Import von Bildern werden die passenden Thumbnails automatisch generiert und gespeichert. Anschließend können alle Bilder bequem online sortiert und individuelle Bildunterschriften und -titel erfasst werden.  
Sie möchten nicht jedem Besucher all Ihre Bilder zeigen?  
Kein Problem: Schützen Sie beliebige Bilderalben einfach mit einem Passwort!  
Ihre Besucher können dann ihre Bilder bequem betrachten (u.a. per Lightbox) und natürlich auch Kommentare zu den schönsten Bildern abgeben.  
[Demo und weitere Informationen](https://www.01-scripts.de/downloads.php)

## Features

-   Automatische Thumbnail-Generierung (festes oder dynamisches Größenverhältnis)
-   Automatische Verkleinerung beim Upload. Originale bleiben zusätzlich erhalten
-   Bilderalben jetzt auch direkt im  [01-Artikelsystem](https://github.com/01-Scripts/01-Artikelsystem)  verwenden
-   Online-Upload-Funktion oder FTP-Import von Bildern
-   Passwortschutz für Bilderalben
-   Kommentarsystem inkl. Spamschutz
-   Integrierte Lightbox
-   Layout einfach per CSS an die eigenen Beüdürfnisse anpassen
-   kostenlos (CC BY-NC-SA-Lizenz)

## Download

Die jeweils aktuellste Version der 01-Gallery kann unter
[https://www.01-scripts.de/down_zip.php?godownload=1&01gallery=1&01acp=1](https://www.01-scripts.de/down_zip.php?godownload=1&01article=1&01acp=1)
heruntergeladen werden.

Einzelne Update-Pakete für alle veröffentlichten Updates sind hier zu finden:
[https://www.01-scripts.de/downloads.php](https://www.01-scripts.de/downloads.php)

## Installation

### Den Administrationsbereich (01ACP) installieren

Um einzelne Module wie das [01-Artikelsystem](https://github.com/01-Scripts/01-Artikelsystem) oder die [01-Gallery](https://github.com/01-Scripts/01-Gallery) zu installieren, muss zuerst einmalig der für alle Module benötigte Administrationsbereich (das [01ACP](https://github.com/01-Scripts/01ACP)) installiert werden:

#### Dateien hochladen

Entpacken sie das heruntergeladene .zip-Archiv in einen beliebigen Ordner auf ihrer lokalen Festplatte.  
Im Verzeichnis  `Administrationsbereich/`  finden sie das Verzeichnis  `01scripts/`  das alle Dateien zur Installation des 01ACP enthält.  
Laden sie das gesamte  `01scripts/`-Verzeichnis mit ihrem  FTP-Programm  in das Hauptverzeichnis ihrer Internetseite hoch. Achten sie dabei unbedingt darauf, dass die Datei- und Verzeichnisstruktur erhalten bleibt!  
  
Im Idealfalls sollte sich nach dem Hochladen der Daten das Verzeichnis  `01scripts/`  auf der gleichen Verzeichnisebene befinden wie ihre Index- oder Startdatei (meist index.php).

#### Installation des 01ACP starten

Nachdem sie alle Dateien auf ihren Webspace hochgeladen haben rufen sie in ihrem Browser bitte folgende Internetadresse auf:  
`http://www.ihre-domain.de/01scripts/01acp/install.php`  
  
Falls sich das Verzeichnis  `01scripts/`  **nicht**  im Hauptverzeichnis ihrer Internetseite befindet, müssen sie den Pfad der URL entsprechend anpssen:  
`http://www.ihre-domain.de/pfad-zum-script/01scripts/01acp/install.php`  
  
Sie werden anschließend mit der Installationsroutine durch die weitere Installation des 01ACP geführt. Folgen sie dazu einfach den Anweisungen auf dem Bildschirm.

#### Module installieren

Nachdem sie das 01ACP erfolgreich installiert haben und den ersten Benutzer-Account angelegt haben löschen sie bitte aus Sicherheitsgründen die beiden Dateien install.php und install_sql.php!  
Anschließend loggen sie sich bitte mit ihrem Admin-Account den sie bei der Installation angelegt haben, in den Administrationsbereich ein:  
`http://www.ihre-domain.de/01scripts/01acp/index.php`

### Module in das 01ACP installieren

Für die weiteren Schritte muss das 01ACP bereits installiert sein!

#### Modul-Verzeichnis hochladen

Wenn sie es noch nicht getan haben, dann entpacken sie das von  01-Scripts.de  heruntergeladene .zip-Archiv in einen beliebigen Ordner auf ihrer lokalen Festplatte.  
Im Verzeichnis  `Module/`  finden sie das von ihnen heruntergeladene Modul in einem Verzeichnis.  
Laden sie das Verzeichnis (z.B.  `01article/`) mit ihrem  FTP-Programm  in folgendes Verzeichnis:  
`/pfad-zum-script/01scripts/01module/`

#### Hochgeladenes Modul installieren

Loggen sie sich nun mit ihrem Admin-Account in das 01ACP ein.  
Nach dem Login finden sie im linken Menü der 01ACP-Startseite den Menüpunkt _Module verwalten_  klicken sie auf ihn.

![Menüpunkt Module verwalten zur Modul-Installation](dok/01acp_module.gif)

In der erscheinenden Tabelle werden alle vorhandenen Module aufgelistet. Mit einem Klick auf den grünen Plus-Button starten sie die Installation des gewünschten Moduls.  
Geben sie einen Installationsnamen für das Modul an.  
Nach der Installation steht das Modul sofort zur Verfügung. In den Einstellungen können nun die Einstellungen für das installierte Modul vorgenommen werden.

![Für Modul-Einstellungen das entsprechende Modul im Dropdown auswählen](dok/01acp_moduleinstellungen.gif)

Die Startseite des Moduls rufen sie über die Drop-Down-Box oben rechts im 01ACP auf:

![Die Modul-Startseite ist per Dropdown oben rechts zu erreichen](dok/01acp_dropdown.gif)

### 01-Gallery per PHP einbinden

Die 01-Gallery ist darauf ausgelegt per PHP mit dem include()-Befehl in eine bestehende Seite eingebunden zu werden. Es wird dringend empfohlen diese Methode zu verwenden! Der Befehl dafür lautet folgendermaßen:

```php
<?PHP
$subfolder  = "01scripts/";         // Unterverzeichnis
$modul      = "01gallery";          // Modul- und Verzeichnisname (=ID-Name)
                                    // ggf. ändern
 
// Hier ggf. weitere Parameter einfügen
 
include($subfolder."01module/".$modul."/01gallery.php");
?>
```

Folgende Parameter können verwendet werden:

```php
<?PHP
// Weitere mögliche Parameter sind:
$galid             = 1;         // Geben Sie hier die ID der Galerie an, die direkt angezeigt werden soll.
$flag_breadcrumps  = TRUE;      // Setzen Sie $flag_breadcrumps = FALSE; wenn keine Albentitel angezeigt werden sollen
$flag_nocss        = FALSE;     // Setzen Sie $flag_nocss = TRUE; wenn kein CSS eingebunden werden soll
?>
```

## Lizenz

Die 01-Gallery ist unter [CC BY-NC-SA](https://creativecommons.org/licenses/by-nc-sa/3.0/de/) -Lizenz veröffentlicht

*Namensnennung - Nicht-kommerziell - Weitergabe unter gleichen Bedingungen 3.0 Deutschland*

* Alle PHP-Scripte von [01-Scripts.de](https://www.01-scripts.de) können für den privaten, nicht-kommerziellen Einsatz kostenlos genutzt werden. 
* Für die kommerzielle Nutzung (darunter fällt auch der Einsatz auf Firmenseiten!) können Sie eine Lizenz zur kommerziellen Nutzung [erwerben](https://www.01-scripts.de/shop.php).
* Wenn Sie den sichtbaren Urheberrechtshinweis entfernen möchten, erwerben Sie bitte eine Non-Copyright-Lizenz.

Lizenzen können hier bestellt werden:
[https://www.01-scripts.de/shop.php](https://www.01-scripts.de/shop.php)
