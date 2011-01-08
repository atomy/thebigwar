<?php
if(!isset($_SERVER['DOCUMENT_ROOT']) || strlen($_SERVER['DOCUMENT_ROOT']) <= 0)
    $_SERVER['DOCUMENT_ROOT'] = getcwd();
    
require_once($_SERVER['DOCUMENT_ROOT'].'/include/config_inc.php');
require($_SERVER['DOCUMENT_ROOT'].'/include/TicketHelper.php');
require($_SERVER['DOCUMENT_ROOT'].'/admin/include.php');

// load class user
__autoload( 'User' );

/**
 * check for access to that page
 * @extern $adminObj
 */
if ( ! isset( $adminObj ) )
{
    die( 'No access.' );
}

/*
 * Als Geist als ein Benutzer anmelden
 */
if ( $adminObj->can( ADMIN_GHOSTMODE ) && isset( $_POST['ghost_username'] ) && User::userExists( trim( $_POST['ghost_username'] ) ) )
{
    $_SESSION['username'] = trim( $_POST['ghost_username'] );
    $_SESSION['ghost'] = true;
    $_SESSION['resume'] = true;
    
    protocol( "1", $_SESSION['username'] );
    
    $url = 'http://' . $_SERVER['HTTP_HOST'] . h_root . '/login/index.php?' . urlencode( session_name() ) . '=' . urlencode( session_id() );
    header( 'Location: ' . $url, true, 303 );
    die( 'HTTP redirect: <a href="' . htmlentities( $url ) . '">' . htmlentities( $url ) . '</a>' );
}

/*
 * Passwort aendern
 */
if ( $adminObj->can( ADMIN_SETUSERSPASS ) && isset( $_POST['passwd_username'] ) && isset( $_POST['passwd_password'] ) && User::userExists( trim( $_POST['passwd_username'] ) ) )
{
    $_POST['passwd_username'] = trim( $_POST['passwd_username'] );
    
    $that_user = Classes::User( $_POST['passwd_username'] );
    $that_user->setPassword( $_POST['passwd_password'] ) && protocol( "2", $_POST['passwd_username'] );
    unset( $that_user );
}

/*
 * Flottenhänger korrigieren
 */
if ( $adminObj->can( ADMIN_FIXSTUCKFLEET ) && isset( $_POST['flug_username'] ) && User::userExists( trim( $_POST['flug_username'] ) ) )
{
    $eventfile = Classes::EventFile();
    #Betroffenen User eintragen
    $user_obj = new User( $_POST['flug_username'] );
    #Flotten holen
    $flotten = $user_obj->getFleetsList();
    foreach ( $flotten as $flotte )
    {
        $fl = Classes::Fleet( $flotte );
        $time = $fl->getArrivalTime();
        if ( $time < time() )
        {
            echo "FleetID:" . "$flotte" . "wurde korrigiert.\n";
            $fleet = new Fleet( $flotte );
            $eventfile->removeFleet( $flotte );
            $eventfile->addNewFleet( $fleet->getNextArrival(), $flotte );
            unset( $fleet );
        }
    }
}

/*
 * Benutzer loeschen
 */
if ( $adminObj->can( ADMIN_DELETEUSERS ) && isset( $_POST['delete_username'] ) && User::userExists( trim( $_POST['delete_username'] ) ) )
{
    $_POST['delete_username'] = trim( $_POST['delete_username'] );
    
    # entferne user
    user_control::removeUser( $_POST['delete_username'] ) && protocol( "4", $_POST['delete_username'] );
}

/*
 * Benutzer sperren / entsperren
 */
if ( $adminObj->can( ADMIN_DISABLEUSERS ) && isset( $_POST['lock_username'] ) && User::userExists( trim( $_POST['lock_username'] ) ) )
{
    
    $_POST['lock_username'] = trim( $_POST['lock_username'] );
    
    $lock_time = false;
    if ( isset( $_POST['user_lock_period'] ) && isset( $_POST['user_lock_period_unit'] ) )
    {
        $_POST['user_lock_period'] = trim( $_POST['user_lock_period'] );
        $_POST['user_lock_period_unit'] = trim( $_POST['user_lock_period_unit'] );
        if ( $_POST['user_lock_period'] )
        {
            switch ( $_POST['user_lock_period_unit'] )
            {
                case 'min':
                    $lock_time = time() + $_POST['user_lock_period'] * 60;
                    break;
                case 'h':
                    $lock_time = time() + $_POST['user_lock_period'] * 3600;
                    break;
                case 'd':
                    $lock_time = time() + $_POST['user_lock_period'] * 86400;
                    break;
            }
        }
    }
    
    $that_user = Classes::User( $_POST['lock_username'] );
    $that_user->lockUser( $lock_time ) && protocol( ( $that_user->userLocked() ? '5.1' : '5.2' ), $_POST['lock_username'] );
    unset( $that_user );
}

