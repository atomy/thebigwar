<?php
	// XDEBUG_SESSION_START
	// XDEBUG_SESSION_STOP
	require_once( '../include/config_inc.php' );
	require( TBW_ROOT.'admin/include.php' );

	// load class user
	__autoload('User');

	if($admin_array['permissions'][1] && isset($_POST['ghost_username']) && User::userExists(trim($_POST['ghost_username'])))
	{
		# Als Geist als ein Benutzer anmelden
		$_SESSION['username'] = trim($_POST['ghost_username']);
		$_SESSION['ghost'] = true;
		$_SESSION['resume'] = true;

		protocol("1", $_SESSION['username']);

		$url = 'http://'.$_SERVER['HTTP_HOST'].h_root.'/login/index.php?'.urlencode(session_name()).'='.urlencode(session_id());
		header('Location: '.$url, true, 303);
		die('HTTP redirect: <a href="'.htmlentities($url).'">'.htmlentities($url).'</a>');
	}

	if($admin_array['permissions'][2] && isset($_POST['passwd_username']) && isset($_POST['passwd_password']) && User::userExists(trim($_POST['passwd_username'])))
	{
		# Passwort aendern

		$_POST['passwd_username'] = trim($_POST['passwd_username']);

		$that_user = Classes::User($_POST['passwd_username']);
		$that_user->setPassword($_POST['passwd_password']) && protocol("2", $_POST['passwd_username']);
		unset($that_user);
	}

	
    if($admin_array['permissions'][3.1] && isset($_POST['ein_username']) && User::userExists(trim($_POST['ein_username'])))
    {
		# Werbung einschalten

		$that_user = Classes::User($_POST['ein_username']);
		$that_user->setSetting('noads', isset($_POST['noads'])) && protocol("2.1", $_POST['ein_username']);
		
    }

    if($admin_array['permissions'][3.2] && isset($_POST['aus_username']) && User::userExists(trim($_POST['aus_username'])))
    {
		# Werbung ausschalten

		$_POST['aus_username'] = trim($_POST['aus_username']);
				$_POST['noads'] = true;
		$that_user = Classes::User($_POST['aus_username']);
                $that_user->setSetting('noads', isset($_POST['noads'])) && protocol("2.2", $_POST['aus_username']);
    }

    if($admin_array['permissions'][4] && isset($_POST['flug_username']) && User::userExists(trim($_POST['flug_username'])))
    {
    	#Flottenhänger korrigieren
		$eventfile = Classes::EventFile();
		#Betroffenen User eintragen
		$user_obj = new User($_POST['flug_username']);
		#Flotten holen
		$flotten = $user_obj->getFleetsList();
		foreach($flotten as $flotte)
		{
		$fl = Classes::Fleet($flotte);
		$time = $fl->getArrivalTime();
		if($time < time())
			{
            echo "FleetID:" ."$flotte". "wurde korrigiert.\n"; 
			$fleet = new Fleet($flotte);
			$eventfile->removeFleet($flotte);
			$eventfile->addNewFleet($fleet->getNextArrival(), $flotte);
			unset($fleet);
			}
		}
	}


	if($admin_array['permissions'][5] && isset($_POST['delete_username']) && User::userExists(trim($_POST['delete_username'])))
	{
		# Benutzer loeschen

		$_POST['delete_username'] = trim($_POST['delete_username']);

		# entferne user
		user_control::removeUser( $_POST['delete_username'] ) && protocol( "4", $_POST['delete_username'] );
	}
		# Benutzer sperren / entsperren
	if($admin_array['permissions'][6] && isset($_POST['lock_username']) && User::userExists(trim($_POST['lock_username'])))
	{
		
		$_POST['lock_username'] = trim($_POST['lock_username']);

                $lock_time = false;
                if(isset($_POST['user_lock_period']) && isset($_POST['user_lock_period_unit']))
                {
                        $_POST['user_lock_period'] = trim($_POST['user_lock_period']);
                        $_POST['user_lock_period_unit'] = trim($_POST['user_lock_period_unit']);
                        if($_POST['user_lock_period'])
                        {
                                switch($_POST['user_lock_period_unit'])
                                {
                                        case 'min': $lock_time = time()+$_POST['user_lock_period']*60; break;
                                        case 'h': $lock_time = time()+$_POST['user_lock_period']*3600; break;
                                        case 'd': $lock_time = time()+$_POST['user_lock_period']*86400; break;
                                }
                        }
                }

                $that_user = Classes::User($_POST['lock_username']);
                $that_user->lockUser($lock_time) && protocol(($that_user->userLocked() ? '5.1' : '5.2'), $_POST['lock_username']);
                unset($that_user);
        }

    if($admin_array['permissions'][7] && isset($_POST['rename_old']) && isset($_POST['rename_new']) && User::userExists(trim($_POST['rename_old'])))
    {
		# Benutzer umbenennen
		$_POST['rename_old'] = trim($_POST['rename_old']);
		$_POST['rename_new'] = substr(trim($_POST['rename_new']), 0, 20);

		$that_user = Classes::User($_POST['rename_old']);
		$that_user->rename($_POST['rename_new']) && protocol("6", $_POST['rename_old'], $_POST['rename_new']);
	}
		#Anfängerschutz
	if($admin_array['permissions'][8] && isset($_POST['noob']))
	{
		if($_POST['noob'] && !file_exists(global_setting("DB_NONOOBS")))
			touch(global_setting("DB_NONOOBS")) && protocol("7.1");
		elseif(!$_POST['noob'] && file_exists(global_setting("DB_NONOOBS")))
			unlink(global_setting("DB_NONOOBS")) && protocol("7.2");
	}
		#Nachricht versenden
	if($admin_array['permissions'][11] && isset($_POST['message_text']) && trim($_POST['message_text']) != '')
	{
		$message = new Message();
		if($message->create())
		{
			if(isset($_POST['message_from']))
				$message->from($_POST['message_from']);
			if(isset($_POST['message_subject']))
				$message->subject($_POST['message_subject']);
			$message->text($_POST['message_text']);
			if(isset($_POST['message_html']) && $_POST['message_html'])
				$message->html(true);
			$to = '';
			if(isset($_POST['message_to'])) $to = trim($_POST['message_to']);
			if(!$to)
			{
				# An alle Benutzer versenden

				$dh = opendir(global_setting("DB_PLAYERS"));
				while(($uname = readdir($dh)) !== false)
                           $message->addUser(urldecode($uname), 6);

                           echo $uname;
      
			
			      closedir($dh);

			}
			else
			{
				$to = explode("\r\n", $to);
				foreach($to as $t)
					$message->addUser(urldecode($t), 6);
			}
			protocol("9", $_POST['message_subject'], str_replace("\r\n", ", ", $_POST['message_to']));
			unset($message);
		}
	}
       
		#wartungsarbeiten
	if($admin_array['permissions'][14] && isset($_POST['wartungsarbeiten']))
	{
		if($_POST['wartungsarbeiten'] && !is_file('../.htaccess.wartungsarbeiten.sav'))
		{
			if(!file_exists('../.htaccess'))
				touch('../.htaccess');
			if(rename('../.htaccess', '../.htaccess.wartungsarbeiten.sav'))
			{
				($fh = fopen('../.htaccess', 'w')) && (protocol("12.1"));
				flock($fh, LOCK_EX);

				fwrite($fh, "Order Deny,Allow\n");
				fwrite($fh, "Deny from All\n");
				fwrite($fh, "ErrorDocument 403 /wartungsarbeiten.html\n");
				fwrite($fh, "<Files \"wartungsarbeiten.html\">\n");
				fwrite($fh, "\tDeny from None\n");
				fwrite($fh, "</Files>\n");

				flock($fh, LOCK_UN);
				fclose($fh);
			}
		}
		elseif(!$_POST['wartungsarbeiten'] && is_file('../.htaccess.wartungsarbeiten.sav'))
		{
			if(is_file('../.htaccess'))
				unlink('../.htaccess');
			rename('../.htaccess.wartungsarbeiten.sav', '../.htaccess') && protocol("12.2");
		}
	}
	#spiel sperren
	if(isset($admin_array['permissions'][15]) && $admin_array['permissions'][15] && isset($_POST['lock']))
	{
		if($_POST['lock'] && !file_exists(global_setting("DB_LOCKED")))
		{
			# Bei allen Benutzern den Eventhandler ausfuehren
			$dh = opendir(global_setting("DB_PLAYERS"));
                        while(($player = readdir($dh)) !== false)
                        {
                                if(!is_file(global_setting("DB_PLAYERS").'/'.$player) || !is_readable(global_setting("DB_PLAYERS").'/'.$player))
                                        continue;
                                $this_user = Classes::User(urldecode($player));
                                $this_user->eventhandler(0, 1,1,1,1,1);
                                unset($this_user);
                        }
                        closedir($dh);

                        ($fh = fopen(global_setting("DB_LOCKED"), "w")) and protocol("13.1");
                        if($fh)
                        {
                                if(isset($_POST['lock_period']) && isset($_POST['lock_period_unit']))
                                {
                                        $lock_time = false;
                                        $_POST['lock_period'] = trim($_POST['lock_period']);
                                        $_POST['lock_period_unit'] = trim($_POST['lock_period_unit']);
                                        if($_POST['lock_period'])
                                        {
                                                switch($_POST['lock_period_unit'])
                                                {
                                                        case 'min': $lock_time = time()+$_POST['lock_period']*60; break;
                                                        case 'h': $lock_time = time()+$_POST['lock_period']*3600; break;
                                                        case 'd': $lock_time = time()+$_POST['lock_period']*86400; break;
                                                }
                                        }
                                        if($lock_time)
                                                fwrite($fh, $lock_time);
                                }
                                fclose($fh);
                        }
                }
                elseif(!$_POST['lock'] && database_locked())
                {

			# Bei allen Benutzern den Eventhandler ausfuehren
			 $dh = opendir(global_setting("DB_PLAYERS"));
                        while(($player = readdir($dh)) !== false)
                        {
                                if(!is_file(global_setting("DB_PLAYERS").'/'.$player) || !is_readable(global_setting("DB_PLAYERS").'/'.$player))
                                        continue;
                                $this_user = Classes::User(urldecode($player));
                                $this_user->eventhandler(0, 1,1,1,1,1);
                                unset($this_user);
                        }
                        closedir($dh);

                        unlink(global_setting("DB_LOCKED")) && protocol("13.2");
                }
        }
			#flottensperre
        if(isset($admin_array['permissions'][16]) && $admin_array['permissions'][16] && isset($_POST['flock']))
        {
                if($_POST['flock'] && !fleets_locked())
                {
                        ($fh = fopen(global_setting("DB_NO_ATTS"), "w")) and protocol("15.1");
                        if($fh)
                        {
                                if(isset($_POST['flock_period']) && isset($_POST['flock_period_unit']))
                                {
                                        $lock_time = false;
                                        $_POST['flock_period'] = trim($_POST['flock_period']);
                                        $_POST['flock_period_unit'] = trim($_POST['flock_period_unit']);
                                        if($_POST['flock_period'])
                                        {
                                                switch($_POST['flock_period_unit'])
                                                {
                                                        case 'min': $lock_time = time()+$_POST['flock_period']*60; break;
                                                        case 'h': $lock_time = time()+$_POST['flock_period']*3600; break;
                                                        case 'd': $lock_time = time()+$_POST['flock_period']*86400; break;
                                                }
                                        }
                                        if($lock_time)
                                                fwrite($fh, $lock_time);
								}	
                                fclose($fh);
                        }
                }
                elseif(!$_POST['flock'] && fleets_locked())
                {
                        unlink(global_setting("DB_NO_ATTS")) && protocol("15.2");
                }
        }

	
	admin_gui::html_head();
