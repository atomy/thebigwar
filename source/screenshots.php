<?php
if(!isset($_SERVER['DOCUMENT_ROOT']) || strlen($_SERVER['DOCUMENT_ROOT']) <= 0)
    $_SERVER['DOCUMENT_ROOT'] = getcwd();
    
require_once($_SERVER['DOCUMENT_ROOT'].'/include/config_inc.php');
require($_SERVER['DOCUMENT_ROOT'].'/include.php');

    startseite_html_head();
?>

<script type="text/javascript" src="javascript/prototype.js"></script>
<script type="text/javascript" src="javascript/scriptaculous.js?load=effects,builder"></script>
<script type="text/javascript" src="javascript/lightbox.js"></script>

<div id="screenshots">
<h1>Screenshots</h1>

<div class="screenbox"><div class="screentext">Flottenmenü</div><div class="screenies">
<a href="../images/screenshots/screenshot_01.png" rel="lightbox[screenshot]"><img src="../images/screenshots/preview_01.png" alt="Screenshot 1" /></a>
</div></div>

<div class="screenbox"><div class="screentext">Accountübersicht</div><div class="screenies">
<a href="../images/screenshots/screenshot_02.png" rel="lightbox[screenshot]"><img src="../images/screenshots/preview_02.png" alt="Screenshot 2" /></a>
</div></div>

<div class="screenbox"><div class="screentext">Karte</div><div class="screenies">
<a href="../images/screenshots/screenshot_03.png" rel="lightbox[screenshot]"><img src="../images/screenshots/preview_03.png" alt="Screenshot 3" /></a>
</div></div>

<div class="screenbox"><div class="screentext">Handelsrechner</div><div class="screenies">
<a href="../images/screenshots/screenshot_07.png" rel="lightbox[screenshot]"><img src="../images/screenshots/preview_07.png" alt="Screenshot 7" /></a>
</div></div>

<div class="screenbox"><div class="screentext">Forschungsmenü</div><div class="screenies">
<a href="../images/screenshots/screenshot_08.png" rel="lightbox[screenshot]"><img src="../images/screenshots/preview_08.png" alt="Screenshot 8" /></a>
</div></div>

<div class="screenbox"><div class="screentext">Imperiumsansicht - Roboter</div><div class="screenies">
<a href="../images/screenshots/screenshot_09.png" rel="lightbox[screenshot]"><img src="../images/screenshots/preview_09.png" alt="Screenshot 9" /></a>
</div></div>

<div class="screenbox"><div class="screentext">Planetenmenü</div><div class="screenies">
<a href="../images/screenshots/screenshot_10.png" rel="lightbox[screenshot]"><img src="../images/screenshots/preview_10.png" alt="Screenshot 10" /></a>
</div></div>

<div class="screenbox"><div class="screentext">Imperiumsansicht - Rohstoffe</div><div class="screenies">
<a href="../images/screenshots/screenshot_11.png" rel="lightbox[screenshot]"><img src="../images/screenshots/preview_11.png" alt="Screenshot 11" /></a>
</div></div>

<div class="screenbox"><div class="screentext"></div><div class="screenies">
</div></div>

</div> <!-- screenshots / -->

<?php 
    startseite_html_foot();
?>