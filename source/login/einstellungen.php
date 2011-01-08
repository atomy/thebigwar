<?php
if(!isset($_SERVER['DOCUMENT_ROOT']) || strlen($_SERVER['DOCUMENT_ROOT']) <= 0)
    $_SERVER['DOCUMENT_ROOT'] = getcwd()."/..";
    
require_once($_SERVER['DOCUMENT_ROOT'].'/include/config_inc.php');
require($_SERVER['DOCUMENT_ROOT'].'/login/scripts/include.php');

	$demo = false;

	if ( $_POST && strtolower( $me->getName() ) == GLOBAL_DEMOACCNAME )
	{
		$demo = true;
?>      
<p class="error">
        Das Veraendern von Einstellungen ist im Demo-Account nicht moeglich.
</p>    
<?php  
	}

	$changed = false;

	$receive_settings = $me->checkSetting('receive');
	$show_building = $me->checkSetting('show_building');

	$messengers = get_messenger_info();
	$messenger_settings = $me->getNotificationType();
	$messenger_receive = $me->checkSetting('messenger_receive');

        if ( !$demo )
        {

	if(isset($_POST['skin-choice']))
	{
		if($_POST['skin-choice'] == 'custom')
		{
			if(isset($_POST['skin']))
				$me->setSetting('skin', array('custom', $_POST['skin']));
		}
		elseif(strstr($_POST['skin-choice'], '/'))
			$me->setSetting('skin', explode('/', $_POST['skin-choice']));
	}

	if(isset($_POST['schrift']))
		$me->setSetting('schrift', ($_POST['schrift'] == true));

	if(isset($_POST['benutzerbeschreibung']))
		$me->setUserDescription($_POST['benutzerbeschreibung']);

	if(isset($_POST['spionagesonden']))
	{
		$sonden = (int) $_POST['spionagesonden'];
		if($sonden <= 0)
			$sonden = 1;
		$me->setSetting('sonden', $sonden);
	}

	if(isset($_POST['autorefresh']))
	{
		$ress_refresh = (real) str_replace(',', '.', $_POST['autorefresh']);
		if($ress_refresh <= 0)
			$ress_refresh = 0;
		if($ress_refresh > 0 && $ress_refresh < 0.2)
			$ress_refresh = 0.2;
		$me->setSetting('ress_refresh', $ress_refresh);
	}

	if(isset($_POST['change-checkboxes']) && $_POST['change-checkboxes'])
	{
		$receive_settings[1][1] = isset($_POST['nachrichten'][1][1]);
		$receive_settings[2][1] = isset($_POST['nachrichten'][2][1]);
		$receive_settings[3][0] = isset($_POST['nachrichten'][3][0]);
		$receive_settings[3][1] = isset($_POST['nachrichten'][3][1]);
		$receive_settings[4][1] = isset($_POST['nachrichten'][4][1]);
		$receive_settings[5][0] = isset($_POST['nachrichten'][5][0]);
		$receive_settings[5][1] = isset($_POST['nachrichten'][5][1]);
		$me->setSetting('receive', $receive_settings);

		$me->setSetting('fastbuild', isset($_POST['fastbuild']));
		$me->setSetting('shortcuts', isset($_POST['shortcuts']));
		$me->setSetting('tooltips', isset($_POST['tooltips']));
		$me->setSetting('ipcheck', isset($_POST['ipcheck']));
		#$me->setSetting('noads', isset($_POST['noads']));
		$me->setSetting('show_extern', isset($_POST['show_extern']));
		$me->setSetting('notify', isset($_POST['notify']));
		$me->setSetting('ajax', isset($_POST['ajax']));

		if(!isset($_POST['im-receive']) || !isset($_POST['im-receive']['messages']))
			$messenger_receive['messages'] = array(1=>false, 2=>false, 3=>false, 4=>false, 5=>false, 6=>false, 7=>false);
		else
		{
			$messenger_receive['messages'][1] = isset($_POST['im-receive']['messages'][1]);
			$messenger_receive['messages'][2] = isset($_POST['im-receive']['messages'][2]);
			$messenger_receive['messages'][3] = isset($_POST['im-receive']['messages'][3]);
			$messenger_receive['messages'][4] = isset($_POST['im-receive']['messages'][4]);
			$messenger_receive['messages'][5] = isset($_POST['im-receive']['messages'][5]);
			$messenger_receive['messages'][6] = isset($_POST['im-receive']['messages'][6]);
			$messenger_receive['messages'][7] = isset($_POST['im-receive']['messages'][7]);
		}
		$me->setSetting('messenger_receive', $messenger_receive);
	}

	if(isset($_POST['building']))
	{
		if(isset($_POST['building']['gebaeude']) && in_array($_POST['building']['gebaeude'], array(0,1)))
			$show_building['gebaeude'] = $_POST['building']['gebaeude'];
		if(isset($_POST['building']['forschung']) && in_array($_POST['building']['forschung'], array(0,1)))
			$show_building['forschung'] = $_POST['building']['forschung'];
		if(isset($_POST['building']['roboter']) && in_array($_POST['building']['roboter'], array(0,1,2,3)))
			$show_building['roboter'] = $_POST['building']['roboter'];
		if(isset($_POST['building']['schiffe']) && in_array($_POST['building']['schiffe'], array(0,1,2,3)))
			$show_building['schiffe'] = $_POST['building']['schiffe'];
		if(isset($_POST['building']['verteidigung']) && in_array($_POST['building']['verteidigung'], array(0,1,2,3)))
			$show_building['verteidigung'] = $_POST['building']['verteidigung'];

		$me->setSetting('show_building', $show_building);
	}

	if(isset($_POST['im-receive']) && isset($_POST['im-receive']['building']))
	{
		$im_recalc = array('gebaeude' => false, 'forschung' => false, 'roboter' => false, 'schiffe' => false, 'verteidigung' => false);
		if(isset($_POST['im-receive']['building']['gebaeude']) && in_array($_POST['im-receive']['building']['gebaeude'], array(0,1)) && $_POST['im-receive']['building']['gebaeude'] != $messenger_receive['building']['gebaeude'])
		{
			$messenger_receive['building']['gebaeude'] = $_POST['im-receive']['building']['gebaeude'];
			$im_recalc['gebaeude'] = true;
		}
		if(isset($_POST['im-receive']['building']['forschung']) && in_array($_POST['im-receive']['building']['forschung'], array(0,1)) && $_POST['im-receive']['building']['forschung'] != $messenger_receive['building']['forschung'])
		{
			$messenger_receive['building']['forschung'] = $_POST['im-receive']['building']['forschung'];
			$im_recalc['forschung'] = true;
		}
		if(isset($_POST['im-receive']['building']['roboter']) && in_array($_POST['im-receive']['building']['roboter'], array(0,1,2,3)) && $_POST['im-receive']['building']['roboter'] != $messenger_receive['building']['roboter'])
		{
			$messenger_receive['building']['roboter'] = $_POST['im-receive']['building']['roboter'];
			$im_recalc['roboter'] = true;
		}
		if(isset($_POST['im-receive']['building']['schiffe']) && in_array($_POST['im-receive']['building']['schiffe'], array(0,1,2,3)) && $_POST['im-receive']['building']['schiffe'] != $messenger_receive['building']['schiffe'])
		{
			$messenger_receive['building']['schiffe'] = $_POST['im-receive']['building']['schiffe'];
			$im_recalc['schiffe'] = true;
		}
		if(isset($_POST['im-receive']['building']['verteidigung']) && in_array($_POST['im-receive']['building']['verteidigung'], array(0,1,2,3)) && $_POST['im-receive']['building']['verteidigung'] != $messenger_receive['building']['verteidigung'])
		{
			$messenger_receive['building']['verteidigung'] = $_POST['im-receive']['building']['verteidigung'];
			$im_recalc['verteidigung'] = true;
		}

		$me->setSetting('messenger_receive', $messenger_receive);
		foreach($im_recalc as $which=>$whether)
		{
			if($whether) $me->refreshMessengerBuildingNotifications($which);
		}
	}

	if(!$me->userLocked() && isset($_POST['umode']) && ($me->permissionToUmode() || isset($_SESSION['admin_username'])))
		$me->umode(!$me->umode());

	if(isset($_POST['email']))
		$me->setSetting('email', $_POST['email']);
	$noblockpassword = true;
	if(isset($_POST['new-password']))
	{
		$keyarray = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', ' ');
		#Passwort Zeichen pruefen
		$stringpassword = $_POST['new-password'];
		for($i=0;$i<strlen($stringpassword);$i++)
		{
			$explode[$i] = substr($stringpassword, $i, 1);
			if(!in_array($explode[$i],$keyarray)) $noblockpassword = false;
		}
		if($noblockpassword == false)
			$error = 'Das Passswort enth�lt ung�ltige Zeichen.';

	}
	if(($noblockpassword == true) && isset($_POST['old-password']) && isset($_POST['new-password']) && isset($_POST['new-password2']) && ($_POST['old-password'] != $_POST['new-password'] || $_POST['new-password'] != $_POST['new-password2']))
	{
		# Passwort aendern
		if(!$me->checkPassword($_POST['old-password']))
			$error = 'Das alte Passwort stimmt nicht.';
		elseif($_POST['new-password'] != $_POST['new-password2'])
			$error = 'Die beiden neuen Passw�rter stimmen nicht �berein.';
		else
			$me->setPassword($_POST['new-password']);
	}

	if((!$messenger_settings && isset($_POST['im-protocol']) && isset($messengers[$_POST['im-protocol']]) && isset($_POST['im-uin']) && trim($_POST['im-uin'])) || ($messenger_settings && ((isset($_POST['im-protocol']) && trim($_POST['im-protocol']) != $messenger_settings[1]) || (isset($_POST['im-uin']) && trim($_POST['im-uin']) != $messenger_settings[0]))))
	{
		if((isset($_POST['im-protocol']) && !isset($messengers[$_POST['im-protocol']])) || (isset($_POST['im-uin']) && !trim($_POST['im-uin'])))
		{
			# IM deaktivieren
			$me->disableNotification();
			$imfile = Classes::IMFile();
			$imfile->removeMessages($me->getName());
		}
		else
		{
			$new_uin = (isset($_POST['im-uin']) ? trim($_POST['im-uin']) : $messenger_settings[0]);
			$new_protocol = ((isset($_POST['im-protocol']) && isset($messengers[$_POST['im-protocol']])) ? trim($_POST['im-protocol']) : $messenger_settings[1]);

			if((!isset($messengers[$new_protocol]['blocked']) || !in_array(strtolower($new_uin), explode(',', strtolower(trim($messengers[$new_protocol]['blocked']))))) && $me->checkNewNotificationType($new_uin, $new_protocol))
			{
				$imfile = Classes::IMFile();
				$rand_id = $imfile->addCheck($new_uin, $new_protocol, $me->getName());
				$imfile->addMessage($new_uin, $new_protocol, $me->getName(), "Sie erhalten diese Nachricht, weil jemand in The Big War diesen Account zur Benachrichtigung eingetragen hat. Ignorieren Sie die Nachricht, wenn Sie die Eintragung nicht vornehmen möchten. Um die Einstellung zu bestätigen, antworten Sie bitte auf diese Nachricht folgenden Code: ".$rand_id);
			}
		}
	 }
	}
	login_gui::html_head();

	$tabindex = 1;