?>
<strong>
<h3>Willkommen im TBW-Adminbereich!</h3>
1.) Benutze niemals eine Funktionen aus dem Adminbereich zu Deinem eigenen Vorteil.<br>
2.) Gebe keine Informationen an Personen weiter, die sich diese Informationen nicht selbst beschaffen könnten.<br>
3.) Zur Sicherheit und um Streitfälle klären zu können, wird jede Aktion der GO's im Adminbereich geloggt.<br><br>
</strong>

<table style="text-align: left; margin-left: auto; margin-right: auto; width: 825px; height: 600px;" border="1" cellpadding="0" cellspacing="0">
  <tbody>
    <tr>
	<td colspan="1" rowspan="6" align="center" nowrap="nowrap" valign="middle"><img src="../downloads/banner/160x600.jpg" alt="tbw-banner"></img></td>

      <td style="width: 200px;" align="center" nowrap="nowrap" valign="middle">
			<a href="<?php echo $RELPATH; ?>index.php#passwort-aendern">Adminpasswort<br>&auml;ndern</a>
      </td>
      <td style="width: 200px;" align="center" nowrap="nowrap" valign="middle">
			<?php if($admin_array['permissions'][0]){?><a href="<?php echo $RELPATH; ?>index.php#action-0">Benutzerliste<br>einsehen</a>
			<?php } else {?><s>Benutzerliste<br>einsehen</s><?php }?>
	  </td>
      <td style="width: 200px;" align="center" nowrap="nowrap" valign="middle">
			<?php if($admin_array['permissions'][1]){?><a href="<?php echo $RELPATH; ?>index.php#action-1">Als Geistbenutzer<br>anmelden</a>
			<?php } else {?><s>Als Geistbenutzer<br>anmelden</s><?php }?>
      </td>
      <td style="width: 200px;" align="center" nowrap="nowrap" valign="middle">
			<?php if($admin_array['permissions'][2]){?><a href="<?php echo $RELPATH; ?>index.php#action-2">Das Passwort eines<br>Benutzers &auml;ndern</a>
			<?php } else {?><s>Das Passwort eines<br>Beutzers &auml;ndern</s><?php }?>
      </td>
    </tr>
    <tr>
      <td style="width: 200px;" align="center" nowrap="nowrap" valign="middle">
            <?php if($admin_array['permissions'][3.1]){?><a href="<?php echo $RELPATH; ?>index.php#action-3.1">Beim Benutzer<br>Werbung einschalten</a>
			<?php } else {?><s>Beim Benutzer<br>die Werbung einschalten</s><?php }?>
      </td>
      <td style="width: 200px;" align="center" nowrap="nowrap" valign="middle">
			<?php if($admin_array['permissions'][3.2]){?><a href="<?php echo $RELPATH; ?>index.php#action-3.2">Beim Benutzer<br>Werbung ausschalten</a>
			<?php } else {?><s>Beim Benutzer<br>die Werbung ausschalten</s><?php }?>
      </td>
      <td style="width: 200px;" align="center" nowrap="nowrap" valign="middle">
			<?php if($admin_array['permissions'][4]){?><a href="<?php echo $RELPATH; ?>index.php#action-4">Flottenhänger beim<br>Benutzer korrigieren</a>
			<?php } else {?><s>Flottenhänger beim<br>Benutzer korrigieren</s><?php }?>
	  </td>
      <td style="width: 200px;" align="center" nowrap="nowrap" valign="middle">
			<?php if($admin_array['permissions'][5]){?><a href="<?php echo $RELPATH; ?>index.php#action-5">Einen Benutzer<br>l&ouml;schen</a>
			<?php } else {?><s>Einen Benutzer<br>l&ouml;schen</s><?php }?>
	  </td>
    </tr>
    <tr>
      <td style="width: 200px;" align="center" nowrap="nowrap" valign="middle">
            <?php if($admin_array['permissions'][6]){?><a href="<?php echo $RELPATH; ?>index.php#action-6">Einen Benutzer<br>sperren/entsperren</a>
			<?php } else {?><s>Einen Benutzer<br>sperren/entsperren</s><?php }?>
      </td>
      <td style="width: 200px;" align="center" nowrap="nowrap" valign="middle">
			<?php if($admin_array['permissions'][7]){?><a href="<?php echo $RELPATH; ?>index.php#action-7">Einen Benutzer<br>umbenennen</a>
			<?php } else {?><s>Einen Benutzer<br>umbenennen</s><?php }?>
	  </td>
      <td style="width: 200px;" align="center" nowrap="nowrap" valign="middle">
			<?php if($admin_array['permissions'][8]){?><a href="<?php echo $RELPATH; ?>index.php#action-8">Anf&auml;ngerschutz<br>ein-/ausschalten</a>
			<?php } else {?><s>Anf&auml;ngerschutz<br>ein-/ausschalten</s><?php }?>
	  </td>
      <td style="vertical-align: middle; white-space: nowrap; text-align: center; width: 200px;">
            <?php if($admin_array['permissions'][9]){?><a href="<?php echo $RELPATH; ?>index.php#action-9"><span xml:lang="en">Changelog</span><br>bearbeiten</a>
			<?php } else {?><s><span xml:lang="en">Changelog</span><br>bearbeiten</s><?php }?>
      </td>
    </tr>
	<tr>
      <td style="width: 200px;" align="center" nowrap="nowrap" valign="middle">
			<?php if($admin_array['permissions'][10]){?><a href="<?php echo $RELPATH; ?>index.php#action-10">Pranger<br>bearbeiten</a>
			<?php } else {?><s>Pranger<br>bearbeiten</s><?php }?>
	  </td>
      <td style="width: 200px;" align="center" nowrap="nowrap" valign="middle">
            <?php if($admin_array['permissions'][11]){?><a href="<?php echo $RELPATH; ?>index.php#action-11">Nachricht<br>versenden</a>
			<?php } else {?><s>Nachricht<br>versenden</s><?php }?>
      </td>
      <td style="width: 200px;" align="center" nowrap="nowrap" valign="middle">
            <?php if($admin_array['permissions'][12]){?><a href="<?php echo $RELPATH; ?>index.php#action-12"><span xml:lang="en">Admin</span>-<span xml:lang="en">Log</span>dateien<br>einsehen</a>
			<?php } else {?><s>Admin</span>-<span xml:lang="en">Log</span>dateien<br>einsehen</s><?php }?>
      </td>
      <td style="width: 200px;" align="center" nowrap="nowrap" valign="middle">
             <?php if($admin_array['permissions'][13]){?><a href="<?php echo $RELPATH; ?>index.php#action-13">Benutzer-<br>verwaltung</a>
			 <?php } else {?><s>Benutzer-<br>verwaltung</s><?php }?>
      </td>
    </tr>
    <tr>
      <td style="width: 200px;" align="center" nowrap="nowrap" valign="middle">
			<?php if($admin_array['permissions'][14]){?><a href="<?php echo $RELPATH; ?>index.php#action-14">Wartungsarbeiten<br>aktivieren</a>
			<?php } else {?><s>Wartungsarbeiten<br>aktivieren</s><?php }?>
      </td>
      <td style="width: 200px;" align="center" nowrap="nowrap" valign="middle">
			<?php if(isset($admin_array['permissions'][15]) && $admin_array['permissions'][15]){?><a href="<?php echo $RELPATH; ?>index.php#action-15">Spiel<br>sperren/entsperren</a>
			<?php } else {?>Spiel<br>sperren/entsperren<?php }?>
      </td>
      <td style="width: 200px;" align="center" nowrap="nowrap" valign="middle">
            <?php if(isset($admin_array['permissions'][16]) && $admin_array['permissions'][16]){?><a href="<?php echo $RELPATH; ?>index.php#action-16">Flottensperre<br>aktivieren</a>
			<?php } else {?>Flottensperre<br>aktivieren<?php }?>
      </td>
      <td style="width: 200px;" align="center" nowrap="nowrap" valign="middle">
			<?php if(isset($admin_array['permissions'][17]) && $admin_array['permissions'][17]){?><a href="<?php echo $RELPATH; ?>index.php#action-17"><span xml:lang="en">News</span><br><span xml:lang="en">bearbeiten</span></a>
			<?php } else {?><span xml:lang="en">News</span><br><span xml:lang="en">bearbeiten (inaktiv, News via Forum!)</span><?php }?>
      </td>
    </tr>
	
	<tr>
       <td style="width: 200px;" align="center" nowrap="nowrap" valign="middle">
            <?php if(isset($admin_array['permissions'][18]) && $admin_array['permissions'][18]){?><a href="<?php echo $RELPATH; ?>index.php#action-18">Flotte / Verteidigung<br>ersetzen</a>
			<?php } else {?>Flotte / Verteidigung<br>ersetzen<?php }?>
      </td>
      <td style="width: 200px;" align="center" nowrap="nowrap" valign="middle">
            <?php if(isset($admin_array['permissions'][19]) && $admin_array['permissions'][19]){?><a href="<?php echo $RELPATH; ?>index.php#action-19">Geb&auml;ude / Forschung<br>ersetzen</a>
			<?php } else {?>Geb&auml;ude / Forschung<br>ersetzen<?php }?>
      </td>
	  <td style="width: 200px;" align="center" nowrap="nowrap" valign="middle">
      </td>
	   <td style="width: 200px;" align="center" nowrap="nowrap" valign="middle">
      </td>
    </tr>
	
	
  </tbody>
