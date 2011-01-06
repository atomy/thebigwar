<?php
require_once ( 'include/config_inc.php' );
require ( TBW_ROOT . 'include.php' );
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>The Big War</title>

<meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
<meta name="author" content="The Big War">
<meta name="publisher" content="The Big War">
<meta name="copyright" content="The Big War">
<meta name="page-topic" content="Spiel">
<meta name="page-type" content="Browsergame">
<meta name="audience" content="Alle">
<meta http-equiv="content-language" content="de">
<meta name="description" content="T-B-W &ndash; The Big War ist ein Online-Spiel, fuer das man nur einen Firefox oder Opera Browser benoetigt. Bauen Sie sich im Weltraum ein kleines Imperium auf und kaempfen und handeln Sie mit Hunderten anderer Spielern.">
<meta name="keywords" content="onlinegame, gaming, allianz, handel, simulation, spiel, internet, freunde, community, wirtschaft, browsergame, freizeit, spass, handygame, kostenlos">
<link href="startseite.css" rel="stylesheet" type="text/css" />
</head>
<body>
<div id="chat">

<h2>TBW Chat</h2>
Den Chat erreicht ihr auch mit jedem beliebigem IRC-Client mit Hilfe folgender Daten:
<ul>
<li>Server: irc.gamesurge.net</li>
<li>Port: 6667</li>
<li>Channel: #tbw </li>
</ul>

</div> <!-- chat/ -->

<div id="chat_parameter">
Oder hier Nickname eintragen und auf verbinden klicken.
<form action="chat.php" method="get" id="chat-form"><label
	for="i-spitzname">Spitzname:</label> <input type="text" name="nickname"
	id="i-spitzname" />
<button type="submit">Zum Chat verbinden</button>
</form>
</div> <!-- paramter/ -->
<?php 
    if ( isset( $_REQUEST['nickname'] ) )
	{
?>
<applet code="IRCApplet.class" codebase="chat/"
	archive="irc.jar,pixx.jar" id="chat-applet" width=640 height=600>
	<param name="CABINETS" value="irc.cab,securedirc.cab,pixx.cab" />
	<param name="nick" value="<?php echo$_REQUEST['nickname']?>" />
	<param name="fullname" value="T-B-W Java User" />
	<param name="host" value="irc.gamesurge.net" />
	<param name="command1" value="/join #tbw" />
	<param name="gui" value="pixx" />
	<param name="port" value="6667" />
	<param name="language" value="english" />
	<param name="pixx:color1" value="000000" />
	<param name="pixx:color2" value="777777" />
	<param name="pixx:color3" value="777777" />
	<param name="pixx:color4" value="777777" />
	<param name="pixx:color5" value="777777" />
	<param name="pixx:color6" value="26252B" />
	<param name="pixx:color7" value="999999" />
	<param name="pixx:color8" value="FF0000" />
	<param name="pixx:color9" value="777777" />
	<param name="pixx:color10" value="777777" />
	<param name="pixx:color11" value="777777" />
	<param name="pixx:color12" value="777777" />
	<param name="pixx:color13" value="777777" />
	<param name="pixx:color14" value="777777" />
	<param name="pixx:color15" value="777777" />
	<param name="highlight" value="true" />
	<param name="pixx:highlightnick" value="true" />
	<param name="pixx:highlightcolor" value="8" />
	<param name="pixx:showconnect" value="false" />
	<param name="pixx:showchanlist" value="false" />
	<param name="pixx:showabout" value="false" />
	<param name="pixx:showhelp" value="false" />
</applet>

<?php 
    }
?>
</div>
</body>
</html>