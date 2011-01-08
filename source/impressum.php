<?php
if(!isset($_SERVER['DOCUMENT_ROOT']) || strlen($_SERVER['DOCUMENT_ROOT']) <= 0)
    $_SERVER['DOCUMENT_ROOT'] = getcwd();
    
require_once($_SERVER['DOCUMENT_ROOT'].'/include/config_inc.php');
require( $_SERVER['DOCUMENT_ROOT'].'/include.php' );

    startseite_html_head();
?>

<div id="imprint">
<h1>Impressum</h1>
<fieldset><legend>Betreiber</legend>
<ul>
		<li><h4>Die Betreiber sind über folgende Mail-Adressen erreichbar:</h4></li>
		<li><a href="mailto:atomy@thebigwar.org">atomy@thebigwar.org</a></li>
		<li><a href="mailto:xsawa@thebigwar.org">xsawa@thebigwar.org</a></li>
</ul>
</fieldset>
<fieldset><legend>Credits</legend>
<ul>
		
		<li>Das Spiel basiert auf dem Programmcode von TBW bzw. SUA</li>
</ul>
</fieldset>
</div> <!-- imprint / -->

<?php 
    startseite_html_foot();
?>