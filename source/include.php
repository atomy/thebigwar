<?php
if(!isset($_SERVER['DOCUMENT_ROOT']) || strlen($_SERVER['DOCUMENT_ROOT']) <= 0)
    $_SERVER['DOCUMENT_ROOT'] = getcwd()."/..";
    
require_once($_SERVER['DOCUMENT_ROOT'].'/include/config_inc.php');
require($_SERVER['DOCUMENT_ROOT'].'/engine/include.php');

	class gui
	{ # Kuemmert sich ums HTML-Grundgeruest der Hauptseite
		public static function html_head( $base = false )
		{
			global $SHOW_META_DESCRIPTION; # Sollte nur auf der Startseite der Fall sein
?>
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
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
?>	
		<title>The Big War | T-B-W</title>
		<link href="stylesheet.css" rel="stylesheet" type="text/css" />
<!-- Piwik -->
<script type="text/javascript">
var pkBaseURL = (("https:" == document.location.protocol) ? "https://piwik.jackinpoint.net/" : "http://piwik.jackinpoint.net/");
document.write(unescape("%3Cscript src='" + pkBaseURL + "piwik.js' type='text/javascript'%3E%3C/script%3E"));
</script><script type="text/javascript">
try {
var piwikTracker = Piwik.getTracker(pkBaseURL + "piwik.php", 1);
piwikTracker.trackPageView();
piwikTracker.enableLinkTracking();
} catch( err ) {}
</script><noscript><p><img src="http://piwik.jackinpoint.net/piwik.php?idsite=1" style="border:0" alt="" /></p></noscript>
<!-- End Piwik Tracking Tag -->		
		</head> 
	<body background="images/back.jpg" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" style="background-repeat:repeat">
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
	
	function startseite_html_head()
	{
	    ?>
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
		<title>The Big War</title>

		<meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
	    <meta name="author" content="The Big War" />
		<meta name="publisher" content="The Big War" />
		<meta name="copyright" content="The Big War" />
		<meta name="page-topic" content="Spiel" />
		<meta name="page-type" content="Browsergame" />
		<meta name="audience" content="Alle" />
		<meta http-equiv="content-language" content="de" />
		<meta name="description" content="T-B-W &ndash; The Big War ist ein Online-Spiel, fuer das man nur einen Firefox oder Opera Browser benoetigt. Bauen Sie sich im Weltraum ein kleines Imperium auf und kaempfen und handeln Sie mit Hunderten anderer Spielern." />
		<meta name="keywords" content="onlinegame, gaming, allianz, handel, simulation, spiel, internet, freunde, community, wirtschaft, browsergame, freizeit, spass, handygame, kostenlos" />
		
		<link href="startseite.css" rel="stylesheet" type="text/css" /> 	
		<link rel="stylesheet" href="css/lightbox.css" type="text/css" media="screen" />
		
		</head>
		<body>
		<div id="main">

		<div id="navibar">

		<div>
		<a href="http://forum.thebigwar.org/" target="_blank">Forum</a>
		</div>
		<div>
		<a href="ueber_tbw.php">Über TBW</a>
		</div>
		<div>
		<a href="register.php">Jetzt registrieren!</a>
		</div>
		<div>
		<a href="screenshots.php">Bilder</a>
		</div>
		<div>
		<a target="_blank" href="chat.php">Chat</a>
		</div>

		</div> <!-- navibar/ -->	 
		
		<div id="logo">
		<a href="index.php">
		</a>
		</div> <!-- logo/ -->  

		<?php     
	}
	
	function startseite_html_foot()
	{
	    ?>
	    <div id="bottomlinks">

		<div id="links_wiki">
		<a target="_blank" href="http://wiki.thebigwar.org/">Wiki</a>
		</div>
		<div id="links_imprint">
		<a href="impressum.php">Impressum</a>
		</div>

		<div id="registerbox">
		<a href="register.php">Jetzt kostenlos registrieren</a>
		</div> <!-- registerbox/ -->

		<div id="links_regeln_agb">
		<a target="_blank" href="http://wiki.thebigwar.org/index.php/Regelwerk">Regeln/AGB</a>
		</div>

		</div> <!-- bottomlinks/ -->
	
		</div> <!-- main/ -->
		
		</body>
		</html>
	    <?php 
	}
		
