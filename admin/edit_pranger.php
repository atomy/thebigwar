<?php
	require_once( '../include/config_inc.php' );
	require( TBW_ROOT.'admin/include.php' );

	if(!$admin_array['permissions'][10])
		die('No access.');

	
	if(isset($_GET['delete']))
	{
		$old_pranger = '';
		if(is_file(global_setting("DB_PRANGER")) && is_readable(global_setting("DB_PRANGER")))
			$old_pranger = trim(file_get_contents(global_setting("DB_PRANGER")));
		if(strlen($old_pranger) <= 0)
			$old_pranger = array();
		else
			$old_pranger = preg_split("/\r\n|\r|\n/", $old_pranger);
		if(isset($old_pranger[count($old_pranger)-$_GET['delete']]))
		{
			$fh = fopen(global_setting("DB_PRANGER"), 'w');
			if($fh)
			{
				flock($fh, LOCK_EX);

				unset($old_pranger[count($old_pranger)-$_GET['delete']]);
				fwrite($fh, implode("\n", $old_pranger));

				flock($fh, LOCK_UN);
				fclose($fh);

				protocol("8.6", $_POST['add']);
			}
		}
		unset($old_pranger);
	}

	if(isset($_POST['add']) && strlen(trim($_POST['add'])) > 0)
	{
		$old_pranger = '';
		if(is_file(global_setting("DB_PRANGER")) && is_readable(global_setting("DB_PRANGER")))
			$old_pranger = trim(file_get_contents(global_setting("DB_PRANGER")));
		$fh = fopen(global_setting("DB_PRANGER"), 'w');
		if($fh)
		{
			flock($fh, LOCK_EX);
			fwrite($fh, time()."\t".$_POST['add']."\n");
			fwrite($fh, $old_pranger);
			flock($fh, LOCK_UN);
			fclose($fh);

			protocol("8.5", $_POST['add']);
		}
		unset($old_pranger);
	}

	admin_gui::html_head();
?>
<fieldset>
<legend>Pranger</legend>
Sperrgrund bitte folgendermaßen eintragen: Nick des Spieler, Grund der Sperre, Dauer der Sperre. (GO-Account)<br>
z.B.: User "XYZ" wurde wegen Beleidigung für 3 Tage gesperrt. (gameoperator1)
<form action="edit_pranger.php" method="post">
	<ul>
		<li><input type="text" name="add" value=""/><button type="submit">Hinzufügen</button></li>

<?php
	$pranger = '';
	if(is_file(global_setting("DB_PRANGER")) && is_readable(global_setting("DB_PRANGER")))
		$pranger = trim(file_get_contents(global_setting("DB_PRANGER")));
	if(strlen($pranger) <= 0)
		$pranger = array();
	else
		$pranger = preg_split("/\r\n|\r|\n/", $pranger);

	foreach($pranger as $i=>$log)
	{
		echo "\t\t<li>";
		$log = explode("\t", $log, 2);
		if(count($log) < 2)
			echo utf8_htmlentities($log[0]);
		else
			echo date('Y-m-d, H:i:s', $log[0]).': '.utf8_htmlentities($log[1]);
		echo " [<a href=\"edit_pranger.php?delete=".htmlentities(urlencode(count($pranger)-$i))."\">Löschen</a>]</li>\n";
	}
?>
	</ul>
</form>
</fieldset>
<?php
	admin_gui::html_foot();
?>
