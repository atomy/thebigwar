<?php
if(!isset($_SERVER['DOCUMENT_ROOT']) || strlen($_SERVER['DOCUMENT_ROOT']) <= 0)
    $_SERVER['DOCUMENT_ROOT'] = getcwd();
    
require_once($_SERVER['DOCUMENT_ROOT'].'/include/config_inc.php');
require( $_SERVER['DOCUMENT_ROOT'].'/login/scripts/include.php' );

	if(isset($_GET['cancel']))
	{
		# Flotte zurueckrufen

		$flotte = Classes::Fleet($_GET['cancel']);
		if($flotte->callBack($_SESSION['username']))
			delete_request();
	}

	# Hier die Haltezeit auf Null setzen, wenn Abweisung
	if( isset( $_GET['abweisen'] ) )
	{
		
		# Flotte zurueckrufen
		$time = 0;
		$flotte = Classes::Fleet($_GET['abweisen']);
		if($flotte->addHoldTime($time))
			delete_request();
	}


	function makeFleetString($user, $fleet)
	{
		$exp = explode("/", $user);
		$userresolved = $exp[0];
		$user = Classes::User($userresolved);
		$flotte_string = array();
		foreach($fleet as $id=>$anzahl)
		{
			if($anzahl > 0 && ($item_info = $user->getItemInfo($id, 'schiffe')))
				$flotte_string[] = utf8_htmlentities($item_info['name']).': '.ths($anzahl);
		}
		$flotte_string = implode('; ', $flotte_string);
		return $flotte_string;
	}

	login_gui::html_head();
     if(!$me->userLocked())
     	{
?>
<ul id="planeten-umbenennen">
	<li><a href="scripts/rename.php?<?php echo htmlentities(session_name().'='.urlencode(session_id()))?>" title="Planeten umbenennen/aufgeben" accesskey="u" tabindex="2"><kbd>u</kbd>mbenennen</a></li>
</ul>
<?php
         }
	if(!$me->checkSetting('notify'))
	{
		global $message_type_names;

		$ncount = array(
			1 => 0,
			2 => 0,
			3 => 0,
			4 => 0,
			5 => 0,
			6 => 0,
			7 => 0
		);
		$ges_ncount = 0;

		$cats = $me->getMessageCategoriesList();
		foreach($cats as $cat)
		{
			$message_ids = $me->getMessagesList($cat);
			foreach($message_ids as $message)
			{
				$status = $me->checkMessageStatus($message, $cat);
				if($status == 1 && $cat != 8)
				{
					$ncount[$cat]++;
					$ges_ncount++;
				}
			}
		}

		if($ges_ncount > 0)
		{
			$title = array();
			$link = 'nachrichten.php';
			foreach($ncount as $type=>$count)
			{
				if($count > 0)
					$title[] = utf8_htmlentities($message_type_names[$type]).':&nbsp;'.htmlentities($count);
				if($count == $ges_ncount)
					$link .= '?type='.urlencode($type);
			}
			$title = implode('; ', $title);
			if(strpos($link, '?') === false)
				$link .= '?';
			else
				$link .= '&';
			$link .= urlencode(session_name()).'='.urlencode(session_id());
?>
<p class="neue-nachrichten">
	<a href="<?php echo htmlentities('http://'.$_SERVER['HTTP_HOST'].h_root.'/login/'.$link)?>" title="<?php echo $title?>">Sie haben <?php echo htmlentities($ges_ncount)?> neue <kbd>N</kbd>achricht<?php echo ($ges_ncount != 1) ? 'en' : ''?>.</a>
</p>
<?php
		}
	}

	$active_planet = $me->getActivePlanet();
	$flotten = $me->getFleetsList();
	if(count($flotten) > 0 && !$me->userLocked() || count($flotten) > 0 && isset($_SESSION['admin_username']))
	{
?>
<h2>Flottenbewegungen</h2>
<dl id="flotten">
<?php
		# Flotten sortieren
		$flotten_sorted = array();
		foreach($flotten as $flotte)
		{
			$fl = Classes::Fleet($flotte, false);
			if(!$fl->getStatus() || count($fl->getUsersList()) <= 0) continue;
			$flotten_sorted[$flotte] = $fl->getArrivalTime();
			
		}

		asort($flotten_sorted, SORT_NUMERIC);

		$countdowns = array();
		foreach($flotten_sorted as $flotte=>$arrival_time)
		{
			$fl = Classes::Fleet($flotte);
			if(!$fl->getStatus()) continue;
			$users = $fl->getUsersList();
			if(count($users) <= 0) continue;
			$hold = $fl->getHoldTime();
			$save = $fl->getSaveFlight();
			#var_dump($users);
			#var_dump($usersresolved);

			$me_in_users = array_search($_SESSION['username'], $users);
			if($me_in_users !== false)
			{
				$first_user = $_SESSION['username'];
				unset($users[$me_in_users]);
			}
			else $first_user = array_shift($users);

			if($me_in_users !== false) $string = 'Ihre';
			else $string = 'Eine';

			$string .= ' <span class="beschreibung schiffe" title="'.makeFleetString($first_user, $fl->getFleetList($first_user)).'">Flotte</span> kommt ';


			if(count($users) > 0)
			{
				$other_strings = array();
				foreach($users as $user)
				{
					$exp = explode("/", $user);
					$userresolved = $exp[0];
				
					
					$from_pos = $fl->from($user);
					$from_array = explode(':', $from_pos);
					$from_galaxy = Classes::Galaxy($from_array[0]);
					$planet_name = $from_galaxy->getPlanetName($from_array[1], $from_array[2]);
					$other_strings[] = 'mit einer <span class="beschreibung schiffe" title="'.makeFleetString($user, $fl->getFleetList($user)).'">Flotte</span> vom Planeten &bdquo; <span class="fleetoverview planetname">'.utf8_htmlentities($planet_name).'</span>&ldquo; <span class="fleetoverview planetpos">('.$from_pos.'</span>, Eigentümer: <span class="fleetoverview planetowner">'.utf8_htmlentities($userresolved).'</span>)';

				}
				if(count($other_strings) == 1)
					$string .= $other_strings[0];
				else
				{
					$last_string = array_pop($other_strings);
					$string .= ' '.implode(' und ', $other_strings).' und '.$last_string;
				}
			}

			$string .= ' ';

			$from_pos = $fl->getLastTarget($first_user);
			if($me->isOwnPlanet($from_pos))
			{
				$active_planet2 = $me->getActivePlanet();
				$me->setActivePlanet($me->getPlanetByPos($from_pos));
				$string .= 'von Ihrem Planeten &bdquo; <span class="fleetoverview planetname">'.utf8_htmlentities($me->planetName()).'</span>&ldquo; (<span class="fleetoverview planetpos">'.$from_pos.'</span>)';
				$me->setActivePlanet($active_planet2);
			}
			else
			{
				$from_array = explode(':', $from_pos);
				$from_galaxy = Classes::Galaxy($from_array[0]);
				$planet_owner = $from_galaxy->getPlanetOwner($from_array[1], $from_array[2]);
				if($planet_owner)
				{
					$planet_name = $from_galaxy->getPlanetName($from_array[1], $from_array[2]);
					$string .= 'vom Planeten &bdquo; <span class="fleetoverview planetname">'.utf8_htmlentities($planet_name).'</span> &ldquo; (<span class="fleetoverview planetpos">'.$from_pos.'</span>, Eigentümer: <span class="fleetoverview planetowner">'.utf8_htmlentities($planet_owner).'</span>)';
				}
				else $string .= 'vom Planeten <span class="fleetoverview planetpos">'.$from_pos.'</span> (unbesiedelt)';
			}

			$string .= ' und erreicht ';

			$to_pos = $fl->getCurrentTarget();
			if($me->isOwnPlanet($to_pos))
			{
				$active_planet2 = $me->getActivePlanet();
				$me->setActivePlanet($me->getPlanetByPos($to_pos));
				$string .= ' Ihren Planeten &bdquo; <span class="fleetoverview planetname">'.utf8_htmlentities($me->planetName()).'</span> &ldquo; (<span class="fleetoverview planetname">'.$to_pos.'</span>).';
				$me->setActivePlanet($active_planet2);
			}
			else
			{
				$to_array = explode(':', $to_pos);
				$to_galaxy = Classes::Galaxy($to_array[0]);
				$planet_owner = $to_galaxy->getPlanetOwner($to_array[1], $to_array[2]);
				if($planet_owner)
				{
					$planet_name = $to_galaxy->getPlanetName($to_array[1], $to_array[2]);
					$string .= ' den Planeten &bdquo; <span class="fleetoverview planetname">'.utf8_htmlentities($planet_name).'</span> &ldquo; (<span class="fleetoverview targetpos">'.$to_pos.'</span>, Eigentümer: <span class="fleetoverview planetowner">'.utf8_htmlentities($planet_owner).'</span>).';
				}
				else $string .= ' den Planeten <span class="fleetoverview planetpos">'.$to_pos.'</span> (unbesiedelt).';
			}

			if($fl->isFlyingBack() || $hold == -1)
				{
				$string .= ' Ihr Auftrag lautete ';
                           }
			
			if(!$fl->isFlyingBack() && ($hold != -1))
				{
				$string .= ' Ihr Auftrag lautet ';
				}

			$ress = array(array(0, 0, 0, 0, 0), array());
			$users = $fl->getUsersList();
			foreach($users as $user)
			{
				$this_ress = $fl->getTransport($user);
                $verb =($me->isVerbuendet($user));
				if(isset($this_ress[0][0])) $ress[0][0] += $this_ress[0][0];
				if(isset($this_ress[0][1])) $ress[0][1] += $this_ress[0][1];
				if(isset($this_ress[0][2])) $ress[0][2] += $this_ress[0][2];
				if(isset($this_ress[0][3])) $ress[0][3] += $this_ress[0][3];
				if(isset($this_ress[0][4])) $ress[0][4] += $this_ress[0][4];
				foreach($this_ress[1] as $id=>$count)
				{
					if(isset($ress[1][$id])) $ress[1][$id] += $count;
					else $ress[1][$id] = $count;
				}
			}
			$ress_string = array();
			if(array_sum($ress[0]) > 0 && $verb || $me_in_users !== false)
			{
				if(isset($ress[0][0])) $ress_string[] = 'Carbon: '.ths($ress[0][0]);
				if(isset($ress[0][1])) $ress_string[] = 'Aluminium: '.ths($ress[0][1]);
				if(isset($ress[0][2])) $ress_string[] = 'Wolfram: '.ths($ress[0][2]);
				if(isset($ress[0][3])) $ress_string[] = 'Radium: '.ths($ress[0][3]);
				if(isset($ress[0][4])) $ress_string[] = 'Tritium: '.ths($ress[0][4]);
            		}
            		else $ress_string[] = '';

			foreach($ress[1] as $id=>$anzahl)
			{
				if($anzahl > 0 && ($item_info = $me->getItemInfo($id, 'roboter')))
					$ress_string[] = utf8_htmlentities($item_info['name']).': '.ths($anzahl);
			}

			$ress_string = implode(', ', $ress_string);

			$string .= '<span class="beschreibung transport"';
			if(strlen($ress_string) > 0) $string .= ' title="'.$ress_string.'"';
			$string .= '>';

			$type = $fl->getCurrentType();
			if(isset($type_names[$type]))
				$string .= htmlentities($type_names[$type]);
			else
				$string .= utf8_htmlentities($type);
			$string .= '</span>.';

			$to_pos = $fl->getCurrentTarget();
			$hold = $fl->getHoldTime();
			if($hold > 0 && !$fl->isFlyingBack()) $string .= ' Haltezeit: ' .$hold .' Sekunden.';
			if($hold == -1) $string .= ' <span class="beschreibung schiffe" title="Halteflotte">Flotte wird gehalten!</span> ';
			if($save == 1 && $me_in_users !== false) $string .= ' Die Flotte befindet sich auf Saveflug!';

			if($hold !== -1)
			{
				$handel = array(array(0, 0, 0, 0, 0), array());
				foreach($users as $user)
				{
					$this_handel = $fl->getHandel($user);
					if(isset($this_handel[0][0])) $handel[0][0] += $this_handel[0][0];
					if(isset($this_handel[0][1])) $handel[0][1] += $this_handel[0][1];
					if(isset($this_handel[0][2])) $handel[0][2] += $this_handel[0][2];
					if(isset($this_handel[0][3])) $handel[0][3] += $this_handel[0][3];
					if(isset($this_handel[0][4])) $handel[0][4] += $this_handel[0][4];
					foreach($this_handel[1] as $id=>$count)
					{
						if(isset($handel[1][$id])) $handel[1][$id] += $count;
						else $handel[1][$id] = $count;
					}
				}

				if(array_sum($handel[0]) > 0 || array_sum($handel[1]) > 0)
				{   	
					$string .= ' Es wird ein <span class="beschreibung handel" title="';
			  	  	$string .= 'Es wurden noch keine Waren an Bord genommen.';
                			if(array_sum($handel[1]) > 0)
					$string .= '; '.makeItemsString($handel[1]);
					$string .= '">Handel</span> durchgeführt werden.';
				}
			}
			#neuer Part AKS
			$user_list = $fl->getUsersList();
	  	       $first_user = Classes::User(array_shift($user_list));
 	  	       if($first_user->getStatus() && $me_in_users !== false)
 	 	       {
 	  	           $fleet_passwd = $first_user->getFleetPasswd($fl->getName());
 	  	           if($fleet_passwd !== null)
 	  	           $string .= " Das Verbundflottenpasswort lautet <span class=\"flottenpasswd\">".htmlspecialchars($fleet_passwd)."</span>.";
 	  	       }
?>
	<dt class="<?php echo ($me_in_users !== false) ? 'eigen' : 'fremd'?> type-<?php echo utf8_htmlentities($fl->getCurrentType())?> <?php echo $fl->isFlyingBack() ? 'rueck' : 'hin'?>flug">
		<?php echo $string."\n"?>
<?php
			if($hold !== -1 && $fl->getCurrentType() == 4 && !$fl->isFlyingBack() && $me->isOwnPlanet($fl->getCurrentTarget()) && !$me->userLocked() && !$me->umode())
			{
?>
				<div class="handel"><a href="flotten_actions.php?action=handel&amp;id=<?php echo htmlspecialchars(urlencode($flotte))?>&amp;<?php echo htmlspecialchars(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Geben Sie dieser Flotte Ladung mit auf den R�ckweg">Handel</a></div>
<?php
 	  	       }
                     if($fl->getCurrentType() == 3 && !$fl->isFlyingBack() && array_search($me->getName(), $fl->getUsersList()) === 0)
 	  	       { 	  	 
?>
 	  	                 <div class="beschreibung schiffe"><a href="flotten_actions.php?action=buendnisangriff&amp;id=<?php echo htmlspecialchars(urlencode($flotte))?>&amp;<?php echo htmlspecialchars(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Erlauben Sie anderen Spielern, der Flotte eigene Schiffe beizusteuern.">Bündnisangriff</a></div>
<?php
			}
			if(($hold > 0) && $me->isOwnPlanet($to_pos) && ($me_in_users == false))
			{
?>
				<div class="abweisen"><a href="index.php?abweisen=<?php echo htmlentities(urlencode($flotte))?>&amp;<?php echo htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" class="abbrechen">Halteposition verweigern.</a></div>
<?php
			}
?>

	</dt>
				<dd class="<?php echo ($me_in_users !== false) ? 'eigen' : 'fremd'?> type-<?php echo utf8_htmlentities($fl->getCurrentType())?> <?php echo $fl->isFlyingBack() ? 'rueck' : 'hin'?>flug" id="restbauzeit-<?php echo utf8_htmlentities($flotte)?>">Ankunft: <?php echo date('H:i:s, Y-m-d', $arrival_time)?> --<?phpprint $arrival_time?>-- (Serverzeit)<?php if(!$fl->isFlyingBack() && ($me_in_users !== false)){?>, <a href="index.php?cancel=<?php echo htmlentities(urlencode($flotte))?>&amp;<?php echo htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" class="abbrechen">Abbrechen</a><?php }?></dd>
<?php
			$countdowns[] = array($flotte, $arrival_time, ($fl->isFlyingBack() || ($me_in_users === false)));
		}
?>
</dl>
<?php
		if(count($countdowns) > 0)
		{
?>
<script type="text/javascript">
<?php
			foreach($countdowns as $countdown)
			{
?>
	init_countdown('<?php echo $countdown[0]?>', <?php echo $countdown[1]?>, <?php echo $countdown[2] ? 'false' : 'true'?>, <?php echo global_setting("EVENTHANDLER_INTERVAL")?>);
<?php
			}
?>
</script>
<?php
		}
	}
