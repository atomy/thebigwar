<?php
	if(isset($_GET['action'])) define('ignore_action', true);

	require_once( '../include/config_inc.php' );
	require( TBW_ROOT.'login/scripts/include.php' );

	if(!defined('ajax')) login_gui::html_head();

	$show_versenden = true;

	$max_flotten = $me->getMaxParallelFleets();
	$my_flotten = $me->getCurrentParallelFleets();

	__autoload('Fleet');
	__autoload('Galaxy');

	if(!defined('ajax'))
	{
?>
<h2>Flotten</h2>
<?php
	}

	$fast_action = false;
	if(isset($_GET['action_galaxy']) && isset($_GET['action_system']) && isset($_GET['action_planet']) && isset($_GET['action']) && ($_GET['action'] == 'spionage' || $_GET['action'] == 'besiedeln' || $_GET['action'] == 'sammeln' || $_GET['action'] == 'shortcut'))
	{
		if($_GET['action'] == 'shortcut')
		{
			$result = $me->addPosShortcut($_GET['action_galaxy'].':'.$_GET['action_system'].':'.$_GET['action_planet']);
			if($result === 2) $return = array('nothingtodo', 'Dieser Planet ist schon in Ihren Lesezeichen.');
			elseif($result) $return = array('successful', 'Der Planet wurde zu den Lesezeichen hinzugefügt.');
			else $return = array('error', 'Datenbankfehler &#40;1039&#41;');

			if(defined('ajax')) return $return;
			elseif($return[0] == 'error')
			{
?>
<p class="<?=htmlspecialchars($return[0])?>"><?=htmlspecialchars($return[1])?></p>
<?php
				login_gui::html_foot();
				exit();
			}
			else
			{
				header($_SERVER['SERVER_PROTOCOL'].' 204 No Content');
				ob_end_clean();
				die();
			}
		}

		$fast_action = true;

		$galaxy = Classes::Galaxy($_GET['action_galaxy']);
		$planet_owner = $galaxy->getPlanetOwner($_GET['action_system'], $_GET['action_planet']);
		if($my_flotten >= $max_flotten)
		{
			if(defined('ajax'))
				return array('error', 'Maximale Flottenzahl erreicht.');
?>
<p class="error">
	Maximale Flottenzahl erreicht.
</p>
<?php
			login_gui::html_foot();
			exit();
		}
		else if ( strtolower( $me->getName() ) == GLOBAL_DEMOACCNAME || strtolower( $planet_owner ) == GLOBAL_DEMOACCNAME )
		{
			if( defined('ajax') ) 
				return array('error', 'DEMO-Account');
?>

<p class="error">
	Das Versenden von Flotten ist im Zusammenhang mit dem Demo-Account nicht moeglich.
</p>

<?php
			login_gui::html_foot();
			exit();
		}		
		else
		{
			$_POST['galaxie'] = $_GET['action_galaxy'];
			$_POST['system'] = $_GET['action_system'];
			$_POST['planet'] = $_GET['action_planet'];

			$_POST['speed'] = 1;

			if($_GET['action'] == 'spionage')
			{
				$_POST['auftrag'] = 5;
				$_POST['flotte'] = array('S5' => 1);
				if($planet_owner && !$me->isVerbuendet($planet_owner))
					$_POST['flotte']['S5'] = $me->checkSetting('sonden');
				if($me->getItemLevel('S5', 'schiffe') < 1)
				{
					if(defined('ajax')) return array('error', 'Keine Spionagesonden vorhanden.');
?>
<p class="error">
	Keine Spionagesonden vorhanden.
</p>
<?php
					login_gui::html_foot();
					exit();
				}
			}
			elseif($_GET['action'] == 'besiedeln')
			{
				$_POST['auftrag'] = 1;
				$_POST['flotte'] = array('S6' => 1);
				if($me->getItemLevel('S6', 'schiffe') < 1)
				{
					if(defined('ajax')) return array('error', 'Kein Besiedelungsschiff vorhanden.');
?>
<p class="error">
	Kein Besiedelungsschiff vorhanden.
</p>
<?php
					login_gui::html_foot();
					exit();
				}
			}
			elseif($_GET['action'] == 'sammeln')
			{
				$_POST['auftrag'] = 2;

				$truemmerfeld = truemmerfeld::get($_GET['action_galaxy'], $_GET['action_system'], $_GET['action_planet']);

				$anzahl = 0;
				if($truemmerfeld !== false)
				{
					# Transportkapazitaet eines Sammlers
					$sammler_info = $me->getItemInfo('S3', 'schiffe');
					$transport = $sammler_info['trans'][0];

					$anzahl = ceil(array_sum($truemmerfeld)/$transport);
				}
				if($anzahl <= 0)
					$anzahl = 1;

				$_POST['flotte'] = array('S3' => $anzahl);

				if($me->getItemLevel('S3', 'schiffe') < 1)
				{
					if(defined('ajax')) return array('error', 'Keine Sammler vorhanden.');
?>
<p class="error">
	Keine Sammler vorhanden.
</p>
<?php
				}
			}
		}
	}
	     $buendnisflug = (isset($_POST["buendnisflug"]) && $_POST["buendnisflug"]);	  	 
 	
	if($me->permissionToAct() && $my_flotten < $max_flotten && isset($_POST['flotte']) && is_array($_POST['flotte']) && ((!$buendnisflug && isset($_POST['galaxie']) && isset($_POST['system']) && isset($_POST['planet'])) || ($buendnisflug && isset($_POST["buendnis_benutzername"]) && isset($_POST["buendnis_flottenpasswort"]))))
	{
		$types = array();
		foreach($_POST['flotte'] as $id=>$anzahl)
		{
			$_POST['flotte'][$id] = $anzahl = (int) $anzahl;
			$item_info = $me->getItemInfo($id, 'schiffe');
			if(!$item_info)
			{
				unset($_POST['flotte'][$id]);
				continue;
			}
			if($anzahl > $item_info['level'])
				$_POST['flotte'][$id] = $anzahl = $item_info['level'];
			if($anzahl < 1)
			{
				unset($_POST['flotte'][$id]);
				continue;
			}
			$show_versenden = false;

			foreach($item_info['types'] as $type)
			{
				if(!in_array($type, $types)) $types[] = $type;
			}
		}
		
		if($buendnisflug)
	  	{
 	  	                         $buendnisflug_user = Classes::User($_POST["buendnis_benutzername"]);
 	  	                         if(!$buendnisflug_user->getStatus())
 	  	                                 $show_versenden = true;
 	  	                         else
 	  	                         {
 	  	                                 $buendnisflug_id = $buendnisflug_user->resolveFleetPasswd($_POST["buendnis_flottenpasswort"]);
 	  	                                 if($buendnisflug_id === null)
 	  	                                         $show_versenden = true;
 	  	                                 else
 	  	                                 {
 	  	                                         $buendnisflug_fleet = Classes::Fleet($buendnisflug_id);
 	  	                                         if(!$buendnisflug_fleet->getStatus())
 	  	                                                 $show_versenden = true;
 	  	                                 }
 	  	                         }
 	  	                 }

             elseif(!preg_match("/^[1-9]([0-9]*)$/", $_POST['galaxie']) || !preg_match("/^[1-9]([0-9]*)$/", $_POST['system']) || !preg_match("/^[1-9]([0-9]*)$/", $_POST['planet']))
		$show_versenden = true;
		if(!$show_versenden)
		{

			if($buendnisflug)
			{
			 $target_koords = explode(":", $buendnisflug_fleet->getCurrentTarget());
			$_POST["galaxie"] = $target_koords[0];
			$_POST["system"] = $target_koords[1];
			$_POST["planet"] = $target_koords[2];
			}
	              else
	  	       $target_koords = array($_POST["galaxie"], $_POST["system"], $_POST["planet"]);

			
			$galaxy_obj = Classes::Galaxy($_POST['galaxie']);
			$planet_owner = $galaxy_obj->getPlanetOwner($_POST['system'], $_POST['planet']);
			$planet_owner_flag = $galaxy_obj->getPlanetOwnerFlag($_POST['system'], $_POST['planet']);

			if($planet_owner === false) $show_versenden = true;

			if(!$show_versenden)
			{
				sort($types, SORT_NUMERIC);

				$types = array_flip($types);
				if($planet_owner && isset($types[1])) # Planet besetzt, Besiedeln nicht moeglich
					unset($types[1]);
				if(!$planet_owner) # Planet nicht besetzt
				{
					if(isset($types[3])) # Angriff nicht moeglich
						unset($types[3]);
					if(isset($types[4])) # Transport nicht moeglich
						unset($types[4]);
					if(isset($types[6])) # Stationieren nicht moeglich
						unset($types[6]);

					if(!$me->checkPlanetCount()) # Planetenlimit erreicht, Besiedeln nicht moeglich
						unset($types[1]);
				}

				$truemmerfeld = truemmerfeld::get($_POST['galaxie'], $_POST['system'], $_POST['planet']);
				if(($truemmerfeld === false || array_sum($truemmerfeld) <= 0) && isset($types[2]))
					unset($types[2]); # Kein Truemmerfeld, Sammeln nicht moeglich

				if($me->getPosString() == $_POST['galaxie'].':'.$_POST['system'].':'.$_POST['planet'] || $planet_owner_flag == 'U')
				{ 
					# Selber Planet / Urlaubsmodus, nur Sammeln
					if($truemmerfeld && isset($types[2]))
						$types = array(2 => 0);
					else
						$types = array();
				}
				elseif($planet_owner == $_SESSION['username'])
				{ 
					# Eigener Planet
					if(isset($types[3])) # Angriff nicht moeglich
						unset($types[3]);
					if(isset($types[5])) # Spionage nicht moeglich
						unset($types[5]);
				}
				else
				{ 	
					# Fremder Planet
					if(isset($types[6])) # Stationieren noch nicht moeglich
						unset($types[6]);
					if($me->isVerbuendet($planet_owner) && isset($types[3])) # Verbuendet, Angriff nicht moeglich
						unset($types[3]);
				}

				if(fleets_locked()) # Flottensperre
  		        {
                          if($planet_owner && !$me->isVerbuendet($planet_owner) && isset($types[5])) # Feindliche Spionage nicht moeglich  
  		                        unset($types[5]);
  		                  if(isset($types[3])) # Angriff nicht erlaubt
  		                        unset($types[3]);
  		        }

				if ( strtolower( $me->getName() ) == GLOBAL_DEMOACCNAME || strtolower( $planet_owner ) == GLOBAL_DEMOACCNAME )
				{
					if( defined('ajax') ) 
						return array('error', 'DEMO-Account');
?>

<p class="error">
	Das Versenden von Flotten ist im Zusammenhang mit dem Demo-Account nicht moeglich.
</p>
<?php
				}
				else if( count( $types ) <= 0 )
				{
					if(defined('ajax')) return array('error', 'Diese Aktion ist auf diesen Planeten nicht möglich.');
?>
<p class="error">
	Sie haben nicht die richtigen Schiffe ausgewählt, um diesen Planeten anzufliegen.
</p>
<?php
                    }
                    else
                    {

					$types = array_flip($types);

					# Transportkapazitaet und Antriebsstaerke berechnen
					$speed = 0;
					$transport = array(0, 0);
					$ges_count = 0;
					foreach($_POST['flotte'] as $id=>$anzahl)
					{
						$item_info = $me->getItemInfo($id);
						if($speed == 0 || ($item_info['speed'] != 0 && $item_info['speed'] < $speed))
							$speed = $item_info['speed'];

						$transport[0] += $item_info['trans'][0]*$anzahl;
						$transport[1] += $item_info['trans'][1]*$anzahl;
						$ges_count += $anzahl;
					}
					
					$show_form2 = true;
					
					if(isset($_POST['auftrag']) || isset($_POST['buendnisflug1']))
					{
						$owner_obj = Classes::User($planet_owner);

						if(isset($_POST['buendnisflug1']))
						{
							$exp = explode("/", $_POST['buendnisflug1']);
							$exp[0] = ereg_replace("&nbsp", ' ', $exp[0]);

							$_POST["buendnis_benutzername"] = $exp[0];
							$_POST["buendnis_flottenpasswort"] = $exp[1];
							$buendnisflug = true;
						}
						
						if( $buendnisflug )
	  	            	{
 	  	                         $buendnisflug_user = Classes::User($_POST["buendnis_benutzername"]);
 	  	                         if(!$buendnisflug_user->getStatus())
 	  	                                 $show_versenden = true;
 	  	                         else
 	  	                         {
 	  	                                 $buendnisflug_id = $buendnisflug_user->resolveFleetPasswd($_POST["buendnis_flottenpasswort"]);
 	  	                                 if($buendnisflug_id === null)
 	  	                                         $show_versenden = true;
 	  	                                 else
 	  	                                 {
 	  	                                         $buendnisflug_fleet = Classes::Fleet($buendnisflug_id);
 	  	                                         if(!$buendnisflug_fleet->getStatus())
 	  	                                                 $show_versenden = true;
 	  	                                 }
 	  	                          }
 	  	                 	 }

						$show_form2 = false;
							if($buendnisflug) $_POST['auftrag'] = $buendnisflug_fleet->getCurrentType();
 	  	                                  else $auftrag = $_POST['auftrag'];
 	  	 
 	  	                                  	if(!$buendnisflug && !in_array($_POST['auftrag'], $types)) 
								$show_form2 = true;
							else
							{
							$that_user = Classes::User($planet_owner);

							$noob = false;
							if($planet_owner && ($_POST['auftrag'] == '3' || $_POST['auftrag'] == '5') && !$that_user->userLocked() && !file_exists(global_setting("DB_NONOOBS")))
							{
								# Anfaengerschutz ueberpruefen
								$that_punkte = $that_user->getScores();
								$this_punkte = $me->getScores();

								if($that_punkte < 2500 || $this_punkte < 2500)
								{
									if(defined('ajax')) return array('error', 'Noobschutz');
?>
<p class="error">
	Es ist eine intergalaktische Störung aufgetreten! Die Sensoren sind beim dem Versuch, einen Anflugspunkt auszumachen, durcheinandergekommen.(<abbr title="Also known as" xml:lang="en">Aka</abbr> Anfängerschutz.)
</p>
<?php
									$noob = true;
								}
								elseif(($that_punkte < 5000 && $this_punkte > $that_punkte*1.3) || ($this_punkte < 5000 && $that_punkte > $this_punkte*1.3))
								{
									if(defined('ajax')) return array('error', 'Noobschutz.');
?>
<p class="error">
	Es ist eine intergalaktische Störung aufgetreten!<br> Die Sensoren sind beim dem Versuch, einen Anflugspunkt auszumachen, durcheinandergekommen.(<abbr title="Also known as" xml:lang="en">Aka</abbr> Anfängerschutz.)
</p>
<?php
									$noob = true;
								}
							}

							if(!$noob)
							{
								
								$fleet_obj = Classes::Fleet();
								if($fleet_obj->create())
								{	
									if($buendnisflug)
									{
									$bndfleet_obj = $buendnisflug_fleet;
									}
									
										
									# Geschwindigkeitsfaktor
									if(!isset($_POST['speed']) || $_POST['speed'] < 0.05 || $_POST['speed'] > 1)
										$_POST['speed'] = 1;

									$fleet_obj->addTarget($_POST['galaxie'].':'.$_POST['system'].':'.$_POST['planet'], $_POST['auftrag'], false);
									if($_POST['auftrag'] != 6)
										$fleet_obj->addTarget($me->getPosString(), $_POST['auftrag'], true);
 	  	         
 	  	                                                #else
									$fleet_obj->addUser($_SESSION['username'], $me->getPosString(), $_POST['speed']);
									foreach($_POST['flotte'] as $id=>$anzahl)
										$fleet_obj->addFleet($id, $anzahl, $_SESSION['username']);
										if(!isset($_POST['haltezeit'])) $_POST['haltezeit'] = 0;
										$fleet_obj->addHoldTime($_POST['haltezeit']);
										if(isset($_POST['saveflug'])) $fleet_obj->addSaveFlight($_POST['saveflug']);
										$ress = $me->getRess();
									if(!isset($_POST['auftrag'])) $_POST['auftrag'] == true;

									#Transport
									if(($_POST['auftrag'] == 4))
									{
										$username1 = array();										
										#Bei eigenen Angriffsflotten nicht halten
										#Planetenbesitzer flotten holen
										$owner_obj = Classes::User($planet_owner);
										$fleets = $owner_obj->getFleetsList();
										
										
										#Fremdflotten durch Userabgleich identifizieren
										foreach($fleets as $fleet)
										{
											$that = Classes::Fleet($fleet);
											$target = $that->getTargetsList();
											$target1 = array($_POST['galaxie'].':'.$_POST['system'].':'.$_POST['planet'], $_POST['auftrag']);
											$username1 = $that->getUsersList();
											$type = $that->getCurrentType();
											$hold = $_POST['haltezeit'];
											#Begrenzung der gehaltenen Flotten	
											if(in_array($_SESSION['username'], $username1) && ($hold > 0) && ($target[0] == $target1[0]) && $type == 3)
											{
												if(defined('ajax')) return array('error', 'Kein Angriff auf eigene Halteflotten!!');
?>
<p class="error">
	Sie können diesen Spieler nicht zum Halten anfliegen, da eigene Flotten mit Angriffsauftrag zum Planeten unterwegs sind.
</p>
<?php

												return false;
											}
										}

										
									
										if(!isset($_POST['transport'])) $_POST['transport'] = array(0,0,0,0,0);
										if(!isset($_POST['rtransport'])) $_POST['rtransport'] = array();
										if($_POST['transport'][0] > $ress[0]) $_POST['transport'][0] = $ress[0];
										if($_POST['transport'][1] > $ress[1]) $_POST['transport'][1] = $ress[1];
										if($_POST['transport'][2] > $ress[2]) $_POST['transport'][2] = $ress[2];
										if($_POST['transport'][3] > $ress[3]) $_POST['transport'][3] = $ress[3];
										if($_POST['transport'][4] > $ress[4]) $_POST['transport'][4] = $ress[4];

										foreach($_POST['rtransport'] as $id=>$anzahl)
										{
											if($anzahl > $me->getItemLevel($id, 'roboter'))
												$_POST['rtransport'][$id] = $me->getItemLevel($id, 'roboter');
										}
                                                                     		if($planet_owner == $_SESSION['username'])
										{
                                           					$fleet_obj->addTransport($_SESSION['username'], $_POST['transport'], $_POST['rtransport']);
										}
										else 
										{
											$fleet_obj->addTransport($_SESSION['username'], $_POST['transport']);
										}

										list($_POST['transport'], $_POST['rtransport']) = $fleet_obj->getTransport($_SESSION['username']);
										
									}

									elseif($_POST['auftrag'] == 3)
									{
										
										$username1 = array();										
										#Bei eigenen Halteflotten Flotten nicht angreifen
										#Fremdflotten holen
										$owner_obj = Classes::User($planet_owner);
										$fleets = $owner_obj->getFleetsList();
										$_POST['transport'] = array(0,0,0,0,0);
										$_POST['rtransport'] = array();

										
										#Fremdflotten durch Userabgleich identifizieren
										foreach($fleets as $fleet)
										{
											$that = Classes::Fleet($fleet);
											$target = $that->getTargetsList();
											$target1 = array($_POST['galaxie'].':'.$_POST['system'].':'.$_POST['planet'], $_POST['auftrag']);
											$username1 = $that->getUsersList();
											$hold = $that->getHoldTime();
											$type = $that->getCurrentType();
											#Begrenzung der gehaltenen Flotten	
											if(in_array($_SESSION['username'], $username1) && ($hold != 0) && ($target[0] == $target1[0]) && $type == 4)
											{
												if(defined('ajax')) return array('error', 'Kein Angriff auf eigene Halteflotten!!');
?>
<p class="error">
	Sie können diesen Spieler nicht angreifen, da eigene Flotten mit Halteauftrag zum Planeten unterwegs sind, oder sich bereits dort befinden.
</p>
<?php

												return false;
											}
										}
									}
									#Stationieren und Besiedeln
									elseif(($_POST['auftrag'] == 1 || $_POST['auftrag'] == 6))
									{
										
										if(!isset($_POST['transport'])) $_POST['transport'] = array(0,0,0,0,0);
										if(!isset($_POST['rtransport'])) $_POST['rtransport'] = array();
										if($_POST['transport'][0] > $ress[0]) $_POST['transport'][0] = $ress[0];
										if($_POST['transport'][1] > $ress[1]) $_POST['transport'][1] = $ress[1];
										if($_POST['transport'][2] > $ress[2]) $_POST['transport'][2] = $ress[2];
										if($_POST['transport'][3] > $ress[3]) $_POST['transport'][3] = $ress[3];
										if($_POST['transport'][4] > $ress[4]) $_POST['transport'][4] = $ress[4];

										foreach($_POST['rtransport'] as $id=>$anzahl)
										{
											if($anzahl > $me->getItemLevel($id, 'roboter'))
												$_POST['rtransport'][$id] = $me->getItemLevel($id, 'roboter');
										}
                                                                     		if($planet_owner == $_SESSION['username'])
										{
                                           						$fleet_obj->addTransport($_SESSION['username'], $_POST['transport'], $_POST['rtransport']);
										}
										else 
										{
											$fleet_obj->addTransport($_SESSION['username'], $_POST['transport']);
										}

										list($_POST['transport'], $_POST['rtransport']) = $fleet_obj->getTransport($_SESSION['username']);
									}

									else
									{
										$_POST['transport'] = array(0,0,0,0,0);
										$_POST['rtransport'] = array();
									}

									$tritium = $fleet_obj->calcNeededTritium($_SESSION['username']);
									$filename = s_root.'/engine/classes/log.txt';
									$fo = fopen($filename, "a");
									fwrite($fo, date('Y-m-d, H:i:s')."  Flotten.php  User: ".$_SESSION['username']." Auftrag: ".$_POST['auftrag']."\n");
								
									$user_obj = Classes::User($_SESSION['username']);
									$alliance_tag = $user_obj->getAllianceTag();
									$tag_obj = Classes::Alliance($alliance_tag);
									$alliance_array = $tag_obj->getUsersList();
									$isAllianceMember = array_search($planet_owner, $alliance_array);
									$verb = $me->isVerbuendet($planet_owner);

									if($_POST['auftrag'] == 4 && $_POST['haltezeit'] > 0 && !$verb && !$isAllianceMember)
									{								
?>
<p class="error">
	Geht bis zur Umstellung des Kampfsystems nicht!!!
</p>
<?php
									}

									elseif($ress[4]-$_POST['transport'][4] < $tritium)
									{
										if(defined('ajax')) return array('error', 'Nicht genug Tritium vorhanden.');
?>
<p class="error">
	Nicht genug Tritium vorhanden.
</p>
<?php
									}
									elseif($buendnisflug && !$bndfleet_obj->getBndUsersCount($_SESSION['username']))
									{ 
?>
<p class="error">
	Die maximale Verbandsuseranzahl wurde bereits erreicht. Sie müssen woanders mitfliegen oder zuhause bleiben.
</p>
<?php											
									$fleet_obj->destroy();
									}

									elseif($buendnisflug && !$fleet_obj->checkBndRaid($buendnisflug_id))
									{ 
?>
<p class="error">
	Ihre Flotte ist zu langsam, um am Verband teilzunehmen.
</p>
<?php											
									$fleet_obj->destroy();
									}

									else
									{
										if($buendnisflug)
										{
											$user = $bndfleet_obj->addUser($_SESSION['username'], $me->getPosString(), $_POST['speed']);
											foreach($_POST['flotte'] as $id=>$anzahl)											
											$bndfleet_obj->addFleet($id, $anzahl, $user);
											$bndfleet_obj->addStartTime($user, time());
											$me->addFleet($bndfleet_obj->getName());
										}
										else $me->addFleet($fleet_obj->getName());
										if(!$buendnisflug && $_POST['auftrag'] != 1 && $_POST['auftrag'] != 2 && $planet_owner != $_SESSION['username'] && $planet_owner)
										{
											# Beim Zielbenutzer die Flottenbewegung eintragen
											$that_user->addFleet($fleet_obj->getName());
										}

										$me->subtractRess(array(0, 0, 0, 0, $tritium));

										# Flotten abziehen
										foreach($_POST['flotte'] as $id=>$anzahl)
											$me->changeItemLevel($id, -$anzahl, 'schiffe');

										# Rohstoffe abziehen
										$me->subtractRess($_POST['transport'], false);

										# Roboter abziehen
										if(isset($_POST['rtransport']))
										{
										foreach($_POST['rtransport'] as $id=>$anzahl)
											$me->changeItemLevel($id, -$anzahl, 'roboter');
										}
										
										
											
											if($buendnisflug) $fleet_obj->destroy();
											else $fleet_obj->start();

											if(defined('ajax')) return array('successful', 'Die Flotte wurde versandt.');
											elseif($fast_action)
											{
												header($_SERVER['SERVER_PROTOCOL'].' 204 No Content');
												ob_end_clean();
												die();
											}
											else
											{
?>
<div class="flotte-versandt">
	<p>
		Die Flotte wurde versandt.
	</p>
	<dl>
<?php
		if($buendnisflug)
		{
?>
		<dt class="c-ziel">Ziel</dt>
		<dd class="c-ziel"><?=utf8_htmlentities($bndfleet_obj->getCurrentTarget())?> &ndash;  <?=$planet_owner ? utf8_htmlentities($galaxy_obj->getPlanetName($_POST['system'], $_POST['planet'])).' <span class="playername">('.utf8_htmlentities($planet_owner).')</span>' : 'Unbesiedelt'?></dd>


		<dt class="c-auftragsart">Auftragsart</dt>
		<dd class="c-auftragsart">Verbandsangriff</dt>

		<dt class="c-ankunft">Ankunft</dt>
		<dd class="c-ankunft"><?=date('H:i:s, Y-m-d', $bndfleet_obj->getArrivalTime())?> (Serverzeit)</dd>
<?php
		}
		if(!$buendnisflug)
		{
?>
		<dt class="c-ziel">Ziel</dt>
		<dd class="c-ziel"><?=utf8_htmlentities($fleet_obj->getCurrentTarget())?> &ndash;  <?=$planet_owner ? utf8_htmlentities($galaxy_obj->getPlanetName($_POST['system'], $_POST['planet'])).' <span class="playername">('.utf8_htmlentities($planet_owner).')</span>' : 'Unbesiedelt'?></dd>


		<dt class="c-auftragsart">Auftragsart</dt>
		<dd class="c-auftragsart"><?=isset($type_names[$_POST['auftrag']]) ? htmlentities($type_names[$_POST['auftrag']]) : utf8_htmlentities($_POST['auftrag'])?></dt>

		<dt class="c-ankunft">Ankunft</dt>
		<dd class="c-ankunft"><?=date('H:i:s, Y-m-d', $fleet_obj->getArrivalTime())?> (Serverzeit)</dd>
<?php
		}
												if($_POST['haltezeit'] !=0)
												{
?>

             <dt class="c-haltezeit">Haltezeit/Minuten</dt>
		<dd class="c-haltezeit"><?=($_POST['haltezeit'])," Minuten"?></dd>
<?php
												}
												if(isset($_POST['saveflug']))
												{

?>

		<dt class="c-saveflug">Saveflug</dt>
		<dd class="c-saveflug"><?="Saveflug"?></dd>
<?php
												}
?>

	</dl>
</div>
<?php
												
										}
									}
								}
							}
						}
					}

					if($show_form2)
					{
						if(defined('ajax')) return array('error', 'Ungültige Aktion.');
						elseif($fast_action)
						{
							header($_SERVER['SERVER_PROTOCOL'].' 204 No Content');
							ob_end_clean();
							die();
						}
						
						$distance = Fleet::getDistance($me->getPosString(), $_POST['galaxie'].':'.$_POST['system'].':'.$_POST['planet']);
						$fleet_obj = Classes::Fleet();
						if($fleet_obj->create())
						{
							$fleet_obj->addUser($_SESSION['username'], $me->getPosString());
							$fleet_obj->addTarget($_POST['galaxie'].':'.$_POST['system'].':'.$_POST['planet'], 0, false);
							foreach($_POST['flotte'] as $id=>$anzahl)
								$fleet_obj->addFleet($id, $anzahl, $_SESSION['username']);
							$time = $fleet_obj->getNextArrival()-time();
							$tritium = $fleet_obj->calcNeededTritium($_SESSION['username']);
							$time_string = '';
							if($time >= 86400)
							{
								$time_string .= floor($time/86400).'&thinsp;<abbr title="Tage">d</abbr>';
								$time2 = $time%86400;
							}
							else
								$time2 = $time;
							$time_string .= add_nulls(floor($time2/3600), 2).':'.add_nulls(floor(($time2%3600)/60), 2).':'.add_nulls(($time2%60), 2);

							$this_ress = $me->getRess();
							$transport = $fleet_obj->getTransportCapacity($_SESSION['username']);

							# Kein Robotertransport zu fremden Planeten
							if($planet_owner != $_SESSION['username']) $transport[1] = 0;

							# Kein Robotertransport zu eigenen Planeten
                                                        if($planet_owner = $_SESSION['username']) $transport[1] = 0;

?>
<form action="flotten.php?<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" method="post" class="flotte-versenden-2" onsubmit="this.setAttribute('onsubmit', 'return confirm(\'Doppelklickschutz: Sie haben ein zweites Mal auf \u201eAbsenden\u201c geklickt. Dadurch wird Ihre Flotte auch zweimal abgesandt (sofern die nötigen Schiffe verfügbar sind). Sind Sie sicher, dass Sie diese Aktion durchführen wollen?\');');">
	<dl>
		<dt class="c-ziel">Ziel</dt>
		<dd class="c-ziel"><?=utf8_htmlentities($_POST['galaxie'].':'.$_POST['system'].':'.$_POST['planet'])?> &ndash; <?=$planet_owner ? utf8_htmlentities($galaxy_obj->getPlanetName($_POST['system'], $_POST['planet'])).' <span class="playername">('.utf8_htmlentities($galaxy_obj->getPlanetOwner($_POST['system'], $_POST['planet'])).')</span>' : 'Unbesiedelt'?></dd>

		<dt class="c-entfernung">Entfernung</dt>
		<dd class="c-entfernung"><?=ths($distance)?>&thinsp;<abbr title="Orbits">Or</abbr></dd>

		<dt class="c-antrieb">Antrieb</dt>
		<dd class="c-antrieb"><?=ths($speed)?>&thinsp;<abbr title="Mikroorbits pro Quadratsekunde">µOr&frasl;s²</abbr></dd>

		<dt class="c-tritiumverbrauch">Tritiumverbrauch</dt>
		<dd class="c-tritiumverbrauch <?=($this_ress[4] >= $tritium) ? 'ja' : 'nein'?>" id="tritium-verbrauch"><?=ths($tritium)?>&thinsp;<abbr title="Tonnen">t</abbr></dd>

		<dt class="c-geschwindigkeit"><label for="speed">Gesch<kbd>w</kbd>indigkeit</label></dt>
		<dd class="c-geschwindigkeit">
			<select name="speed" id="speed" accesskey="w" tabindex="1" onchange="recalc_values();" onkeyup="recalc_values();">
<?php
							for($i=1,$pr=100; $i>0; $i-=.05,$pr-=5)
							{
?>
				<option value="<?=htmlentities($i)?>"><?=htmlentities($pr)?>&thinsp;%</option>
<?php
							}
?>
			</select>
		</dd>

		<dt class="c-flugzeit">Flugzeit</dt>
		<dd class="c-flugzeit" id="flugzeit" title="Ankunft: <?=date('H:i:s, Y-m-d', time()+$time)?> (Serverzeit)"><?=$time_string?></dd>
<?php
						if($buendnisflug)
						{
							$arrival_time1 = $buendnisflug_fleet->getArrivalTime();
							$countdown1 = array($buendnisflug_id, $arrival_time1 , true);
?>
	
		<dt class="c-tritiumverbrauch">Flugzeit Verbandsflotte</dt>
		<dd class="c-tritiumverbrauch <?=($this_ress[4] >= $tritium) ? 'ja' : 'nein'?>" id="restbauzeit-<?=utf8_htmlentities($buendnisflug_id)?>">Ankunft: <?=date('H:i:s, Y-m-d', $arrival_time1)?> (Serverzeit)</dd>		
			<script type="text/javascript">	
	init_countdown('<?=$countdown1[0]?>', <?=$countdown1[1]?>, <?=$countdown1[2] ? 'false' : 'true'?>, <?=global_setting("EVENTHANDLER_INTERVAL")?>);		
	</script>
		</dd>	
			<dt class="c-auftrag"><label for="auftrag">A<kbd>u</kbd>ftrag</label></dt>
		<dd class="c-auftrag"><legend>Verbandsangriff</legend>
</dd>
<?php
						}
?>

		<dt class="c-transportkapazitaet">Transportkapazität</dt>
		<dd class="c-transportkapazitaet"><?=ths($transport[0])?>&thinsp;<abbr title="Tonnen">t</abbr></dd>
<?php
						if(!$buendnisflug)
						{
?>

		<script type="text/javascript">
			// <![CDATA[
			document.write('<dt class="c-verbleibend" id="transport-verbleibend-dt">Verbleibend</dt>');
			document.write('<dd class="c-verbleibend" id="transport-verbleibend-dd"><?=ths($transport[0])?>&thinsp;<abbr title="Tonnen">t</abbr></dd>');
			// ]]>
		</script>
		<dt class="c-auftrag"><label for="auftrag">A<kbd>u</kbd>ftrag</label></dt>
		<dd class="c-auftrag">

			<select name="auftrag" id="auftrag" accesskey="u" tabindex="2" onchange="recalc_values();" onkeyup="recalc_values();">
<?php
							foreach($types as $type)
							{
?>
				<option value="<?=utf8_htmlentities($type)?>"><?=isset($type_names[$type]) ? htmlentities($type_names[$type]) : $type?></option>
<?php
							}
?>
			</select>
		</dd>
<?php
						}
						if(!$buendnisflug && ($transport[0] > 0 || $transport[1] > 0))
						{
?>

		<dt class="c-transport" id="transport-dt">Tra<kbd>n</kbd>sport</dt>
		<dd class="c-transport" id="transport-dd">
			<dl>
<?php
								if($transport[0] > 0)
								{
								$ress = $me->getRess();
?>
				<dt><label for="transport-carbon">Carbo<kbd>n</kbd></label> - <a href="#" onclick='document.getElementById("transport-carbon").value = "<?=ereg_replace("&nbsp;","",ths($ress[0]))?>";recalc_values();' ondblclick='document.getElementById("transport-carbon").value = "0";recalc_values();'/>max</a></dt>
				<dd><input type="text" name="transport[0]" id="transport-carbon" value="0" onchange="recalc_values();" accesskey="n" tabindex="3" onkeyup="recalc_values();" onclick="recalc_values();" /></dd>

				<dt><label for="transport-aluminium">Aluminium</label> - <a href="#" onclick='document.getElementById("transport-aluminium").value = "<?=ereg_replace("&nbsp;","",ths($ress[1]))?>";recalc_values();' ondblclick='document.getElementById("transport-aluminium").value = "0";recalc_values();'/>max</a></dt>
				<dd><input type="text" name="transport[1]" id="transport-aluminium" value="0" onchange="recalc_values();" tabindex="4" onkeyup="recalc_values();" onclick="recalc_values();" /></dd>

				<dt><label for="transport-wolfram">Wolfram</label> - <a href="#" onclick='document.getElementById("transport-wolfram").value = "<?=ereg_replace("&nbsp;","",ths($ress[2]))?>";recalc_values();' ondblclick='document.getElementById("transport-wolfram").value = "0";recalc_values();'/>max</a></dt>
				<dd><input type="text" name="transport[2]" id="transport-wolfram" value="0" onchange="recalc_values();" tabindex="5" onkeyup="recalc_values();" onclick="recalc_values();" /></dd>

				<dt><label for="transport-radium">Radium</label> - <a href="#" onclick='document.getElementById("transport-radium").value = "<?=ereg_replace("&nbsp;","",ths($ress[3]))?>";recalc_values();' ondblclick='document.getElementById("transport-radium").value = "0";recalc_values();'/>max</a></dt>
				<dd><input type="text" name="transport[3]" id="transport-radium" value="0" onchange="recalc_values();" tabindex="6" onkeyup="recalc_values();" onclick="recalc_values();" /></dd>

				<dt><label for="transport-tritium">Tritium</label> - <a href="#" onclick='document.getElementById("transport-tritium").value = "<?=ereg_replace("&nbsp;","",ths($ress[4]))-ereg_replace("&nbsp;","",ths($tritium+2))?>";recalc_values();' ondblclick='document.getElementById("transport-tritium").value = "0";recalc_values();'/>max</a></dt>
				<dd><input type="text" name="transport[4]" id="transport-tritium" value="0" onchange="recalc_values();" tabindex="7" onkeyup="recalc_values();" onclick="recalc_values();" /></dd>
<?php
								}
								if($transport[1] > 0)
								{
									if($transport[0] > 0)
										echo "\n";

									$tabindex = 8;
									foreach($me->getItemsList('roboter') as $rob)
									{
										$item_info = $me->getItemInfo($rob, 'roboter');
?>
				<dt><label for="rtransport-<?=utf8_htmlentities($rob)?>"><?=utf8_htmlentities($item_info['name'])?></label></dt>
				<dd><input type="text" name="rtransport[<?=utf8_htmlentities($rob)?>]" id="rtransport-<?=utf8_htmlentities($rob)?>" value="0" onchange="recalc_values();" tabindex="<?=$tabindex++?>" onkeyup="recalc_values();" onclick="recalc_values();" /></dd>
<?php
									}
								}
?>
			</dl>
<?php
							}
							if(!$buendnisflug && ($transport[0] > 0 || $transport[1] > 0))	
							{
?>			
				<dl>		
				<dt class="c-transport1" id="transport1-dt"><label for="transport1-haltezeit-dt">Haltezeit</label></dt>
				<dd class="c-transport1" id="transport1-dd"><input type="text" name="haltezeit" id="haltezeit" value="0" onchange="recalc_values();" accesskey="n" tabindex="8" onkeyup="recalc_values();" onclick="recalc_values();" /></dd>
				
				<dt class="c-transport2" id="transport2-dt"><label for="transport2-saveflug-dt">Saveflug</label></dt>
				<dd class="c-transport2" id="transport2-dd"><input type="checkbox" name="saveflug" id="saveflug" value="1"><br></dd>
				</dl>
			  

		</dd>

				
<?php
							}
						
					
?>
	</dl>
	<script type="text/javascript">
		// <![CDATA[
			function recalc_values()
			{
<?php
							if($buendnisflug)
							{
?>
							// Transport
				var auftraege = new Array();
				auftraege[1] = false;
                 		auftraege[2] = false;
				auftraege[3] = false;
				auftraege[4] = false;
				auftraege[5] = false;
				auftraege[6] = false;

<?php
							}
							if(!$buendnisflug &&($transport[0] > 0 || $transport[1] > 0))
							{
?>

				


				// Transport
				var auftraege = new Array();
				auftraege[1] = true;
                 		auftraege[2] = false;
				auftraege[3] = false;
				auftraege[4] = true;
				auftraege[5] = false;
				auftraege[6] = true;

				var auftrag = document.getElementById('auftrag');
				var use_transport = auftraege[auftrag.options[auftrag.selectedIndex].value];
				document.getElementById('transport-dt').style.display = (use_transport ? 'block' : 'none');
				document.getElementById('transport-dd').style.display = (use_transport ? 'block' : 'none');
				document.getElementById('transport-verbleibend-dt').style.display = (use_transport ? 'block' : 'none');
				document.getElementById('transport-verbleibend-dd').style.display = (use_transport ? 'block' : 'none');

				// Transport1
				var auftraege = new Array();
				auftraege[1] = false;
                 		auftraege[2] = false;
				auftraege[3] = false;
				auftraege[4] = true;
				auftraege[5] = false;
				auftraege[6] = false;

				var auftrag = document.getElementById('auftrag');
				var use_transport1 = auftraege[auftrag.options[auftrag.selectedIndex].value];
				document.getElementById('transport1-dt').style.display = (use_transport1 ? 'block' : 'none');
				document.getElementById('transport1-dd').style.display = (use_transport1 ? 'block' : 'none');
				document.getElementById('transport2-dt').style.display = (use_transport1 ? 'block' : 'none');
				document.getElementById('transport2-dd').style.display = (use_transport1 ? 'block' : 'none');
<?php
							}
?>



				// Tritiumverbrauch
				var speed_obj = document.getElementById('speed');
				var speed = parseFloat(speed_obj.options[speed_obj.selectedIndex].value);
				var tritium = <?=$tritium?>;
				if(!isNaN(speed))
					tritium = Math.floor(tritium*speed);
				document.getElementById('tritium-verbrauch').innerHTML = ths(tritium)+'&thinsp;<abbr title="Tonnen">t</abbr>';
				document.getElementById('tritium-verbrauch').className = 'c-tritiumverbrauch '+((<?=$this_ress[4]?> >= tritium) ? 'ja' : 'nein');

				// Flugzeit
				var time = <?=$time?>;
				if(!isNaN(speed))
					time /= speed;
				time = Math.round(time);

				var time_string = '';
				if(time >= 86400)
				{
					time_string += Math.floor(time/86400)+'&thinsp;<abbr title="Tage">d</abbr> ';
					var time2 = time%86400;
				}
				else
					var time2 = time;
				time_string += mk2(Math.floor(time2/3600))+':'+mk2(Math.floor((time2%3600)/60))+':'+mk2(Math.floor(time2%60));
				document.getElementById('flugzeit').innerHTML = time_string;

				var jetzt = new Date();
				var ankunft_server = new Date(jetzt.getTime()+(time*1000));
				var ankunft_server_server = new Date(ankunft_server.getTime()-time_diff);

				var attrName;
				if(document.getElementById('flugzeit').getAttribute('titleAttribute'))
					attrName = 'titleAttribute';
				else
					attrName = 'title';
				document.getElementById('flugzeit').setAttribute(attrName, 'Ankunft: '+mk2(ankunft_server.getHours())+':'+mk2(ankunft_server.getMinutes())+':'+mk2(ankunft_server.getSeconds())+', '+ankunft_server.getFullYear()+'-'+mk2(ankunft_server.getMonth()+1)+'-'+mk2(ankunft_server.getDate())+' (Lokalzeit); '+mk2(ankunft_server.getHours())+':'+mk2(ankunft_server.getMinutes())+':'+mk2(ankunft_server.getSeconds())+', '+ankunft_server.getFullYear()+'-'+mk2(ankunft_server.getMonth()+1)+'-'+mk2(ankunft_server.getDate())+' (Serverzeit)');
<?php
							if(!$buendnisflug && ($transport[0] > 0 || $transport[1] > 0))
							{
?>

				// Verbleibendes Ladevermoegen
				if(use_transport)
				{
<?php
								if($transport[0] > 0)
								{
?>
					var ges_ress = myParseInt(document.getElementById('transport-carbon').value)+myParseInt(document.getElementById('transport-aluminium').value)+myParseInt(document.getElementById('transport-wolfram').value)+myParseInt(document.getElementById('transport-radium').value)+myParseInt(document.getElementById('transport-tritium').value);
<?php
								}
								else
								{
?>
					var ges_ress = 0;
<?php
								}
								if($transport[1] > 0)
								{
									$robs_arr = array();
									foreach($me->getItemsList('roboter') as $rob)
										$robs_arr[] = "myParseInt(document.getElementById('rtransport-".$rob."').value)";
?>
					var ges_rob = <?=implode('+', $robs_arr)?>;
<?php
								}
								else
								{
?>
					var ges_rob = 0;
<?php
								}
?>
					var remain_ress = <?=$transport[0]?>;
					if(!isNaN(ges_ress))
						remain_ress -= ges_ress;
					var remain_rob = <?=$transport[1]?>;
					if(!isNaN(ges_rob))
						remain_rob -= ges_rob;
					if(remain_ress < 0)
						remain_ress = "\u22120";
					else
						remain_ress = ths(remain_ress);



/* #Roboter weg!					if(remain_rob < 0)
						remain_rob = "\u22120";
					else
						remain_rob = ths(remain_rob); */



					document.getElementById('transport-verbleibend-dd').innerHTML = remain_ress+'&thinsp;<abbr title="Tonnen">t</abbr> &nbsp;';
				}
<?php
							}
?>
			}

			recalc_values();
		// ]]>
	</script>
	<div>
<?php
							foreach($_POST['flotte'] as $id=>$anzahl)
							{
?>
		<input type="hidden" name="flotte[<?=utf8_htmlentities($id)?>]" value="<?=utf8_htmlentities($anzahl)?>" />
<?php
							}
?>
		<input type="hidden" name="galaxie" value="<?=utf8_htmlentities($_POST['galaxie'])?>" />
		<input type="hidden" name="system" value="<?=utf8_htmlentities($_POST['system'])?>" />
		<input type="hidden" name="planet" value="<?=utf8_htmlentities($_POST['planet'])?>" />
<?php
							if($buendnisflug)
							{
								$_POST["buendnis_benutzername1"] = ereg_replace(' ', "&nbsp", $_POST["buendnis_benutzername"]);
?>
							
		<!--<button type="submit" accesskey="d" name="buendnisflug1" value=<?=$_POST["buendnis_benutzername"]?>>Absen<kbd>d</kbd>en</button>-->
		<button type="submit" accesskey="d" name="buendnisflug1" value=<?=$_POST["buendnis_benutzername1"]."/".$_POST["buendnis_flottenpasswort"]?>>Absen<kbd>d</kbd>en</button>

<?php
							}
							else
							{
?>
		<button type="submit" accesskey="d">Absen<kbd>d</kbd>en</button>
<?php
							}
?>
	</div>
</form>
<?php
						}
					}
				}
			}
		}
	}

	if($show_versenden)
	{
		if(defined('ajax')) return array('error', 'Ungültige Aktion.');
		elseif($fast_action)
		{
			header($_SERVER['SERVER_PROTOCOL'].' 204 No Content');
			ob_end_clean();
			die();
		}
?>
<h3>Flotte versenden</h3>
<p class="flotte-anzahl<?=($my_flotten >= $max_flotten) ? ' voll' : ''?>">
	Sie haben derzeit <?=ths($my_flotten)?> von <?=ths($max_flotten)?> <?=($max_flotten == 1) ? 'möglichen Flotte' : 'möglichen Flotten'?> unterwegs.<br />
	Bauen Sie das Kontrollwesen aus, um die maximale Anzahl zu erhöhen.
</p>
<?php
		$this_pos = $me->getPos();
		if(isset($_GET['action_galaxy'])) $this_pos[0] = $_GET['action_galaxy'];
		if(isset($_GET['action_system'])) $this_pos[1] = $_GET['action_system'];
		if(isset($_GET['action_planet'])) $this_pos[2] = $_GET['action_planet'];
?>
<form action="flotten.php?<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" method="post" class="flotte-versenden">
<?php
		if($my_flotten < $max_flotten && $me->permissionToAct())
		{
?>
	<fieldset class="flotte-koords">
		<legend><input type="radio" name="buendnisflug" value="0"<?php if(!$buendnisflug){?> checked="checked"<?php }?> id="i-eigenes-ziel" /> <label for="i-eigenes-ziel">Eigenes Ziel</label></legend>
		<dl>
			<dt class="c-ziel"><label for="ziel-galaxie"><kbd>Z</kbd>iel</label></dt>
			<dd class="c-ziel"><input type="text" id="ziel-galaxie" name="galaxie" value="<?=utf8_htmlentities($this_pos[0])?>" title="Ziel: Galaxie" accesskey="z" tabindex="1" onclick="syncronise(true);" onchange="syncronise(true);" onkeyup="syncronise(true);" maxlength="<?=strlen(getGalaxiesCount())?>" />:<input type="text" id="ziel-system" name="system" value="<?=utf8_htmlentities($this_pos[1])?>" title="Ziel: System" tabindex="2" onclick="syncronise(true);" onchange="syncronise(true);" onkeyup="syncronise(true);" maxlength="3" />:<input type="text" id="ziel-planet" name="planet" value="<?=utf8_htmlentities($this_pos[2])?>" title="Ziel: Planet" tabindex="3" onclick="syncronise(true);" onchange="syncronise(true);" onkeyup="syncronise(true);" maxlength="2" /></dd>
			<script type="text/javascript">
				// <![CDATA[
					document.write('<dt class="c-planet"><label for="ziel-planet-wahl">Pla<kbd>n</kbd>et</label></dt>');
					document.write('<dd class="c-planet">');
					document.write('<select id="ziel-planet-wahl" accesskey="n" tabindex="4" onchange="syncronise(false);" onkeyup="syncronise(false);">');
					document.write('<option value="">Benutzerdefiniert</option>');
<?php
				$shortcuts = $me->getPosShortcutsList();
				if(count($shortcuts) > 0)
				{
?>
					document.write('<optgroup label="Lesezeichen">');
<?php
					foreach($shortcuts as $shortcut)
					{
						$s_pos = explode(':', $shortcut);
						$galaxy_obj = Classes::Galaxy($s_pos[0]);
						$owner = $galaxy_obj->getPlanetOwner($s_pos[1], $s_pos[2]);
						$s = $shortcut.': ';
						if($owner)
						{
							$s .= $galaxy_obj->getPlanetName($s_pos[1], $s_pos[2]).' (';
							$alliance = $galaxy_obj->getPlanetOwnerAlliance($s_pos[1], $s_pos[2]);
							if($alliance) $s .= '['.$alliance.'] ';
							$s .= $owner.')';
						}
						else $s .= '[unbesiedelt]';
?>
					document.write('<option value="<?=htmlspecialchars($shortcut)?>"><?=preg_replace('/[\'\\\\]/', '\\\\\\0', htmlspecialchars($s))?></option>');
<?php
					}
?>
					document.write('</optgroup>');
<?php
				}
?>
					document.write('<optgroup label="Eigene Planeten">');
<?php
				$planets = $me->getPlanetsList();
				$active_planet = $me->getActivePlanet();
				foreach($planets as $planet)
				{
					$me->setActivePlanet($planet);
?>
					document.write('<option value="<?=utf8_htmlentities($me->getPosString())?>"<?=($planet == $active_planet) ? ' selected="selected"' : ''?>><?=utf8_htmlentities($me->getPosString())?>: <?=preg_replace('/[\'\\\\]/', '\\\\\\0', utf8_htmlentities($me->planetName()))?></option>');
<?php
				}
				$me->setActivePlanet($active_planet);
?>
					document.write('</select>');
					document.write('</optgroup>');
<?php
				if(count($shortcuts) > 0)
				{
?>
					document.write('<a href="flotten_actions.php?action=shortcuts&amp;<?=htmlspecialchars(urlencode(session_name()).'='.urlencode(session_id()))?>" class="lesezeichen-verwalten-link">[Lesezeichen verwalten]</a>');
<?php
				}
?>
					document.write('</dd>');

					function syncronise(input_select)
					{
						var select_obj = document.getElementById('ziel-planet-wahl');
						if(!input_select)
						{
							var pos = select_obj.options[select_obj.selectedIndex].value;
							if(pos != '')
							{
								pos = pos.split(/:/);
								document.getElementById('ziel-galaxie').value = pos[0];
								document.getElementById('ziel-system').value = pos[1];
								document.getElementById('ziel-planet').value = pos[2];
							}
						}
						else
						{
							var pos = new Array(3);
							pos[0] = document.getElementById('ziel-galaxie').value;
							pos[1] = document.getElementById('ziel-system').value;
							pos[2] = document.getElementById('ziel-planet').value;
							pos = pos.join(':');

							var one = false;
							for(var sindex=0; sindex<select_obj.options.length; sindex++)
							{
								if(pos == select_obj.options[sindex].value)
								{
									select_obj.selectedIndex = sindex;
									one = true;
									break;
								}
							}

							if(!one)
								select_obj.selectedIndex = 0;
						}
					}

					syncronise(true);
				// ]]>
			</script>
		</dl>
	</fieldset>
	<fieldset class="buendnisflug">
	  	                 <legend><input type="radio" name="buendnisflug" value="1"<?php if($buendnisflug){?> checked="checked"<?php }?> id="i-buendnisflug" /> <label for="i-buendnisflug">Bündnisflug</label></legend>
 	  	                 <dl>
 	  	                         <dt class="c-benutzername"><label for="i-buendnis-benutzername">Benutzername</label></dt>
 	  	                         <dd class="c-benutzername"><input type="text" id="i-buendnis-benutzername" name="buendnis_benutzername"<?php if(isset($_POST["buendnis_benutzername"])){?> value="<?=htmlspecialchars($_POST["buendnis_benutzername"])?>"<?php }?> /></dd>
 	  	 
 	  	                         <dt class="c-passwort"><label for="i-buendnis-flottenpasswort">Flottenpasswort</label></dt>
 	  	                         <dd class="c-passwort"><input type="text" id="i-buendnis-flottenpasswort" name="buendnis_flottenpasswort"<?php if(isset($_POST["buendnis_flottenpasswort"])){?> value="<?=htmlspecialchars($_POST["buendnis_flottenpasswort"])?>"<?php }?> /></dd>
 	  	                 </dl>
 	  	         </fieldset>
 	  	         <script type="text/javascript">
 	  	                 activate_users_list(document.getElementById("i-buendnis-benutzername"));
 	  	         </script>

<?php
		}
?>
	<fieldset class="flotte-schiffe">
		<legend>Schiffe</legend>
		<dl>
<?php
		$i = 5;
		foreach($me->getItemsList('schiffe') as $id)
		{
			if($me->getItemLevel($id, 'schiffe') < 1) continue;
			$item_info = $me->getItemInfo($id, 'schiffe');
?>
			<dt><a href="help/description.php?id=<?=htmlentities(urlencode($id))?>&amp;<?=htmlentities(urlencode(session_name()).'='.urlencode(session_id()))?>" title="Genauere Informationen anzeigen"><?=utf8_htmlentities($item_info['name'])?></a> <span class="vorhanden">(<?=ths($item_info['level'])?>&nbsp;vorhanden)</span></dt>
			<dd>
			  <input type="text" name="flotte[<?=utf8_htmlentities($id)?>]" value="0" tabindex="<?=$i?>"<?=($my_flotten >= $max_flotten || !$me->permissionToAct()) ? ' readonly="readonly"' : ''?>  id="<?=$i?>"/>
			  <input name="button" type="button" onclick='document.getElementById("<?=$i?>").value = "<?=$item_info['level']?>"'  ondblclick='document.getElementById("<?=$i?>").value = "0"' value="max"/>
			</dd>
<?php
			$i++;
		}
?>
		</dl>
	</fieldset>
<?php
		if($i>5 && $my_flotten < $max_flotten && $me->permissionToAct())
		{
?>
	<div><button type="submit" accesskey="w" tabindex="<?=$i?>"><kbd>W</kbd>eiter</button></div>
<?php
		}
?>
</form>
<?php
	}

	login_gui::html_foot();
?>
