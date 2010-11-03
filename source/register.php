<?php
	require_once( 'include/config_inc.php' );
	require( TBW_ROOT.'include.php' );
	include_once( TBW_ROOT.'include/php2egg.php' );
	
    startseite_html_head();
?>

<h1 id="register">Registrierung</h1>
<?php
	$databases = get_databases();

	$_POST['database'] = key($databases);

	if(isset($_POST['username']) && isset($_POST['password']) && isset($_POST['password2']) &&	isset($_POST['email']) && isset($_POST['database']) && isset($databases[$_POST['database']]))
	{
		define_globals($_POST['database']);

		$error = '';
	
	$keyarray = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', ' ');
	#Username Zeichen pruefen
	$stringusername = $_POST['username'];
	$noblockuser = true;
	for($i=0;$i<strlen($stringusername);$i++)
	{
		$explode[$i] = substr($stringusername, $i, 1);
		if(!in_array($explode[$i],$keyarray)) $noblockuser = false;
	}
	#Passwort Zeichen pruefen
	$stringpassword = $_POST['password'];
	$noblockpassword = true;
	for($i=0;$i<strlen($stringpassword);$i++)
	{
		$explode[$i] = substr($stringpassword, $i, 1);
		if(!in_array($explode[$i],$keyarray)) $noblockpassword = false;
	}
	#Name Hauptplanet Zeichen pruefen
	$stringhomeplanet = $_POST['hauptplanet'];
	$noblockhomeplanet = true;
	for($i=0;$i<strlen($stringhomeplanet);$i++)
	{
		$explode[$i] = substr($stringhomeplanet, $i, 1);
		if(!in_array($explode[$i],$keyarray)) $noblockhomeplanet = false;
	}
		if($noblockuser == false)
			$error = 'Der Benutzername enthält ungültige Zeichen.';
		elseif($noblockpassword == false)
			$error = 'Das Passwort enthält ungültige Zeichen.';
		elseif($noblockhomeplanet == false)
			$error = 'Der Name des Hauptplaneten enthält ungültige Zeichen.';
		elseif(!isset($_POST['nutzungsbedingungen']) || !$_POST['nutzungsbedingungen'])
			$error = 'Sie müssen die Nutzungsbedingungen  / AGB lesen und akzeptieren, um am Spiel teilnehmen zu können.';
		elseif(strlen(trim($_POST['username'])) > USERNAME_MAXLEN || (strlen(trim($_POST['username'])) < 1))
			$error = 'Der Benutzername darf maximal 20 Bytes groß sein und muss mindesten 1 Byte groß sein.';
		elseif(strlen(trim($_POST['hauptplanet'])) > 16)
			$error = 'Der Name des Hauptplanets darf maximal 16 Bytes groß sein.';
		#elseif(preg_match('/[a-zA-Z0-9]/', $_POST['username'])) # Steuerzeichen
			#$error = 'Der Benutzername enthält ungültige Zeichen.';
		elseif(strlen(trim($_POST['password'])) > 24 || (strlen(trim($_POST['password'])) < 3))               
			$error = 'Das Passwort darf maximal 24 Zeichen lang sein und muss mindestens 3 Zeichen lang sein.';
		elseif($_POST['password'] != $_POST['password2'])
			$error = 'Die beiden Passworte stimmen nicht überein.';
		elseif(strlen(trim($_POST['email'])) < 6)
			$error = 'Die Email muss mindestens 6 Bytes groß und GÜLTIG sein!';
		else
		{
			$_POST['username'] = str_replace("\x0a", ' ', trim($_POST['username'])); # nbsp

			__autoload('User');
			if(User::UserExists($_POST['username']))
				$error = 'Dieser Spieler existiert bereits. Bitte wählen Sie einen anderen Namen.';
			elseif(substr($_POST['username'], -4) == ' (U)')
				$error = 'Der Benutzername darf nicht auf (U) enden.';
			elseif(substr($_POST['username'], -4) == ' (g)')
				$error = 'Der Benutzername darf nicht auf (g) enden.';
			else
			{
				$user_obj = Classes::User($_POST['username']);
				if(!$user_obj->create())
					$error = 'Datenbankfehler beim Anlegen des Benutzeraccounts.';		

				__autoload('Galaxy');
				
				# Koordinaten des Hauptplaneten bestimmen
				$koords = getFreeKoords();
				
				if( !$koords )
				{
					$error = 'Es gibt keine freien Planeten mehr.';
					$user_obj->destroy();
				}
				else
				{
					$index = $user_obj->registerPlanet($koords);
					
					if($index === false)
					{
						$error = 'Der Hauptplanet konnte nicht besiedelt werden.';
						$user_obj->destroy();
					}

					$user_obj->setActivePlanet($index);

					$user_obj->addRess(array(500000, 400000, 300000, 200000, 100000));
					$user_obj->setPassword($_POST['password']);

					if(isset($_POST['email']))
						$user_obj->setSetting('email', $_POST['email']);

					# Planetenname
					if(trim($_POST['hauptplanet']) == '')
						$user_obj->planetName('Hauptplanet');
					else $user_obj->planetName($_POST['hauptplanet']);

					phpbb2egg("\00304Ein neuer Spieler ist dem Universum beigetreten: ".$_POST['username'], "tbw" );
?>
<p class="successful">
	Die Registrierung war erfolgreich. Sie können sich nun anmelden. Die Koordinaten Ihres Hauptplaneten lauten <?=htmlentities($koords)?>.
</p>
<ul>
	<li><a href="./">Zur&uuml;ck zur Startseite</a></li>
</ul>
<?php
					gui::html_foot();
					exit();
				}
			}
		}
		if($error != '')
		{
?>
<p class="error">
	<?=utf8_htmlentities($error)."\n"?>
</p>
<?php
		}
	}