</table>
<br>

<fieldset><legend id="passwort-aendern">Adminpasswort ändern</h2></legend>
<?php
	if(isset($_POST['old_password']) && isset($_POST['new_password']) && isset($_POST['new_password2']))
	{
		if(md5($_POST['old_password']) != $admin_array['password'])
		{
?>
<p class="error"><strong>Sie haben das falsche alte Passwort eingegeben.</strong></p>
<?php
		}
		elseif($_POST['new_password'] != $_POST['new_password2'])
		{
?>
<p class="error"><strong>Die beiden neuen Passwörter stimmen nicht überein.</strong></p>
<?php
		}
		else
		{
			$admin_array['password'] = md5($_POST['new_password']);
			write_admin_list($admins);
?>
<p class="successful"><strong>Das Passwort wurde erfolgreich geändert.</strong></p>
<?php
		}
	}
?>
<form action="index.php" method="post">
	<dl>
		<dt><label for="old-password-input">Altes Passwort</label></dt>
		<dd><input type="password" name="old_password" id="old-password-input" /></dd>

		<dt><label for="new-password-input">Neues Passwort</label></dt>
		<dd><input type="password" name="new_password" id="new-password-input" /></dd>

		<dt><label for="new-password2-input">Neues Passwort wiederholen</label></dt>
		<dd><input type="password" name="new_password2" id="new-password2-input" /></dd>
	</dl>
	<div><button type="submit">Passwort ändern</button></div>