/*
 * Benutzer umbenennen
 */
if ( $adminObj->can( ADMIN_RENAMEUSERS ) && isset( $_POST['rename_old'] ) && isset( $_POST['rename_new'] ) && User::userExists( trim( $_POST['rename_old'] ) ) )
{
    $_POST['rename_old'] = trim( $_POST['rename_old'] );
    $_POST['rename_new'] = substr( trim( $_POST['rename_new'] ), 0, 20 );
    
    $that_user = Classes::User( $_POST['rename_old'] );
    $ret = $that_user->rename( $_POST['rename_new'] ) && protocol( "6", $_POST['rename_old'], $_POST['rename_new'] );
    
    if ( $ret )
        TicketHelper::userRenamed($_POST['rename_old'], $_POST['rename_new']);
}

/*
 * Anfängerschutz
 */
if ( $adminObj->can( ADMIN_SETNOOBPROTECT ) && isset( $_POST['noob'] ) )
{
    if ( $_POST['noob'] && ! file_exists( global_setting( "DB_NONOOBS" ) ) )
        touch( global_setting( "DB_NONOOBS" ) ) && protocol( "7.1" );
    elseif ( ! $_POST['noob'] && file_exists( global_setting( "DB_NONOOBS" ) ) )
        unlink( global_setting( "DB_NONOOBS" ) ) && protocol( "7.2" );
}

/*
 * Nachricht versenden
 */
if ( $adminObj->can( ADMIN_SENDMESSAGES ) && isset( $_POST['message_text'] ) && trim( $_POST['message_text'] ) != '' )
{
    $message = new Message();
    if ( $message->create() )
    {
        if ( isset( $_POST['message_from'] ) )
        {
            $message->from( $_POST['message_from'] );
        }
        
        if ( isset( $_POST['message_subject'] ) )
        {                    
            $message->subject( $_POST['message_subject'] );
        }
        
        $message->text( $_POST['message_text'] );
        
        if ( isset( $_POST['message_html'] ) && $_POST['message_html'] )
        {
            $message->html( true );
        }
        
        $to = '';
        
        if ( isset( $_POST['message_to'] ) )
        {
            $to = trim( $_POST['message_to'] );
        }
        if ( ! $to )
        {
            # An alle Benutzer versenden            

            $dh = opendir( global_setting( "DB_PLAYERS" ) );
            
            while ( ( $uname = readdir( $dh ) ) !== false )
            {
                $message->addUser( urldecode( $uname ), 6 );
            }
            
            echo $uname;            
            closedir( $dh );
        
        }
        else
        {
            $to = explode( "\r\n", $to );
            
            foreach ( $to as $t )
            {
                $message->addUser( urldecode( $t ), 6 );
            }
                
        }
        protocol( "9", $_POST['message_subject'], str_replace( "\r\n", ", ", $_POST['message_to'] ) );
        unset( $message );
    }
}

/*
 * Wartungsarbeiten
 */
if ( $adminObj->can( ADMIN_SETMAINTENANCE ) && isset( $_POST['wartungsarbeiten'] ) )
{
    if ( $_POST['wartungsarbeiten'] && ! is_file( '../.htaccess.wartungsarbeiten.sav' ) )
    {
        if ( ! file_exists( '../.htaccess' ) )
            touch( '../.htaccess' );
        if ( rename( '../.htaccess', '../.htaccess.wartungsarbeiten.sav' ) )
        {
            ( $fh = fopen( '../.htaccess', 'w' ) ) && ( protocol( "12.1" ) );
            flock( $fh, LOCK_EX );
            
            fwrite( $fh, "Order Deny,Allow\n" );
            fwrite( $fh, "Deny from All\n" );
            fwrite( $fh, "ErrorDocument 403 /wartungsarbeiten.html\n" );
            fwrite( $fh, "<Files \"wartungsarbeiten.html\">\n" );
            fwrite( $fh, "\tDeny from None\n" );
            fwrite( $fh, "</Files>\n" );
            
            flock( $fh, LOCK_UN );
            fclose( $fh );
        }
    }
    elseif ( ! $_POST['wartungsarbeiten'] && is_file( '../.htaccess.wartungsarbeiten.sav' ) )
    {
        if ( is_file( '../.htaccess' ) )
            unlink( '../.htaccess' );
        rename( '../.htaccess.wartungsarbeiten.sav', '../.htaccess' ) && protocol( "12.2" );
    }
}

