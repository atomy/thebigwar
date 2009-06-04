<?php
	require('include.php');

	$players = 0;
	$alliances = 0;
	$databases = get_databases();
	$first = true;
	foreach($databases as $dbid=>$database)
	{
		if($first)
		{
			define_globals($dbid);
			$first = false;
		}
		$players += Highscores::getCount('users', $database[0].'/highscores');
		$alliances += Highscores::getCount('alliances', $database[0].'/highscores');
	}

	$items = Classes::Items();

	gui::html_head();
?>
<h2 xml:lang="en">T-B-W &ndash; Features</h2>
<fieldset><legend>Features</legend>
<ul>
	<li><?=count($items->getItemsList('gebaeude'))?> Gebäude</li>
	<li><?=count($items->getItemsList('forschung'))?> Forschungsmöglichkeiten</li>
	<li><?=count($items->getItemsList('roboter'))?> verschiedene Roboter</li>
	<li><?=count($items->getItemsList('schiffe'))?> Raumschiffklassen</li>
	<li><?=count($items->getItemsList('verteidigung'))?> Verteidigungsanlagen</li>
	<li>Das Spiel läuft in Echtzeit, es gibt keine lästigen <span xml:lang="en">Eventhandler</span>-Wartezeiten</li>
	<li>Forschung lässt sich global oder lokal durchführen</li>
	<li>Ausgeklügeltes Allianzsystem</li>
	<li>Schließen Sie Bündnisse mit einzelnen Spielern</li>
	<li>Variabler Handelskurs, der sich den Zuständen im Universum anpasst</li>
	<li>Handelssystem: Geben Sie sich nähernden Transporten Rohstoffe mit auf den Rückweg</li>
	<li>Komfortable Einstellungsmöglichkeiten, die das Spielen erleichtern</li>
	<li>Völlige Ummodellierbarkeit des <span xml:lang="en">Design</span>s durch <span xml:lang="en">Skins</span></li>
	<li>Flug- und Kampferfahrungspunkte verschaffen Vorteil</li>
	<li><abbr title="Secure Hypertext Transfer Protocol" xml:lang="en"><span xml:lang="de">HTTPS</span></abbr> schützt vertrauliche Daten</li>
	<li>Stationieren Sie Flotten bei Ihren Verbündeten, um diesen zu unterstützen</li>
	<li>Fliegen Sie gemeinsame Angriffe mit Ihren Verbündeten</li>
	<li>Sichern Sie Ihre Ressourcen per Saveflugfunktion</li>
	<li>Veröffentlichen Sie Ihre Nachrichten einfach per Mausklick.
	<li>derzeit <?=$players?> Spieler</li>
	<li>derzeit <?=$alliances?> Allianz<?=($alliances != 1) ? 'en' : ''?></li>
<?php
	if(count($databases) > 1)
	{
?>
	<li>derzeit <?=count($databases)?> verschiedene Runden</li>
<?php
	}
?>
</ul>
</fieldset>
<p></p>
<fieldset><legend>Screenshots</legend>
<ul class="screenshots">
	<li><a href="images/screenshots/screenshot_01.png"><img src="images/screenshots/preview_01.png" alt="Screenshot 1" /></a>&nbsp;&nbsp;&nbsp;</li>
	<li><a href="images/screenshots/screenshot_02.png"><img src="images/screenshots/preview_02.png" alt="Screenshot 2" /></a>&nbsp;&nbsp;&nbsp;</li>
	<li><a href="images/screenshots/screenshot_03.png"><img src="images/screenshots/preview_03.png" alt="Screenshot 3" /></a>&nbsp;&nbsp;&nbsp;</li>
	<li><a href="images/screenshots/screenshot_04.png"><img src="images/screenshots/preview_04.png" alt="Screenshot 4" /></a>&nbsp;&nbsp;&nbsp;</li>
	<li><a href="images/screenshots/screenshot_05.png"><img src="images/screenshots/preview_05.png" alt="Screenshot 5" /></a>&nbsp;&nbsp;&nbsp;</li>
	<li><a href="images/screenshots/screenshot_06.png"><img src="images/screenshots/preview_06.png" alt="Screenshot 6" /></a>&nbsp;&nbsp;&nbsp;</li>
	<li><a href="images/screenshots/screenshot_07.png"><img src="images/screenshots/preview_07.png" alt="Screenshot 7" /></a>&nbsp;&nbsp;&nbsp;</li>
	<li><a href="images/screenshots/screenshot_08.png"><img src="images/screenshots/preview_08.png" alt="Screenshot 8" /></a>&nbsp;&nbsp;&nbsp;</li>
	<li><a href="images/screenshots/screenshot_09.png"><img src="images/screenshots/preview_09.png" alt="Screenshot 9" /></a>&nbsp;&nbsp;&nbsp;</li>
	<li><a href="images/screenshots/screenshot_10.png"><img src="images/screenshots/preview_10.png" alt="Screenshot 10" /></a>&nbsp;&nbsp;&nbsp;</li>
	<li><a href="images/screenshots/screenshot_11.png"><img src="images/screenshots/preview_11.png" alt="Screenshot 11" /></a>&nbsp;&nbsp;&nbsp;</li>
	<li><a href="images/screenshots/screenshot_12.png"><img src="images/screenshots/preview_12.png" alt="Screenshot 12" /></a>&nbsp;&nbsp;&nbsp;</li>
</ul>
</fieldset>
<p></p>
<?php
	gui::html_foot();
?>