</form>
<p><a href="#top">Zurück zum Menü</a></p>
</fieldset>

<?php
	if($admin_array['permissions'][0])
	{
?>
<fieldset><legend id="action-0">Benutzerliste einsehen</legend>
<form action="userlist.php" method="get">
	<ul>
		<li><button type="submit">Unsortiert</button></li>
		<li><button type="submit" name="sort" value="1">Sortiert</button></li>
	</ul>
</form>
<p><a href="#top">Zurück zum Menü</a></p>
</fieldset>

<?php
	}

	if($admin_array['permissions'][1])
	{
?>
<fieldset><legend id="action-1">Als Geist als ein Benutzer anmelden</legend>
<form action="index.php" method="post">
	<dl>
		<dt><label for="ghost-input">Benutzername</label></dt>
		<dd><input type="text" name="ghost_username" id="ghost-input" /></dd>
	</dl>
	<div><button type="submit">Anmelden</button></div>
</form>
<p><a href="#top">Zurück zum Menü</a></p>
</fieldset>
<script type="text/javascript">
	// Autocompletion
	activate_users_list(document.getElementById('ghost-input'));
</script>
<?php
	}

	if($admin_array['permissions'][2])
	{
?>
<fieldset><legend id="action-2">Das Passwort eines Benutzers ändern</legend>
<form action="index.php" method="post">
	<dl>
		<dt><label for="passwd-name-input">Benutzername</label></dt>
		<dd><input type="text" name="passwd_username" id="passwd-name-input" /></dd>

		<dt><label for="passwd-passwd-input">Passwort</label></dt>
		<dd><input type="text" name="passwd_password" id="passwd-passwd-input" /></dd>
	</dl>
	<div><button type="submit">Passwort ändern</button></div>
</form>
<p><a href="#top">Zurück zum Menü</a></p>
</fieldset>
<script type="text/javascript">
	// Autocompletion
	activate_users_list(document.getElementById('passwd-name-input'));
</script>
<?php
	}

	if($admin_array['permissions'][3.1])
	{
?>
<fieldset><legend id="action-3.1">Beim Benutzer die Werbung einschalten</legend>
<form action="index.php" method="post">
	<dl>
		<dt><label for="ein-username-input">Benutzername</label></dt>
		<dd><input type="text" name="ein_username" id="ein-username-input" /></dd>
	</dl>
	<div><button type="submit">Werbung einschalten</button></div>
</form>
<p><a href="#top">Zurück zum Menü</a></p>
</fieldset>
<script type="text/javascript">
	// Autocompletion
	activate_users_list(document.getElementById('ein-username-input'));
</script>
<?php
	}

	if($admin_array['permissions'][3.2])
	{
?>
<fieldset><legend id="action-3.2">Beim Benutzer die Werbung ausschalten</legend>
<form action="index.php" method="post">
	<dl>
		<dt><label for="aus-username-input">Benutzername</label></dt>
		<dd><input type="text" name="aus_username" id="aus-username-input" /></dd>
	</dl>
	<div><button type="submit">Werbung ausschalten</button></div>
</form>
<p><a href="#top">Zurück zum Menü</a></p>
</fieldset>
<script type="text/javascript">
	// Autocompletion
	activate_users_list(document.getElementById('aus-username-input'));
</script>
<?php
	}

	if($admin_array['permissions'][4])
	{
?>
<fieldset><legend id="action-4">Flottenhänger beim Benutzer korrigieren</legend>
<form action="index.php" method="post">
    <dl>
        <dt><label for="flug-username-input">Benutzername</label></dt>
        <dd><input type="text" name="flug_username" id="flug-username-input" /></dd>
    </dl>
    <div><button type="submit">Flug neu berechnen</button></div>
</form>
<p><a href="#top">Zurück zum Menü</a></p>
</fieldset>
<script type="text/javascript">
    // Autocompletion
    activate_users_list(document.getElementById('flug-username-input'));
</script>
<?php
	}

	if($admin_array['permissions'][5])
	{
?>
<fieldset><legend id="action-5">Einen Benutzer löschen</legend>
<p><strong>Bitte nicht wegen Regelverstoßes durchführen (dann Benutzer sperren), nur bei fehlerhaften Registrierungen oder Ähnlichem.</strong></p>
<form action="index.php" method="post">
	<dl>
		<dt><label for="delete-input">Benutzername</label></dt>
		<dd><input type="text" name="delete_username" id="delete-input" /></dd>
	</dl>
	<div><button type="submit">Löschen</button></div>
</form>
<p><a href="#top">Zurück zum Menü</a></p>
</fieldset>
<script type="text/javascript">
	// Autocompletion
	activate_users_list(document.getElementById('delete-input'));
</script>
<?php
	}

	if($admin_array['permissions'][6])
	{
?>
<fieldset><legend id="action-6">Einen Benutzer sperren / entsperren</legend>
<form action="index.php" method="post">
        <dl>
                <dt><label for="user-lock-input">Benutzername</label></dt>
                <dd><input type="text" name="lock_username" id="user-lock-input" /></dd>

                <dt><label for="user-lock-period-input">Dauer der Sperre</label></dt>
                <dd><input type="text" name="user_lock_period" id="user-lock-period-input"> <select name="user_lock_period_unit"><option value="min">Minuten</option><option value="h">Stunden</option><option value="d">Tage</option></select></dd>
        </dl>
        <div><button type="submit">Sperren / Entsperren</button></div>
</form>
<p><a href="#top">Zurück zum Menü</a></p>
</fieldset>
<script type="text/javascript">
        // Autocompletion
        activate_users_list(document.getElementById('user-lock-input'));
</script>
<?php
        }

        if($admin_array['permissions'][7])
        {
?>
<fieldset><legend id="action-7">Einen Benutzer umbenennen (max. einmal in 7 Tagen!)</legend>
<form action="index.php" method="post">
	<dl>
		<dt><label for="rename-from">Alter Name</label></dt>
		<dd><input type="text" name="rename_old" id="rename-from" /></dd>

		<dt><label for="rename-to">Neuer Name</label></dt>
		<dd><input type="text" name="rename_new" id="rename-to" /></dd>
	</dl>
	<div><button type="submit">Umbenennen</button></div>
</form>
<p><a href="#top">Zurück zum Menü</a></p>
</fieldset>
<script type="text/javascript">
	// Autocompletion
	activate_users_list(document.getElementById('rename-from'));
</script>
<?php
	}

	if($admin_array['permissions'][8])
	{
?>
<fieldset><legend id="action-8">Anfängerschutz ein-/ausschalten</legend>
<?php
		if(file_exists(global_setting("DB_NONOOBS")))
		{
?>
<form action="index.php" method="post">
        <div><input type="hidden" name="noob" value="0" /><button type="submit">Anfängerschutz einschalten</button></div>
</form>
<?php
		}
		else
		{
?>
<form action="index.php" method="post">
        <div><input type="hidden" name="noob" value="1" /><button type="submit">Anfängerschutz ausschalten</button></div>
</form>
<p><a href="#top">Zurück zum Menü</a></p>
</fieldset>
<?php
		}
	}

	if($admin_array['permissions'][9])
	{
?>
<fieldset><legend id="action-9">Changelog bearbeiten</legend>
<h2><a href="edit_changelog.php"><span xml:lang="en">Changelog</span> bearbeiten</a></h2>
<p><a href="#top">Zurück zum Menü</a></p>
</fieldset>
<?php
	}
	

	if($admin_array['permissions'][10])
	{
?>
<fieldset><legend id="action-10">Pranger bearbeiten</legend>
<h2><a href="edit_pranger.php"><span xml:lang="en">Pranger</span> bearbeiten</a></h2>
<p><a href="#top">Zurück zum Menü</a></p>
</fieldset>
<?php
	}

	if($admin_array['permissions'][11])
	{
?>
<fieldset><legend id="action-11">Nachricht versenden</legend>
<form action="index.php" method="post">
	<dl>
		<dt><label for="message-absender-input">Absender</label></dt>
		<dd><input type="text" name="message_from" id="message-absender-input" /></dd>

		<dt><label for="message-empfaenger-textarea">Empfänger</label></dt>
		<dd><textarea cols="20" rows="4" name="message_to" id="message-empfaenger-textarea"></textarea> Bleibt dieses Feld leer, wird an alle Benutzer verschickt.</dd>

		<dt><label for="message-betreff-input">Betreff</label></dt>
		<dd><input type="text" name="message_subject" id="message-betreff-input" /></dd>

		<dt><label for="message-html-checkbox"><abbr title="Hypertext Markup Language" xml:lang="en"><span xml:lang="de">HTML</span></abbr>?</label></dt>
		<dd><input type="checkbox" name="message_html" id="message-html-checkbox" /></dd>

		<dt><label for="message-text-textarea">Text</label></dt>
		<dd><textarea cols="50" rows="10" name="message_text" id="message-text-textarea"></textarea></dd>
	</dl>
	<div><button type="submit">Absenden</button></div>
</form>
<p><a href="#top">Zurück zum Menü</a></p>
</fieldset>
<?php
	}

	if($admin_array['permissions'][12])
	{
?>
<fieldset><legend id="action-12">Admin-Logdateien einsehen</legend>
<h2><a href="logs.php"><span xml:lang="en">Admin</span>-<span xml:lang="en">Log</span>dateien einsehen</a></h2>
<p><a href="#top">Zurück zum Menü</a></p>
</fieldset>
<?php
	}

	if($admin_array['permissions'][13])
	{
?>
<fieldset><legend id="action-13">Benutzerverwaltung</legend>
<ul>
	<li><a href="usermanagement.php?action=edit">Bestehende Benutzer bearbeiten</a></li>
	<li><a href="usermanagement.php?action=add">Neuen Benutzer anlegen</a></li>
</ul>
<p><a href="#top">Zurück zum Menü</a></p>
</fieldset>
<?php
	}

	if($admin_array['permissions'][14])
	{
?>
<fieldset><legend id="action-14">Wartungsarbeiten</legend>
<?php
		if(is_file('../.htaccess.wartungsarbeiten.sav'))
		{
?>
<form action="index.php" method="post">
	<div><input type="hidden" name="wartungsarbeiten" value="0" /><button type="submit">Wartungsarbeiten deaktivieren</button></div>
</form>
<p><a href="#top">Zurück zum Menü</a></p>
</fieldset>
<?php
		}
		else
		{
?>
<form action="index.php" method="post">
	<div><input type="hidden" name="wartungsarbeiten" value="1" /><button type="submit">Wartungsarbeiten aktivieren</button></div>
</form>
<p><a href="#top">Zurück zum Menü</a></p>
</fieldset>
<?php
		}
	}

	if(isset($admin_array['permissions'][15]) && $admin_array['permissions'][15])
	{
		if(database_locked())
		{
?>
<fieldset><legend id="action-15">Spiel entsperren</legend>
<form action="index.php" method="post">
	<div><input type="hidden" name="lock" value="0" /><button type="submit">Entsperren</button></div>
</form>
<p><a href="#top">Zurück zum Menü</a></p>
</fieldset>
<?php
		}
		else
		{
?>
<fieldset><legend id="action-15">Spiel sperren</legend>

<form action="index.php" method="post">
        <dl>
                <dt><label for="lock-period-input">Dauer der Sperre</label></dt>
                <dd><input type="text" name="lock_period" id="lock-period-input"> <select name="lock_period_unit"><option value="min">Minuten</option><option value="h">Stunden</option><option value="d">Tage</option></select></dd>
        </dl>
        <div><input type="hidden" name="lock" value="1" /><button type="submit">Sperren</button></div>
</form>
<p><a href="#top">Zurück zum Menü</a></p>
</fieldset>
<?php
                }
        }

	if(isset($admin_array['permissions'][16]) && $admin_array['permissions'][16])
  		    {
  		        if(fleets_locked())
  		        {
  		?>
<fieldset><legend id="action-16">Flottensperre</legend>
  		<form action="index.php" method="post">
  		<div><input type="hidden" name="flock" value="0" /><button type="submit">Aufheben</button></div>
  		</form>
		<p><a href="#top">Zurück zum Menü</a></p>
		</fieldset>
  		<?php
  		        }
  		        else
  		        {
  		?>
  		<fieldset><legend id="action-16">Flottensperre</legend>
  		<form action="index.php" method="post">
  		    <dl>
  		        <dt><label for="flock-period-input">Dauer der Flottensperre</label></dt>
  		        <dd><input type="text" name="flock_period" id="lock-period-input"> <select name="flock_period_unit"><option value="min">Minuten</option><option value="h">Stunden</option><option value="d">Tage</option></select></dd>
  		    </dl>
  		    <div><input type="hidden" name="flock" value="1" /><button type="submit">Setzen</button></div>
  		</form>
		<p><a href="#top">Zurück zum Menü</a></p>
		</fieldset>

  		
  		<?php
  		        }
  		    }
		
	if(isset($admin_array['permissions'][17]) && $admin_array['permissions'][17])
	{

?>
<fieldset><legend id="action-17">News bearbeiten</legend>
	<h2><a href="news.php"><span xml:lang="en">News</span> bearbeiten</a></h2>
	<p><a href="#top">Zurück zum Menü</a></p>
</fieldset>
<?php
	}


	if(isset($admin_array['permissions'][18]) && $admin_array['permissions'][18])
	{

?>


<fieldset><legend id="action-18">Flotte / Verteidigung ersetzten [+/-] (NOCH NICHT AKTIV!)</legend>
<form action="index.php" method="post">
<table cellpadding="2" cellspacing="2">
	<thead>
			<tr>
				<td>
					<dt><label for="username-input">Benutzername</label></dt>
					<dt><input type="text" name="username" id="username-input" /></dt>
					<script type="text/javascript">
					// Autocompletion
						activate_users_list(document.getElementById('username-input'));
					</script>
				</td>
				
				<td>
					<dt><label for="planet-input">Planetennummer</label></dt>
					<dt><input type="text" name="planetnr" id="planet-input" /></dt>
				</td>
			</tr>
	</thead>
	<tbody>
			<tr>
				<td>
					<dl>
						<label for="fleetadd-S0">Kleiner Transporter</label>&nbsp;<dt><input type="text" name="fleetadd" id="fleetadd-S0" /></dt>
						<dt><label for="fleetadd-S1">Großer Transporter</label>&nbsp;<dt><input type="text" name="fleetadd" id="fleetadd-S1" /></dt>
						<dt><label for="fleetadd-S2">Transcube</label>&nbsp;<dt><input type="text" name="fleetadd" id="fleetadd-S2" /></dt>
						<dt><label for="fleetadd-S3">Sammler</label>&nbsp;<dt><input type="text" name="fleetadd" id="fleetadd-S3" /></dt>
						<dt><label for="fleetadd-S5">Spionagesonde</label>&nbsp;<dt><input type="text" name="fleetadd" id="fleetadd-S5" /></dt>
						<dt><label for="fleetadd-S6">Besiedelungsschiff</label>&nbsp;<dt><input type="text" name="fleetadd" id="fleetadd-S5" /></dt>
						<dt><label for="fleetadd-S7">Kampfkapsel</label>&nbsp;<dt><input type="text" name="fleetadd" id="fleetadd-S7" /></dt>
						<dt><label for="fleetadd-S8">Leichter Jäger</label>&nbsp;<dt><input type="text" name="fleetadd" id="fleetadd-S8" /></dt>
						<dt><label for="fleetadd-S9">Schwerer Jäger</label>&nbsp;<dt><input type="text" name="fleetadd" id="fleetadd-S9" /></dt>
						<dt><label for="fleetadd-S10">Leichte Fregatte</label>&nbsp;<dt><input type="text" name="fleetadd" id="fleetadd-S10" /></dt>
						<dt><label for="fleetadd-S11">Schwere Fregatte</label>&nbsp;<dt><input type="text" name="fleetadd" id="fleetadd-S11" /></dt>
						<dt><label for="fleetadd-S12">Leichter Kreuzer</label>&nbsp;<dt><input type="text" name="fleetadd" id="fleetadd-S12" /></dt>
						<dt><label for="fleetadd-S13">Schwerer Kreuzer</label>&nbsp;<dt><input type="text" name="fleetadd" id="fleetadd-S13" /></dt>
						<dt><label for="fleetadd-S14">Schlachtschiff</label>&nbsp;<dt><input type="text" name="fleetadd" id="fleetadd-S14" /></dt>
						<dt><label for="fleetadd-S15">Zerstörer</label>&nbsp;<dt><input type="text" name="fleetadd" id="fleetadd-S15" /></dt>
						<dt><label for="fleetadd-S16">Warcube</label>&nbsp;<dt><input type="text" name="fleetadd" id="fleetadd-S16" /></dt>
					</dl>
				</td>
			
				<td>
					<dl>
						<dt><label for="fleetadd-V0">Einfaches Lasergeschütz</label>&nbsp;<dt><input type="text" name="fleetadd" id="fleetadd-V0" /></dt>
						<dt><label for="fleetadd-V1">Gatling</label>&nbsp;<dt><input type="text" name="fleetadd" id="fleetadd-V1" /></dt>
						<dt><label for="fleetadd-V2">Mehrfachraketenwerfer</label>&nbsp;<dt><input type="text" name="fleetadd" id="fleetadd-V2" /></dt>
						<dt><label for="fleetadd-V6">Schweres Lasergeschütz</label>&nbsp;<dt><input type="text" name="fleetadd" id="fleetadd-V6" /></dt>
						<dt><label for="fleetadd-V3">EMP</label>&nbsp;<dt><input type="text" name="fleetadd" id="fleetadd-V3" /></dt>
						<dt><label for="fleetadd-V4">Ionenkanone</label>&nbsp;<dt><input type="text" name="fleetadd" id="fleetadd-V4" /></dt>
						<dt><label for="fleetadd-V5">Radonkanone</label>&nbsp;<dt><input type="text" name="fleetadd" id="fleetadd-V5" /></dt>
					</dl>
				</td>	
			</tr>	
	</tbody>
	
	<tfoot>
			<tr><td style="text-align: center" colspan="20"><div><button type="submit">ersetzen</button></div></td></tr>
	</tfoot>
</table>	
</form>
<p><a href="#top">Zurück zum Menü</a></p></fieldset>

<?php
	}