/*
 * Spiel sperren
 */
if ( $adminObj->can( ADMIN_DISABLEGAME ) && isset( $_POST['lock'] ) )
{
    if ( $_POST['lock'] && ! file_exists( global_setting( "DB_LOCKED" ) ) )
    {
        # Bei allen Benutzern den Eventhandler ausfuehren
        $dh = opendir( global_setting( "DB_PLAYERS" ) );
        while ( ( $player = readdir( $dh ) ) !== false )
        {
            if ( ! is_file( global_setting( "DB_PLAYERS" ) . '/' . $player ) || ! is_readable( global_setting( "DB_PLAYERS" ) . '/' . $player ) )
                continue;
            $this_user = Classes::User( urldecode( $player ) );
            $this_user->eventhandler( 0, 1, 1, 1, 1, 1 );
            unset( $this_user );
        }
        closedir( $dh );
        
        ( $fh = fopen( global_setting( "DB_LOCKED" ), "w" ) ) and protocol( "13.1" );
        if ( $fh )
        {
            if ( isset( $_POST['lock_period'] ) && isset( $_POST['lock_period_unit'] ) )
            {
                $lock_time = false;
                $_POST['lock_period'] = trim( $_POST['lock_period'] );
                $_POST['lock_period_unit'] = trim( $_POST['lock_period_unit'] );
                if ( $_POST['lock_period'] )
                {
                    switch ( $_POST['lock_period_unit'] )
                    {
                        case 'min':
                            $lock_time = time() + $_POST['lock_period'] * 60;
                            break;
                        case 'h':
                            $lock_time = time() + $_POST['lock_period'] * 3600;
                            break;
                        case 'd':
                            $lock_time = time() + $_POST['lock_period'] * 86400;
                            break;
                    }
                }
                if ( $lock_time )
                    fwrite( $fh, $lock_time );
            }
            fclose( $fh );
        }
    }
    elseif ( ! $_POST['lock'] && database_locked() )
    {        
        # Bei allen Benutzern den Eventhandler ausfuehren
        $dh = opendir( global_setting( "DB_PLAYERS" ) );
        while ( ( $player = readdir( $dh ) ) !== false )
        {
            if ( ! is_file( global_setting( "DB_PLAYERS" ) . '/' . $player ) || ! is_readable( global_setting( "DB_PLAYERS" ) . '/' . $player ) )
                continue;
            $this_user = Classes::User( urldecode( $player ) );
            $this_user->eventhandler( 0, 1, 1, 1, 1, 1 );
            unset( $this_user );
        }
        closedir( $dh );
        
        unlink( global_setting( "DB_LOCKED" ) ) && protocol( "13.2" );
    }
}

/*
 * Flottensperre
 */
if ( $adminObj->can( ADMIN_DISABLEFLEETS ) && isset( $_POST['flock'] ) )
{
    if ( $_POST['flock'] && ! fleets_locked() )
    {
        ( $fh = fopen( global_setting( "DB_NO_ATTS" ), "w" ) ) and protocol( "15.1" );
        if ( $fh )
        {
            if ( isset( $_POST['flock_period'] ) && isset( $_POST['flock_period_unit'] ) )
            {
                $lock_time = false;
                $_POST['flock_period'] = trim( $_POST['flock_period'] );
                $_POST['flock_period_unit'] = trim( $_POST['flock_period_unit'] );
                if ( $_POST['flock_period'] )
                {
                    switch ( $_POST['flock_period_unit'] )
                    {
                        case 'min':
                            $lock_time = time() + $_POST['flock_period'] * 60;
                            break;
                        case 'h':
                            $lock_time = time() + $_POST['flock_period'] * 3600;
                            break;
                        case 'd':
                            $lock_time = time() + $_POST['flock_period'] * 86400;
                            break;
                    }
                }
                if ( $lock_time )
                    fwrite( $fh, $lock_time );
            }
            fclose( $fh );
        }
    }
    elseif ( ! $_POST['flock'] && fleets_locked() )
    {
        unlink( global_setting( "DB_NO_ATTS" ) ) && protocol( "15.2" );
    }
}

