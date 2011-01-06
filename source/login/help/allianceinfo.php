<?php
	require_once( '../../include/config_inc.php' );
	require( TBW_ROOT.'login/scripts/include.php' );

	login_gui::html_head();

	if(!isset($_GET['alliance']) || !Alliance::allianceExists($_GET['alliance']))
	{
?>
<p class="error">
	Diese Allianz gibt es nicht.
</p>
<?php
	}
	else
	{
		$alliance = Classes::Alliance($_GET['alliance']);

		if(!$alliance->getStatus())
		{
?>
<p class="error">Datenbankfehler &#40;1032&#41;</p>
<?php
		}
		else
		{
			$overall = $alliance->getTotalScores();
			$members = $alliance->getMembersCount();
			$average = floor($overall/$members);
?>
<h2>Allianzinfo <em class="alliancename"><?php echoutf8_htmlentities($alliance->getName())?></em></h2>
<dl class="allianceinfo">
	<dt class="c-allianztag">Allianz<span xml:lang="en">tag</span></dt>
	<dd class="c-allianztag"><?php echoutf8_htmlentities($alliance->getName())?></dd>

	<dt class="c-name">Name</dt>
	<dd class="c-name"><?php echoutf8_htmlentities($alliance->name())?></dd>

	<dt class="c-mitglieder">Mitglieder</dt>
	<dd class="c-mitglieder"><?php echohtmlentities($members)?></dd>

	<dt class="c-punkteschnitt">Punkteschnitt</dt>
	<dd class="c-punkteschnitt"><?php echoths($average)?> <span class="platz">(Platz <?php echoths($alliance->getRankAverage())?> von <?php echoths(getAlliancesCount())?>)</span></dd>

	<dt class="c-gesamtpunkte">Gesamtpunkte</dt>
	<dd class="c-gesamtpunkte"><?php echoths($overall)?> <span class="platz">(Platz <?php echoths($alliance->getRankTotal())?> von <?php echoths(getAlliancesCount())?>)</span></dd>
</dl>
<h3 id="allianzbeschreibung">Allianzbeschreibung</h3>
<div class="allianz-externes">
<?php
			print($alliance->getExternalDescription());
?>
</div>
<?php
			if(!$me->allianceTag())
			{
				if($alliance->allowApplications())
				{
?>
<ul class="allianz-bewerben">
	<li><a href="../allianz.php?action=apply&amp;for=<?php echohtmlentities(urlencode($alliance->getName()))?>&amp;<?php echohtmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>">Bei dieser Allianz bewerben</a></li>
</ul>
<?php
				}
				else
				{
?>
<p class="allianz-bewerben error">Diese Allianz akzeptiert keine neuen Bewerbungen.</p>
<?php
				}
			}
		}
	}

	login_gui::html_foot();
?>
