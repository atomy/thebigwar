<?php
	require_once( 'include/config_inc.php' );
	require( TBW_ROOT.'engine/include.php' );

	class gui
	{ # Kuemmert sich ums HTML-Grundgeruest der Hauptseite
		public static function html_head($base=false)
		{
			global $SHOW_META_DESCRIPTION; # Sollte nur auf der Startseite der Fall sein
?>
<html>
	<head>
		<title>The Big War | T-B-W</title>
		<link href="stylesheet.css" rel="stylesheet" type="text/css" /> 
		<body>

<?php
			if(isset($SHOW_META_DESCRIPTION) && $SHOW_META_DESCRIPTION)
			{
?>
	<meta name="author" content="The Big War">
	<meta name="publisher" content="The Big War">
	<meta name="copyright" content="The Big War">
	<meta name="page-topic" content="Spiel">
	<meta name="page-type" content="Browsergame">
	<meta name="audience" content="Alle">
	<meta http-equiv="content-language" content="de">
	<meta name="description" content="T-B-W &ndash; The Big War ist ein Online-Spiel, fuer das man nur einen Firefox oder Opera Browser benoetigt. Bauen Sie sich im Weltraum ein kleines Imperium auf und kaempfen und handeln Sie mit Hunderten anderer Spielern.">
	<meta name="keywords" content="onlinegame, gaming, allianz, handel, simulation, spiel, internet, freunde, community, wirtschaft, browsergame, freizeit, spass, handygame, kostenlos">

<?php
			}
			if($base)
			{
?>
		<base href="<?=htmlentities($base)?>" />
<?php
			}
?>
	</head>
	<body background="images/back.jpg" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" style="background-repeat:repeat">
		<table border="0" cellpadding="0" cellspacing="0" style="width:100%; " height="100%">
			<tr>
				<td background="images/fighter.gif" style="background-position:bottom right; background-repeat:no-repeat; background-attachment:fixed; " width="100%">
					<table border="0" cellpadding="0" cellspacing="0" width="949" height="100%">
						<tr>
							<td width="286" colspan="2"><img src="images/logo.jpg"></td>
							<td width="350" background="images/login.gif" class="login2">
				
								<div class="login" style="width:400px; position:absolute; top:0px; left:286px; background-image:url('images/login.gif'); background-repeat:no-repeat;">
				
								<form method="post" action="<?echo GLOBAL_GAMEURL;?>login/index.php" id="login-form">
									<table class="login" border="0">
										<tbody>
											<tr>
												<td colspan="1" rowspan="2">
													<div class="login3"><input type="text" id="login-username" name="username" class="name" /> Name</div>
													<div class="login3"><input type="password" id="login-password" name="password" class="passwort" /> Passwort</div>
												</td>
											</tr>
										</tbody>
									</table>
									<div class="login3">
									<select name="database" id="login-runde">
<?php			
                        $databases = get_databases();
                        foreach($databases as $id=>$info)
                        {
?>
	                                                        	<option value="<?=utf8_htmlentities($id)?>"><?=utf8_htmlentities($info[1])?></option>
<?php
                        }
?>
		
								</select>&nbsp;<input type="submit" name="anmelden" style=" font-weight:bold; color:#FCFCCC; " value="Anmelden" /></div>
								<div class="login4"><a href="http://<?=$_SERVER['HTTP_HOST'].h_root?>/passwd.php">Passwort vergessen?</a>
								<div class="login5"><a href="http://<?=$_SERVER['HTTP_HOST'].h_root?>/login/guest.php?database=<?=utf8_htmlentities($id)?>">Gast Zugang</a>
							</div>
						</form>
					</div>
				</td>
			</tr>
			<tr>
				<td width="176" height="100%" background="images/navfoot.gif" style="vertical-align:top; background-repeat:no-repeat;">
					<table cellspacing="0" cellpadding="0" border="0">
						<tr>
							<td width="176" height="212" background="images/navigation.jpg" class="navigation">
								<a href="http://<?=htmlentities($_SERVER['HTTP_HOST'].h_root)?>/index.php">&nbsp;&nbsp;&nbsp;News</a><br />
								<a href="http://<?=htmlentities($_SERVER['HTTP_HOST'].h_root)?>/features.php">&nbsp;&nbsp;&nbsp;Features</a><br />
								<a href="http://<?=htmlentities($_SERVER['HTTP_HOST'].h_root)?>/register.php">&nbsp;&nbsp;&nbsp;Registrieren</a><br />
								<a href="http://<?=htmlentities($_SERVER['HTTP_HOST'].h_root)?>/faq.php">&nbsp;&nbsp;&nbsp;FAQ</a><br />
								<a href="http://<?=htmlentities($_SERVER['HTTP_HOST'].h_root)?>/chat.php">&nbsp;&nbsp;&nbsp;Chat</a><br />
								<a href="http://www.stephanlinden.net/forum/" target="_blank">&nbsp;&nbsp;&nbsp;Forum</a><br />
								<a href="http://<?=htmlentities($_SERVER['HTTP_HOST'].h_root)?>/impressum.php">&nbsp;&nbsp;&nbsp;Impressum</a><br />
								<a href="http://<?=htmlentities($_SERVER['HTTP_HOST'].h_root)?>/story.php">&nbsp;&nbsp;&nbsp;TBW Story</a><br />
								<a href="http://<?=htmlentities($_SERVER['HTTP_HOST'].h_root)?>/rules.php">&nbsp;&nbsp;&nbsp;AGB</a>
							</td>
						</tr>
					</table>
				</td>
				<td width="663" height="100%" colspan="2" style="vertical-align:top; " class="startseite">
<?php
	}

	public static function html_foot()
	{
?>
				</td>
			</tr>
		</table>
	</td>
</tr>
</table>
</body>
</html>
<?php
		}
	}
