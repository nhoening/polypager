<?
/*
	PolyPager - a lean, mean web publishing system
    Copyright (C) 2006 Nicolas H�ning
	polypager.nicolashoening.de
	
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA' .
*/

/*This function gives german translations for english texts
	two things need to be followed:
	1. do HTML-Umlaute like &uuml;, also no "..." in either text
	2. every string should be in one line (also in the original file, of course)
	@param text: the text to be translated
*/
function getTranslation($text) {
	$translation_table = array(
/*	admin/index.php	*/
'Your administrator-name or the administrator-password is empty. You should consider going to the ' =>
	'Der Adminstratorname oder das Passwort ist leer. Sie sollten zu den ',
'system property section' => 'Systemeinstellungen',
' and secure your system!' => ' gehen, um Ihr System abzusichern.',
'attempted to create sys tables... '	=> 'Es wurde versucht die Systemtabellen zu erstellen... ',
'attempted to chmod directories... '	=> 'Es wurde versucht die erforderlichen Berechtigungen zu setzen... ',
'The dbms reported the following error: '	=> 'Das Datenbank-System hat folgenden Fehler gemeldet: ',
'The system reported the following error: '	=> 'Das System hat folgenden Fehler gemeldet: ',
'The dbms reported no errors.'	=> 'Das Datenbank-System hat keinen Fehler gemeldet.',
'The system reported no errors.'	=> 'Das System hat keinen Fehler gemeldet.',
'By clicking on this link, you will see a file browser where you can upload files and create folders to store what you need. There are directories for different types of files (File, Image, Media, Flash).'	
	=> 'Wenn Sie auf diesen Link klicken, werden Sie einen Datei-Browser sehen, mit dem Sie Ihre Dateien auf dem Server verwalten k&ouml;nnen (Upload, eigen Ordner usw.). Zu Ihrer groben Orientierung gibt es vier Hauptverzeichnisse f&uuml;r verschiedene Dateitypen (File, Image, Media, Flash).',
'open file browser'	=> 'Datei-Browser &ouml;ffnen',
'open list window' => 'Listen-Ansicht &ouml;ffnen',
'On this page, you can make extra statements about fields, for example a label or a list of possible values. To do this, you need to create an entry for that field first.'
	=>'Auf dieser Seite k&ouml;nnen Sie Aussagen &uuml;ber Felder machen wie z.B. einen Namen, der als Label verwendet werden soll oder eine Liste mit M&ouml;glichen Werten. Dazu muss erst ein Eintrag f&uuml;r das entsprechende Feld angelegt werden.',
'By clicking on this link, you can see (and search for) entries of the page you select. Note that this is the only place you will actually see not published entries/sections.' 
	=> 'Wenn Sie auf diesen Link klicken, werden Sie Listen Ihrer Eintr&auml;ge sehen (und durchsuchen) k&ouml;nnen. Dort geben Sie an, auf welcher Seite Sie nach Eintr&auml;gen suchen. Beachten Sie, dass dies der einzige Ort ist, an dem Sie Eintr&auml;ge sehen k&ouml;nnen, die als nicht &ouml;ffentlich gekennzeichnet wurden.',
'By clicking on this link, you can see what pages you have and maybe enter new ones or delete some.' 
=> 'Wenn Sie auf diesen Link klicken, k&ouml;nnen Sie Ihre Seiten verwalten: Listen Sie alle bestehenden auf, erstellen Sie neue oder entfernen Sie alte.',
'What'	=> 'Was',
'Here you can insert a new entry for this page.'	=> 'Hier k&ouml;nnen Sie einen neuen Eintrag f&uuml;r diese Seite erstellen.',
'Here you can make statements about another field.'	=> 'Hier k&ouml;nnen Sie zu einem weiteren Feld Angaben machen.',
'Here you can insert a new page.'	=> 'Hier k&ouml;nnen Sie eine neue Seite erstellen.',
'new entry'	=> 'neuer Eintrag',
'Here you can edit an introduction text for this page. (It will only be seen if the skin template uses writeIntroDiv())'	
	=> 'Hier k&ouml;nnen Sie einen kurzen Beschreibungs-Text f&uuml;r diese Seite editieren. (Man wird ihn nur sehen koennen, wenn das Skin-Template writeIntroDiv verwendet)',
'show'	=> 'zeige',
'show intro of page'	=> 'zeige intro der Seite:',
'page:' => 'Seite:',
'comments' => 'Kommentare',
'feeds' => 'Feeds',
'view the comments list.' => 'rufen Sie die Kommentarliste auf.',
'view the feed list. delete here what you do not want in the feed (latest entries) list.' =>
	'Rufen Sie hier die Feed-Liste auf. L&ouml;schen Sie dort, was Sie lieber nicht im Feed (der Liste der letzten Eintr&auml;ge) sehen wollen.',					
'create'	=> 'Erstelle',
'the system table(s)'	=> 'die Systemtabellen',
'create a new table from a template'	=> 'erstelle eine neue Tabelle aus einer Vorlage',
'Name'	=> 'Name',
'Admin Area' => 'Adminstrationsbereich',
'the system' => 'Das System',
'files' => 'Dateien',
'pages' => 'Seiten',
'content' => 'Inhalte',
'fields' => 'Felder',
'Let\'s talk about...' => 'Zeig mir...',
'you have been logged out...' => 'Sie haben Sich abgemeldet...',
'hide templates' => 'verstecke Schablonen',
'show templates' => 'zeige Schablonen',

/* admin/list.php */
'Admininstration view of page' => 'Administrationsansicht der Seite',
'get list' => 'zeige Liste',
'There is no entry in the database for this page...' => 'In der Datenbank gibt es keinen Eintrag f&uuml;r diese Seite',
'show search options' => 'zeige Suchoptionen',
'hide search options' => 'verstecke Suchoptionen',
'edit intro' => 'Intro bearbeiten',
'new simple page' => 'neue einfache Seite',
'new complex page' => 'neue komplexe Seite',
'Here you can insert a new simple page (internally also called singlepage). Its entries will simply have a heading and a content, that is all. PolyPager will store it in a special table and you will not need to put much thought in how the page behaves.'
	=> 'Hier k&ouml;nnen Sie eine einfache Seite anlegen. Ihre Eintr&auml;ge werden lediglich eine &Uuml;berschrift und einen Textinhalt besitzen. PolyPager wird die Daten in einer eigenen Tabelle speichern und Sie m&uuml;ssen sich nicht viele Gedanken um das Verhalten der Seite machen.',
'Here you can insert a new complex page (internally also called multipage). The difference to simple pages is that you can use these ones for tables in the database that you have made (and that have any structure). You will have a lot of options to change the behavior of this page.' 
	=> 'Hier k&ouml;nnen Sie eine neue komplexe Seite anlegen (intern auch multipage genannt).Der Unterschied zu einfachen Seiten ist, dass diese f&uuml;r Tabellen in der Datenbank verwendet werden k&ouml;nnen, die Sie angelegt haben (also jede gew&uuml;nschte Struktur haben). Sie werden hier viele Optionen haben, um das Verhalten der Seite zu bestimmen.',
'Go!' => 'Los!',
'this link is only active when there are tables in the database that multipages would operate on (that means all tables but system tables -they all start with [_sys_] -).'
    => 'Dieser Link ist nur aktiv, wenn es Tabellen in der Datenbank gibt, auf denen komplexe Seiten operieren k&ouml;nnten (das sind alle Tabellen, die keine Systemtabellen sind -deren Namen beginnen alle mit [_sys_] -)',
'new page named' => 'neue Seite namens',
'from template:' => 'aus Schablone:',
'A page template creates a page for you that fulfills some well-known function which is used often on websites. So this might be useful for you. After you created the page, you can still edit its properties or delete it.'
	=> 'Eine Seiten-Schablone erstellt eine Seite f&uuml;r Sie. Sie k&ouml;nnen aus Typen von Seiten w&auml;hlen, die wohlbekannte und oft verwendete Funktionen erf&uuml;llen. Nach der Erstellung k&ouml;nnen Sie die Eigenschaften der Seite ver&auml;ndern oder sie l&ouml;schen.',
'a simple guestbook' => 'ein einfaches G&auml;stebuch',
'an FAQ (Frequently asked questions)' => 'ein FAQ (Frequently asked questions)',
'a Weblog (often called Blog)' => 'ein Webtagebuch (oft Blog genannt)',

/*	admin/edit.php */
'There is no page specified.'	=> 'Es wurde keine Seite angegeben.',
'A database-error ocurred...'	=> 'Es ist ein Datenbank-Fehler aufgetreten...',
'The %s-command was successful'	=> 'Das %s-Kommando war erfolgreich',
'click to see all administration options' => 'klicken Sie hier, um zur Administrations&uuml;bersicht zu gelangen',
'click to see the published page' => 'klicken Sie hier, um die bearbeitete Seite zu sehen',
'click to insert a new section on the %s-page'	=> 'klicken Sie hier, um eine neue Sektion auf der %s-Seite zu erstellen.',
'insert a new section'	=> 'erstelle eine neue Sektion',
'click to insert a new record in [%s]'	=> 'klicken Sie hier, um einen neuen Eintrag in [%s] zu erstellen',
'click to make a new entry on the %s - page' => 'klicken Sie hier, um einen neuen Eintrag auf der %s - Seite zu erstellen',
'back to list view' => 'zur&uuml;ck zur Liste',
'go back to the list overview where you chose the edited entry.' => 'gelangen Sie hier zur Listenansicht, wo Sie den bearbeiteten Eintrag ausgew&auml;hlt haben.',
'back to admin index' => 'zur&uuml;ck zur Administrations&uuml;bersicht',
'go back to the administration page.' => 'gehen Sie hier zur&uuml;ck zur &Uuml;bersichtsseite von der Sie kamen.',
'insert a'		=> 'erstelle einen',
'new record'	=> 'neuen Eintrag',
'insert a new entry' => 'einen neuen Eintrag erstellen',
'see the page'	=> 'zeige die Seite',
'Editing page:'	=> 'Bearbeitungsansicht der Seite:',
'Uncheck the publish-checkbox if you do not want users to see the entry yet.'	
	=> 'Deaktivieren Sie die ver&ouml;ffentlichen-Checkbox, wenn Sie noch nicht wollen, dass Benutzer diesen Eintrag sehen.',
'Here you can edit an entry. You find an HTML Form where you can type in values.&lt;br/&gt;Hit the Save-Button to save your changes to the database.&lt;br/&gt;You can also delete existing entries with the Delete-Button.&lt;br/&gt;&lt;br/&gt; %s'
	=> 'Hier k&ouml;nnen Sie einen Beitrag bearbeiten. Sie sehen ein HTML Formular, in das Sie Ihre Daten eingeben k&ouml;nnen&lt;br/&gt;Bet&auml;tigen Sie den Speichern-Button um Ihre Eingaben in der Datenbank zu speichern.&lt;br/&gt;Sie k&ouml;nnen auch den L&ouml;schen-Button bet&auml;tigen, um einen bestehenden Eintrag wieder aus der Datenbank zu l&ouml;schen.&lt;br/&gt; %s',
'How'	=> 'Wie',
'edit impressum' => 'Impressum editieren',
'Here you can edit the impressum.' => 
	'Hier k&ouml;nnen Sie das Impressum editieren.',
'Here you can edit the %s-page.&lt;br/&gt;&lt;br/&gt; A page consists of sections. One section might be all you need. If so, go ahead and type into the header- and the textfield what you want to publish (uncheck the publish-checkbox if you do not want users to see it yet).&lt;br/&gt;&lt;br/&gt;You can always add other sections if the page grows more complex. Then it might also be useful to check the show-in-submenu checkbox so users can access your structure quickly.' => 'Hier k&ouml;nnen Sie die %s-Seite bearbeiten.&lt;br/&gt;&lt;br/&gt; Eine Seite (hier eine sog. singlepage) besteht aus Sektionen. Eine Sektion ist vielleicht alles was Sie derzeit brauchen. Wenn dem so ist, dann geben Sie jetzt in &Uuml;berschrift- und Textfeld ein, was Sie ver&ouml;ffentlichen wollen (Deaktivieren Sie die ver&ouml;ffentlichen-Checkbox, wenn Sie noch nicht wollen, dass Benutzer diesen Eintrag sehen).&lt;br/&gt;&lt;br/&gt; Sie k&ouml;nnen immer weitere Sektionen hinzuf&uuml;gen wenn die Seite komplexer werden soll. Dann wird es vielleicht auch n&uuml;tzlich sein, die sog. -anw&auml;hlbar im Submen&uuml;- - Checkbox zu aktivieren, damit Benutzer diese Sektionen schon aus dem Men&uuml; heraus anw&auml;hlen k&ouml;nnen.',
'Editing intro for page'	=> 'Bearbeitung des Intros f&uuml;r die Seite',
'Editing system properties'  => 'Systemeigenschaften',
'DB-Error:'	=> 'Datenbank-Fehler:',
'search for what you are looking for'	=> 'finden Sie hier wonach Sie suchen',
'write that intro now'	=> 'das Intro jetzt verfassen',
'there is nothing here yet - create that entry now'	=> 'bis jetzt gibt es hier nichts - verfassen Sie den Eintrag jetzt',
'unknown page' => 'unbekannte Seite',
'Editing a multipage' => 'Multipage - Bearbeitung',
'Creating a multipage' => 'Multipage - Erstellung',
'Editing a singlepage' => 'Singlepage - Bearbeitung',
'Creating a singlepage' => 'Singlepage - Erstellung',
'This form has not yet been submitted.' => 
	'Dieses Formular wurde noch nicht gespeichert.',

/*	index.php	*/
'DB-Error:'	=> 'Datenbank_Fehler:',
'for admins: edit this page'	=> 'f&uuml;r Administratoren: Diese Seite editieren',
'Sorry, we could not find this page.'	=> 'Leider haben wir diese Seite nicht gefunden.',
'for admins: enter contents for this page now.'	=> 'f&uuml;r Administratoren: Geben Sie jetzt Inhalte f&uuml;r die Seite ein.',
'There is no entry in the database yet...'	=> 'Es gibt derzeit keine Eintr&auml;ge in der Datenbank.',
'for admins: make a new entry'	=> 'f&uuml;r Administratoren: Erstellen Sie einen neuen Eintrag',
'the chosen number was too high - showing newest'	=> 'Die gew&auml;hlte Nummer war zu hoch... zeige neueste',
'go to search options'	=> 'zeige Suchoptionen',
'find entries specifying your criteria...' => 'finden Sie Eintr&auml;ge mittels mehrerer Suchoptionen ',
'new entry in page'	=> 'neuer Eintrag auf der Seite',
'No fitting entry in the database was found...'	=> 
    'Es wurde kein passender Eintrag in der Datenbank gefunden...',
'you are seeing a selection of all entries on this page. ' =>
	'Sie sehen nur eine Auswahl aller Eintr&auml;ge dieser Seite.',
' to see all there are.' =>
	', um alle zu sehen.',

/*	lib/PolyPagerLib_Editing.php	*/
'Error: command is %s, but no id is given'	=> 'Fehler: Kommando ist %s, aber es wurde keine id angegeben.',
'heading:'	=> '&Uuml;berschrift:',
'Your Text:'	=> 'Ihr Text:',
'publish this section:'	=> 'diese Sektion ver&ouml;ffentlichen:',
'accessible in submenu:'	=> 'anw&auml;hlbar im Submen&uuml;:',
'delete'	=> 'l&ouml;schen',
'save'	=> 'speichern',
'There are no sections for this page in the database yet.'	=> 'F&uuml;r diese Seite wurde noch keine Sektion gespeichert.',
'Send'	=> 'Senden',
'last edited'	=> 'zuletzt ge&auml;ndert am',
'show next entry'	=> 'zeige n&auml;chsten Eintrag',
'Delete'	=> 'L&ouml;schen',
'Save'	=> 'Speichern',
'The record with %s = %s does not exist.'	=> 'Ein Datensatz mit %s = %s konnte nicht gefunden werden.',

/*	lib/PolyPagerLib_HTMLFraming.php	*/
'Administration Page'	=> 'Administration',
'show all %s-pages'	=> 'zeige alle %s-Seiten',
'show this page'	=> 'zeige diese Seite',
'click to see options (re-click to close)' => 'klicken Sie, um Unterkategorien zu zeigen (nochmals klicken, um sie zu verstecken)',
'gallery' => 'Galerie',
'It seems that PolyPager does not yet have its database configured. By clicking on this link you can assure that all the tables that PolyPager needs to operate are being created (if they have not been already). Note that this does not include your own data, i.e. the tables where all the data for multipages would be.'
	=> 'PolyPagers Datenbank scheint noch nicht konfiguriert zu sein. Durch das Anklicken dieses Buttons k&ouml;nnen Sie sicherstellen, dass alle Systemtabellen, die PolyPager zum Arbeiten ben&ouml;tigt, erstellt werden. Beachten Sie, dass dies nicht die Tabellen beinhaltet, die die Daten f&uuml;r Ihre Multipages beinhalten.',
'all' => 'alle',

/*	lib/PolyPagerLib_Sidepane.php	*/
'the RSS feed for this site. &lt;br/&gt; That means you will get to see the newest entries in the XML-Format.&lt;br/&gt;If you want, you can add that URL to your favorite news feed program.'
	=> 'der RSS-Feed f&uuml;r diese Seite. &lt;br/&gt;Das bedeutet, dass Sie unter diesem Link die neuesten Eintr&auml;ge dieser Website sehen. Der Link selbst f&uuml;hrt zur XML-Ansicht. Wenn Sie wollen, k&ouml;nnen Sie die URL dieses Links Ihrem News Feed Programm hinzuf&uuml;gen',
'from the page' => 'von der Seite',
'This is a demo version of PolyPager' => 'Dies ist eine Demo-Version von PolyPager',
'Admin name and password are set to "admin". Have fun!' =>
	'Administratorname und -Kennwort sind auf "admin" eingestellt. Viel Spass!',

/*	lib/PolyPagerLib_HTMLForms.php	*/
'If you tick this checkbox before hitting the save button, this entry will be fed to the list of latest entries and the RSS.' =>
	'Wenn Sie diese Checkbox aktivieren, wird dieser Eintrag in der Feed-Liste und im RSS zu sehen sein.',
' (If you do not publish this entry, it will be invisible there, too)' => 
    '(Wenn Sie diesen Eintrag nicht ver&ouml;ffentlichen, wird er dort ebenfalls unsichtbar sein.)',
'choose ' => 'W&auml;hlen Sie ',
'from:' => 'Auswahl:',
'This lower list is here to make sensible suggestions to fill the upper list conveniently. Click on an item to paste it into the text box. Clicking reset will restore the state of both lists to the load-time of the page.' =>
	'Diese untere Liste ist hier um Ihnen sinnvolle Vorschl&auml;ge zum F&uuml;llen des obigen Listfelds zu geben. Klicken Sie auf einen Eintrag, um ihn in das Feld zu kopieren. Der reset-link stellt den Zustand zum Zeitpunkt des letzten Ladens der Seite wieder her.',
'(show)' => '(zeigen)',
'(hide)' => '(verstecken)',
'other' => 'anders',

/*	lib/PolyPagerLib_Utils.php	*/
'the latest entries:'	=> 'Die neuesten Eintr&auml;ge:',
'click to see this %s from %s'	=> 'klicken Sie hier, um diese(n) %s vom %s zu sehen.',
'about this page'	=> '&Uuml;ber diese Seite',
'not set' => 'nicht gesetzt',
'no date set yet' => 'kein Datum gesetzt',
'this field is important: it defines which table to use for this page. Some of the fields below depend on what is given here, because PolyPager finds the values for those fields in this table.'
=> 'dies ist ein wichtiges Feld: Es definiert, welche Tabelle f&uuml;r diese Seite benutzt wird. Viele der Feld-Felder unten h&auml;ngen davon ab, was Sie hier angeben, denn PolyPager findet die Werte f&uuml;r diese Felder in der Tabelle.',
'these fields will not be shown to the public. Select fields from the list by clicking on them.' 
=> 'diese Felder sind &ouml;ffentlich nicht zu sehen. W&auml;hlen Sie Felder aus der untenstehenden Liste aus.',
'this field will be used to switch if the entry should be public or not' 
=> 'dieses Feld wird benutzt werden, um den jeweiligen Eintrag sichtbar bzw. unsichtbar zu machen.',
'this field will be used by PolyPager to group entries of this page. It will also be used to create sumenu entries (so the visitor can select what to see quickly) and search criteria.'
=> 'dieses Feld wird von PolyPager genutzt werden, um Eintr&auml;ge zu gruppieren. Es wird auch verwendet, um Untermen&uuml;-Eintr&auml;ge anzubieten (damit der Besucher besser aus dem Men&uuml; w&auml;hlen kann) und sinnvolle Suchkriterien zu erstellen.',
'this (date)field stores the time its entry was created.' 
=> 'dieses Feld speichert das Erstellungsdatum.',
'this (date)field would display when the last change to its entry took place.' 
=> 'dieses Feld wird anzeigen wann die letzte &Auml;nderung des Eintrags stattfand.',
'here you specify how many entries should be shown on one page. You can use a number or simply all'
=> 'hier geben Sie an, wieviel Eintr&auml;ge auf einer Seite zusammen zu sehen sein sollten. Geben Sie eine Nummer an oder einfach all',
'please specify any text here.' => 'Bitte geben Sie hier irgendeinen Text ein.',
'there is no table specified for this page yet' 
=> 'f&uuml;r diese Seite ist noch keine Tabelle angegeben',
'Warning: The selected skin couldn\'t be found.'
=>'Achtung: Die angegebene Skin konnte nicht gefunden werden.',
'there is no table in the database yet'
=> 'es gibt noch keine (nicht-System-)Tabellen in der Datenbank.',
'the name of the page.' => 'der Name der Seite',
'here you can specify allowed values for this field (via a comma-separated list). By doing so, you can choose from this list conveniently when editing the form.'
=> 'Sie k&ouml;nnen hier erlaubte Werte f&uuml;r das Feld angeben (mit eine Komma-separierten Liste). Dann k&ouml;nnen Sie im Formular immer gem&uuml;tlich aus dieser Liste ausw&auml;hlen.',
'[This field is disabled because the database specifies these values]'
=>'[Dieses Feld is nicht editierbar, da die diese Werte in der Datenbank angegeben sind]',
'you can chose a validation method that is checked on the content of this field when you submit a form.'
=> 'Sie k&ouml;nnen hier eine Validation ausw&auml;hlen, die zu Pr&uuml;fung verwendet wird, wenn Sie ein Formular abschicken.',
'check this box when this field contains much data (e.g. long texts). It will then only be shown if the page shows one entry and a link to it otherwise.'
=> 'Machen Sie hier einen Haken wenn der Inhalt des Felds sehr lang werden kann. Dann wird es nur angezeigt, wenn auf der Seite gerade nur einen Eintrag zu sehen ist (ansonsten ein Link)',
'when shown, the fields of an entry are ordered by the order in their table (0 to n). you can change the order index for this field here.'
=> 'Bei der Anzeige eines Eintrags werden die Felder in der Reihenfolge der Tabelle angezeigt (0 bis n). Hier k&ouml;nnen Sie den index f&uuml;r dieses Feld &auml;ndern.',
'you can embed the contents of this field within a string when it is displayed. Use &quot;[CONTENT]&quot; to represent its content. For instance, &lt; img src=&quot;path/to/[CONTENT]&quot;/> lets you display image names as actual image on the page.'
=> 'Sie k&ouml;nnen den Inhalt dieses Felds in Text einbetten, wenn es angezeigt wird, Benutzen Sie &quot;[CONTENT]&quot; um dabei den Inhalt zu repr&auml;sentieren. Zum Beispiel: &lt; img src=&quot;path/to/[CONTENT]&quot;/> zeigt einen Bildnamen als tats&auml;chliches Bild auf der Seite.',
'when this field is checked, you will find a link to this page in the menu.'
=> 'Wenn dieses Feld markiert ist, wird PolyPager einen Link zu dieser Seite im Men&uuml; erstellen.',
'this field holds a number which determines the order in which pages that are shown in the menu (see above) are arranged.'
=> 'Hier k&ouml;nnen Sie eine Nummer eintragen, die bestimmt, in welcher Reihenfolge die Links im Men&uuml; (siehe oben) erscheinen.',
'when this field is checked, entries on this page will be commentable by users.'
=> 'Wenn dieses Feld markiert ist, werden Eintr&auml;ge dieser Seite von Besuchern Ihrer Seite kommentiert werden k&ouml;nnen.',
'when this field is checked, administration info under each entry (edit-link,date of last change, ...) will not be shown.'
=> 'Wenn dieses Feld markiert ist, werden Administrationshinweise unter den Eintr&auml;gen (Bearbeitungslink, Datum der letzten &Auml;nderung,...) nicht gezeigt.',
'when this field is checked, the link to search form will not be shown.'
=> 'Wenn dieses Feld markiert ist, wird der Link zum Suchformular nicht gezeigt.',
'when this field is checked, the table of contents on top of the page will not be shown.'
=> 'Wenn dieses Feld markiert ist, wird die Inhalts&uuml;bersicht am Kopf der Seite nicht gezeigt.',
'here you can choose which field should be the order criterium.'
=> 'Hier k&ouml;nnen Sie ausw&auml;hlen, welches Feld der Tabelle Sortierungskriterium sein soll. ',
'ASC stands for ascending. Take numbers for an example: lowest numbers will come first, highest last. DESC means descending and works the other way round'
=> '&quot;ASC&quot; steht f&uuml;r &quot;ascending&quot;. Wenn im Sortierkriteriumsfeld beispielsweise Nummern stehen, bedeutet das: niedrige Nummern kommen zuerst, hohe zuletzt. &quot;DESC&quot; bedeutet &quot;descending&quot; und funktioniert andersherum.',
'this field will be used as title field. It will therefore look different to the others.'
=> 'Dieses Feld wird als Titel-Feld benutzt werden. Das bedeutet, dass es anders aussehen wird als andere und im Feeding als &Uuml;berschrift verwendet wird.',
'if this field is checked, new entries of this page will be fed. That means they will be listed under the latest entries (right on the page) and they will be available via RSS.'
=> 'Wenn dieses Feld markiert ist, werden neue Eintr&auml;ge dieser Seite gef&uuml;ttert. Das bedeutet, sie werden in der Liste namens &quot;Die neuesten Eintr&auml;ge&quot; erscheinen und per RSS verf&uuml;gbar sein.',
'this field has currently no meaning (that means: it is not yet implemented)'
=> 'dieses Feld hat beim derzeitigen Entwicklungsstand noch keine Bedeutung (es wurde noch nichts daf&uuml;r implementiert).',
'this field should be checked if you want only admins to see it (you can also protect all pages at once in the system settings)'
=> 'Dieses Feld sollte markiert sein, wenn diese Seite nur f&uuml;r Administratoren zug&auml;nglich sein soll (Sie k&ouml;nnen auch alle Seiten auf einmal besch&uuml;tzen, siehe Systemeigenschaften).',
'if this field is checked, users can search for entries of this page made in a particular month.'
=> 'Wenn dieses Feld markiert ist, k&ouml;nnen Besucher nach Eintr&auml;gen dieser Seite suchen, die in einem bestimmten Monat erstellt wurden.',
'if this field is checked, users can search for entries of this page made in a particular year.'
=> 'Wenn dieses Feld markiert ist, k&ouml;nnen Besucher nach Eintr&auml;gen dieser Seite suchen, die in einem bestimmten Jahr erstellt wurden.',
'if this field is checked, users can search for entries of this page by typing in a keyword.'
=> 'Wenn dieses Feld markiert ist, k&ouml;nnen Besucher nach Eintr&auml;gen dieser Seite suchen, indem sie ein Suchwort eingeben.',
'if this field is checked, users can navigate through entries of this page using previous- and next-links. Only use this when your entries are ordered by (see field order_by, above) the primary key of the table.'
=> 'Wenn dieses Feld markiert ist, k&ouml;nnen Besucher durch die Eintr&auml;gen dieser Seite navigieren, indem sie &quot;fr&uuml;here&quot;- bzw &quot;sp&auml;tere&quot;-links verwenden. Benutzen Sie dieses Feld, wenn als Sortierkriterium dieser Seite das Primary Key-Feld der Tabelle eingetragen ist.',
'if this field is checked, the label of each field is shown.'
=> 'Wenn dieses Feld markiert ist, werden die Labels neben den Feldern gezeigt (zur besseren Verst&auml;ndlichkeit).',
'here you can specify groups as a comma separated list. If you do so, the sections of this page can be assigned to one of those groups and the groups will each be an entry in the submenu of the page. If you enter something here, it will override the behavior of letting some sections be anchors to the page that are accessible from the submenu!'
=> 'Sie k&ouml;nnen hier Gruppen angeben (als Komma-separierte Liste). Sie k&ouml;nnen dann die Sektionen dieser Seite einzelnen Gruppen zuordnen, und diese Gruppen sind dann per Untermen&uuml; erreichbar. Wenn Sie das tun, &uuml;berschreibt dieses Verhalten die M&ouml;glichkeit, einzelne Sektionen per HTML-Anker vom Untermen&uuml; aus erreichbar zu machen.',
'the group of this entry (you will find the groups in the specifications for this page). The standard group contains entries that are always shown.'
 => 'die Gruppe dieses Eintrags (Sie finden die Gruppen f&auml;r diese Seite in auf deren Konfigurationsseite). Die standard-Gruppe enth&auml;lt Eintr&auml;ge, die immer gezeigt werden.',
'Activate this if you want your commenters to proof they are human before entering a comment. They will have to do so by entering one or two words. This will only work if you have PHP version >= 5 and if you get your personal access keys for this service (recaptcha.net) and fill them in below (It is worth it).'
 => 'Aktivieren Sie diese Option, wenn Sie Ihre Kommentare vor Spam sch&uuml;tzen wollen. Jeder Kommentator muss hier beweisen, dass er/sie ein Mensch ist, indem er/sie zwei W&ouml;rter eingibt. Sie brauchen daf&uuml; PHP Version >= 5 und zwei Zahlenschl&uuml;ssel von recaptcha.net, die Sie unten eintragen.',
 
 
/*	lib/PolyPagerLib_Showing.php	*/
'There are no pages yet. If you are the admin of this site, you can add your first page <a href="admin/?page=_sys_pages&amp;topic=pages">here</a>.'
    => 'Es gibt bisher keine Seiten. Wenn Sie Administrator dieser Website sind, k&ouml;nnen Sie <a href="admin/?page=_sys_pages&amp;topic=pages">hier</a> eine erstellen.',
'There is no start page set. If you are the admin of this site, you can set it at <a href="admin/edit.php?_sys_sys">the system properties</a>.'
    => 'Es ist keine Startseite angegeben, Wenn Sie Administrator dieser Website sind, k&ouml;nnen Sie das <a href="admin/edit.php?_sys_sys">in den Systemeinstellungen</a> nachholen.',
'please do not use SQL Code here in your keyword search...'	=> 'bitte benutzen Sie keine SQL-Schl&uuml;sselw&ouml;rter in Ihrer Stichwortsuche',
'Here you can find entries of your interest.&lt;br/&gt; You see several options that help you specifying your search for this kind of entry.&lt;br/&gt; Click on the symbol to the left of the option to in- or exclude it into your search. Several keywords are implicitely connected by AND.'
	=> 'Sie k&ouml;nnen hier nach Eintr&auml;gen suchen. Sie sehen verschiedene Optionen, die Ihnen helfen, Ihre Suche nach einem Eintrag dieser Seite zu spezifieren&lt;br/&gt; Klicken Sie auf das Symbol zur Linken der Option, um Sie in Ihrer Suche aufzunehemn bzw. sie auszuschliessen. Mehrere Schl&uuml;sselw&ouml;rter werden implizit mit UND verbunden.',
'you are seeing Nr %s (in whole there are %s entries)'	=> 'Sie sehen Nr %s (im Ganzen sind es %s Eintr&auml;ge)',
'you are seeing Nr %s through Nr %s (in whole there are %s entries)'	=> 'Sie sehen Nr %s bis %s (im Ganzen sind es %s Eintr&auml;ge)',
'show entries %s through %s'	=> 'zeige Eintr&auml;ge %s bis %s',
'previous'	=> 'vorherige',
'next'	=> 'n&auml;chste',
'This entry is viewable to the public' => 'Dieser Eintrag ist &ouml;ffentlich sichtbar',
'This entry is not viewable to the public' => 'Dieser Eintrag ist nicht &ouml;ffentlich sichtbar',
'show this entry in full length'	=> 'zeige diesen Eintrag in voller L&auml;nge',
'show whole entry'	=> 'zeige ganzen Beitrag',
'edit this entry.'	=> 'Diesen Eintrag bearbeiten.',
'for admins: edit this entry'	=> 'f&uuml;r Administratoren: Diesen Eintrag bearbeiten',
'for admins: edit this entry.'	=> 'f&uuml;r Administratoren: Diesen Eintrag bearbeiten.',
'delete this entry.' => 'Diesen Eintrag l&ouml;schen',
'make extra statements about fields of this page (a label, a list of possible values etc.)' 
	=> 'machen Sie hier Angaben zu Feldern dieser Seite (ein Label, eine Liste mit m&ouml;glichen Werten etc.).',
'January' => 'Januar',
'February' => 'Februar',
'March' => 'M&auml;rz',
'April' => 'April',
'May' => 'Mai',
'June' => 'Juni',
'July' => 'Juli',
'August' => 'August',
'September' => 'September',
'October' => 'Oktober',
'November' => 'November',
'December' => 'Dezember',
'entered in month' => 'eingetragen im Monat',
'of year' => 'des Jahres',
'entered in year' => 'eingetragen im Jahr',
'for keyword:' => 'nach Stichwort:',
'with keyword:' => 'mit Stichwort:',
'search' => 'Suchen',
'Search this site for:' => 'Suche:',
'Enter one or more keywords here to search for on this website.'
    => 'Geben Sie ein oder mehrere Suchw&ouml;rter an, nach denen Sie auf dieser Website suchen m&ouml;chten.',
'wow, you sure entered your comment quick. So quick, actually, that I labeled you as a machine and your comment as spam. Your comment has not been saved.'
	=> 'Wow, Sie haben Ihren Kommentar schnell eingegeben. So schnell, dass ich Sie als Maschine klassifiziert habe, und Ihren Kommentar als Spam. Letzterer wurde nicht gespeichert.',
'Your text contains tags that are not allowed. You can use one of those: &lt;b&gt;&lt;i&gt;&lt;ul&gt;&lt;ol&gt;&lt;li&gt;&lt;br&gt;&lt;p&gt;. Your comment has not been saved.'
	=> 'Ihr Text enthielt nicht erlaubte Tags. Sie k&ouml;nnen diese hier verwenden: &lt;b&gt;&lt;i&gt;&lt;ul&gt;&lt;ol&gt;&lt;li&gt;&lt;br&gt;&lt;p&gt;.Ihr Kommentar wurde nicht gespeichert.',
'you searched for:' => 'Sie suchten nach:',
'show index' => 'zeige Index',
'hide index' => 'verstecke Index',
'comments' => 'Kommentare',
'add a comment' => 'Kommentar schreiben',
'This Link gives you an RSS feed that tracks all comments on this entry. That way you can be follow the discussion without always coming here to check for new comments.'
    => 'Dieser Link f&uuml;hrt zu einem RSS Feed. Damit k&ouml;nnen Sie eine Disukussion verfolgen, ohne immer pers&ouml;nlich hier nach neuen Kommentaren zu suchen.',
'Related ' => 'Verbundene ',
    
/* javascript.php (here Umlaute need not be escaped)*/
'In detail: the content' => 'Im Detail heisst das: Der Inhalt',
'of the field' => 'des Felds',
'does not match the Regular Expression' => 'passt nicht zum vorgegebenen regulaeren Ausdruck',
'Are you sure you want to delete this entry?' => 'Sind Sie sicher, dass Sie diesen Eintrag loeschen wollen?',
'The following of the data you entered do not meet the given specifications:' 
	=> 'Folgende Eintraege sollten Sie vor dem Speichern noch korrigieren:',
'a change this field is important for other fields in this form. I therefore would like to reload this page. OK?'
	=> 'Eine Aenderung an diesem Feld ist wichtig fuer andere Felder dieses Formulars. Ich w&uuml;rde also gerne das Formular neu laden, damit die Daten erneuert werden. OK?',
'please specify a valid email address here.' =>
	'Bitte geben Sie eine gueltige email-Adresse an.',
'please specify a valid URL here.' =>
	'bitte geben Sie hier eine valide URL (Web-Adresse) an.',

/* rss.php */
'Comments on ' => 'Kommentare auf ',


/* formgroups*/
'admin' => 'Admin',
'metadata' => 'Metadaten',
'gallery' => 'Galerie',
'misc' => 'Sonstiges',
'name/table' => 'Name/Tabelle',
'menu-settings' => 'Men&uuml; Einstellungen',
'what to hide or show' => 'Was angezeigt oder versteckt werden soll',
'fields with special meaning' => 'Felder mit besonderer Bedeutung',

/* Labels */
//_sys_multipages / _sys_singlepages
'tablename' => 'Tabellenname',
'name' => 'Name',	
'in_menue' => 'im Men&uuml; zu sehen',
'menue_index' => 'Men&uuml;-Index',
'hide_options' => 'verstecke Optionen',
'hide_search' => 'verstecke Suchoptionen',
'hide_toc' => 'verstecke Inshaltsverzeichnis',
'hidden_fields' => 'nicht anzuzeigende Felder',
'order_by' => 'ordne nach',
'order_order' => 'ab/aufsteigend sortieren',
'publish_field' => 'publish-Feld',
'group_field' => 'group-Feld',
'group_order' => 'ab/aufsteigende Sortierung d. Gruppen',
'date_field' => 'Datums-Feld',
'edited_field' => 'zuletzt-editiert-Feld',	
'title_field' => 'Titel-Feld',
'feed' => 'feed',
'step' => 'Schritt',
'commentable' => 'kommentierbar',
'only_admin_access' => 'Zugang nur f&uuml;r Admins',
'hide_comments' => 'verstecke Kommentare', 	
'taggable' => 'tags benutzbar',
'search_month' => 'Suche nach Monat', 	
'search_year' => 'Suche nach Jahr',
'search_keyword' => 'Suche nach Keyword',	
'hide_labels' => 'verstecke Labels',
'search_range' => 'Suche nach Nummernfolgen',
'grouplist' => 'Gruppen',
'publish' => 'ver&ouml;ffentlichen',
'in_submenu' => 'diese Sektion als Untermen&uuml;eintrag',

//_sys_fields
'pagename' => 'Name der Seite',
'valuelist' => 'Liste mit m&ouml;glichen Werten',
'validation' => 'Validierung',
'not_brief' => 'Inhalt ist nicht gerade kurz',
'order_index' => 'Reihenfolgeindex',
'embed_in' => 'einbetten in',

//_sys_comments
'Date' => 'Datum',
'Time' => 'Uhrzeit',
'Name' => 'Name',
'eMail' => 'eMail',
'Homepage' => 'Homepage',
'Comment' => 'Kommentar',
 
//_sys_sys
'title' => 'Titel',
'author' => 'Autor',
'keywords' => 'Keywords',
'admin_name' => 'Administrator-Name',
'admin_pass' => 'Administrator-Kennwort',
'feed_amount' => 'Anzahl von Feeds',
'full_feed' => 'Voller Inhalt im Feed',
'start_page' => 'Startseite',
'lang' => 'Sprache',
'skin' => 'Skin',
'template' => 'Template',
'submenus_always_on' => 'Untermen&uuml;s statisch anzeigen',
'hide_public_popups' => 'Hilfe-Popups im &ouml;ffentlichen Bereich nicht zeigen',
'whole_site_admin_access' => 'Zugriff auf alle Seiten nur mit Admin-Passwort',
'link_to_gallery_in_menu' => 'Link zur Galerie im Men&uuml;',
'gallery_name'=> 'Name der Galerie',  	
'gallery_index'=> 'Galerie im Menu an Stelle',

//system pagenames
'_sys_comments' => 'Kommentare',
'_sys_fields' => 'Felder',
'_sys_feed' => 'Feeds'
);
	return $translation_table[$text];
}
?>
