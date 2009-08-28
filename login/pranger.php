<?php
	require_once( '../include/config_inc.php' );
	require( TBW_ROOT.'login/scripts/include.php' );

	login_gui::html_head();

	$pranger = '';
	if(is_file(global_setting("DB_PRANGER")) && is_readable(global_setting("DB_PRANGER")))
		$pranger = file_get_contents(global_setting("DB_PRANGER"));

	$pranger = preg_split("/\r\n|\r|\n/", $pranger);
?>
<h2 id="changelog" xml:lang="en">Pranger - <?=utf8_htmlentities($databases[$_SESSION['database']][1])?></h2>
<ol class="changelog">
<?php
	foreach($pranger as $log)
	{
		$log = explode("\t", $log, 2);
		if(count($log) < 2)
		{
?>
	<li><?=utf8_htmlentities($log[0])?></li>
<?php
		}
		else
		{
?>
	<li><span class="zeit"><?=date('Y-m-d, H:i:s', $log[0])?>:</span> <?=utf8_htmlentities($log[1])?></li>
<?php
		}
	}

	login_gui::html_foot();
?>