admin_gui::html_head();
?>
<strong>
<h3>Willkommen im TBW-Adminbereich!</h3>
1.) Benutze niemals eine Funktionen aus dem Adminbereich zu Deinem
eigenen Vorteil.<br>
2.) Gebe keine Informationen an Personen weiter, die sich diese
Informationen nicht selbst beschaffen könnten.<br>
3.) Zur Sicherheit und um Streitfälle klären zu können, wird jede Aktion
der GO's im Adminbereich geloggt.<br>
<br>
</strong>

<table
	style="text-align: left; margin-left: auto; margin-right: auto; width: 825px; height: 600px;"
	border="1" cellpadding="0" cellspacing="0">
	<tbody>
		<tr>
			<td colspan="1" rowspan="6" align="center" nowrap="nowrap"
				valign="middle"><img src="../downloads/banner/160x600.jpg"
				alt="tbw-banner"></img></td>

			<td style="width: 200px;" align="center" nowrap="nowrap"
				valign="middle">
				<a href="<?php echo $RELPATH?>index.php#passwort-aendern">
				Adminpasswort<br/>ändern</a>
			</td>
			<td style="width: 200px;" align="center" nowrap="nowrap"
				valign="middle">
			<?php
if ( $adminObj->can( ADMIN_LISTUSERS ) )
{
    ?>
    <a href="<?php echo $RELPATH?>index.php#action-0">Benutzerliste<br>einsehen</a>
	<?php
}
else
{
    ?><s>Benutzerliste<br>
			einsehen</s><?php
}
?>
	  </td>
			<td style="width: 200px;" align="center" nowrap="nowrap"
				valign="middle">
			<?php
if ( $adminObj->can( ADMIN_GHOSTMODE ) )
{
    ?><a
				href="<?php
    echo $RELPATH;
    ?>index.php#action-1">Als Geistbenutzer<br>
			anmelden</a>
			<?php
}
else
{
    ?><s>Als Geistbenutzer<br>
			anmelden</s><?php
}
?>
      </td>
			<td style="width: 200px;" align="center" nowrap="nowrap"
				valign="middle">
			<?php
if ( $adminObj->can( ADMIN_SETUSERSPASS ) )
{
    ?><a
				href="<?php
    echo $RELPATH;
    ?>index.php#action-2">Das Passwort eines<br>
			Benutzers ändern</a>
			<?php
}
else
{
    ?><s>Das Passwort eines<br>
			Beutzers ändern</s><?php
}
?>
      </td>
		</tr>
		<tr>
			<td style="width: 200px;" align="center" nowrap="nowrap"
				valign="middle">
			<?php
if ( $adminObj->can( ADMIN_FIXSTUCKFLEET ) )
{
    ?><a
				href="<?php
    echo $RELPATH;
    ?>index.php#action-4">Flottenhänger beim<br>
			Benutzer korrigieren</a>
			<?php
}
else
{
    ?><s>Flottenhänger beim<br>
			Benutzer korrigieren</s><?php
}
?>
	  </td>
			<td style="width: 200px;" align="center" nowrap="nowrap"
				valign="middle">
			<?php
if ( $adminObj->can( ADMIN_DELETEUSERS ) )
{
    ?><a
				href="<?php
    echo $RELPATH;
    ?>index.php#action-5">Einen Benutzer<br>
			löschen</a>
			<?php
}
else
{
    ?><s>Einen Benutzer<br>
			löschen</s><?php
}
?>
	  </td>
			<td style="width: 200px;" align="center" nowrap="nowrap"
				valign="middle">
            <?php
            if ( $adminObj->can( ADMIN_EDITITEMS ) )
            {
                ?><a
				href="<?php
                echo $RELPATH;
                ?>index.php#action-18">Items<br>
			ersetzen</a>
			<?php
            }
            else
            {
                ?>Items<br>ersetzen<?php
            }
            ?>
      </td>
			<td style="width: 200px;" align="center" nowrap="nowrap"
				valign="middle">
            <?php
            if ( $adminObj->can( ADMIN_TICKETSYSTEM ) )
            {
                ?><a
				href="<?php
                echo $RELPATH;
                ?>index.php#action-19">Ticketsystem</a>
			<?php
            }
            else
            {
                ?>Ticketsystem<?php
            }
            ?>
      </td>	  
		</tr>
		<tr>
			<td style="width: 200px;" align="center" nowrap="nowrap"
				valign="middle">
            <?php
            if ( $adminObj->can( ADMIN_DISABLEUSERS ) )
            {
                ?><a
				href="<?php
                echo $RELPATH;
                ?>index.php#action-6">Einen Benutzer<br>
			sperren/entsperren</a>
			<?php
            }
            else
            {
                ?><s>Einen Benutzer<br>
			sperren/entsperren</s><?php
            }
            ?>
      </td>
			<td style="width: 200px;" align="center" nowrap="nowrap"
				valign="middle">
			<?php
