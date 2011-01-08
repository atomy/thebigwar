<?php
if(!isset($_SERVER['DOCUMENT_ROOT']) || strlen($_SERVER['DOCUMENT_ROOT']) <= 0)
    $_SERVER['DOCUMENT_ROOT'] = getcwd();
    
require_once($_SERVER['DOCUMENT_ROOT'].'/include/config_inc.php');
require($_SERVER['DOCUMENT_ROOT'].'/include.php');

    startseite_html_head();
?>

<div id="history">
<h1>Über TheBigWar</h1>
<p>
TheBigWar ist ein Aufbau- und Strategiespiel welches im Weltraum spielt. So können in einem gemeinsamen Universum Planeten bevölkert werden, Gebäude gebaut, geforscht und Kämpfe zwischen den Planeten ausgefochten werden. Jeder Spieler ist der Herr über eine begrenzte Anzahl von Planeten mit deren Hilfe er sich eine Flotte aufbauen kann und somit neue Welten erforschen und in Kontakt mit anderen Spielern gerät. So kann der neue Kontakt in einem friedlichen und regen Handel von Rohstoffen einher gehen aber auch zu einem erbittertem Krieg führen, in welchem es um das Erkämpfen von Rohstoffen und Vernichten der gegnerischen Flotte geht.
</p>
<p>
Das Projekt basiert auf dem Sourcecode vorangegangener Entwickler denen hier auch nochmal ein Dank ausgesprochen werden soll. Ein davon noch aktives Projekt findet sich unter <a href="http://www.s-u-a.net/">Stars Under Attack</a>.
</p>

<h2>Features:</h2>
<ul>
<li>11 Gebäude</li>
<li>12 Forschungsmöglichkeiten</li>
<li>6 verschiedene Roboter</li>
<li>16 Raumschiffklassen</li>
<li>7 Verteidigungsanlagen</li>
<li>Handelsrechner</li>
<li>Imperiumsübersicht</li>
<li>Echtzeitstrategie</li>
<li>Forschung lässt sich global oder lokal durchführen</li>
<li>Ausgeklügeltes Allianzsystem</li>
<li>Schlie&szlig;e Bündnisse mit einzelnen Spielern</li>
<li>Variabler Handelskurs, der sich den Zuständen im Universum anpasst</li>
<li>Handelssystem: Gebe nähernden Transporten Rohstoffe mit auf den Rückweg</li>
<li>Komfortable Einstellungsmöglichkeiten, die das Spielen erleichtern</li> 
<li>Völlige Ummodellierbarkeit des Designs durch Skins</li>
<li>Stationiere Flotten bei Verbündeten, um diese zu unterstützen</li>
<li>Fliege gemeinsame Angriffe mit deinen Verbündeten</li>
<li>Sichern deine Ressourcen per Saveflugfunktion</li>
<li>Veröffentliche Nachrichten einfach per Mausklick.</li>
</ul>
</div> <!-- History / -->

<?php 
    startseite_html_foot();
?>