?>
<h2>Einstellungen</h2>
<?php
	if(isset($error) && trim($error) != '')
	{
?>
<p class="error">
	<?php echo htmlentities($error)."\n"?>
</p>
<?php
	}
?>
<form action="<?php echo htmlentities(global_setting("USE_PROTOCOL").'://'.$_SERVER['HTTP_HOST'].h_root.'/login/einstellungen.php?'.urlencode(session_name()).'='.urlencode(session_id()))?>" method="post" class="einstellungen-formular">

	<fieldset class="verschiedene-einstellungen">
		<legend>Verschiedene Einstellungen<input type="hidden" name="change-checkboxes" value="1" /></legend>
		<dl>
			<dt class="c-skin"><label for="skin-choice">Ski<kbd>n</kbd></label></dt>
			<dd class="c-skin">
				<select name="skin-choice" id="skin-choice" tabindex="<?php echo $tabindex++?>" onchange="recalc_skin();" onkeyup="recalc_skin();">
<?php
	$my_skin = $me->checkSetting('skin');
	foreach($skins as $skin=>$skin_info)
	{
		$skin_selected = ($my_skin && $skin == $my_skin[0]);
?>
					<optgroup label="<?php echo htmlspecialchars($skin_info[0])?>">
<?php
		foreach($skin_info[1] as $type=>$type_info)
		{
			$type_selected = ($skin_selected && $my_skin && $type == $my_skin[1]);
?>
						<option value="<?php echo htmlspecialchars($skin)?>/<?php echo htmlspecialchars($type)?>"<?php echo $type_selected ? ' selected="selected"' : ''?>><?php echo htmlspecialchars($type_info[0])?></option>
<?php
		}
?>
					</optgroup>
<?php
	}
	$custom_skin = ($my_skin && $my_skin[0] == 'custom');