if ( $adminObj->can( ADMIN_RENAMEUSERS ) )
{
    ?><a
				href="<?php
    echo $RELPATH;
    ?>index.php#action-7">Einen Benutzer<br>
			umbenennen</a>
			<?php
}
else
{
    ?><s>Einen Benutzer<br>
			umbenennen</s><?php
}
?>
	  </td>
			<td style="width: 200px;" align="center" nowrap="nowrap"
				valign="middle">
			<?php
if ( $adminObj->can( ADMIN_SETNOOBPROTECT ) )
{
    ?><a
				href="<?php
    echo $RELPATH;
    ?>index.php#action-8">Anfängerschutz<br>
			ein-/ausschalten</a>
			<?php
}
else
{
    ?><s>Anfängerschutz<br>
			ein-/ausschalten</s><?php
}
?>
	  </td>
			<td style="width: 200px;" align="center" nowrap="nowrap"
				valign="middle">
            <?php
            if ( $adminObj->can( ADMIN_DISABLEFLEETS ) )
            {
                ?><a
				href="<?php
                echo $RELPATH;
                ?>index.php#action-16">Flottensperre<br>
			aktivieren</a>
			<?php
            }
            else
            {
                ?>Flottensperre<br>aktivieren<?php
            }
            ?>
      </td>	  
		</tr>
		<tr>
			<td style="width: 200px;" align="center" nowrap="nowrap"
				valign="middle">
			<?php
if ( $adminObj->can( ADMIN_EDITPRANGER ) )
{
    ?><a
				href="<?php
    echo $RELPATH;
    ?>index.php#action-10">Pranger<br>
			bearbeiten</a>
			<?php
}
else
{
    ?><s>Pranger<br>
			bearbeiten</s><?php
}
?>
	  </td>
			<td style="width: 200px;" align="center" nowrap="nowrap"
				valign="middle">
            <?php
            if ( $adminObj->can( ADMIN_SENDMESSAGES ) )
            {
                ?><a
				href="<?php
                echo $RELPATH;
                ?>index.php#action-11">Nachricht<br>
			versenden</a>
			<?php
            }
            else
            {
                ?><s>Nachricht<br>
			versenden</s><?php
            }
            ?>
      </td>
			<td style="width: 200px;" align="center" nowrap="nowrap"
				valign="middle">
            <?php
            if ( $adminObj->can( ADMIN_VIEWLOGS ) )
            {
                ?><a
				href="<?php
                echo $RELPATH;
                ?>index.php#action-12"><span
				xml:lang="en">Admin</span>-<span xml:lang="en">Log</span>dateien<br>
			einsehen</a>
			<?php
            }
            else
            {
                ?><s>Admin</span>-<span xml:lang="en">Log</span>dateien<br>
			einsehen</s><?php
            }
            ?>
      </td>
			<td style="width: 200px;" align="center" nowrap="nowrap"
				valign="middle">
             <?php
            if ( $adminObj->can( ADMIN_MANAGEADMINS ) )
            {
                ?><a
				href="<?php
                echo $RELPATH;
                ?>index.php#action-13">Benutzer-<br>
			verwaltung</a>
			 <?php
            }
            else
            {
                ?><s>Benutzer-<br>
			verwaltung</s><?php
            }
            ?>
      </td>
		</tr>
		<tr>
			<td style="width: 200px;" align="center" nowrap="nowrap"
				valign="middle">
			<?php
if ( $adminObj->can( ADMIN_SETMAINTENANCE ) )
{
    ?><a
				href="<?php
    echo $RELPATH;
    ?>index.php#action-14">Wartungsarbeiten<br>
			aktivieren</a>
			<?php
}
else
{
    ?><s>Wartungsarbeiten<br>
			aktivieren</s><?php
}
?>
      </td>
			<td style="width: 200px;" align="center" nowrap="nowrap"
				valign="middle">
			<?php