if(isset($admin_array['permissions'][19]) && $admin_array['permissions'][19])
	{

?>
<fieldset><legend id="action-19">Geb&auml;ude/ Forschung ersetzten [+/-]</legend>
<form action="index.php" method="post">
<table cellpadding="2" cellspacing="2">
	<thead>
			<tr>
				<td>
					<dt><label for="username-input">Benutzername</label></dt>
					<dt><input type="text" name="username" id="username-input" /></dt>
					<script type="text/javascript">
					// Autocompletion
						activate_users_list(document.getElementById('username-input'));
					</script>
				</td>
				
				<td>
					<dt><label for="planet-input">Planetennummer</label></dt>
					<dt><input type="text" name="planetnr" id="planet-input" /></dt>
				</td>
			</tr>
	</thead>
	<tbody>
			<tr>
				<td>
					<dl>
						<dt><label for="gebadd-B0">Carbonfabrik</label>&nbsp;<dt><input type="text" name="gebadd" id="gebadd-B0" /></dt>
						<dt><label for="gebadd-B1">Aluminiumgießerei</label>&nbsp;<dt><input type="text" name="gebadd" id="gebadd-B1" /></dt>
						<dt><label for="gebadd-B2">Radiumgrube</label>&nbsp;<dt><input type="text" name="gebadd" id="gebadd-B2" /></dt>
						<dt><label for="gebadd-B3">Tritiumgenerator</label>&nbsp;<dt><input type="text" name="gebadd" id="gebadd-B3" /></dt>
						<dt><label for="gebadd-B5">Bolarkraftwerk</label>&nbsp;<dt><input type="text" name="gebadd" id="gebadd-B5" /></dt>
						<dt><label for="gebadd-B6">Bonnenwindkraftwerk</label>&nbsp;<dt><input type="text" name="gebadd" id="gebadd-B5" /></dt>
						<dt><label for="gebadd-B7">Wärmekraftwerk</label>&nbsp;<dt><input type="text" name="gebadd" id="gebadd-B7" /></dt>
						<dt><label for="gebadd-B8">Forschungslabor</label>&nbsp;<dt><input type="text" name="gebadd" id="gebadd-B8" /></dt>
						<dt><label for="gebadd-B9">Roboterfabrik</label>&nbsp;<dt><input type="text" name="gebadd" id="gebadd-B9" /></dt>
						<dt><label for="gebadd-B10">Werft</label>&nbsp;<dt><input type="text" name="gebadd" id="gebadd-B10" /></dt>
					</dl>
				</td>

				<td>
					<dl>
						<dt><label for="gebadd-F0">Kontrollwesen</label>&nbsp;<dt><input type="text" name="gebadd" id="gebadd-F0" /></dt>
						<dt><label for="gebadd-F1">Spionagetechnik</label>&nbsp;<dt><input type="text" name="gebadd" id="gebadd-F1" /></dt>
						<dt><label for="gebadd-F2">Roboterbautechnik</label>&nbsp;<dt><input type="text" name="gebadd" id="gebadd-F2" /></dt>
						<dt><label for="gebadd-F3">Energietechnik</label>&nbsp;<dt><input type="text" name="gebadd" id="gebadd-F3" /></dt>
						<dt><label for="gebadd-F4">Waffentechnik</label>&nbsp;<dt><input type="text" name="gebadd" id="gebadd-F4" /></dt>
						<dt><label for="gebadd-F5">Verteidigungsstrategie</label>&nbsp;<dt><input type="text" name="gebadd" id="gebadd-F5" /></dt>
						<dt><label for="gebadd-F10">Schildtechnik</label>&nbsp;<dt><input type="text" name="gebadd" id="gebadd-F10" /></dt>
						<dt><label for="gebadd-F6">Rückstoßantrieb</label>&nbsp;<dt><input type="text" name="gebadd" id="gebadd-F6" /></dt>
						<dt><label for="gebadd-F7">Ionenantrieb</label>&nbsp;<dt><input type="text" name="gebadd" id="gebadd-F7" /></dt>
						<dt><label for="gebadd-F8">Kernantrieb</label>&nbsp;<dt><input type="text" name="gebadd" id="gebadd-F8" /></dt>
						<dt><label for="gebadd-F9">Ingenieurswissenschaft</label>&nbsp;<dt><input type="text" name="gebadd" id="gebadd-F9" /></dt>
						<dt><label for="gebadd-F11">Laderaumerweiterung</label>&nbsp;<dt><input type="text" name="gebadd" id="gebadd-F11" /></dt>
					</dl>
				</td>
			</tr>	
	</tbody>
	
	<tfoot>
			<tr><td style="text-align: center" colspan="20"><div><button type="submit">ersetzen</button></div></td></tr>
	</tfoot>
</table>	
</form>
<p><a href="#top">Zurück zum Menü</a></p></fieldset>

<?php
	}
	

	admin_gui::html_foot();
?>
</fieldset>
