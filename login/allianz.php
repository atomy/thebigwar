<?php
	require('scripts/include.php');

	login_gui::html_head();

	$action = false;
	if(isset($_GET['action']))
		$action = $_GET['action'];

	__autoload('Alliance');

	if(!$me->allianceTag() || !($alliance = Classes::Alliance($me->allianceTag())) || !$alliance->getStatus())
	{
		$application = $me->allianceApplication();
		if($application)
		{
			if($action == 'cancel' && $me->cancelAllianceApplication())
			{
?>
<h2><a href="allianz.php?<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Zurück zur Allianzübersicht" tabindex="<?=$tabindex++?>" accesskey="z">Allian<kbd>z</kbd></a></h2>
<p class="successful">Ihre Bewerbung wurde zurückgezogen.</p>
<?php
			}
			else
			{
?>
<h2>Allianz</h2>
<p class="allianz-laufende-bewerbung">Sie haben derzeit eine laufende Bewerbung bei der Allianz <a href="help/allianceinfo.php?alliance=<?=htmlentities(urlencode($application))?>&amp;<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>"><?=utf8_htmlentities($application)?></a>.</p>
<ul class="allianz-laufende-bewerbung-aktionen">
	<li><a href="allianz.php?action=cancel&amp;<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>">Bewerbung zurückziehen</a></li>
</ul>
<?php
			}
		}
		elseif($action == 'gruenden')
		{
?>
<h2><a href="allianz.php?<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Zurück zur Allianzübersicht" tabindex="<?=$tabindex++?>" accesskey="z">Allian<kbd>z</kbd></a></h2>
<?php
			if(isset($_POST['tag']) && isset($_POST['name']))
			{
				$_POST['tag'] = trim($_POST['tag']);
				$keyarray = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', ' ');
				#ALLyTag Zeichen pruefen
				$stringallytag = $_POST['tag'];
				$noblocktag = true;
				for($i=0;$i<strlen($stringallytag);$i++)
				{
					$explode[$i] = substr($stringallytag, $i, 1);
					if(!in_array($explode[$i],$keyarray)) $noblocktag = false;
				}
				#ALLyName Zeichen pruefen
				$stringallyname = $_POST['name'];
				$noblockname = true;
				for($i=0;$i<strlen($stringallyname);$i++)
				{
					$explode[$i] = substr($stringallyname, $i, 1);
					if(!in_array($explode[$i],$keyarray)) $noblockname = false;
				}

				if($noblockname == false || $noblocktag == false)
				{
?>
<p class="error">Der Allianztag oder der Allianz<span xml:lang="en">name</span> enthalten ungültige <span xml:lang="en">Zeich</span>en.</p>
<?php
				}

				elseif(strlen($_POST['tag']) < 2)
				{
?>
<p class="error">Das Allianz<span xml:lang="en">tag</span> muss mindestens zwei <span xml:lang="en">Bytes</span> lang sein.</p>
<?php
				}
				elseif(strlen($_POST['tag']) > 6)
				{
?>
<p class="error">Das Allianz<span xml:lang="en">tag</span> darf höchstens sechs <span xml:lang="en">Bytes</span> lang sein.</p>
<?php
				}
				elseif(Alliance::allianceExists($_POST['tag']))
				{
?>
<p class="error">Es gibt schon eine Allianz mit diesem <span xml:lang="en">Tag</span>.</p>
<?php
				}
				else
				{
					$alliance_obj = Classes::Alliance($_POST['tag']);
					if(!$alliance_obj->create())
					{
?>
<p class="error">Datenbankfehler.</p>
<?php
					}
					$alliance_obj->name($_POST['name']);
					$alliance_obj->addUser($_SESSION['username'], $me->getScores());
					$alliance_obj->setUserStatus($_SESSION['username'], "Gr\xc3\xbcnder");
					$alliance_obj->setUserPermissions($_SESSION['username'], 0, true);
					$alliance_obj->setUserPermissions($_SESSION['username'], 1, true);
					$alliance_obj->setUserPermissions($_SESSION['username'], 2, true);
					$alliance_obj->setUserPermissions($_SESSION['username'], 3, true);
					$alliance_obj->setUserPermissions($_SESSION['username'], 4, true);
					$alliance_obj->setUserPermissions($_SESSION['username'], 5, true);
					$alliance_obj->setUserPermissions($_SESSION['username'], 6, true);
					$alliance_obj->setUserPermissions($_SESSION['username'], 7, true);
					$alliance_obj->setUserPermissions($_SESSION['username'], 8, true);
					$me->allianceTag($_POST['tag']);
?>
<p class="successful">Die Allianz <?=utf8_htmlentities($_POST['tag'])?> wurde erfolgreich gegründet.</p>
<?php
					login_gui::html_foot();
					exit();
				}
			}
?>
<form action="allianz.php?action=gruenden&amp;<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" method="post" class="allianz-gruenden-form">
	<dl>
		<dt><label for="allianztag-input">Allianz<span xml:lang="en">tag</span></label></dt>
		<dd><input type="text" name="tag" id="allianztag-input" value="<?=isset($_POST['tag']) ? utf8_htmlentities($_POST['tag']) : ''?>" title="Das Allianztag wird in der Karte und in den Highscores vor dem Benutzernamen angezeigt." maxlength="6" tabindex="<?=$tabindex++?>" /></dd>

		<dt><label for="allianzname-input">Allianzname</label></dt>
		<dd><input type="text" name="name" id="allianzname-input" value="<?=isset($_POST['name']) ? utf8_htmlentities($_POST['name']) : ''?>" tabindex="<?=$tabindex++?>" /></dd>
	</dl>
<?php
		if(!$me->userLocked())
		{
?>
	<div><button type="submit" tabindex="<?=$tabindex++?>" accesskey="n">Allia<kbd>n</kbd>z gründen</button></div>
<?php
		}
?>
</form>
<?php
		}
		elseif($action == 'apply')
		{
			if(!isset($_GET['for']))
				$_GET['for'] = '';
			if(!Alliance::allianceExists($_GET['for']))
			{
?>
<h2><a href="allianz.php?<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Zurück zur Allianzübersicht" accesskey="z" tabindex="<?=$tabindex++?>">Allian<kbd>z</kbd></a></h2>
<p class="error">Diese Allianz gibt es nicht.</p>
<?php
			}
			else
			{
				$alliance = Classes::Alliance($_GET['for']);
				if(!$alliance->allowApplications())
				{
?>
<h2><a href="allianz.php?<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Zurück zur Allianzübersicht" accesskey="z" tabindex="<?=$tabindex++?>">Allian<kbd>z</kbd></a></h2>
<p class="error">Diese Allianz akzeptiert keine neuen Bewerbungen.</p>
<?php
				}
				elseif(!isset($_POST['text']))
				{
?>
<h2><a href="allianz.php?<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Zurück zur Allianzübersicht" accesskey="z" tabindex="<?=$tabindex++?>">Allian<kbd>z</kbd></a></h2>
<form action="allianz.php?action=apply&amp;for=<?=htmlentities(urlencode($_GET['for']))?>&amp;<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" method="post" class="allianz-bewerben-form">
	<dl>
		<dt><label for="bewerbungstext-textarea">Bewerbungste<kbd>x</kbd>t</label></dt>
		<dd><textarea name="text" id="bewerbungstext-textarea" cols="50" rows="17" accesskey="x" tabindex="<?=$tabindex++?>"></textarea></dd>
	</dl>
<?php
		if(!$me->userLocked())
		{
?>
	<div><button type="submit" accesskey="n" tabindex="<?=$tabindex++?>">Bewerbu<kbd>n</kbd>g absenden</button></div>
<?php
		}
?>
</form>
<?php
				}
				else
				{
?>
<h2><a href="allianz.php?<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Zurück zur Allianzübersicht" tabindex="<?=$tabindex++?>" accesskey="z">Allian<kbd>z</kbd></a></h2>
<?php
					if($me->allianceApplication($_GET['for'], $_POST['text']))
					{
?>
<p class="successful">Ihre Bewerbung wurde erfolgreich abgesandt.</p>
<?php
					}
					else
					{
?>
<p class="error">Datenbankfehler.</p>
<?php
					}
				}
			}
		}
		else
		{
?>
<h2>Allianz</h2>
<p class="allianz-keine">Sie gehören derzeit keiner Allianz an. Es bieten sich Ihnen zwei Möglichkeiten.</p>
<form action="allianz.php" method="get" class="allianz-moeglichkeiten">
	<ul>
		<li><a href="allianz.php?action=gruenden&amp;<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" tabindex="<?=$tabindex+2?>" accesskey="z">Eigene Allian<kbd>z</kbd> gründen</a></li>
		<li><input type="text" name="search" value="<?=(isset($_GET['search'])) ? utf8_htmlentities($_GET['search']) : ''?>" tabindex="<?=$tabindex++?>" accesskey="n" /> <button type="submit" tabindex="<?=$tabindex++?>">Allia<kbd>n</kbd>z suchen</button><input type="hidden" name="<?=htmlentities(session_name())?>" value="<?=htmlentities(session_id())?>" /></li>
	</ul>
</form>
<?php
			$tabindex += 2;
			if(isset($_GET['search']) && $_GET['search'])
			{
?>
<h3 id="allianz-suchergebnisse">Suchergebnisse</h3>
<?php
				$alliances = findAlliance($_GET['search']);

				if(count($alliances) <= 0)
				{
?>
<p class="error">Es wurden keine Allianzen gefunden.</p>
<?php
				}
				else
				{
?>
<ul class="allianz-suchergebnisse">
<?php
					foreach($alliances as $alliance)
					{
?>
	<li><a href="help/allianceinfo.php?alliance=<?=htmlentities(urlencode($alliance))?>&amp;<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Genauere Informationen anzeigen"><?=utf8_htmlentities($alliance)?></a></li>
<?php
					}
?>
</ul>
<?php
				}
?>
</ul>
<?php
			}
		}
	}
	else
	{
		if($action == 'liste')
		{
			$sort = (isset($_GET['sortby']) && in_array($_GET['sortby'], array('punkte', 'rang', 'time')));
			if(!isset($_GET['invert']) || !$_GET['invert'])
				$invert = '&amp;invert=1';
			else
				$invert = '';
?>
<h2><a href="allianz.php?<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Zurück zur Allianzübersicht" accesskey="z" tabindex="<?=$tabindex++?>">Allian<kbd>z</kbd></a></h2>
<?php
			if($alliance->checkUserPermissions($_SESSION['username'], 6) || $alliance->checkUserPermissions($_SESSION['username'], 5) || $alliance->checkUserPermissions($_SESSION['username'], 4))
			{
?>
<form action="allianz.php?action=liste<?=isset($_GET['sortby']) ? '&amp;sortby='.htmlentities(urlencode($_GET['sortby'])) : ''?><?=isset($_GET['invert']) ? '&amp;invert='.htmlentities(urlencode($_GET['invert'])) : ''?>&amp;<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" method="post" class="allianz-liste-form">
<?php
			}
			if($alliance->checkUserPermissions($_SESSION['username'], 5))
			{
?>
<script type="text/javascript">
	var kick_warned = false;
</script>
<?php
			}
?>
<table class="allianz-liste">
	<thead>
		<tr>
			<th class="c-name"><a href="allianz.php?action=liste<?=$sort ? '' : $invert?>&amp;<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Nach Namen sortieren">Name</a></th>
			<th class="c-rang"><a href="allianz.php?action=liste&amp;sortby=rang<?=($sort && $_GET['sortby'] == 'rang') ? $invert : ''?>&amp;<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Nach Rang sortieren">Rang</a></th>
			<th class="c-punkte"><a href="allianz.php?action=liste&amp;sortby=punkte<?=($sort && $_GET['sortby'] == 'punkte') ? $invert : ''?>&amp;<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Nach Punkten sortieren" accesskey="u">P<kbd>u</kbd>nkte</a></th>
			<th class="c-aufnahmezeit"><a href="allianz.php?action=liste&amp;sortby=time<?=($sort && $_GET['sortby'] == 'time') ? $invert : ''?>&amp;<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Nach Aufnahmezeit sortieren" accesskey="n">Auf<kbd>n</kbd>ahmezeit</a></th>
<?php
			if($alliance->checkUserPermissions($_SESSION['username'], 5))
			{
?>
			<th class="c-kick">Kick</th>
<?php
			}
?>
		</tr>
	</thead>
	<tbody>
<?php
			if($sort)
				$liste = $alliance->getUsersList($_GET['sortby'], (isset($_GET['invert']) && $_GET['invert']));
			else $liste = $alliance->getUsersList(true, (isset($_GET['invert']) && $_GET['invert']));

			$liste_count = count($liste);
			foreach($liste as $i=>$member_name)
			{
				if($alliance->checkUserPermissions($_SESSION['username'], 5) && isset($_POST['kick']) && isset($_POST['kick'][$i]) && $member_name != $_SESSION['username'])
				{
					$alliance->kickUser($member_name, $_SESSION['username']);
					continue;
				}
?>
		<tr>
			<th class="c-name"><a href="help/playerinfo.php?player=<?=htmlentities(urlencode($member_name))?>&amp;<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Informationen zu diesem Spieler anzeigen"><?=utf8_htmlentities($member_name)?></a></th>
<?php
				if($alliance->checkUserPermissions($_SESSION['username'], 6))
				{
					if(isset($_POST['rang']) && isset($_POST['rang'][$i]))
						$alliance->setUserStatus($member_name, $_POST['rang'][$i]);
?>
			<td class="c-rang"><input type="text" name="rang[<?=utf8_htmlentities($i)?>]" value="<?=utf8_htmlentities($alliance->getUserStatus($member_name))?>" tabindex="<?=$tabindex++?>" /></td>
<?php
					$kick_tabindex = $tabindex+$liste_count;
				}
				else
				{
?>
			<td class="c-rang"><?=utf8_htmlentities($alliance->getUserStatus($member_name))?></td>
<?php
					$kick_tabindex = $tabindex++;
				}
?>
			<td class="c-punkte"><?=ths($alliance->getUserScores($member_name))?></td>
			<td class="c-aufnahmezeit"><?=date('Y-m-d, H:i:s', $alliance->getUserJoiningTime($member_name))?></td>
<?php
				if($alliance->checkUserPermissions($_SESSION['username'], 5))
				{
?>
			<td class="c-kick"><input type="checkbox" name="kick[<?=utf8_htmlentities($i)?>]" onchange="if(!kick_warned){ kick_warned=true; alert('Die ausgewählten Benutzer werden beim Speichern der Änderungen aus der Allianz geworfen.'); }"<?=($member_name == $_SESSION['username']) ? ' disabled="disabled"' : ''?> tabindex="<?=$kick_tabindex?>" /></td>
<?php
				}
?>
		</tr>
<?php
			}
?>
	</tbody>
</table>
<?php
			if($alliance->checkUserPermissions($_SESSION['username'], 4))
			{
				if(isset($_POST['toggle_allow_applications']) && $_POST['toggle_allow_applications'])
					$alliance->allowApplications(isset($_POST['allow_applications']));
?>
	<div><input type="hidden" name="toggle_allow_applications" value="1" /><input type="checkbox" name="allow_applications" id="i-allow-applications"<?=$alliance->allowApplications() ? ' checked="checked"' : ''?> /> <label for="i-allow-applications">Neue Bewerbungen akzeptieren</label></div>
<?php
			}
			if($alliance->checkUserPermissions($_SESSION['username'], 6) || $alliance->checkUserPermissions($_SESSION['username'], 5) || $alliance->checkUserPermissions($_SESSION['username'], 4))
			{
?>
	<div><button type="submit" tabindex="<?=$tabindex++?>" accesskey="n">Ä<kbd>n</kbd>derungen speichern</button></div>
</form>
<?php
			}
			if($alliance->checkUserPermissions($_SESSION['username'], 5))
				$tabindex += $liste_count;
		}
		else
		{
			$austreten = !$alliance->checkUserPermissions($_SESSION['username'], 7);
			if(!$austreten)
			{
				$members = $alliance->getUsersList();
				foreach($members as $name)
				{
					if($name == $_SESSION['username'])
						continue;
					if($alliance->checkUserPermissions($name, 7))
					{
						$austreten = true;
						break;
					}
				}
			}

			if($alliance->checkUserPermissions($_SESSION['username'], 8) && $action == 'aufloesen')
			{
?>
<h2><a href="allianz.php?<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Zurück zur Allianzübersicht" tabindex="<?=$tabindex++?>" accesskey="z">Allian<kbd>z</kbd></a></h2>
<?php
				if($alliance->destroy($_SESSION['username']))
				{
?>
<p class="successful">Die Allianz wurde aufgelöst.</p>
<?php
				}
				else
				{
?>
<p class="error">Datenbankfehler.</p>
<?php
				}
			}
			elseif($austreten && $action == 'austreten')
			{
?>
<h2><a href="allianz.php?<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Zurück zur Allianzübersicht" accesskey="z" tabindex="<?=$tabindex++?>">Allian<kbd>z</kbd></a></h2>
<?php
				if($me->quitAlliance())
				{
?>
<p class="successful">Sie haben die Allianz verlassen.</p>
<?php
				}
				else
				{
?>
<p class="error">Datenbankfehler.</p>
<?php
				}
			}
			elseif($alliance->checkUserPermissions($_SESSION['username'], 2) && $action == 'intern')
			{
				if(isset($_POST['intern-text']))
					$alliance->setInternalDescription($_POST['intern-text']);
?>
<h2><a href="allianz.php?<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Zurück zur Allianzübersicht" tabindex="<?=$tabindex+2?>" accesskey="z">Allian<kbd>z</kbd></a></h2>
<form action="allianz.php?action=intern&amp;<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" method="post" class="allianz-intern-form">
	<dl>
		<dt><label for="allianz-intern-textarea">Interner Allianzte<kbd>x</kbd>t</label></dt>
		<dd><textarea name="intern-text" id="allianz-intern-textarea" cols="50" rows="17" tabindex="<?=$tabindex++?>" accesskey="x"><?=preg_replace("/[\t\r\n]/e", "'&#'.ord('\$0').';'", utf8_htmlentities($alliance->getInternalDescription(false)))?></textarea></dd>
	</dl>
<?php
	if(!$me->userLocked())
		{
?>
	<div><button type="submit" tabindex="<?=$tabindex++?>" accesskey="n">Speicher<kbd>n</kbd></button></div>
<?php
		}
?>
</form>
<?php
				$tabindex++;
			}
			elseif($alliance->checkUserPermissions($_SESSION['username'], 3) && $action == 'extern')
			{
?>
<h2><a href="allianz.php?<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Zurück zur Allianzübersicht" tabindex="<?=$tabindex+2?>" accesskey="z">Allian<kbd>z</kbd></a></h2>
<?php
				if(isset($_POST['extern-text']))
					$alliance->setExternalDescription($_POST['extern-text']);
				if(isset($_POST['extern-name']) && strlen(trim($_POST['extern-name'])) > 0)
					$alliance->name($_POST['extern-name']);
				if(isset($_POST['extern-tag']) && trim($_POST['extern-tag']) != $alliance->getName() && $alliance->renameAllowed())
				{
					if(strlen($_POST['extern-tag']) < 2)
					{
?>
<p class="error">Das Allianz<span xml:lang="en">tag</span> muss mindestens zwei <span xml:lang="en">Bytes</span> lang sein.</p>
<?php
					}
					elseif(strlen($_POST['extern-tag']) > 6)
					{
?>
<p class="error">Das Allianz<span xml:lang="en">tag</span> darf höchstens sechs <span xml:lang="en">Bytes</span> lang sein.</p>
<?php
					}
					elseif(Alliance::allianceExists($_POST['extern-tag']))
					{
?>
<p class="error">Es gibt schon eine Allianz mit diesem <span xml:lang="en">Tag</span>.</p>
<?php
					}
					else $alliance->rename($_POST['extern-tag']);
				}
?>
<form action="allianz.php?action=extern&amp;<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" method="post" class="allianz-extern-form">
	<dl>
		<dt class="c-tag"><label for="i-allianz-tag">Allianztag</label></dt>
		<dd class="c-tag"><input type="text" name="extern-tag" id="i-allianz-tag" value="<?=htmlspecialchars($alliance->getName())?>" tabindex="<?=$tabindex+3?>"<?php if(!$alliance->renameAllowed()){?> disabled="disabled"<?php }?> /> <span class="allianztag-aendern-hinweis">Das Allianztag kann alle <?=htmlspecialchars(global_setting("ALLIANCE_RENAME_PERIOD"))?> Tag<?=(global_setting("ALLIANCE_RENAME_PERIOD") != 1) ? 'e' : ''?> geändert werden.</span></dd>

		<dt class="c-name"><label for="allianz-name-input">Allianzname</label></dt>
		<dd class="c-name"><input type="text" name="extern-name" id="allianz-name-input" value="<?=utf8_htmlentities($alliance->name())?>" tabindex="<?=$tabindex+4?>" /></dd>

		<dt class="c-text"><label for="allianz-extern-textarea">E<kbd>x</kbd>terner Allianztext</label></dt>
		<dd class="c-text"><textarea name="extern-text" id="allianz-extern-textarea" cols="50" rows="17" tabindex="<?=$tabindex++?>" accesskey="x"><?=preg_replace("/[\t\r\n]/e", "'&#'.ord('\$0').';'", utf8_htmlentities($alliance->getExternalDescription(false)))?></textarea></dd>
	</dl>
<?php
		if(!$me->userLocked())
		{
?>
	<div><button type="submit" accesskey="n" tabindex="<?=$tabindex++?>">Speicher<kbd>n</kbd></button></div>
<?php
		}
?>
</form>
<?php
				$tabindex += 3;
			}
			elseif($alliance->checkUserPermissions($_SESSION['username'], 7) && $action == 'permissions')
			{
?>
<h2><a href="allianz.php?<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Zurück zur Allianzübersicht" accesskey="z">Allian<kbd>z</kbd></a></h2>
<form action="allianz.php?action=permissions&amp;<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" method="post" class="allianz-rechte-form">
	<table>
		<thead>
			<tr>
				<th>Mitglied</th>
				<th><abbr title="Rundschreiben">Rundschr.</abbr></th>
				<th title="Koordinaten der anderen Allianzmitglieder einsehen">Koordinaten</th>
				<th title="Internen Bereich bearbeiten">Intern</th>
				<th title="Externen Bereich bearbeiten">Extern</th>
				<th title="Bewerbungen annehmen oder ablehnen">Bewerbungen</th>
				<th title="Mitglieder aus der Allianz werfen"><span xml:lang="en">Kick</span></th>
				<th title="Ränge verteilen">Ränge</th>
				<th title="Benutzerrechte verteilen">Rechte</th>
				<th title="Bündnis auflösen">Auflösen</th>
			</tr>
		</thead>
		<tbody>
<?php
				$member_names = $alliance->getUsersList(true);
				foreach($member_names as $i=>$member_name)
				{
					if(isset($_POST['permissions']) && isset($_POST['permissions'][$i]))
					{
						$alliance->setUserPermissions($member_name, 0, isset($_POST['permissions'][$i][0]));
						$alliance->setUserPermissions($member_name, 1, isset($_POST['permissions'][$i][1]));
						$alliance->setUserPermissions($member_name, 2, isset($_POST['permissions'][$i][2]));
						$alliance->setUserPermissions($member_name, 3, isset($_POST['permissions'][$i][3]));
						$alliance->setUserPermissions($member_name, 4, isset($_POST['permissions'][$i][4]));
						$alliance->setUserPermissions($member_name, 5, isset($_POST['permissions'][$i][5]));
						$alliance->setUserPermissions($member_name, 6, isset($_POST['permissions'][$i][6]));
						if($member_name != $_SESSION['username'])
							$alliance->setUserPermissions($member_name, 7, isset($_POST['permissions'][$i][7]));
						$alliance->setUserPermissions($member_name, 8, isset($_POST['permissions'][$i][8]));
					}
?>
			<tr>
				<th><a href="help/playerinfo.php?player=<?=htmlentities(urlencode($member_name))?>&amp;<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Informationen zu diesem Spieler anzeigen"><?=utf8_htmlentities($member_name)?></a><input type="hidden" name="permissions[<?=utf8_htmlentities($i)?>][9]" value="on" /></th>
				<td><input type="checkbox" name="permissions[<?=utf8_htmlentities($i)?>][0]"<?=$alliance->checkUserPermissions($member_name, 0) ? ' checked="checked"' : ''?> /></td>
				<td><input type="checkbox" name="permissions[<?=utf8_htmlentities($i)?>][1]"<?=$alliance->checkUserPermissions($member_name, 1) ? ' checked="checked"' : ''?> /></td>
				<td><input type="checkbox" name="permissions[<?=utf8_htmlentities($i)?>][2]"<?=$alliance->checkUserPermissions($member_name, 2) ? ' checked="checked"' : ''?> /></td>
				<td><input type="checkbox" name="permissions[<?=utf8_htmlentities($i)?>][3]"<?=$alliance->checkUserPermissions($member_name, 3) ? ' checked="checked"' : ''?> /></td>
				<td><input type="checkbox" name="permissions[<?=utf8_htmlentities($i)?>][4]"<?=$alliance->checkUserPermissions($member_name, 4) ? ' checked="checked"' : ''?> /></td>
				<td><input type="checkbox" name="permissions[<?=utf8_htmlentities($i)?>][5]"<?=$alliance->checkUserPermissions($member_name, 5) ? ' checked="checked"' : ''?> /></td>
				<td><input type="checkbox" name="permissions[<?=utf8_htmlentities($i)?>][6]"<?=$alliance->checkUserPermissions($member_name, 6) ? ' checked="checked"' : ''?> /></td>
				<td><input type="checkbox" name="permissions[<?=utf8_htmlentities($i)?>][7]"<?=$alliance->checkUserPermissions($member_name, 7) ? ' checked="checked"' : ''?><?=($member_name == $_SESSION['username']) ? ' disabled="disabled"' : ''?> /></td>
				<td><input type="checkbox" name="permissions[<?=utf8_htmlentities($i)?>][8]"<?=$alliance->checkUserPermissions($member_name, 8) ? ' checked="checked"' : ''?> /></td>
			</tr>
<?php
				}
?>
		</tbody>
	</table>
<?php
		if(!$me->userLocked())
		{
?>
	<div><button type="submit">Speichern</button></div>
<?php
		}
?>
</form>
<?php
			}
			else
			{
?>
<h2>Allianz</h2>
<?php
				if($alliance->checkUserPermissions($_SESSION['username'], 4))
				{
					if($action == 'annehmen' && isset($_GET['which']))
						$alliance->acceptApplication($_GET['which'], $_SESSION['username']);
					elseif($action == 'ablehnen' && isset($_GET['which']))
						$alliance->rejectApplication($_GET['which'], $_SESSION['username']);
					$applications = $alliance->getApplicationsList();

					if(count($applications) > 0)
					{
?>
<h3 id="laufende-bewerbungen">Laufende Bewerbungen</h3>
<dl class="allianz-laufende-bewerbungen">
<?php
						foreach($applications as $bewerbung)
						{
?>
	<dt><a href="help/playerinfo.php?player=<?=htmlentities(urlencode($bewerbung))?>&amp;<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>"><?=utf8_htmlentities($bewerbung)?></a></dt>
	<dd><ul>
		<li><a href="allianz.php?action=annehmen&amp;which=<?=htmlentities(urlencode($bewerbung))?>&amp;<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>">Annehmen</a></li>
		<li><a href="allianz.php?action=ablehnen&amp;which=<?=htmlentities(urlencode($bewerbung))?>&amp;<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" onclick="return confirm('Sind Sie sicher, dass Sie die Bewerbung des Benutzers <?=utf8_jsentities($bewerbung)?> ablehnen wollen?');">Ablehnen</a></li>
	</ul></dd>
<?php
						}
?>
</dl>
<h3 id="allianz-informationen">Allianz-Informationen</h3>
<?php
					}
				}
				$overall = $alliance->getTotalScores();
				$average = floor($overall/$alliance->getMembersCount());
?>
<dl class="allianceinfo">
	<dt class="c-allianztag">Allianz<span xml:lang="en">tag</span></dt>
	<dd class="c-allianztag"><?=utf8_htmlentities($alliance->getName())?></dd>

	<dt class="c-name">Name</dt>
	<dd class="c-name"><?=utf8_htmlentities($alliance->name())?></dd>

	<dt class="c-ihr-rang">Ihr Rang</dt>
	<dd class="c-ihr-rang"><?=utf8_htmlentities($alliance->getUserStatus($_SESSION['username']))?></dd>

	<dt class="c-mitglieder">Mitglieder</dt>
	<dd class="c-mitglieder"><?=htmlentities($alliance->getMembersCount())?> <span class="liste">(<a href="allianz.php?action=liste&amp;<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Mitgliederliste der Allianz einsehen" tabindex="<?=$tabindex++?>" accesskey="z" title="[Z]">Liste</a>)</span></dd>

	<dt class="c-punkteschnitt">Punkteschnitt</dt>
	<dd class="c-punkteschnitt"><?=ths($average)?> <span class="platz">(Platz <?=ths($alliance->getRankAverage())?> von <?=ths(getAlliancesCount())?>)</span></dd>

	<dt class="c-gesamtpunkte">Gesamtpunkte</dt>
	<dd class="c-gesamtpunkte"><?=ths($overall)?> <span class="platz">(Platz <?=ths($alliance->getRankTotal())?> von <?=ths(getAlliancesCount())?>)</span></dd>
</dl>
<?php
				if($alliance->checkUserPermissions($_SESSION['username'], 8) || $austreten || $alliance->checkUserPermissions($_SESSION['username'], 2) || $alliance->checkUserPermissions($_SESSION['username'], 3) || $alliance->checkUserPermissions($_SESSION['username'], 7))
				{
?>
<ul class="allianz-aktionen">
<?php
					if($austreten)
					{
?>
	<li class="c-austreten"><a href="allianz.php?action=austreten&amp;<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" onclick="return confirm('Wollen Sie wirklich aus der Allianz austreten?');">Aus der Allianz austreten</a></li>
<?php
					}
					if($alliance->checkUserPermissions($_SESSION['username'], 8) && !$me->userLocked())
					{
?>
	<li class="c-aufloesen"><a href="allianz.php?action=aufloesen&amp;<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" onclick="if(confirm('Sind Sie sicher, dass Sie diese Allianz komplett auflösen und allen Mitgliedern die Mitgliedschaft kündigen wollen?')) return !confirm('Haben Sie es sich doch noch anders überlegt?'); else return false;">Allianz auflösen</a></li>
<?php
					}
					if($alliance->checkUserPermissions($_SESSION['username'], 2))
					{
?>
	<li class="c-interner-bereich"><a href="allianz.php?action=intern&amp;<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" tabindex="<?=$tabindex++?>" accesskey="n">I<kbd>n</kbd>ternen Bereich bearbeiten</a></li>
<?php
					}
					if($alliance->checkUserPermissions($_SESSION['username'], 3))
					{
?>
	<li class="c-externer-bereich"><a href="allianz.php?action=extern&amp;<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" tabindex="<?=$tabindex++?>" accesskey="x">E<kbd>x</kbd>ternen Bereich bearbeiten</a></li>
<?php
					}
					if($alliance->checkUserPermissions($_SESSION['username'], 7))
					{
?>
	<li class="c-benutzerrechte"><a href="allianz.php?action=permissions&amp;<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" tabindex="<?=$tabindex++?>" accesskey="w">Benutzerrechte ver<kbd>w</kbd>alten</a></li>
<?php
					}
?>
</ul>
<?php
				}
?>
<h3 id="internes">Internes</h3>
<div class="allianz-internes">
<?php
				print($alliance->getInternalDescription());
?>
</div>
<h3 id="externes">Externes</h3>
<div class="allianz-externes">
<?php
				print($alliance->getExternalDescription());
?>
</div>
<?php
				if($alliance->checkUserPermissions($_SESSION['username'], 0) && $alliance->getMembersCount() > 1)
				{
?>
<h3 id="allianzrundschreiben">Allianzrundschreiben</h3>
<?php
					if(isset($_POST['rundschreiben-text']) && strlen(trim($_POST['rundschreiben-text'])) > 0)
					{
						$message = Classes::Message();
						if($message->create())
						{
                                                $betreff = '';
							if(isset($_POST['rundschreiben-betreff']))
								$betreff = $_POST['rundschreiben-betreff'];
							#if(strlen(trim($betreff)) <= 0)
								#$betreff = 'Allianzrundschreiben';
							$message->subject($betreff);

							$message->from($_SESSION['username']);
                                                $message->to('Allianzrundschreiben');
							$message->text($_POST['rundschreiben-text']);

							$members = $alliance->getUsersList();
							foreach($members as $member)
							{
								if($member == $_SESSION['username']) continue;
								$message->addUser($member);
							}
?>
<p class="successful">Das Allianzrundschreiben wurde erfolgreich versandt.</p>
<?php
						}
						else
						{
?>
<p class="error">Datenbankfehler.</p>
<?php
						}
					}
?>
<form action="allianz.php?<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>#allianzrundschreiben" method="post" class="allianz-rundschreiben-form">
	<dl>
		<dt class="c-betreff"><label for="allianz-rundschreiben-betreff-input">Betreff</label></dt>
		<dd class="c-betreff"><input type="text" name="rundschreiben-betreff" id="allianz-rundschreiben-betreff-input" /></dd>

		<dt class="c-text"><label for="allianz-rundschreiben-text-textarea">Text</label></dt>
		<dd class="c-text"><textarea name="rundschreiben-text" id="allianz-rundschreiben-text-textarea" cols="50" rows="17"></textarea></dd>
	</dl>
<?php
		if(!$me->userLocked())
		{
?>
	<div><button type="submit">Allianzrundschreiben verschicken</button></div>
<?php
		}
?>
</form>
<?php
				}
			}
		}
	}
?>
<?php
	login_gui::html_foot();
?>
