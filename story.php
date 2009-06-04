<?php
	require('include.php');
	
	$players = 0;
	$alliances = 0;
	$databases = get_databases();
	$first = true;
	foreach($databases as $database)
	{
		if($first)
		{
			define_globals($database[0]);
			$first = false;
		}
		$players += Highscores::getCount('users', $database[0].'/highscores');
		$alliances += Highscores::getCount('alliances', $database[0].'/highscores');
	}
	
	$items = Classes::Items();
	
	gui::html_head();
?>
<h2 xml:lang="en">T-B-W Story</h2>
<fieldset><legend>Story</legend>
	<p>Allgemeiner Hintergrund:</p>
	<p>2063
Durchbruch in der Nukleartechnik ; ein internationales Team von
Wissenschaftlern entwickelt die erste funktionsfähige Kernfusionsanlage
auf Wasserstoffbasis</p>

	<p>2065 Der Große Krieg beginnt auf der Erde
; verschiedene Nationen verschmelzen im Kampf um die letzten fossilen
Brennstoffe zu den Machtblöcken ; welche in den nächsten 3 Jahrzehnten
die Erde beherrschen werden:</p>
	<p>-Die Westliche Allianz<br>-Freie Asiatische Konförderation<br>-Vereinigte Staaten von Afrika<br>-Die Islamische Konförderation</p>
	<p>2083
Das erste kommerzielle Raumschiff mit Plasmastrahlantrieb wird gebaut
und getestet ; erste feste Ansiedlungen auf dem irdischen Mond , dem
Mars und den Marsmonden Deimos und Phobos</p>
	<p>2090 Bau einer
Aussenstation auf dem Jupitermond Io , äusserste Grenze der
menschlichen Präsenz im Sonnensystem...erste nicht-staatliche
Ansiedlung im All , Bergwerksanlage , ermöglicht durch die Fusion
mehrerer Konzerne zu einem MegaKonzern</p>
	<p>bis 3011 ...es bilden
sich immer mehr MegaKonzerne und mittlere Konzerne , die staatliche
Führungsrolle verliert durch rapide ansteigende Erdbevölkerung ,
Massenarbeitslosigkeit und ausufernde Korruption an Macht und
Bedeutung...</p>

	<p>3021 Das Versorgungsschiff "Yamato XII" stösst bei
einem Nachschubflug nach Io auf eine havariertes Raumschiff der
extraterrestrischen Rasse der Sumui-Baan ; es gelingt den menschen ,
den beschädigten Handelsraumer bis nach Io in Schlepp zu nehmen und mit
Hilfe der dortigen Rohstoffe und Techniken den Aliens eine Reparatur
der Defekte zu ermöglichen</p>
	<p>3023 Die legendäre "Goldene Flotte"
erscheint im Solarsystem ; die Sumui-Baan , eine Rasse interstellarer
Weltraumnomaden und Händler , nehmen Kontakt zu den Führungsspitzen der
irdischen Konzerne und Regierungen auf ; zu den diversen technischen
neuerungen , welche der Kontakt bringt , zählt als wichtigstes wohl der
Hyperspaceantrieb , der es zum ersten Mal ermöglicht , die Grenzen des
Solarsystems zu überwinden und Kolonien auf fernen Welten zu errichten..</p>
	<p>Die
nächsten 150 Jahre sind geprägt von einer Erscheinung , die gemeinhin
"Der Exodus" genannt wird....Millionen und Abermillionen von Menschen
verlassen Terra und das Soare System , um in den Tiefen der Galaxis
nach Glück und Reichtum zu suchen , sei es als Angestellte im Dienst
der Konzerne , sei es als Mitglied einer der unzähligen philospohisch ,
religiös oder ideologisch motivierten Gemeinschaften...</p>
	<p>Die
"Alten Rassen" , welche das Universum bislang beherrschten , werden
durch das plözliche Auftauchen einer dynamischen , jungen und
agressiven Menschheit ihrer Rolle als Führungsgremium enthoben ; es
kommt sowohl zu friedlichen Kontakten und gegenseitiger Toleranz wie
auch zu schweren Kämpfen und Kriegen...</p>
	<p>Doch der Vormarsch der
Menschheit ist nicht aufzuhalten ,und so gibt es im Jahr 3719 Kolonien
, Sternenreiche und Ansiedlungen in allen Ecken und Winkeln der
Galaxis...</p>
	<p>Bedauerlicherweise hat die soziale Entwicklung nicht
mit dem technischen Aufschwung Schritt halten können...und das Erbe der
Vergangenheit holt die Menschen erneut ein....</p>

	<p>Wieder toben
Kriege , diesmal zwischen Sternenreichen , und Planeten verglühen im
atomaren Feuer der schrecklichsten Vernichtungswaffen , die der Mensch
je besass...</p>
	<p>Erneut schwingt der Tod seine Sense und zwischen
den kämpfenden Allianzen suchen mordgierige Banden von Freibeutern
ebenso den persönlichen Vorteil wie auch gewiefte
Sternenhändler... </p><br>
</fieldset>
<p></p><p></p>
<?php
	gui::html_foot();
?>
