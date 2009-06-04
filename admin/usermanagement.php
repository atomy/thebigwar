<?php
	require('include.php');

	if(!$admin_array['permissions'][13])
		die('No access.');

	if(!isset($_GET['action']))
	{
		$url = global_setting("PROTOCOL").'://'.$_SERVER['HTTP_HOST'].h_root.'/admin/index.php';
		header('Location: '.$url, true, 303);
		die('HTTP redirect: <a href="'.htmlentities($url).'">'.htmlentities($url).'</a>');
	}

	admin_gui::html_head();

	switch($_GET['action'])
	{
		case 'add':
			if(isset($_POST['new_admin']) && count($_POST['new_admin']) >= 2)
			{
				if(isset($admins[$_POST['new_admin'][0]]))
				{
					if(preg_match('/_([0-9]+)$/', $_POST['new_admin'][0], $match))
					{
						$i = $match[1]+1;
						$_POST['new_admin'][0] = substr($_POST['new_admin'][0], 0, -strlen($i)-1);
					}
					else
						$i=0;
					while(isset($admins[$_POST['new_admin'][0].'_'.$i]))
						$i++;
					$_POST['new_admin'][0] .= '_'.$i;
				}

				$admins[$_POST['new_admin'][0]]['password'] = md5($_POST['new_admin'][1]);
				$admins[$_POST['new_admin'][0]]['permissions'] = array();

				for($i=0; $i<=19; $i++)
					$admins[$_POST['new_admin'][0]]['permissions'][$i] = (isset($_POST['new_admin'][$i+2]) ? '1' : '0');

				write_admin_list($admins) && protocol("11.1", $_POST['new_admin'][0]);
			}
			else
			{
?>
<form action="usermanagement.php?action=add" method="post">
	<table border="1">
		<thead>
			<tr>
				<th rowspan="2" title="Name des Administrators">Name</th>
				<th rowspan="2" title="Passwort">Passwort</th>
				<th colspan="8" title="Benutzeraktionen">Benutzeraktionen</th>
				<th rowspan="2" title="Anfängerschutz ein-/ausschalten">Noob-<br>schutz</th>
				<th rowspan="2" xml:lang="en" title="Changelog bearbeiten">Change-<br>log</th>
				<th rowspan="2" title="Pranger bearbeiten">Pranger</th>
				<th rowspan="2" title="Nachricht versenden">Nachricht</th>
				<th rowspan="2" title="Log-Dateien ansehen"><span xml:lang="en">Logs</span></th>
				<th rowspan="2" title="Adminstratoren verwalten"><span xml:lang="en">Admins</span></th>
				<th rowspan="2" title="Wartungsarbeiten ein-/ausschalten">Wartung</th>
				<th rowspan="2" title="Spiel sperren/entsperren">Spiel<br>sperren</th>
				<th rowspan="2" title="Flottensperre einstellen">Flotten-<br>sperre</th>
				<th rowspan="2" title="News bearbeiten"><span xml:lang="en">News</span></th>
				<th rowspan="2" title="Flotte / Verteidigung ersetzten"><span xml:lang="en">Add-1</span></th>
				<th rowspan="2" title="Gebaeude / Forschung ersetzten"><span xml:lang="en">Add-2</span></th>
			</tr>
			<tr>
				<th title="Die Benutzerliste einsehen">Liste</th>
				<th title="Als Geist als ein Benutzer anmelden">Geist</th>
				<th title="Beim Benutzer die Werbung ein-auschalten">Werbung</th>
				<th title="Flottenhänger beim Benutzer korrigieren">Flotten-<br>hänger</th>
				<th title="Das Passwort eines Benutzers ändern">Pass</th>
				<th title="Einen Benutzer löschen">Löschen</th>
				<th title="Einen Benutzer sperren/entsperren">Sperren</th>
				<th title="Einen Benutzer umbenennen">Rename</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><input type="text" size="14" name="new_admin[0]" /></td>
				<td><input type="text" size="14" name="new_admin[1]" /></td>
<?php
				for($j=0; $j<=19; $j++)
				{
					if($j == 19) $j = 18; elseif($j == 18) $j = 19;
?>
				<td><input type="checkbox" name="new_admin[<?=htmlentities($j+2)?>]" value="1" /></td>
<?php
				if($j == 19) $j = 18; elseif($j == 18) $j = 19;
				}
?>
			</tr>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="20"><button type="submit">Hinzufügen</button></td>
			</tr>
		</tfoot>
	</table>
</form>
<?php
				break;
			}

		case 'delete':
			$admin_keys = array_keys($admins);
			if(isset($_GET['delete']) && isset($admin_keys[$_GET['delete']]) && $admin_keys[$_GET['delete']] != $_SESSION['admin_username'])
			{
				unset($admins[$admin_keys[$_GET['delete']]]);
				write_admin_list($admins) && protocol("11.4", $_GET['delete']);
			}

		case 'edit':
			if(isset($_POST['admin_array']))
			{
				$old_admins = array_keys($admins);
				$new_admins = array();
				$session_key = array_search($_SESSION['admin_username'], $old_admins);
				foreach($_POST['admin_array'] as $no=>$admin)
				{
					if(!isset($old_admins[$no]))
						continue;
					$this_password = $admins[$old_admins[$no]]['password'];
					$this_name = $admin[0];
					if(isset($new_admins[$this_name]))
					{
						if(preg_match('/_([0-9]+)$/', $this_name, $match))
						{
							$i = $match[1]+1;
							$this_name = substr($this_name, 0, -strlen($i)-1);
						}
						else
							$i=0;
						while(isset($new_admins[$this_name.'_'.$i]))
							$i++;
						$this_name .= '_'.$i;
					}
					if($old_admins[$no] != $this_name)
					{
						protocol("11.3", $old_admins[$no], $this_name);
						if($no == $session_key)
							$_SESSION['admin_username'] = $this_name;
					}
					$new_admins[$this_name] = array();
					$new_admins[$this_name]['password'] = $this_password;
					$new_admins[$this_name]['permissions'] = array();
					$prot = false;
					for($i=0; $i<=19; $i++)
					{
						$new_admins[$this_name]['permissions'][$i] = (isset($admin[$i+1]) ? '1' : '0');
						if($admins[$this_name]['permissions'][$i] != $new_admins[$this_name]['permissions'][$i])
							$prot = true;
					}
					if($prot) protocol("11.2", $this_name);
					$new_admins[$_SESSION['admin_username']]['permissions'][11] = '1';
				}
				write_admin_list($new_admins);
				$admins = $new_admins;
			}
?>
<form action="usermanagement.php?action=edit" method="post">
	<table border="1">
		<thead>
			<tr>
				<th rowspan="2" title="Name des Administrators">Name</th>
				<th colspan="8" title="Benutzeraktionen">Benutzeraktionen</th>
				<th rowspan="2" title="Anfängerschutz ein-/ausschalten">Noob-<br>schutz</th>
				<th rowspan="2" xml:lang="en" title="Changelog bearbeiten">Changelog</th>
				<th rowspan="2" title="Pranger bearbeiten">Pranger</th>
				<th rowspan="2" title="Nachricht versenden">Nachricht</th>
				<th rowspan="2" title="Admin-Log-Dateien ansehen"><span xml:lang="en">Logs</span></th>
				<th rowspan="2" title="Adminstratoren verwalten"><span xml:lang="en">Admin</span></th>
				<th rowspan="2" title="Wartungsarbeiten ein-/ausschalten">Wartung</th>
				<th rowspan="2" title="Spiel sperren/entsperren">Spiel-<br>sperren</th>
				<th rowspan="2" title="Flottensperre einstellen">Flotten-<br>sperre</th>
				<th rowspan="2" title="News bearbeiten"><span xml:lang="en">News</span></th>
				<th rowspan="2" title="Flotte / Verteidigung ersetzten"><span xml:lang="en">Edit Fleet</span></th>
				<th rowspan="2" title="Gebaeude / Forschung ersetzten"><span xml:lang="en">Edit Buildings/Research</span></th>
				<th rowspan="2" title="Admin (GO) Account löschen">Löschen</th>
			</tr>
			<tr>
				<th title="Die Benutzerliste einsehen">Liste</th>
				<th title="Als Geist als ein Benutzer anmelden">Geist</th>
				<th title="Das Passwort eines Benutzers ändern">Passw</th>
				<th title="Beim Benutzer die Werbung ein-auschalten">Werbung</th>
				<th title="Flottenhänger beim Benutzer korrigieren">Flotten-<br>hänger</th>
				<th title="Einen Benutzer löschen">Löschen</th>
				<th title="Einen Benutzer sperren/entsperren">Sperren</th>
				<th title="Einen Benutzer umbenennen"><span xml:lang="en">Rename</span></th>
			</tr>
		</thead>
		<tbody>
<?php
			$i = 0;
			foreach($admins as $name=>$settings)
			{
?>
			<tr>
				<td><input type="text" name="admin_array[<?=htmlentities($i)?>][0]" value="<?=utf8_htmlentities($name)?>" /></td>
<?php
				for($j=0; $j<=19; $j++)
				{
				if($j == 18) $j = 19; elseif($j == 19) $j = 18;
?>
				<td><input type="checkbox" name="admin_array[<?=htmlentities($i)?>][<?=htmlentities($j+1)?>]" value="1"<?=isset($settings['permissions'][$j]) && $settings['permissions'][$j] ? ' checked="checked"' : ''?><?=($j==11 && $name==$_SESSION['admin_username'])? ' disabled="disabled"' : ''?> /></td>
<?php
				if($j == 18) $j = 19; elseif($j == 19) $j = 18;
				}

				if($name == $_SESSION['admin_username'])
				{
?>
				<td>[Löschen]</td>
<?php
				}
				else
				{
?>
				<td><a href="?action=delete&amp;delete=<?=htmlentities(urlencode($i))?>">[Löschen]</a></td>
<?php
				}
?>
			</tr>
<?php
				$i++;
			}
?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="22"><button type="submit">Speichern</button></td>
			</tr>
		</tfoot>
	</table>
</form>
<?php
			break;
	}

	admin_gui::html_foot();
?>