?>
					<option value="custom"<?php echo $custom_skin ? ' selected="selected"' : ''?>>Benutzerdefiniert</option>
				</select>
				<input type="text" name="skin" id="skin" value="<?php echo htmlentities($my_skin[1])?>" tabindex="<?php echo $tabindex++?>" />
			</dd>

			<dt class="c-schrift"><label for="schrift-choice">Schrift</label></dt>
			<dd class="c-schrift">
				<select name="schrift" id="schrift-choice" tabindex="<?php echo $tabindex++?>">
					<option value="1"<?php echo $me->checkSetting('schrift') ? ' selected="selected"' : ''?>>Lieblingsschrift des Admins</option>
					<option value="0"<?php echo !$me->checkSetting('schrift') ? ' selected="selected"' : ''?>>Ihre Lieblingsschrift</option>
				</select>
			</dd>

			<dt class="c-benutzerbeschreibung"><label for="benutzerbeschreibung">Ben<kbd>u</kbd>tzerbeschreibung</label></dt>
			<dd class="c-benutzerbeschreibung"><textarea name="benutzerbeschreibung" id="benutzerbeschreibung" cols="50" rows="10" accesskey="u" tabindex="<?php echo $tabindex++?>"><?php echo preg_replace("/[\r\n\t]/e", '\'&#\'.ord(\'$0\').\';\'', utf8_htmlentities($me->getUserDescription(false)))?></textarea></dd>

			<dt class="c-spionagesonden"><label for="spionagesonden">Spionagesonden</label></dt>
			<dd class="c-spionagesonden"><input type="text" name="spionagesonden" id="spionagesonden" value="<?php echo utf8_htmlentities($me->checkSetting('sonden'))?>" title="Anzahl Spionagesonden, die bei der Spionage eines fremden Planeten aus der Karte geschickt werden sollen [J]" accesskey="j" tabindex="<?php echo $tabindex++?>" /></dd>

			<dt class="c-auto-schnellbau"><label for="fastbuild">Auto-Schnellbau</label></dt>
			<dd class="c-auto-schnellbau"><input type="checkbox" name="fastbuild" id="fastbuild"<?php echo $me->checkSetting('fastbuild') ? ' checked="checked"' : ''?> title="Wird ein Gebäude in Auftrag gegeben, wird automatisch zum nächsten unbeschäftigten Planeten gewechselt [Q]" accesskey="q" tabindex="<?php echo $tabindex++?>" /></dd>

			<dt class="c-schnell-shortcuts"><label for="shortcuts">Schnell-Shortcuts</label></dt>
			<dd class="c-schnell-shortcuts"><input type="checkbox" name="shortcuts" id="shortcuts"<?php echo $me->checkSetting('shortcuts') ? ' checked="checked"' : ''?> title="Mit dieser Funktion brauchen Sie zum Ausführen der Shortcuts keine weitere Taste zu drücken [X]" accesskey="x" tabindex="<?php echo $tabindex++?>" /></dd>

			<dt class="c-javascript-tooltips"><label for="tooltips">Javascript-Tooltips</label></dt>
			<dd class="c-javascript-tooltips"><input type="checkbox" name="tooltips" id="tooltips"<?php echo $me->checkSetting('tooltips') ? ' checked="checked"' : ''?> title="Nicht auf langsamen Computern verwenden! Ist dieser Punkt aktiviert, werden die normalen Tooltips durch hübsche JavaScript-Tooltips ersetzt. [Y]" accesskey="y" tabindex="<?php echo $tabindex++?>" /></dd>

			<dt class="c-auto-refresh"><label for="autorefresh">Auto-Refresh</label></dt>
			<dd class="c-auto-refresh"><input type="text" name="autorefresh" id="autorefresh" value="<?php echo utf8_htmlentities($me->checkSetting('ress_refresh'))?>" title="Wird hier eine Zahl größer als 0 eingetragen, wird in deren Sekundenabstand die Rohstoffanzeige oben automatisch aktualisiert. (Hinweis: Diese Funktion erzeugt keinen zusätzlichen Traffic)" tabindex="<?php echo $tabindex++?>" /></dd>

			<dt class="c-ip-schutz"><label for="ipcheck">IP-Schutz</label></dt>
			<dd class="c-ip-schutz"><input type="checkbox" name="ipcheck" id="ipcheck"<?php echo $me->checkSetting('ipcheck') ? ' checked="checked"' : ''?> title="Wenn diese Option deaktiviert ist, kann Ihre Session von mehreren IP-Adressen gleichzeitig genutzt werden. (Unsicher!)" tabindex="<?php echo $tabindex++?>" /></dd>

			<dt class="c-externe-navigationslinks"><label for="show-extern">Externe Navigationslinks</label></dt>
			<dd class="c-externe-navigationslinks"><input type="checkbox" name="show_extern" id="show-extern"<?php echo $me->checkSetting('show_extern') ? ' checked="checked"' : ''?> title="Wenn diese Option aktiviert ist, werden in der Navigation Links auf spielexterne Seiten wie das Board angezeigt." tabindex="<?php echo $tabindex++?>" /></dd>

			<dt class="c-nachrichteninformierung"><label for="notify">Nachrichteninformierung</label></dt>
			<dd class="c-nachrichteninformierung"><input type="checkbox" name="notify" id="notify"<?php echo $me->checkSetting('notify') ? ' checked="checked"' : ''?> title="Wenn diese Option aktiviert ist, wird nicht nur in der Übersicht angezeigt, dass Sie eine neue Nachricht erhalten haben, sondern auf allen Seiten." tabindex="<?php echo $tabindex++?>" /></dd>

			<dt class="c-ajax"><label for="ajax"><acronym title="Asynchronous JavaScript and XML">AJAX</acronym> aktivieren</label></dt>
			<dd class="c-ajax"><input type="checkbox" name="ajax" id="ajax"<?php echo $me->checkSetting('ajax') ? ' checked="checked"' : ''?> title="Nützliche Eingabevereinfachungen, empfehlenswert in neuen Browsern mit schneller Internetverbindung." tabindex="<?php echo $tabindex++?>" /></dd>
		</dl>
		<script type="text/javascript">
			function recalc_skin()
			{
				var skin = document.getElementById('skin-choice').value;
				if(skin == 'custom')
				{
					document.getElementById('skin').removeAttribute('readonly');
				}
				else
				{
					document.getElementById('skin').setAttribute('readonly', 'readonly');
					document.getElementById('skin').value = skin;
				}
			}
			recalc_skin();
		</script>
	</fieldset>
	<fieldset class="nachrichtentypen-empfangen">
		<legend>Nachrichtentypen empfangen</legend>
		<table>
			<thead>
				<tr>
					<th class="c-nachrichtentyp">Nachrichtentyp</th>
					<th class="c-ankunft">Ankunft</th>
					<th class="c-rueckkehr">Rückkehr</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<th class="c-nachrichtentyp">Kämpfe</th>
					<td class="c-ankunft leer"></td>
					<td class="c-rueckkehr"><input type="checkbox" name="nachrichten[1][1]" tabindex="<?php echo $tabindex++?>"<?php echo $receive_settings[1][1] ? ' checked="checked"' : ''?> /></td>
				</tr>
				<tr>
					<th class="c-nachrichtentyp">Spionage</th>
					<td class="c-ankunft leer"></td>
					<td class="c-rueckkehr"><input type="checkbox" name="nachrichten[2][1]" tabindex="<?php echo $tabindex++?>"<?php echo $receive_settings[2][1] ? ' checked="checked"' : ''?> /></td>
				</tr>
				<tr>
					<th class="c-nachrichtentyp">Transport</th>
					<td class="c-ankunft"><input type="checkbox" name="nachrichten[3][0]" tabindex="<?php echo $tabindex++?>"<?php echo $receive_settings[3][0] ? ' checked="checked"' : ''?> /></td>
					<td class="c-rueckkehr"><input type="checkbox" name="nachrichten[3][1]" tabindex="<?php echo $tabindex++?>"<?php echo $receive_settings[3][1] ? ' checked="checked"' : ''?> /></td>
				</tr>
				<tr>
					<th class="c-nachrichtentyp">Sammeln</th>
					<td class="c-ankunft leer"></td>
					<td class="c-rueckkehr"><input type="checkbox" name="nachrichten[4][1]" tabindex="<?php echo $tabindex++?>"<?php echo $receive_settings[4][1] ? ' checked="checked"' : ''?> /></td>
				</tr>
				<tr>
					<th class="c-nachrichtentyp">Besiedelung</th>
					<td class="c-ankunft"><input type="checkbox" name="nachrichten[5][0]" tabindex="<?php echo $tabindex++?>"<?php echo $receive_settings[5][0] ? ' checked="checked"' : ''?> /></td>
					<td class="c-rueckkehr"><input type="checkbox" name="nachrichten[5][1]" tabindex="<?php echo $tabindex++?>"<?php echo $receive_settings[5][1] ? ' checked="checked"' : ''?> /></td>
				</tr>
			</tbody>
		</table>
	</fieldset>
	<fieldset class="bauende-gegenstaende">
		<legend>Bauende Gegenstände in der Übersicht</legend>
		<table>
			<thead>
				<tr>
					<th>Gegenstandsart</th>
					<th title="Zeigt keine verbleibende Bauzeit in der Übersicht">Ausgeschaltet</th>
					<th title="Zeigt die verbleibende Bauzeit zu dem Gegenstand an, der gerade gebaut wird.">Erster Gegenstand</th>
					<th title="Zeigt die verbleibende Bauzeit aller bauenden Gegenstände des aktuellen Gegenstandstyps an (zum Beispiel 5 Bauroboter).">Erster Gegenstandstyp</th>
					<th title="Zeigt die verbleibende Bauzeit aller bauenden Gegenstände an.">Alle Gegenstände</th>
				</tr>
			</thead>
			<tbody>
				<tr class="c-gebauede">
					<th>Gebäude</th>
					<td><input type="radio" name="building[gebaeude]" value="0"<?php echo ($show_building['gebaeude']==0) ? ' checked="checked"' : ''?> tabindex="<?php echo $tabindex++?>" /></td>
					<td><input type="radio" name="building[gebaeude]" value="1"<?php echo ($show_building['gebaeude']==1) ? ' checked="checked"' : ''?> tabindex="<?php echo $tabindex++?>" /></td>
					<td></td>
					<td></td>
				</tr>
				<tr class="c-forschung">
					<th>Forschung</th>
					<td><input type="radio" name="building[forschung]" value="0"<?php echo ($show_building['forschung']==0) ? ' checked="checked"' : ''?> tabindex="<?php echo $tabindex++?>" /></td>
					<td><input type="radio" name="building[forschung]" value="1"<?php echo ($show_building['forschung']==1) ? ' checked="checked"' : ''?> tabindex="<?php echo $tabindex++?>" /></td>
					<td></td>
					<td></td>
				</tr>
				<tr class="c-roboter">
					<th>Roboter</th>
					<td><input type="radio" name="building[roboter]" value="0"<?php echo ($show_building['roboter']==0) ? ' checked="checked"' : ''?> tabindex="<?php echo $tabindex++?>" /></td>
					<td><input type="radio" name="building[roboter]" value="1"<?php echo ($show_building['roboter']==1) ? ' checked="checked"' : ''?> tabindex="<?php echo $tabindex++?>" /></td>
					<td><input type="radio" name="building[roboter]" value="2"<?php echo ($show_building['roboter']==2) ? ' checked="checked"' : ''?> tabindex="<?php echo $tabindex++?>" /></td>
					<td><input type="radio" name="building[roboter]" value="3"<?php echo ($show_building['roboter']==3) ? ' checked="checked"' : ''?> tabindex="<?php echo $tabindex++?>" /></td>
				</tr>
				<tr class="c-schiffe">
					<th>Schiffe</th>
					<td><input type="radio" name="building[schiffe]" value="0"<?php echo ($show_building['schiffe']==0) ? ' checked="checked"' : ''?> tabindex="<?php echo $tabindex++?>" /></td>
					<td><input type="radio" name="building[schiffe]" value="1"<?php echo ($show_building['schiffe']==1) ? ' checked="checked"' : ''?> tabindex="<?php echo $tabindex++?>" /></td>
					<td><input type="radio" name="building[schiffe]" value="2"<?php echo ($show_building['schiffe']==2) ? ' checked="checked"' : ''?> tabindex="<?php echo $tabindex++?>" /></td>
					<td><input type="radio" name="building[schiffe]" value="3"<?php echo ($show_building['schiffe']==3) ? ' checked="checked"' : ''?> tabindex="<?php echo $tabindex++?>" /></td>
				</tr>
				<tr class="c-verteidigung">
					<th>Verteidigung</th>
					<td><input type="radio" name="building[verteidigung]" value="0"<?php echo ($show_building['verteidigung']==0) ? ' checked="checked"' : ''?> tabindex="<?php echo $tabindex++?>" /></td>
					<td><input type="radio" name="building[verteidigung]" value="1"<?php echo ($show_building['verteidigung']==1) ? ' checked="checked"' : ''?> tabindex="<?php echo $tabindex++?>" /></td>
					<td><input type="radio" name="building[verteidigung]" value="2"<?php echo ($show_building['verteidigung']==2) ? ' checked="checked"' : ''?> tabindex="<?php echo $tabindex++?>" /></td>
					<td><input type="radio" name="building[verteidigung]" value="3"<?php echo ($show_building['verteidigung']==3) ? ' checked="checked"' : ''?> tabindex="<?php echo $tabindex++?>" /></td>
				</tr>
			</tbody>
		</table>
	</fieldset>
