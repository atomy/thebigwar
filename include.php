<?php
       $__FILE__ = str_replace("\\", "/", __FILE__);
       $include_filename = dirname($__FILE__).'/engine/include.php';
       require_once($include_filename);
	   require('include/config_inc.php');

	   class gui
	{ # Kuemmert sich ums HTML-Grundgeruest der Hauptseite
		function html_head($base=false)
		{
			global $SHOW_META_DESCRIPTION; # Sollte nur auf der Startseite der Fall sein
?>
<html>
	<head>
		<meta name="verify-v1" content="ymwSeWOA4UZOmT0NiAWbWMC5tG080GhtCu9q1gKZlHU=" />
		<title>The Big War | T-B-W</title>
		<link href="stylesheet.css" rel="stylesheet" type="text/css" />
		<!-- Schneefall -->
		<body onLoad="snow()" onLoad="javascript:countdown();">

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
    <meta name="keywords" content="onlinegame, gaming, allianz, handel, simulation, spiel, internet, freunde, community, handelsversicherung, wirtschaft, partner, zeitschrift, science-fiction, browsergame, kurse, freizeit, sicherheit, spass, handygame, sms, payment, werbung, kostenlos">

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
<div class="login">
<form method="post" action="<?GLOBAL_GAMEURL?>login/index.php" id="login-form">
<table class="login">
	<tbody>
		<tr>
			<td colspan="1" rowspan="2">
				<div class="login3"><input type="text" id="login-username" name="username" class="name" /> Name</div>
				<div class="login3"><input type="password" id="login-password" name="password" class="passwort" /> Passwort</div>
			</td>
		</tr>
		<tr>
			<td>
<!--				&nbsp;&nbsp;&nbsp;&nbsp;<img src="images/bronze-klein-startpage.png" alt="sbs2007 award"></img> -->
			</td>
		</tr>
	</tbody>
</table>
<div class="login3"><select name="database" id="login-runde">
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
<?php
#			if(global_setting("USE_PROTOCOL") == 'https')
#			{
?> 
<!-- <a href="http://<?=$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']?>?nossl=1">SSL abschalten</a> -->
<?php
#			}
#			else
#			{
?> 
<!-- <a href="http://<?=$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']?>?nossl=0">SSL einschalten</a> -->
<?php
#			}
?>
</div>
</form>
</div>
</td>
			</tr>
			<tr>
				<td width="176" height="100%" background="images/navfoot.gif" style="vertical-align:top; background-repeat:no-repeat;">
					<table cellspacing="0" cellpadding="0">
					<tr>
						<td width="176" height="212" background="images/navigation.jpg" class="navigation">
<a href="http://<?=htmlentities($_SERVER['HTTP_HOST'].h_root)?>/index.php">&nbsp;&nbsp;&nbsp;News</a><br />
<a href="http://<?=htmlentities($_SERVER['HTTP_HOST'].h_root)?>/features.php">&nbsp;&nbsp;&nbsp;Features</a><br />
<a href="http://<?=htmlentities($_SERVER['HTTP_HOST'].h_root)?>/register.php">&nbsp;&nbsp;&nbsp;Registrieren</a><br />
<!-- <a href="http://<?=htmlentities($_SERVER['HTTP_HOST'].h_root)?>/tbwforum/viewtopic.php?t=80" target="_blank">&nbsp;&nbsp;&nbsp;Regeln</a><br /> -->
<a href="http://<?=htmlentities($_SERVER['HTTP_HOST'].h_root)?>/faq.php">&nbsp;&nbsp;&nbsp;FAQ</a><br />
<!-- <a href="http://<?=htmlentities($_SERVER['HTTP_HOST'].h_root)?>/mediawiki/" target="_blank">&nbsp;&nbsp;&nbsp;Wiki</a><br /> -->
<a href="http://<?=htmlentities($_SERVER['HTTP_HOST'].h_root)?>/chat.php">&nbsp;&nbsp;&nbsp;Chat</a><br />
<a href="http://www.stephanlinden.net/forum/" target="_blank">&nbsp;&nbsp;&nbsp;Forum</a><br />
<a href="http://<?=htmlentities($_SERVER['HTTP_HOST'].h_root)?>/impressum.php">&nbsp;&nbsp;&nbsp;Impressum</a><br />
<a href="http://<?=htmlentities($_SERVER['HTTP_HOST'].h_root)?>/story.php">&nbsp;&nbsp;&nbsp;TBW Story</a><br />
<a href="http://<?=htmlentities($_SERVER['HTTP_HOST'].h_root)?>/rules.php">&nbsp;&nbsp;&nbsp;AGB</a></td>

					</tr>
					</table>
				</td>
				<td width="110" height="100%"></td>
				<td width="663" height="100%" colspan="2" style="vertical-align:top; " class="startseite">

			<div id="innercontent1-1"><div id="innercontent1-2">
			<div id="innercontent2-1"><div id="innercontent2-2">
			<div id="innercontent3">

</div></div></div>
		</div></div><?php
		}

		function html_foot()
		{
?>
</td>
			</tr>
		</table>
	</td>
	</tr>
	</table>
	</body>

<!-- Schneefall -->
<!--		<SCRIPT LANGUAGE="JavaScript" SRC="snow.js"></SCRIPT>
		<SCRIPT LANGUAGE="JavaScript">
			function snow()
				{
				Falling(10,"<IMG SRC='images/snow.gif'>");
				}
		</SCRIPT>
-->

<script language="JavaScript">
<!--

var Serverzeit = new Date();

function UhrzeitAnzeigen()
{
	if(!document.all && !document.getElementById)
	{
		return;
	}

	var Stunden = Serverzeit.getHours();
	var Minuten = Serverzeit.getMinutes();
	var Sekunden = Serverzeit.getSeconds();
	Serverzeit.setSeconds(Sekunden+1);

	if(Stunden <= 9)
	{
		Stunden = "0" + Stunden;
	}

	if(Minuten <= 9)
	{
		Minuten = "0" + Minuten;
	}

	if(Sekunden <= 9)
	{
		Sekunden = "0" + Sekunden;
	}

	Uhrzeitanzeige = Stunden + ":" + Minuten + ":" + Sekunden;

	if(document.getElementById)
	{
		document.getElementById("Uhrzeit").innerHTML = Uhrzeitanzeige
	}
	else if(document.all)
	{
		Uhrzeit.innerHTML = Uhrzeitanzeige;
	}

	setTimeout("UhrzeitAnzeigen()", 1000);
}
//-->
</script>

	</html>
<?php
		}
	}