?>
<h2 id="planeten">Planeten</h2>
<ol id="planets">
<?php
	$me->setActivePlanet($active_planet);
	$show_building = $me->checkSetting('show_building');
	$countdowns = array();
	$tabindex = 3;
	$planets = $me->getPlanetsList();
	foreach($planets as $planet)
	{
		$me->setActivePlanet($planet);
		$class = $me->getPlanetClass();
?>
	<li class="planet-<?php echo htmlentities($class)?><?php echo ($planet == $active_planet) ? ' active' : ''?>"><?php echo ($planet != $active_planet) ? '<a href="index.php?planet='.htmlentities(urlencode($planet).'&'.urlencode(session_name()).'='.urlencode(session_id())).'" tabindex="'.($tabindex++).'">' : ''?><?php echo utf8_htmlentities($me->planetName())?><?php echo ($planet != $active_planet) ? '</a>' : ''?> <span class="koords">(<?php echo utf8_htmlentities($me->getPosString())?>)</span>
		<dl class="planet-info">
			<dt class="c-felder">Felder</dt>
			<dd class="c-felder"><?php echo ths($me->getUsedFields())?> <span class="gesamtgroesse">(<?php echo ths($me->getTotalFields())?>)</span></dd>
<?php
		if($show_building['gebaeude'])
		{
?>
			<dt class="c-gebaeudebau">Gebäudebau</dt>
<?php
			$building_gebaeude = $me->checkBuildingThing('gebaeude');
			if($building_gebaeude && !$me->umode())
			{
				$item_info = $me->getItemInfo($building_gebaeude[0], 'gebaeude');
?>
			<dd class="c-gebaeudebau"><?php echo utf8_htmlentities($item_info['name'])?> <span class="restbauzeit" id="restbauzeit-ge-<?php echo utf8_htmlentities($planet)?>">Fertigstellung: <?php echo date('H:i:s, Y-m-d', $building_gebaeude[1])?> (Serverzeit)</span></dd>
<?php
				$countdowns[] = array('ge-'.$planet, $building_gebaeude[1]);
			}
			elseif($me->getRemainingFields() <= 0 && !$me->umode())
			{
?>
			<dd class="c-gebaeudebau ausgebaut">Ausgebaut</dd>
<?php
			}
			elseif($building_gebaeude && $me->umode())
			{
				$item_info = $me->getItemInfo($building_gebaeude[0], 'gebaeude');
?>
				<dd class="c-gebaeudebau gelangweilt"><?php echo utf8_htmlentities($item_info['name'])?> im Urlaubsmodus</dd><?php
			}

			else
			{
?>
			<dd class="c-gebaeudebau gelangweilt"><a href="gebaeude.php?planet=<?php echo htmlentities(urlencode($me->getActivePlanet()))?>&amp;<?php echo htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Gelangweilter Gebäudebau auf Planet: &bdquo;<?php echo utf8_htmlentities($me->planetName())?>&ldquo; (<?php echo utf8_htmlentities($me->getPosString())?>)">Gelangweilt</a></dd>
<?php
			}
		}

		if($show_building['forschung'] && $me->getItemLevel('B8', 'gebaeude') > 0)
		{
?>

			<dt class="c-forschung">Forschung</dt>
<?php
			$building_forschung = $me->checkBuildingThing('forschung');
			$buildingb = $me->checkBuildingThing( 'gebaeude' );
			
			if ( $buildingb[0] == 'B8' )
			{
				?>
					<dd class="c-forschung im-ausbau">Im Ausbau</dd>
				<?php
			}
			else if( $building_forschung && !$me->umode() )
			{
				$item_info = $me->getItemInfo($building_forschung[0], 'forschung');
?>
			<dd class="c-forschung"><?php echo utf8_htmlentities($item_info['name'])?> <span id="restbauzeit-fo-<?php echo utf8_htmlentities($planet)?>">Fertigstellung: <?php echo date('H:i:s, Y-m-d', $building_forschung[1])?> (Serverzeit)</span></dd>
<?php
				$countdowns[] = array('fo-'.$planet, $building_forschung[1]);
			}
			elseif($building_forschung && $me->umode())
			{
				$item_info = $me->getItemInfo($building_forschung[0], 'forschung');
?>
				<dd class="c-forschung gelangweilt"><?php echo utf8_htmlentities($item_info['name'])?> im Urlaubsmodus</dd><?php
			}

			else
			{
?>
			<dd class="c-forschung gelangweilt"><a href="forschung.php?planet=<?php echo htmlentities(urlencode($me->getActivePlanet()))?>&amp;<?php echo htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Gelangweilte Forschung auf Planet: &bdquo;<?php echo utf8_htmlentities($me->planetName())?>&ldquo; (<?php echo utf8_htmlentities($me->getPosString())?>)">Gelangweilt</a></dd>
<?php
			}
		}

		if($show_building['roboter'] && $me->getItemLevel('B9', 'gebaeude') > 0)
		{
?>

			<dt class="c-roboter">Roboter</dt>
<?php
			$building = $me->checkBuildingThing('roboter');
			$buildingb = $me->checkBuildingThing( 'gebaeude' );
			
			if ( $buildingb[0] == 'B9' )
			{
				?>
					<dd class="c-roboter im-ausbau">Im Ausbau</dd>
				<?php
			}
			else if( $building && !$me->umode() )
			{
				switch($show_building['roboter'])
				{
					case 3:
						$last_building = array_pop($building);
						$item_info = $me->getItemInfo($last_building[0], 'roboter');
						$finishing_time = $last_building[1]+$last_building[2]*$last_building[3];
?>
			<dd class="c-roboter">(<?php echo utf8_htmlentities($item_info['name'])?>) <span id="restbauzeit-ro-<?php echo utf8_htmlentities($planet)?>">Fertigstellung: <?php echo date('H:i:s, Y-m-d', $finishing_time)?> (Serverzeit)</span></dd>
<?php
						break;
					case 2:
						$first_building = array_shift($building);
						$item_info = $me->getItemInfo($first_building[0], 'roboter');
						$finishing_time = $first_building[1]+$first_building[2]*$first_building[3];
?>
			<dd class="c-roboter"><?php echo utf8_htmlentities($item_info['name'])?> <span class="anzahl">(<?php echo ths($first_building[2])?>)</span> <span id="restbauzeit-ro-<?php echo utf8_htmlentities($planet)?>">Fertigstellung: <?php echo date('H:i:s, Y-m-d', $finishing_time)?> (Serverzeit)</span></dd>
<?php
						break;
					case 1:
						$first_building = array_shift($building);
						$item_info = $me->getItemInfo($first_building[0], 'roboter');
						$finishing_time = $first_building[1]+$first_building[3];
?>
			<dd class="c-roboter"><?php echo utf8_htmlentities($item_info['name'])?> <span id="restbauzeit-ro-<?php echo utf8_htmlentities($planet)?>">Fertigstellung: <?php echo date('H:i:s, Y-m-d', $finishing_time)?> (Serverzeit)</span></dd>
<?php
						break;
				}
				$countdowns[] = array('ro-'.$planet, $finishing_time);
			}
			elseif($building && $me->umode())
			{
				$first_building = array_pop($building);
				$item_info = $me->getItemInfo($first_building[0], 'roboter');
?>
				<dd class="c-roboter gelangweilt"><?php echo utf8_htmlentities($item_info['name'])?> im Urlaubsmodus</dd><?php
			}

			else
			{
?>
			<dd class="c-roboter gelangweilt"><a href="roboter.php?planet=<?php echo htmlentities(urlencode($me->getActivePlanet()))?>&amp;<?php echo htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Gelangweilte Roboterfabrik auf Planet: &bdquo;<?php echo utf8_htmlentities($me->planetName())?>&ldquo; (<?php echo utf8_htmlentities($me->getPosString())?>)">Gelangweilt</a></dd>
<?php
			}
		}

		if($show_building['schiffe'] && $me->getItemLevel('B10', 'gebaeude') > 0)
		{
?>

			<dt class="c-schiffe">Schiffe</dt>
<?php
			$building = $me->checkBuildingThing('schiffe');
			$buildingb = $me->checkBuildingThing( 'gebaeude' );
			
			if ( $buildingb[0] == 'B10' )
			{
				?>
					<dd class="c-schiffe im-ausbau">Im Ausbau</dd>
				<?php
			}
			else if( $building && !$me->umode() )
			{
				switch($show_building['schiffe'])
				{
					case 3:
						$last_building = array_pop($building);
						$item_info = $me->getItemInfo($last_building[0], 'schiffe');
						$finishing_time = $last_building[1]+$last_building[2]*$last_building[3];
?>
			<dd class="c-roboter">(<?php echo utf8_htmlentities($item_info['name'])?>) <span id="restbauzeit-ro-<?php echo utf8_htmlentities($planet)?>">Fertigstellung: <?php echo date('H:i:s, Y-m-d', $finishing_time)?> (Serverzeit)</span></dd>
<?php
						break;
					case 2:
						$first_building = array_shift($building);
						$item_info = $me->getItemInfo($first_building[0], 'schiffe');
						$finishing_time = $first_building[1]+$first_building[2]*$first_building[3];
?>
			<dd class="c-schiffe"><?php echo utf8_htmlentities($item_info['name'])?> <span class="anzahl">(<?php echo ths($first_building[2])?>)</span> <span id="restbauzeit-sc-<?php echo utf8_htmlentities($planet)?>">Fertigstellung: <?php echo date('H:i:s, Y-m-d', $finishing_time)?> (Serverzeit)</span></dd>
<?php
						break;
					case 1:
						$first_building = array_shift($building);
						$item_info = $me->getItemInfo($first_building[0], 'schiffe');
						$finishing_time = $first_building[1]+$first_building[3];
?>
			<dd class="c-schiffe"><?php echo utf8_htmlentities($item_info['name'])?> <span id="restbauzeit-sc-<?php echo utf8_htmlentities($planet)?>">Fertigstellung: <?php echo date('H:i:s, Y-m-d', $finishing_time)?> (Serverzeit)</span></dd>
<?php
						break;
				}
				$countdowns[] = array('sc-'.$planet, $finishing_time);
			}
			elseif($building && $me->umode())
			{
				$first_building = array_pop($building);
				$item_info = $me->getItemInfo($first_building[0], 'schiffe');

?>
				<dd class="c-schiffe gelangweilt"><?php echo utf8_htmlentities($item_info['name'])?> im Urlaubsmodus</dd><?php
			}

			else
			{
?>
			<dd class="c-schiffe gelangweilt"><a href="schiffswerft.php?planet=<?php echo htmlentities(urlencode($me->getActivePlanet()))?>&amp;<?php echo htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Gelangweilte Schiffswerft auf Planet: &bdquo;<?php echo utf8_htmlentities($me->planetName())?>&ldquo; (<?php echo utf8_htmlentities($me->getPosString())?>)">Gelangweilt</a></dd>
<?php
			}
		}

		if( $show_building['verteidigung'] && $me->getItemLevel('B10', 'gebaeude') > 0)
		{
?>

			<dt class="c-verteidigung">Verteidigung</dt>
<?php
			$building = $me->checkBuildingThing( 'verteidigung' );
			$buildingb = $me->checkBuildingThing( 'gebaeude' );
			
			if ( $buildingb[0] == 'B10' )
			{
				?>
					<dd class="c-verteidgung im-ausbau">Im Ausbau</dd>
				<?php
			}
			else if( $building && !$me->umode() )
			{
				switch($show_building['verteidigung'])
				{
					case 3:
						$last_building = array_pop($building);
						$item_info = $me->getItemInfo($last_building[0], 'verteidigung');
						$finishing_time = $last_building[1]+$last_building[2]*$last_building[3];
?>
			<dd class="c-roboter">(<?php echo utf8_htmlentities($item_info['name'])?>) <span id="restbauzeit-ro-<?php echo utf8_htmlentities($planet)?>">Fertigstellung: <?php echo date('H:i:s, Y-m-d', $finishing_time)?> (Serverzeit)</span></dd>
<?php
						break;
					case 2:
						$first_building = array_shift($building);
						$item_info = $me->getItemInfo($first_building[0], 'verteidigung');
						$finishing_time = $first_building[1]+$first_building[2]*$first_building[3];
?>
			<dd class="c-verteidigung"><?php echo utf8_htmlentities($item_info['name'])?> <span class="anzahl">(<?php echo ths($first_building[2])?>)</span> <span id="restbauzeit-ve-<?php echo utf8_htmlentities($planet)?>">Fertigstellung: <?php echo date('H:i:s, Y-m-d', $finishing_time)?> (Serverzeit)</span></dd>
<?php
						break;
					case 1:
						$first_building = array_shift($building);
						$item_info = $me->getItemInfo($first_building[0], 'verteidigung');
						$finishing_time = $first_building[1]+$first_building[3];
?>
			<dd class="c-verteidigung"><?php echo utf8_htmlentities($item_info['name'])?> <span id="restbauzeit-ve-<?php echo utf8_htmlentities($planet)?>">Fertigstellung: <?php echo date('H:i:s, Y-m-d', $finishing_time)?> (Serverzeit)</span></dd>
<?php
						break;
				}
				$countdowns[] = array('ve-'.$planet, $finishing_time);
			}
			else if( $building && $me->umode() )
			{
				$first_building = array_pop($building);
				$item_info = $me->getItemInfo($first_building[0], 'verteidigung');

?>
				<dd class="c-verteidgung gelangweilt"><?php echo utf8_htmlentities($item_info['name'])?> im Urlaubsmodus</dd><?php
			}

			else
			{
?>
			<dd class="c-verteidigung gelangweilt"><a href="verteidigung.php?planet=<?php echo htmlentities(urlencode($me->getActivePlanet()))?>&amp;<?php echo htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Gelangweilte Verteidigung auf Planet: &bdquo;<?php echo utf8_htmlentities($me->planetName())?>&ldquo; (<?php echo utf8_htmlentities($me->getPosString())?>)">Gelangweilt</a></dd>
<?php
			}
		}
?>
		</dl>
	</li>
<?php
	}

	$me->setActivePlanet($active_planet);
