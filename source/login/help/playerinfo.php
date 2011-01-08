<?php
if(!isset($_SERVER['DOCUMENT_ROOT']) || strlen($_SERVER['DOCUMENT_ROOT']) <= 0)
    $_SERVER['DOCUMENT_ROOT'] = getcwd()."/..";
    
require_once($_SERVER['DOCUMENT_ROOT'].'/include/config_inc.php');
require($_SERVER['DOCUMENT_ROOT'].'/login/scripts/include.php');

	login_gui::html_head();

	if(!isset($_GET['player']) || !User::userExists($_GET['player']))
	{
?>
<p class="error">
	Diesen Spieler gibt es nicht.
</p>
<?php
	}
	else
	{
		$user = Classes::User($_GET['player']);
		if(!$user->getStatus())
		{
?>
<p class="error">Datenbankfehler &#40;1033&#41;</p>
<?php
		}
		else
		{
			$at = $user->allianceTag();
			$suf = '';
			if($user->userLocked()) $suf = ' (g)';
			elseif($user->umode()) $suf = ' (U)';
?>
<h2>Spielerinfo <?php if($at){?><span class="playerinfo-allianz">[<a href="allianceinfo.php?alliance=<?php echo htmlentities(urlencode($at).'&'.urlencode(session_name()).'='.urlencode(session_id()))?>" title="Informationen zu dieser Allianz anzeigen"><?php echo utf8_htmlentities($at)?></a>]</span> <?php }?><em class="playername"><?php echo utf8_htmlentities($user->getName())?></em><span class="suffix"><?php echo $suf?></span></h2>


<?php
			$show_koords = $me->maySeeKoords($user->getName());
			if($show_koords)
			{
?>
<h3 id="punkte">Punkte</h3>
<dl class="punkte">
	<dt class="c-gebaeude">Gebäude</dt>
	<dd class="c-gebaeude"><?php echo ths($user->getScores(0))?></dd>

	<dt class="c-forschung">Forschung</dt>
	<dd class="c-forschung"><?php echo ths($user->getScores(1))?></dd>

	<dt class="c-roboter">Roboter</dt>
	<dd class="c-roboter"><?php echo ths($user->getScores(2))?></dd>

	<dt class="c-flotte">Flotte</dt>
	<dd class="c-flotte"><?php echo ths($user->getScores(3))?></dd>

	<dt class="c-verteidigung">Verteidigung</dt>
	<dd class="c-verteidigung"><?php echo ths($user->getScores(4))?></dd>

	<dt class="c-flugerfahrung">Flugerfahrung</dt>
	<dd class="c-flugerfahrung"><?php echo ths($user->getScores(5))?></dd>

	<dt class="c-kampferfahrung">Kampferfahrung</dt>
	<dd class="c-kampferfahrung"><?php echo ths($user->getScores(6))?></dd>

	<dt class="c-gesamt">Gesamtpunkte</dt>
	<dd class="c-gesamt"><?php echo ths($user->getScores())?></span> <span class="platz">(Platz&nbsp;<?php echo ths($user->getRank())?> <span class="gesamt-spieler">von <?php echo ths(getUsersCount())?>)</span></span></dd>
</dl>
<?php
			}
            		else
			{
?>
<h3 id="punkte">Punkte</h3>
<dl class="punkte">
<dt class="c-gesamt">Gesamtpunkte</dt>
<dd class="c-gesamt"><?php echo ths($user->getScores())?></span> <span class="platz">(Platz&nbsp;<?php echo ths($user->getRank())?> <span class="gesamt-spieler">von <?php echo ths(getUsersCount())?>)</span></span></dd>
</dl>
<?php
			}
			if($show_koords)
			{
?>
<h3 id="ausgegebene-rohstoffe">Ausgegebene Rohstoffe</h3>
<dl class="punkte">
	<dt class="c-carbon">Carbon</dt>
	<dd class="c-carbon"><?php echo ths($user->getSpentRess(0))?></dd>

	<dt class="c-eisenerz">Aluminium</dt>
	<dd class="c-eisenerz"><?php echo ths($user->getSpentRess(1))?></dd>

	<dt class="c-wolfram">Wolfram</dt>
	<dd class="c-wolfram"><?php echo ths($user->getSpentRess(2))?></dd>

	<dt class="c-radium">Radium</dt>
	<dd class="c-radium"><?php echo ths($user->getSpentRess(3))?></dd>

	<dt class="c-tritium">Tritium</dt>
	<dd class="c-tritium"><?php echo ths($user->getSpentRess(4))?></dd>

	<dt class="c-gesamt">Gesamt</dt>
	<dd class="c-gesamt"><?php echo ths($user->getSpentRess())?></dd>
</dl>
<?php
			}
?>

<h3 id="benutzerbeschreibung">Benutzerbeschreibung</h3>
<div class="benutzerbeschreibung">
<?php
			print($user->getUserDescription());
?>

</div>
<?php
                        if($show_koords)
                        {
?>

<h3 id="buendnisse">Bündnisse</h3>
<?php
			$verbuendet = $user->getVerbuendetList();
			if(count($verbuendet) <= 0)
			{
?>
<p class="buendnisse-keine">
	Dieser Benutzer ist derzeit in keinem Bündnis.
</p>
<?php
			}
			else
			{
?>
<ul class="buendnis-informationen">
<?php
				foreach($verbuendet as $verbuendeter)
				{
?>
	<li><a href="playerinfo.php?player=<?php echo htmlentities(urlencode($verbuendeter))?>&amp;<?php echo htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Informationen zu diesem Spieler anzeigen"><?php echo utf8_htmlentities($verbuendeter)?></a></li>
<?php
				}
?>
</ul>
<?php
       		 }
?>
<?php
	}
?>
<h3 id="daten">Daten</h3>
<dl class="daten">
        <dt class="c-letzte-aktivitaet">Letzte Aktivität</dt>
<?php
                        $last_activity = $user->getLastActivity();
                        if($last_activity !== false && isset($verbuendet) || $last_activity !== false && $show_koords)
                        {
?>
        <dd class="c-letzte-aktivitaet"><?php echo date('H:i:s, Y-m-d', $last_activity)?> (Serverzeit)</dd>
<?php
                        }
			    elseif(($last_activity + 86400) > time())
			   {
?>
 	<dd class="c-letzte-aktivitaet nie">Innerhalb der letzten 24 Stunden</dd>

<?php
                        }
                        else
                        {
?>
        <dd class="c-letzte-aktivitaet nie">Unbekannt</dd>
<?php
                        }
?>


	<dt class="c-registrierung">Registrierung</dt>
<?php
			$registration_time = $user->getRegistrationTime();
			if($registration_time !== false)
			{
?>
	<dd class="c-registrierung"><?php echo date('H:i:s, Y-m-d', $registration_time)?> (Serverzeit)</dd>
<?php
			}
			else
			{
?>
	<dd class="c-registriergung unbekannt">Unbekannt</dd>
<?php
			}
?>
</dl>
<?php
			if($show_koords)
			{
?>
<h3 id="planeten">Planeten</h3>
<ul class="playerinfo-planeten">
<?php
				$planets = $user->getPlanetsList();
				$active_planet = $user->getActivePlanet();
				foreach($planets as $planet)
				{
					$user->setActivePlanet($planet);
					$pos = $user->getPos();
					$pos_string = $user->getPosString();
?>
	<li><?php echo utf8_htmlentities($user->planetName())?> <span class="koords">(<a href="../karte.php?galaxy=<?php echo htmlentities(urlencode($pos[0]))?>&amp;system=<?php echo htmlentities(urlencode($pos[1]))?>&amp;<?php echo htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Jenes Sonnensystem in der Karte ansehen"><?php echo utf8_htmlentities($pos_string)?></a>)</span></li>
<?php
				}
				if($active_planet !== false) $user->setActivePlanet($active_planet);
?>
</ul>
<?php
			}

			if($user->getName() != $_SESSION['username'])
			{
?>
<h3 id="nachricht">Nachricht</h3>
<form action="../nachrichten.php?to=&amp;<?php echo htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" method="post" class="playerinfo-nachricht" onsubmit="this.setAttribute('onsubmit', 'return confirm(\'Doppelklickschutz: Sie haben ein zweites Mal auf \u201eAbsenden\u201c geklickt. Dadurch wird die Nachricht auch ein zweites Mal abgeschickt. Sind Sie sicher, dass Sie diese Aktion durchführen wollen?\');');">
	<dl>
		<dt class="c-betreff"><label for="betreff-input">Betreff</label></dt>
		<dd class="c-betreff"><input type="text" id="betreff-input" name="betreff" maxlength="30" tabindex="1" /></dd>

		<dt class="c-inhalt"><label for="inhalt-input">Inhalt</label></dt>
		<dd class="c-inhalt"><textarea id="inhalt-input" name="inhalt" cols="50" rows="10" tabindex="2"></textarea></dd>
	</dl>
<?php
				if(!$me->userLocked())
				  {
?>
	<div><button type="submit" accesskey="n" tabindex="3"><kbd>N</kbd>achricht absenden</button><input type="hidden" name="empfaenger" value="<?php echo utf8_htmlentities($user->getName())?>" /></div>
<?php
		}
?>
</form>
<?php
			}
		}
	}
	login_gui::html_foot();
?>
