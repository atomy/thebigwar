<?php
	require('include.php');
	require_once('include/config_inc.php');

        $channels = array(
            		    'tbw' => array('TBW Chat ', 'irc.eu.gamesurge.net', '#tbw')#,
#			    'tbw-support' => array('Support Chat ', 'irc.eu.gamesurge.net', '#tbw-support')
            		 );

	$popup = (isset($_REQUEST['channel']) && isset($_REQUEST['nickname']) && isset($channels[$_REQUEST['channel']]) && isset($_GET['popup']) && $_GET['popup']);

	if(!$popup)
	{
		gui::html_head('http://'.$_SERVER['HTTP_HOST'].h_root.'/chat/');
?>
<h2><abbr title="The Big War" xml:lang="en">T-B-W</abbr> &ndash; <span xml:lang="en">Chat</span></h2>
                <style type="text/css">
                        html,body { width:100%; height:100%; margin:0; padding:0; border-style:none; }
                </style>

<?php
	}

	if(!isset($_REQUEST['channel']) || !isset($_REQUEST['nickname']) || !isset($channels[$_REQUEST['channel']]))
	{
?>
<form action="<?=htmlentities(global_setting("USE_PROTOCOL").'://'.$_SERVER['HTTP_HOST'].h_root.'/chat.php')?>" method="get" id="chat-form">
	<fieldset>
                <legend>Chat</legend>

	<dl>
		<dt class="c-kanal"><label for="i-kanal">Kanal</label></dt>
		<dd class="c-kanal"><select name="channel" id="i-kanal">
<?php
		foreach($channels as $id=>$info)
		{
?>
			<option value="<?=htmlspecialchars($id)?>"><?=$info[0]?></option>
<?php
		}
?>
		</select></dd>

		<dt class="c-spitzname"><label for="i-spitzname">Spitzname</label></dt>
		<dd class="c-spitzname"><input type="text" name="nickname" id="i-spitzname" /></dd>
	</dl>
	<div><button type="submit">Verbinden</button></div>
</form>
<script type="text/javascript">
// <![CDATA[
	document.getElementById('i-spitzname').parentNode.parentNode.appendChild(dt_el = document.createElement('dt'));
	dt_el.className = 'c-neues-fenster';
	dt_el.appendChild(label_el = document.createElement('label'));
	label_el.setAttribute('for', 'i-neues-fenster');
	label_el.appendChild(document.createTextNode('Chat in neuem Fenster öffnen'));
	dt_el.parentNode.appendChild(dd_el = document.createElement('dd'));
	input_el = document.createElement('input');
	input_el.type = 'checkbox';
	input_el.id = 'i-neues-fenster';
	dd_el.appendChild(input_el);
	document.getElementById('chat-form').onsubmit = function()
	{
		if(input_el.checked)
		{
open('<?=global_setting("USE_PROTOCOL").'://'.$_SERVER['HTTP_HOST'].h_root.'/chat.php'?>?channel='+encodeURIComponent(document.getElementById('i-kanal').value)+'&nickname='+encodeURIComponent(document.getElementById('i-spitzname').value)+"&popup=1", "_blank", "location=no,menubar=no,resizable=yes,scrollbars=yes,status=yes,toolbar=no");
			return false;
		}
	}
// ]]>
</script>
<p id="chat-hinweis">Sie erreichen die Kanäle alternativ mit einem beliebigen <abbr title="Internet Relay Chat" xml:lang="en"><span xml:lang="de">IRC</span></abbr>-<span xml:lang="en">Client</span>.</p>
<dl id="chat-irc-liste">
<?php
		foreach($channels as $id=>$info)
		{
			if(!isset($info[3])) $info[3] = 6667;
?>
	<dt><?=$info[0]?></dt>
	<dd><a href="irc://<?=htmlentities($info[1])?>:<?=htmlentities($info[3])?>/<?=htmlentities($info[2])?>"><?=htmlentities($info[2])?> auf <?=htmlentities($info[1])?>, Port <?=htmlentities($info[3])?></a></dd>
<?php
		}
?>
</dl>
<?php
	}
	else
	{
		if($popup)
		{
?>
<?='<?xml version="1.0" encoding="UTF-8"?>'."\n"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de">
	<head>
		<title><?=$channels[$_REQUEST['channel']][0]?></title>
		<base href="<?=htmlspecialchars('http://'.$_SERVER['HTTP_HOST'].h_root.'/chat/')?>" />
		<style type="text/css"> 
			html,body,#chat-applet { width:100%; height:100%; margin:0; padding:0; border-style:none; } 
		</style> 
	</head>
	<body>
<?php
		}
		else
		{
?>
<h3><?=$channels[$_REQUEST['channel']][0]?></h3>
<?php
		}
?>
<applet code="IRCApplet.class" codebase="chat/" archive="irc.jar,pixx.jar" id="chat-applet" width=640 height=600>
	<param name="CABINETS" value="irc.cab,securedirc.cab,pixx.cab" />
	<param name="nick" value="<?=$_REQUEST['nickname']?>" />
	<param name="fullname" value="T-B-W Java User" />
	<param name="host" value="<?=htmlentities($channels[$_REQUEST['channel']][1])?>" />
	<param name="command1" value="/join <?=htmlentities($channels[$_REQUEST['channel']][2])?>" />
	<param name="gui" value="pixx" />
<?php
		if(isset($channels[$_REQUEST['channel']][3]))
		{
?>
	<param name="port" value="<?=htmlentities($channels[$_REQUEST['channel']][3])?>" />
<?php
		}
?>
	<param name="language" value="english" />
	<param name="quitmessage" value="<?GLOBAL_GAMEURL?>" />
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
		if($popup)
		{
?>
	</body>
</html>
<?php
		}
	}

	gui::html_foot();
?>