?>
</ol>
<?php
	if(count($countdowns) > 0)
	{
?>
<script type="text/javascript">
<?php
		foreach($countdowns as $countdown)
		{
?>
	init_countdown('<?php echo $countdown[0]?>', <?php echo $countdown[1]?>, false);
<?php
		}
?>
</script>
<?php
	}
?>
<h2 id="punkte">Punkte</h2>
<dl class="punkte">
	<dt class="c-gebaeude">Gebäude</dt>
	<dd class="c-gebaeude"><?php echo ths($me->getScores(0))?></dd>

	<dt class="c-forschung">Forschung</dt>
	<dd class="c-forschung"><?php echo ths($me->getScores(1))?></dd>

	<dt class="c-roboter">Roboter</dt>
	<dd class="c-roboter"><?php echo ths($me->getScores(2))?></dd>

	<dt class="c-flotte">Flotte</dt>
	<dd class="c-flotte"><?php echo ths($me->getScores(3))?></dd>

	<dt class="c-verteidigung">Verteidigung</dt>
	<dd class="c-verteidigung"><?php echo ths($me->getScores(4))?></dd>

	<dt class="c-flugerfahrung">Flugerfahrung</dt>
	<dd class="c-flugerfahrung"><?php echo ths($me->getScores(5))?></dd>

	<dt class="c-kampferfahrung">Kampferfahrung</dt>
	<dd class="c-kampferfahrung"><?php echo ths($me->getScores(6))?></dd>

	<dt class="c-gesamt">Gesamt</dt>
	<dd class="c-gesamt"><?php echo ths($me->getScores())?> <span class="gesamt-spieler">(Platz <?php echo ths($me->getRank())?> von <?php echo ths(getUsersCount())?>)</span></dd>
</dl>

<?php

	login_gui::html_foot();

?>