<?php
	if(NULL && isset($messengers['jabber']) && isset($messengers['jabber']['username']) && isset($messengers['jabber']['password']))
	{
?>
	<fieldset class="instant-messenger">
		<legend>Instant-Messenger-Benachrichtigung</legend>
		<p>Nach Änderung des Accounts wird zunächst eine Bestätigungsnachricht versandt.</p>
		<dl>
			<dt class="c-im-account"><label for="i-im-protocol"><abbr title="Instant-Messaging">IM</abbr>-Account</label></dt>
			<dd class="c-im-account">
				<select name="im-protocol" id="i-im-protocol" onchange="document.getElementById('i-im-uin').disabled = !this.value;" onkeyup="this.onchange();">
					<option value="">Deaktiviert</option>
<?php
		foreach($messengers as $protocol=>$minfo)
		{
			$name = (isset($minfo['name']) ? $minfo['name'] : $protocol);
?>
					<option value="<?php echo htmlspecialchars($protocol)?>"<?php echo ($messenger_settings && $messenger_settings[1] == $protocol) ? ' selected="selected"' : ''?>><?php echo htmlspecialchars($name)?></option>
<?php
		}
?>
				</select>
				<input type="text" name="im-uin" id="i-im-uin" title="UIN"<?php echo $messenger_settings ? ' value="'.htmlspecialchars($messenger_settings[0]).'"' : ''?> />
			</dd>
		</dl>
		<script type="text/javascript">
			document.getElementById('i-im-uin').disabled = !document.getElementById('i-im-protocol').value;
		</script>
		<fieldset class="benachrichtigung-nachrichten">
			<legend>Benachrichtigung bei Nachrichten</legend>
			<dl>
				<dt class="c-kaempfe"><label for="i-im-message-kaempfe">Kämpfe</label></dt>
				<dd class="c-kaempfe"><input type="checkbox" name="im-receive[messages][1]" id="i-im-message-kaempfe"<?php echo $messenger_receive['messages'][1] ? ' checked="checked"' : ''?> /></dd>

				<dt class="c-spionage"><label for="i-im-message-spionage">Spionage</label></dt>
				<dd class="c-spionage"><input type="checkbox" name="im-receive[messages][2]" id="i-im-message-spionage"<?php echo $messenger_receive['messages'][2] ? ' checked="checked"' : ''?> /></dd>

				<dt class="c-transport"><label for="i-im-message-transport">Transport</label></dt>
				<dd class="c-transport"><input type="checkbox" name="im-receive[messages][3]" id="i-im-message-transport"<?php echo $messenger_receive['messages'][3] ? ' checked="checked"' : ''?> /></dd>

				<dt class="c-sammeln"><label for="i-im-message-sammeln">Sammeln</label></dt>
				<dd class="c-sammeln"><input type="checkbox" name="im-receive[messages][4]" id="i-im-message-sammeln"<?php echo $messenger_receive['messages'][4] ? ' checked="checked"' : ''?> /></dd>

				<dt class="c-besiedelung"><label for="i-im-message-besiedelung">Besiedelung</label></dt>
				<dd class="c-besiedelung"><input type="checkbox" name="im-receive[messages][5]" id="i-im-message-besiedelung"<?php echo $messenger_receive['messages'][5] ? ' checked="checked"' : ''?> /></dd>

				<dt class="c-benutzernachrichten"><label for="i-im-message-benutzernachrichten">Benutzernachrichten</label></dt>
				<dd class="c-benutzernachrichten"><input type="checkbox" name="im-receive[messages][6]" id="i-im-message-benutzernachrichten"<?php echo $messenger_receive['messages'][6] ? ' checked="checked"' : ''?> /></dd>

				<dt class="c-verbuendete"><label for="i-im-message-verbuendete">Verbündete</label></dt>
				<dd class="c-verbuendete"><input type="checkbox" name="im-receive[messages][7]" id="i-im-message-verbuendete"<?php echo $messenger_receive['messages'][7] ? ' checked="checked"' : ''?> /></dd>
			</dl>
		</fieldset>
		<fieldset class="benachrichtigung-fertigstellung">
			<legend>Benachrichtigung bei Fertigstellung</legend>
			<table>
				<thead>
					<tr>
						<th>Gegenstandsart</th>
						<th title="Benachrichtigt nicht bei Fertigstellung">Ausgeschaltet</th>
						<th title="Benachrichtigt bei jedem Gegenstand, der fertiggestellt wird.">Jeder Gegenstand</th>
						<th title="Benachricht, sobald alle Gegenstände einer Sorte fertiggestellt wurden (zum Beispiel sobald alle Bauroboter fertiggestellt wurden und nun mit der Produktion von Carbonrobotern begonnen wird).">Jeder Gegenstandstyp</th>
						<th title="Benachrichtigt, sobald alle Gegenstände fertiggestellt wurden.">Alle Gegenstände</th>
					</tr>
				</thead>
				<tbody>
					<tr class="c-gebauede">
						<th>Gebäude</th>
						<td><input type="radio" name="im-receive[building][gebaeude]" value="0"<?php echo ($messenger_receive['building']['gebaeude']==0) ? ' checked="checked"' : ''?> tabindex="<?php echo $tabindex++?>" /></td>
						<td><input type="radio" name="im-receive[building][gebaeude]" value="1"<?php echo ($messenger_receive['building']['gebaeude']==1) ? ' checked="checked"' : ''?> tabindex="<?php echo $tabindex++?>" /></td>
						<td></td>
						<td></td>
					</tr>
					<tr class="c-forschung">
						<th>Forschung</th>
						<td><input type="radio" name="im-receive[building][forschung]" value="0"<?php echo ($messenger_receive['building']['forschung']==0) ? ' checked="checked"' : ''?> tabindex="<?php echo $tabindex++?>" /></td>
						<td><input type="radio" name="im-receive[building][forschung]" value="1"<?php echo ($messenger_receive['building']['forschung']==1) ? ' checked="checked"' : ''?> tabindex="<?php echo $tabindex++?>" /></td>
						<td></td>
						<td></td>
					</tr>
					<tr class="c-roboter">
						<th>Roboter</th>
						<td><input type="radio" name="im-receive[building][roboter]" value="0"<?php echo ($messenger_receive['building']['roboter']==0) ? ' checked="checked"' : ''?> tabindex="<?php echo $tabindex++?>" /></td>
						<td><input type="radio" name="im-receive[building][roboter]" value="1"<?php echo ($messenger_receive['building']['roboter']==1) ? ' checked="checked"' : ''?> tabindex="<?php echo $tabindex++?>" /></td>
						<td><input type="radio" name="im-receive[building][roboter]" value="2"<?php echo ($messenger_receive['building']['roboter']==2) ? ' checked="checked"' : ''?> tabindex="<?php echo $tabindex++?>" /></td>
						<td><input type="radio" name="im-receive[building][roboter]" value="3"<?php echo ($messenger_receive['building']['roboter']==3) ? ' checked="checked"' : ''?> tabindex="<?php echo $tabindex++?>" /></td>
					</tr>
					<tr class="c-schiffe">
						<th>Schiffe</th>
						<td><input type="radio" name="im-receive[building][schiffe]" value="0"<?php echo ($messenger_receive['building']['schiffe']==0) ? ' checked="checked"' : ''?> tabindex="<?php echo $tabindex++?>" /></td>
						<td><input type="radio" name="im-receive[building][schiffe]" value="1"<?php echo ($messenger_receive['building']['schiffe']==1) ? ' checked="checked"' : ''?> tabindex="<?php echo $tabindex++?>" /></td>
						<td><input type="radio" name="im-receive[building][schiffe]" value="2"<?php echo ($messenger_receive['building']['schiffe']==2) ? ' checked="checked"' : ''?> tabindex="<?php echo $tabindex++?>" /></td>
						<td><input type="radio" name="im-receive[building][schiffe]" value="3"<?php echo ($messenger_receive['building']['schiffe']==3) ? ' checked="checked"' : ''?> tabindex="<?php echo $tabindex++?>" /></td>
					</tr>
					<tr class="c-verteidigung">
						<th>Verteidigung</th>
						<td><input type="radio" name="im-receive[building][verteidigung]" value="0"<?php echo ($messenger_receive['building']['verteidigung']==0) ? ' checked="checked"' : ''?> tabindex="<?php echo $tabindex++?>" /></td>
						<td><input type="radio" name="im-receive[building][verteidigung]" value="1"<?php echo ($messenger_receive['building']['verteidigung']==1) ? ' checked="checked"' : ''?> tabindex="<?php echo $tabindex++?>" /></td>
						<td><input type="radio" name="im-receive[building][verteidigung]" value="2"<?php echo ($messenger_receive['building']['verteidigung']==2) ? ' checked="checked"' : ''?> tabindex="<?php echo $tabindex++?>" /></td>
						<td><input type="radio" name="im-receive[building][verteidigung]" value="3"<?php echo ($messenger_receive['building']['verteidigung']==3) ? ' checked="checked"' : ''?> tabindex="<?php echo $tabindex++?>" /></td>
					</tr>
				</tbody>
			</table>
		</fieldset>
	</fieldset>
<?php
	}
