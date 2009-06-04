<?php
	require( 'include.php' );
	#require( s_root.'/engine/classes/fleet.php' );
	
	// TODO, fleet list access
	if(!$admin_array['permissions'][0])
		die('No access.');

	admin_gui::html_head();

?>
<h2>Fleetliste:</h2>
<?php
	$events = Classes::EventFile();
	$result = $events->_getListOfEvents();
?>
<table>
<?php
	$i = 0;
	$raw = 0;
	
	while( $output = sqlite_fetch_array( $result ) )
	{
		print "<tr>\n";
		if ( $i == 0 )
		{
			print "<tr>\n";
			print "<td>time</td>\n";
			print "<td>fleet</td>\n";
			print "<td>exists on fsys?</td>\n";
			print "<td>status</td>\n";
			print "<td>raw</td>\n";
			print "</tr>\n";
		}
	
		print "<td>".$output['time']."</td>\n";
		print "<td>".$output['fleet']."</td>\n";

		$fleet = Classes::Fleet( $output['fleet'], false );

		if ( $fleet->fleetExists( $output['fleet'] ) )
		{
			echo "<td>yes</td>\n";
			echo "<td>".$fleet->getStatus()."</td>\n";
		}
		else
		{
			echo "<td>no</td>\n";
			echo "<td>???</td>\n";
		}

		if ( $raw = $fleet->getRaw() )
		{
			print "<td>";
			var_dump( $raw );
			print "</td>\n";
		}
		else
			print "<td>none</td>\n";
			
		print "</tr>\n";
		$i++;
	}
	echo "</table>\n";

	admin_gui::html_foot();
?>