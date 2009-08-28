<?php
	$LOGIN = true;
	define('ignore_action', true);
	define('ajax', true);
	
	require_once( '../../include/config_inc.php' );
	require( TBW_ROOT.'engine/include.php' );

	header('Content-type: text/xml;charset=UTF-8');
	echo "<xmlresponse>\n";

	if(!isset($_GET['action'])) $_GET['action'] = null;
	else
	{
		$databases = get_databases();
		if(!isset($_GET['database']) || !isset($databases[$_GET['database']])) $_GET['action'] = false;
		else define_globals($_GET['database']);
	}

	switch($_GET['action'])
	{
		case 'userlist':
			$query = '';
			if(isset($_GET['query']))
				$query = strtolower(urlencode($_GET['query']));
			$query_length = strlen($query);
			if($query_length < global_setting("LIST_MIN_CHARS")) break;

			$results = array();
			$dh = opendir(global_setting("DB_PLAYERS"));
			while(($fname = readdir($dh)) !== false)
			{
				if($fname == '.' || $fname == '..') continue;
				$fname = $fname;

				if(strlen($fname) >= $query_length && substr($fname, 0, $query_length) == $query)
					$results[] = urldecode($fname);
			}
			closedir($dh);

			natcasesort($results);

			foreach($results as $result)
				echo "\t<result>".htmlspecialchars(urldecode($result))."</result>\n";

			break;

		 case 'alliancelist':
                        $query = '';
                        if(isset($_GET['query']))
                                $query = strtolower(urlencode($_GET['query']));
                        $query_length = strlen($query);
                        if($query_length < global_setting("LIST_MIN_CHARS")) break;

                        $results = array();
                        $dh = opendir(global_setting("DB_ALLIANCES"));
                        while(($fname = readdir($dh)) !== false)
                        {
                                if($fname == '.' || $fname == '..') continue;
                                $fname = $fname;

                                if(strlen($fname) >= $query_length && substr($fname, 0, $query_length) == $query)
                                        $results[] = urldecode($fname);
                        }
                        closedir($dh);

                        natcasesort($results);

                        foreach($results as $result)
                                echo "\t<result>".htmlspecialchars(urldecode($result))."</result>\n";

                        break;



		case 'spionage': case 'besiedeln': case 'sammeln': case 'shortcut':
			list($classname, $result) = include('../flotten.php');
			echo "\t<classname>".htmlspecialchars($classname)."</classname>\n";
			echo "\t<result>".htmlspecialchars($result)."</result>\n";
			break;

		case 'universe':
			if(!isset($_GET['system']) || !is_array($_GET['system'])) $_GET['system'] = array();
			foreach($_GET['system'] as $systemo)
			{
				$system = explode(':', $systemo);
				if(count($system) != 2) continue;
				$galaxy_obj = Classes::Galaxy($system[0]);
				if(!$galaxy_obj->getStatus() || !($planets_count = $galaxy_obj->getPlanetsCount($system[1])))
					continue;
				echo "\t<system number=\"".htmlspecialchars($systemo)."\">\n";

				for($i=1; $i<=$planets_count; $i++)
				{
					echo "\t\t<planet number=\"".htmlspecialchars($i)."\">\n";
					echo "\t\t\t<owner>".htmlspecialchars($galaxy_obj->getPlanetOwner($system[1], $i))."</owner>\n";
					echo "\t\t\t<name>".htmlspecialchars($galaxy_obj->getPlanetName($system[1], $i))."</name>\n";
					echo "\t\t\t<alliance>".htmlspecialchars($galaxy_obj->getPlanetOwnerAlliance($system[1], $i))."</alliance>\n";
					echo "\t\t\t<flag>".htmlspecialchars($galaxy_obj->getPlanetOwnerFlag($system[1], $i))."</flag>\n";
					$truemmerfeld = truemmerfeld::get($system[0], $system[1], $i);
					if(array_sum($truemmerfeld) > 0)
						echo "\t\t\t<truemmerfeld carbon=\"".htmlspecialchars($truemmerfeld[0])."\" aluminium=\"".htmlspecialchars($truemmerfeld[1])."\" wolfram=\"".htmlspecialchars($truemmerfeld[2])."\" radium=\"".htmlspecialchars($truemmerfeld[3])."\" />\n";
					echo "\t\t</planet>\n";
				}

				echo "\t</system>\n";
			}
	}

	echo "</xmlresponse>\n";
?>