?>
	<div class="einstellungen-speichern-1"><input type="submit" title="[W]" value="Speichern" /></div>
<?php
	$save_tabindex = $tabindex++;

	if(!$me->userLocked())
	{
?>
	<fieldset class="urlaubsmodus">
		<legend>Urlaubsmodus</legend>
<?php
		if(!$me->umode())
		{
			
			if($me->permissionToUmode() || isset($_SESSION['admin_username']))
			{
?>
		<div><input type="submit" name="umode" value="Urlaubsmodus" tabindex="<?php echo $tabindex++?>" onclick="return confirm('Wollen Sie den Urlaubsmodus wirklich betreten?');" /></div>
		<center><p>Sie werden frühestens nach zwei Tagen (<?php echo date('Y-m-d, H:i', $me->getUmodeReturnTime())?>, Serverzeit) aus dem Urlaubsmodus zurückkehren können.<br>Sie können erst in den Umod wechseln, wenn Ihre Flotten sich alle auf dem Rückweg befinden.<br>Aktive Bau- oder Forschungsaktivitäten werden im Umode eingefroren.<br> Wenn Sie den Urlaubmodus aktivieren, werden alle Ihre Flotten zurückgerufen.</p></center>
<?php
			
			
			}
			else
			{
?>
		<center><p>Sie können erst wieder ab dem <?php echo date('Y-m-d, H:i', $me->getUmodeReturnTime())?> (Serverzeit) in den Urlaubsmodus wechseln..<br>Sie können erst in den Umod wechseln, wenn Ihre Flotten sich alle auf dem Rückweg befinden.<br>Aktive Bau- oder Forschungsaktivitäten werden im Umode eingefroren.<br> Wenn Sie den Urlaubmodus aktivieren, werden alle Ihre Flotten zurückgerufen.</p></center>
<?php
			}
		}
		elseif($me->permissionToUmode() || isset($_SESSION['admin_username']))
		{
?>
		<div><input type="submit" name="umode" value="Urlaubsmodus verlassen" tabindex="<?php echo $tabindex++?>" onclick="return confirm('Wollen Sie den Urlaubsmodus wirklich verlassen?');" /></div>
<?php
		}
		else
		{
?>
		<p>Sie können den Urlaubsmodus frühestens am <?php echo date('Y-m-d, H:i', $me->getUmodeReturnTime())?> (Serverzeit) verlassen.</p>
<?php
		}
?>
	</fieldset>
<?php
	}