if ( $adminObj->can( ADMIN_DISABLEGAME ) )
{
    ?><a
				href="<?php
    echo $RELPATH;
    ?>index.php#action-15">Spiel<br>
			sperren/entsperren</a>
			<?php
}
else
{
    ?>Spiel<br>sperren/entsperren<?php
}
?>
      </td>
			<td style="width: 200px;" align="center" nowrap="nowrap"
				valign="middle"></td>
			<td style="width: 200px;" align="center" nowrap="nowrap"
				valign="middle"></td>
								
		</tr>


	</tbody>
</table>
<br>

<fieldset><legend id="passwort-aendern">Adminpasswort ändern
</h2>
</legend>
<?php
if ( isset( $_POST['old_password'] ) && isset( $_POST['new_password'] ) && isset( $_POST['new_password2'] ) )
{
    if ( md5( $_POST['old_password'] ) != $admin_array['password'] )
    {
        ?>
<p class="error"><strong>Sie haben das falsche alte Passwort eingegeben.</strong></p>
<?php
    }
    elseif ( $_POST['new_password'] != $_POST['new_password2'] )
    {
        ?>
<p class="error"><strong>Die beiden neuen Passwörter stimmen nicht
überein.</strong></p>
<?php
    }
    else
    {
        $admin_array['password'] = md5( $_POST['new_password'] );
        write_admin_list( $admins );
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
	<dd><input type="password" name="new_password2"
		id="new-password2-input" /></dd>
</dl>
<div>
<button type="submit">Passwort ändern</button>
</div>
</form>
<p><a href="#top">Zurück zum Menü</a></p>
</fieldset>

<?php
if ( $adminObj->can( ADMIN_LISTUSERS ) )
{
    ?>
<fieldset><legend id="action-0">Benutzerliste einsehen</legend>
<form action="userlist.php" method="get">
<ul>
	<li>
	<button type="submit">Unsortiert</button>
	</li>
	<li>
	<button type="submit" name="sort" value="1">Sortiert</button>
	</li>
</ul>
</form>
<p><a href="#top">Zurück zum Menü</a></p>
</fieldset>

<?php
}

if ( $adminObj->can( ADMIN_GHOSTMODE ) )
{
    ?>
<fieldset><legend id="action-1">Als Geist als ein Benutzer anmelden</legend>
<form action="index.php" method="post">
<dl>
	<dt><label for="ghost-input">Benutzername</label></dt>
	<dd><input type="text" name="ghost_username" id="ghost-input" /></dd>
</dl>
<div>
<button type="submit">Anmelden</button>
</div>
</form>
<p><a href="#top">Zurück zum Menü</a></p>
</fieldset>
<script type="text/javascript">
	// Autocompletion
	activate_users_list(document.getElementById('ghost-input'));
</script>
<?php
}

if ( $adminObj->can( ADMIN_SETUSERSPASS ) )
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
<div>
<button type="submit">Passwort ändern</button>
</div>
</form>
<p><a href="#top">Zurück zum Menü</a></p>
</fieldset>
<script type="text/javascript">
	// Autocompletion
	activate_users_list(document.getElementById('passwd-name-input'));
</script>
<?php
}

if ( $adminObj->can( ADMIN_FIXSTUCKFLEET ) )
{
    ?>
<fieldset><legend id="action-4">Flottenhänger beim Benutzer korrigieren</legend>
<form action="index.php" method="post">
<dl>
	<dt><label for="flug-username-input">Benutzername</label></dt>
	<dd><input type="text" name="flug_username" id="flug-username-input" /></dd>
</dl>
<div>
<button type="submit">Flug neu berechnen</button>
</div>
</form>
<p><a href="#top">Zurück zum Menü</a></p>
</fieldset>
<script type="text/javascript">
    // Autocompletion
    activate_users_list(document.getElementById('flug-username-input'));
</script>
<?php
}

if ( $adminObj->can( ADMIN_DELETEUSERS ) )
{
    ?>
<fieldset><legend id="action-5">Einen Benutzer löschen</legend>
<p><strong>Bitte nicht wegen Regelverstoßes durchführen (dann Benutzer
sperren), nur bei fehlerhaften Registrierungen oder Ähnlichem.</strong></p>
<form action="index.php" method="post">
<dl>
	<dt><label for="delete-input">Benutzername</label></dt>
	<dd><input type="text" name="delete_username" id="delete-input" /></dd>
</dl>
<div>
<button type="submit">Löschen</button>
</div>
</form>
<p><a href="#top">Zurück zum Menü</a></p>
</fieldset>
<script type="text/javascript">
	// Autocompletion
	activate_users_list(document.getElementById('delete-input'));
</script>
<?php
}

if ( $adminObj->can( ADMIN_DISABLEUSERS ) )
{
    ?>
<fieldset><legend id="action-6">Einen Benutzer sperren / entsperren</legend>
<form action="index.php" method="post">
<dl>
	<dt><label for="user-lock-input">Benutzername</label></dt>
	<dd><input type="text" name="lock_username" id="user-lock-input" /></dd>

	<dt><label for="user-lock-period-input">Dauer der Sperre</label></dt>
	<dd><input type="text" name="user_lock_period"
		id="user-lock-period-input"> <select name="user_lock_period_unit">
		<option value="min">Minuten</option>
		<option value="h">Stunden</option>
		<option value="d">Tage</option>
	</select></dd>
</dl>
<div>
<button type="submit">Sperren / Entsperren</button>
</div>
</form>
<p><a href="#top">Zurück zum Menü</a></p>
</fieldset>
<script type="text/javascript">
        // Autocompletion
        activate_users_list(document.getElementById('user-lock-input'));
</script>
<?php
}

if ( $adminObj->can( ADMIN_RENAMEUSERS ) )
{
    ?>
<fieldset><legend id="action-7">Einen Benutzer umbenennen (max. einmal
in 7 Tagen!)</legend>
<form action="index.php" method="post">
<dl>
	<dt><label for="rename-from">Alter Name</label></dt>
	<dd><input type="text" name="rename_old" id="rename-from" /></dd>

	<dt><label for="rename-to">Neuer Name</label></dt>
	<dd><input type="text" name="rename_new" id="rename-to" /></dd>
</dl>
<div>
<button type="submit">Umbenennen</button>
</div>
</form>
<p><a href="#top">Zurück zum Menü</a></p>
</fieldset>
<script type="text/javascript">
	// Autocompletion
	activate_users_list(document.getElementById('rename-from'));
</script>
<?php
}

if ( $adminObj->can( ADMIN_SETNOOBPROTECT ) )
{
    ?>
<fieldset><legend id="action-8">Anfängerschutz ein-/ausschalten</legend>
<?php
    if ( file_exists( global_setting( "DB_NONOOBS" ) ) )
    {
        ?>
<form action="index.php" method="post">
<div><input type="hidden" name="noob" value="0" />
<button type="submit">Anfängerschutz einschalten</button>
</div>
</form>
<?php
    }
    else
    {
        ?>
<form action="index.php" method="post">
<div><input type="hidden" name="noob" value="1" />
<button type="submit">Anfängerschutz ausschalten</button>
</div>
</form>
<p><a href="#top">Zurück zum Menü</a></p>
</fieldset>
<?php
    }
}

if ( $adminObj->can( ADMIN_EDITPRANGER ) )
{
    ?>
<fieldset><legend id="action-10">Pranger bearbeiten</legend>
<h2><a href="edit_pranger.php"><span xml:lang="en">Pranger</span>
bearbeiten</a></h2>
<p><a href="#top">Zurück zum Menü</a></p>
</fieldset>
<?php
}

if ( $adminObj->can( ADMIN_SENDMESSAGES ) )
{
    ?>
<fieldset><legend id="action-11">Nachricht versenden</legend>
<form action="index.php" method="post">
<dl>
	<dt><label for="message-absender-input">Absender</label></dt>
	<dd><input type="text" name="message_from" id="message-absender-input" /></dd>

	<dt><label for="message-empfaenger-textarea">Empfänger</label></dt>
	<dd><textarea cols="20" rows="4" name="message_to"
		id="message-empfaenger-textarea"></textarea> Bleibt dieses Feld leer,
	wird an alle Benutzer verschickt.</dd>

	<dt><label for="message-betreff-input">Betreff</label></dt>
	<dd><input type="text" name="message_subject"
		id="message-betreff-input" /></dd>

	<dt><label for="message-html-checkbox"><abbr
		title="Hypertext Markup Language" xml:lang="en"><span xml:lang="de">HTML</span></abbr>?</label></dt>
	<dd><input type="checkbox" name="message_html"
		id="message-html-checkbox" /></dd>

	<dt><label for="message-text-textarea">Text</label></dt>
	<dd><textarea cols="50" rows="10" name="message_text"
		id="message-text-textarea"></textarea></dd>
</dl>
<div>
<button type="submit">Absenden</button>
</div>
</form>
<p><a href="#top">Zurück zum Menü</a></p>
</fieldset>
<?php
}

if ( $adminObj->can( ADMIN_VIEWLOGS ) )
{
    ?>
<fieldset><legend id="action-12">Admin-Logdateien einsehen</legend>
<h2><a href="logs.php"><span xml:lang="en">Admin</span>-<span
	xml:lang="en">Log</span>dateien einsehen</a></h2>
<p><a href="#top">Zurück zum Menü</a></p>
</fieldset>
<?php
}

if ( $adminObj->can( ADMIN_MANAGEADMINS ) )
{
    ?>
<fieldset><legend id="action-13">Benutzerverwaltung</legend>
<ul>
	<li><a href="usermanagement.php?action=edit">Bestehende Benutzer
	bearbeiten</a></li>
	<li><a href="usermanagement.php?action=add">Neuen Benutzer anlegen</a></li>
</ul>
<p><a href="#top">Zurück zum Menü</a></p>
</fieldset>
<?php
}

if ( $adminObj->can( ADMIN_SETMAINTENANCE ) )
{
    ?>
<fieldset><legend id="action-14">Wartungsarbeiten</legend>
<?php
    if ( is_file( '../.htaccess.wartungsarbeiten.sav' ) )
    {
        ?>
<form action="index.php" method="post">
<div><input type="hidden" name="wartungsarbeiten" value="0" />
<button type="submit">Wartungsarbeiten deaktivieren</button>
</div>
</form>
<p><a href="#top">Zurück zum Menü</a></p>
</fieldset>
<?php
    }
    else
    {
        ?>
<form action="index.php" method="post">
<div><input type="hidden" name="wartungsarbeiten" value="1" />
<button type="submit">Wartungsarbeiten aktivieren</button>
</div>
</form>
<p><a href="#top">Zurück zum Menü</a></p>
</fieldset>
<?php
    }
}

if ( $adminObj->can( ADMIN_DISABLEGAME ) )
{
    if ( database_locked() )
    {
        ?>
<fieldset><legend id="action-15">Spiel entsperren</legend>
<form action="index.php" method="post">
<div><input type="hidden" name="lock" value="0" />
<button type="submit">Entsperren</button>
</div>
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
	<dd><input type="text" name="lock_period" id="lock-period-input"> <select
		name="lock_period_unit">
		<option value="min">Minuten</option>
		<option value="h">Stunden</option>
		<option value="d">Tage</option>
	</select></dd>
</dl>
<div><input type="hidden" name="lock" value="1" />
<button type="submit">Sperren</button>
</div>
</form>
<p><a href="#top">Zurück zum Menü</a></p>
</fieldset>
<?php
    }
}

if ( $adminObj->can( ADMIN_DISABLEFLEETS ) )
{
    if ( fleets_locked() )
    {
        ?>
<fieldset><legend id="action-16">Flottensperre</legend>
<form action="index.php" method="post">
<div><input type="hidden" name="flock" value="0" />
<button type="submit">Aufheben</button>
</div>
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
	<dd><input type="text" name="flock_period" id="lock-period-input"> <select
		name="flock_period_unit">
		<option value="min">Minuten</option>
		<option value="h">Stunden</option>
		<option value="d">Tage</option>
	</select></dd>
</dl>
<div><input type="hidden" name="flock" value="1" />
<button type="submit">Setzen</button>
</div>
</form>
<p><a href="#top">Zurück zum Menü</a></p>
</fieldset>


<?php
    }
}

if ( $adminObj->can( ADMIN_EDITITEMS ) )
{
    
    ?>

<fieldset><legend id="action-18">Items ersetzten [+/-]</legend>
<h2><a href="<?php
    echo h_root;
    ?>/admin/changeItems.php">Items ersetzen</a></h2>
<p><a href="#top">Zurück zum Menü</a></p>
</fieldset>

<?php
}

if ( $adminObj->can( ADMIN_TICKETSYSTEM ) )
{
    
    ?>
 
<fieldset><legend id="action-19">Ticketsystem</legend>
<h2><a href="<?php
    echo h_root;
    ?>/admin/ticketsystem.php">Ticketsystem</a></h2>
<p><a href="#top">Zurück zum Menü</a></p>
</fieldset>

<?php
}

admin_gui::html_foot();
?>
</fieldset>