?>
<form action="<?=htmlentities(global_setting("USE_PROTOCOL").'://'.$_SERVER['HTTP_HOST'].h_root.'/register.php')?>" method="post" id="register-form">
	<div id="registerinput">
		<div>
			<label for="username">Benutzername*</label>
			<input type="text" id="username" name="username"<?=isset($_POST['username']) ? ' value="'.utf8_htmlentities($_POST['username']).'"' : ''?> maxlength="24" />
		</div>	

		<div>
			<label for="password">Passwort*</label>			
			<input type="password" id="password" name="password" />
		</div>

		<div>
			<label for="password2">Passwort wiederholen</label>
			<input type="password" id="password2" name="password2" />
		</div>

		<div>
			<label for="email"><span xml:lang="en">E-Mail</span>-Adresse</label>
			<input type="text" name="email" id="email"<?=isset($_POST['email']) ? ' value="'.utf8_htmlentities($_POST['email']).'"' : ''?> />
		</div>

		<div>
			<label for="hauptplanet">Gew&uuml;nschter Name des Hauptplaneten*</label>
			<input type="text" id="hauptplanet" name="hauptplanet"<?=isset($_POST['hauptplanet']) ? ' value="'.utf8_htmlentities($_POST['hauptplanet']).'"' : ''?> maxlength="24" />
		</div>
		
		<div id="agb_ack">
			<input type="checkbox" class="checkbox" name="nutzungsbedingungen" id="nutzungsbedingungen" /> <label for="nutzungsbedingungen">Ich habe die <a href="http://wiki.thebigwar.org/index.php/Regelwerk" target="_blank">Nutzungsbedingungen / Regeln</a> gelesen und akzeptiere sie.</label>
		</div>					
		<i>* Erlaubte Zeichen: A-Z, a-z, 0-9 und Leerzeichen.</i>	
		<div id="reg_buttonbox"><button id="reg_button" type="submit">Registrieren</button></div>			
	</div>
		
</form>
<?php 
    startseite_html_foot();
?>