?>
	<fieldset class="email-adresse">
		<legend>E-Mail-Adresse</legend>
		<dl>
			<dt class="c-email-adresse"><label for="email">E-Mail-Adresse</label></dt>
			<dd class="c-email-adresse"><input type="text" name="email" id="email" value="<?php echo utf8_htmlentities($me->checkSetting('email'))?>" title="Ihre E-Mail-Adresse wird benötigt, wenn Sie Ihr Passwort vergessen haben. [Z]" tabindex="<?php echo $tabindex++?>" accesskey="z" /></dd>
		</dl>
	</fieldset>
	<fieldset class="passwort-aendern">
		<legend>Passwort ändern</legend>
		<dl>
			<dt class="c-altes-passwort"><label for="old-password">Altes Passw<kbd>o</kbd>rt</label></dt>
			<dd class="c-altes-passwort"><input type="password" name="old-password" id="old-password" tabindex="<?php echo $tabindex++?>" accesskey="o" /></dd>

			<dt class="c-neues-passwort"><label for="new-password">Neues Passwort</label></dt>
			<dd class="c-neues-passwort"><input type="password" name="new-password" id="new-password" tabindex="<?php echo $tabindex++?>" /></dd>

			<dt class="c-neues-passwort-wiederholen"><label for="new-password2">Neues Passwort wiederholen</label></dt>
			<dd class="c-neues-passwort-wiederholen"><input type="password" name="new-password2" id="new-password2" tabindex="<?php echo $tabindex++?>" /></dd>
		</dl>
	</fieldset>
	<div class="einstellungen-speichern-2"><button type="submit" tabindex="<?php echo $save_tabindex?>" accesskey="w" title="[W]">Speichern</button></div>
</form>
<?php
	login_gui::html_foot();
?>
