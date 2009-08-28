<?php
	require_once( 'include/config_inc.php' );
	require( TBW_ROOT.'include.php' );

	gui::html_head();
?>
<h2><abbr title="The Big War" xml:lang="en">T-B-W</abbr> &ndash; Impressum</h2>

<fieldset><legend>Spielbetreiber</legend>
<ul>
	<li>Projektleader:</li><br><br>
	<li>atomy@thebigwar.org</li><br>
	<li>xsawa@thebigwar.org</li><br>
</ul>
<ul>
<li><em xml:lang="en">The Big War</em> ist ein privates Projekt, das zur Zeit keinerlei kommerzielle Absichten verfolgt.</li>
</ul>
</fieldset>

<p></p>

<fieldset><legend>Kontakt</legend>
<p>Sollten Sie eine Frage oder Anmerkung bezüglich des Spiels haben, haben Sie folgende Möglichkeiten:</p>
<ul>
	<li>Setzen Sie sich per <a href="mailto:atomy@thebigwar.org">eMail</a> mit dem Betreiber in Verbindung.</li>
	<li>Stellen Sie ihre Frage öffentlich im <a href="http://www.stephanlinden.net/forum/" xml:lang="en">Forum</a>.</li>
	<li>Fragen Sie im Support-Kanal des <a href="http://<?=$_SERVER['HTTP_HOST'].h_root?>/chat.php">Chats</a> nach.</li>

</ul>
</fieldset>

<p></p>

<fieldset><legend>Credits</legend>
<ul>
		
		<li>Das Spiel basiert auf den Programmcode von TBW bzw. SUA</li>
</ul>
</fieldset>

<p></p>
</fieldset>
<p></p><p></p>
<?php
	gui::html_foot();
?>
