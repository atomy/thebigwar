<?php
if(!isset($_SERVER['DOCUMENT_ROOT']) || strlen($_SERVER['DOCUMENT_ROOT']) <= 0)
    $_SERVER['DOCUMENT_ROOT'] = getcwd()."/..";
    
require_once($_SERVER['DOCUMENT_ROOT'].'/include/config_inc.php');
require($_SERVER['DOCUMENT_ROOT'].'/login/scripts/include.php');

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
<h2>Allianzinfo <em class="alliancename"><?php echo utf8_htmlentities($alliance->getName())?></em></h2>
<dl class="allianceinfo">
	<dt class="c-allianztag">Allianz<span xml:lang="en">tag</span></dt>
	<dd class="c-allianztag"><?php echo utf8_htmlentities($alliance->getName())?></dd>

	<dt class="c-name">Name</dt>
	<dd class="c-name"><?php echo utf8_htmlentities($alliance->name())?></dd>

	<dt class="c-mitglieder">Mitglieder</dt>
	<dd class="c-mitglieder"><?php echo htmlentities($members)?></dd>

	<dt class="c-punkteschnitt">Punkteschnitt</dt>
	<dd class="c-punkteschnitt"><?php echo ths($average)?> <span class="platz">(Platz <?php echo ths($alliance->getRankAverage())?> von <?php echo ths(getAlliancesCount())?>)</span></dd>

	<dt class="c-gesamtpunkte">Gesamtpunkte</dt>
	<dd class="c-gesamtpunkte"><?php echo ths($overall)?> <span class="platz">(Platz <?php echo ths($alliance->getRankTotal())?> von <?php echo ths(getAlliancesCount())?>)</span></dd>
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
	<li><a href="../allianz.php?action=apply&amp;for=<?php echo htmlentities(urlencode($alliance->getName()))?>&amp;<?php echo htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>">Bei dieser Allianz bewerben</a></li>
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
