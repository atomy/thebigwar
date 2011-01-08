<?php
if(!isset($_SERVER['DOCUMENT_ROOT']) || strlen($_SERVER['DOCUMENT_ROOT']) <= 0)
    $_SERVER['DOCUMENT_ROOT'] = getcwd();
    
require_once($_SERVER['DOCUMENT_ROOT'].'/include/config_inc.php');
require($_SERVER['DOCUMENT_ROOT'].'/login/scripts/include.php');

	login_gui::html_head();

	$changelog = '';
	if(is_file(global_setting("DB_CHANGELOG")) && is_readable(global_setting("DB_CHANGELOG")))
		$changelog = file_get_contents(global_setting("DB_CHANGELOG"));

	$changelog = preg_split("/\r\n|\r|\n/", $changelog);
?>
<h2 id="changelog">Changelog</h2>
<ol class="changelog">
<?php
	foreach($changelog as $log)
	{
		$log = explode("\t", $log, 2);
		if(count($log) < 2)
		{
?>
	<li><?php echo utf8_htmlentities($log[0])?></li>
<?php
		}
		else
		{
?>
	<li><span class="zeit"><?php echo date('Y-m-d, H:i:s', $log[0])?>:</span> <?php echo utf8_htmlentities($log[1])?></li>
<?php
		}
	}
?>
</ol>
<?php 
	login_gui::html_foot();
?>