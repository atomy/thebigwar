<?php
	require_once( 'include/config_inc.php' );
	require( TBW_ROOT.'include.php' );

    startseite_html_head();
?>

<div id="importantbox">

<div id="newsbox">

<div>
Neuigkeiten:
</div>
<iframe name="newsframe" id="newsframe" scrolling="no" frameborder="0" src="http://forum.thebigwar.org/ext/smallnews.php" >
  <p>Ihr Browser kann leider keine eingebetteten Frames anzeigen:
  Sie k&ouml;nnen die eingebettete Seite &uuml;ber den folgenden Verweis
  aufrufen: <a href="http://forum.thebigwar.org/ext/news.php">News</a></p>
</iframe>

</div> <!-- newsbox -->

<div id="middlebox">

<div id="loginbox">

<form method="post" action="<?php echo GLOBAL_GAMEURL; ?>login/index.php" id="login-form">

<div id="inputbox">

<div id="login_user">
<div>Name:</div>
<input type="text" id="login-username" name="username" class="name" />
</div> <!-- login_user/ -->

<div id="login_pass">
Passwort:
<input type="password" id="login-password" name="password" class="passwort" />
</div> <!-- login_pass/ -->

</div> <!-- inputbox/ -->

<div id="login_button">
<input type="submit" name="anmelden" value="Anmelden" />
</div> <!-- login_button/ -->

</form>

</div> <!-- loginbox/ -->

<div id="login_links">
<a id="login_links_pass" href="http://www.thebigwar.org/passwd.php">Passwort vergessen?</a>
<a id="login_links_guest" href="http://www.thebigwar.org/login/guest.php?database=Universum1">Probezugang</a>
</div> <!-- login_links/ -->

<div id="votebox">
<div id="gdynamite" style="">
<a href="http://bgs.gdynamite.de/charts_vote_1066.html" target="_blank"><img src="http://voting.gdynamite.de/images/gd_animbutton.gif" alt="games-dynamite vote button" border="0" /></a>
</div>

<div id="gnews">
<a href="http://www.galaxy-news.de/charts/?op=vote&amp;game_id=3353" target="_blank"><img src="images/vote.gif" style="border:0;" alt="galaxy-news vote button" /></a>
</div>

</div> <!-- votebox/ -->

</div> <!-- middlebox/ -->

</div> <!-- importantbox/ -->

<?php 
    startseite_html_foot();
?>