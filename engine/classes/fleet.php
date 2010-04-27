<?php

if ( ! defined( TBW_ROOT ) && file_exists( '../include/config_inc.php' ) )
{
    require_once ( '../include/config_inc.php' );
}
else if ( ! defined( TBW_ROOT ) && file_exists( '../../include/config_inc.php' ) )
{
    require_once ( '../../include/config_inc.php' );
}

require_once( TBW_ROOT.'loghandler/logger.php' );

	class Fleet extends Dataset
    {
        protected $datatype = 'fleet';

        function __construct($name=false, $write=true)
        {
            $this->save_dir = global_setting("DB_FLEETS");
            parent::__construct($name, $write);
        }

        function create()
        {
            if(file_exists($this->filename)) return false;
            $this->raw = array(array(), array(), false, array());
            $this->write( true );
            $this->__construct($this->name);

            return true;
        }

        function write( $force = false )
        {
            if( $this->started() || $force ) 
                return Dataset::write($force);
            else 
            {
                return $this->destroy();
            }
        }

        function destroy()
        {
            if(!$this->status) return false;

            foreach($this->raw[1] as $user=>$info)
            {
                $user_obj = Classes::User($user);
                $user_obj->unsetFleet($this->getName());
            }

            $status = ( unlink( $this->filename) );

            $log = new Logger();
            $log->logIt( LOG_USER_FLEET, "destroy() -- fleet ".$this->filename." deleted." );
            
            if( $status )
            {
                $this->status = 0;
                $this->changed = false;

                return true;
            }
            else return false;
        }

        public static function fleetExists($fleet)
        {
            $filename = global_setting("DB_FLEETS").'/'.urlencode($fleet);
            return (is_file($filename) && is_readable($filename));
        }

        function getTargetsList()
        {
            if(!$this->status) 
            {
                return false;
            }

            $targets = array_keys($this->raw[0]);
            
            foreach($targets as $i=>$target)
            {
                if(substr($target, -1) == 'T') 
                    $targets[$i] = substr($target, 0, -1);
            }
            return $targets;
        }


                function getTargetsInformation()
                {
                        if(!$this->status) return false;

                        return $this->raw[0];
                }
    

        function getOldTargetsList()
        {
            if(!$this->status) return false;

            $targets = array_keys($this->raw[3]);
            foreach($targets as $i=>$target)
            {
                if(substr($target, -1) == 'T') $targets[$i] = substr($target, 0, -1);
            }
            return $targets;
        }


        function getNeededSlots()
                  {
                      if(!$this->status) return false;
          
                      $slots = 0;
                      foreach($this->raw[0] as $k=>$v)
                      {
                          if(!$v[1]) $slots++;
                      }
                      foreach($this->raw[3] as $k=>$v)
                      {
                          if(!$v[1]) $slots++;
                      }
                      return $slots;
                  }



        function addTarget($pos, $type, $back)
        {
            if(!$this->status) return false;

            if($type == 2 && !$back) $pos .= 'T';

            if(isset($this->raw[0][$pos])) return false;

            $this->raw[0][$pos] = array($type, $back);

            $this->changed = true;
            return true;
        }

        function userExists($user)
        {
            if(!$this->status) return false;

            return isset($this->raw[1][$user]);
        }

        function getCurrentType()
        {
            if(!$this->status) return false;

            $keys = array_keys($this->raw[0]);
            return $this->raw[0][array_shift($keys)][0];
        }

        function getCurrentTarget()
        {
            if(!$this->status) return false;

            $keys = array_keys($this->raw[0]);
            $t = array_shift($keys);
            if(substr($t, -1) == 'T') $t = substr($t, 0, -1);
            return $t;
        }

        function getLastTarget($user=false)
        {
            if(!$this->status) return false;

            $keys = array_keys($this->raw[1]);
            $first_user = array_shift($keys);
            if($user === false) $user = $first_user;
            if($user == $first_user && count($this->raw[3]) > 0)
            {
                $keys = array_keys($this->raw[3]);
                $l = array_pop($keys);
                if(substr($l, -1) == 'T') $l = substr($l, 0, -1);
                return $l;
            }
            else
            {
                if(!isset($this->raw[1][$user])) return false;

                return $this->raw[1][$user][1];
            }
        }

        function getNextArrival()
        {
            if(!$this->status) return false;

            if($this->started()) $start_time = $this->raw[2];
            else $start_time = time();
            $users = array_keys($this->raw[1]);
            $duration = $this->calcTime(array_shift($users), $this->getLastTarget(), $this->getCurrentTarget());
            return $start_time+$duration;
        }

        function getNextDuration()
        {
            if(!$this->status) return false;

            if($this->started()) $start_time = $this->raw[2];
            else $start_time = time();
            $users = array_keys($this->raw[1]);
            $duration = $this->calcTime(array_shift($users), $this->getLastTarget(), $this->getCurrentTarget());
            return $duration;
        }

        function getBackTime()
        {
            if(!$this->status) return false;

            if($this->started()) $start_time = $this->raw[2];
            else $start_time = time();
            $users = array_keys($this->raw[1]);
            $duration = $this->calcTime(array_shift($users), $this->getLastTarget(), $this->getCurrentTarget());
            $hold = $this->getHoldTime();
            return $start_time+$duration+$duration+$hold;
        }




        function getHoldTime()
        {
                      if($this->isFlyingBack()) $time = 0;
            else
                 {
                if(isset($this->raw[6][0]))
                {
                          $time = $this->raw[6][0];
                   }
                else $time = 0;
            }
             return $time;
                }

		function getRandomShipID($ShipArray) {
	        	$ShipArray = array_split($ShipArray,'isMilitaryShip');
	        	$MilitaryShips = $ShipArray[0];
	        	$CivilShips = $ShipArray[1];
	        	if(count($MilitaryShips)>0) {
	        		return array_rand($MilitaryShips);
	        	}
	        	return array_rand($CivilShips);
		}	                
                
        function getArrivalTime()
              {
            if(!$this->status) return false;            
            if(!isset($this->raw[6][0])) $this->raw[6][0] = 0;
              if(isset($this->raw[7][0]) && $this->raw[7][0] > time() && $this->raw[6][0] !== -1) return $this->raw[7][0];
            if(isset($this->raw[7][1]) && $this->raw[6][0] == -1 && $this->raw[7][1] > time()) return $this->raw[7][1];
            #if(isset($this->raw[7][2]) && $this->raw[7][2] > time() && $this->isFlyingBack() == true) return $this->raw[7][2];
            #Wird f�r Callback gebraucht
            else
            {
                $conn = sqlite_open(global_setting("EVENT_FILE"), 0666);
                        $gta = $this->name;
                        $q = "SELECT time FROM events WHERE fleet = '".$gta."'" or die ('ERROR');
                        $r = sqlite_query($conn, $q);
                    
                       while ($xyz = sqlite_fetch_array($r))
                        {
                                return $xyz['time'];
                        }
//            print "error: ".sqlite_error_string( sqlite_last_error($conn) );
                    sqlite_close($conn);
            }
        }


        function isFlyingBack()
        {
            if(!$this->status) return false;

            $keys = array_keys($this->raw[0]);
            return (bool) $this->raw[0][array_shift($keys)][1];
        }

        function addFleet($id, $count, $user)
        {
            if(!$this->status) return false;

            $count = (int) $count;
            if($count < 0) return false;

            if(!isset($this->raw[1][$user])) return false;

            $keys = array_keys($this->raw[1]);
            $first = !array_search($user, $keys);
            if(isset($this->raw[1][$user][0][$id])) $this->raw[1][$user][0][$id] += $count;
            else $this->raw[1][$user][0][$id] = $count;

            $this->changed = true;
            return true;
        }

        function subForeignFleet($id, $count2, $user)
        {
            if(!$that->status) return false;

            $count2 = (int) $count2;
            if($count2 < 0) return false;

            if(!isset($that->raw[1][$user])) return false;

            $keys = array_keys($that->raw[1]);
            $first = !array_search($user, $keys);
            if(isset($that->raw[1][$user][0][$id])) $that->raw[1][$user][0][$id] = $count2;
            #else $that->raw[1][$user][0][$id] = $count;

            $that->changed = true;
            return true;
        }

        function addStartTime($user, $time)
        {
            if(!$this->status) return false;
            $this->raw[1][$user]['startzeit'] = $time;
            $this->changed = true;
            return true;
        }

        function addHoldTime($htime)
        {
            if(!$this->status) return false;
                     $htime = ($htime*60);
            $this->raw[6][0] = $htime;
            $this->changed = true;
            return true;
        }

        function addNewHoldTime($time, $id)
        {
            if(!$this->status) return false;
            #echo("\n".$time);
                     $time = ($time+time());
            $htime = $this->getArrivalTime();
            #echo("\n".$time);
            #echo("\n".$htime);
            #echo("\n".$id);
            if($time > $htime)
            {
                #echo("\nHaltezeit neu setzen");
                $this->raw[7][1] = $time;
                $conn = sqlite_open(global_setting("EVENT_FILE"), 0666);

                        $gta = $id;

                        $q = "UPDATE events SET time = '".$time."' WHERE fleet = '".$gta."'" or die ('ERROR');

                        return sqlite_query($conn, $q);
                        
                 sqlite_close($conn);
                         $this->changed = true;        
            }

            return true;
        }
        


        function addSaveFlight($save)
        {
            if(!$this->status) return false;

            $this->raw[6][1] = $save;

            $this->changed = true;


            return true;
        }

        function getSaveFlight()
        {
                  if($this->isFlyingBack())
                       {
                           $save = 0;
                       }
                       else
                 {
                if(!isset($this->raw[6][1])) $save = 0;
                else $save = $this->raw[6][1];
                    }
               return $save;
        }

        function addUser($user, $from, $factor=1)
        {
            if(!$this->status) return false;

            if($this->started())
            {


                if(isset($this->raw[1][$user][1]) && $from !== $this->raw[1][$user][1])
                {
                    $trennzeichen = '/';
                    $user = $user.$trennzeichen.$from;

                    $change = 1;
                }
    
                if(count($this->raw[1]) <= 0) return false;
                $keys = array_keys($this->raw[1]);
                $user2 = array_shift($keys);
                $koords = array_keys($this->raw[0]);
                $koords = array_shift($koords);
                if(substr($koords, -1) == 'T') $koords = substr($koords, 0, -1);
                $time = $this->calcTime($user2, $this->raw[1][$user2][1], $koords);
                $time2 = $this->calcTime($user2, $from, $koords);
                
                
                #Buendnissflug funktioniert damit nicht
                #if($time2 > $time) return false;
                #$factor = $time2/$time;
                #self::$database->setLog("read: $user", User);
            }
            #elseif(count($this->raw[1]) > 0) $factor = 1;
            if(isset($this->raw[1][$user][1]) && $from == $this->raw[1][$user][1]) return $user; 

            if($factor <= 0) $factor = 0.01;
            $this->raw[1][$user] = array(
                array(), # Flotten
                $from, # Startkoordinaten
                $factor, # Geschwindigkeitsfaktor
                array(array(0, 0, 0, 0, 0), array(), 0), # Mitgenommene Rohstoffe
                array(array(0, 0, 0, 0, 0), array()), # Handel
                0, # Verbrauchtes Tritium
                'startzeit'=>time() #Startzeit f�r Verbandsflotte
            );
            #self::$database->setLog("read: $user", User);
            $this->changed = true;
            if($change = 1) return $user;
            else return true;
            
        }

        function getBndUsersCount($username)
        {    
            $count = 0;
            foreach($this->raw[1] as $user=>$info)
            {
                $exp = explode("/", $user);
                if($exp[0] == $user)
                $count += 1;
                if($username == $user) return true;
            }
            if($count < 5) return true;
            else return false;
        }

        function getTransportCapacity($user)
        {
            if(!$this->status || !isset($this->raw[1][$user])) return false;

            $trans = array(0, 0);
            $user_object = Classes::User($user);

            foreach($this->raw[1][$user][0] as $id=>$count)
            {
                $item_info = $user_object->getItemInfo($id, 'schiffe');
                $trans[0] += $item_info['trans'][0]*$count;
                $trans[1] += $item_info['trans'][1]*$count;
            }
            
            return $trans;
        }

        /**
         * Adds a given ressource or robot amount to the fleets transport
         * @param $user - username to add fleet to
         * @param $ress - ressource array holds the ressources to add
         * @param $robs - robot array holds the robots to add
         * @return bool - true on success
         */
    function addTransport( $user, $ress = false, $robs = false )
    {
        if ( ! $this->status || ! isset( $this->raw[1][$user] ) ) {
            return false;
        }
        
        list( $max_ress, $max_robs ) = $this->getTransportCapacity( $user );
        $max_ress -= array_sum( $this->raw[1][$user][3][0] );
        $max_robs -= array_sum( $this->raw[1][$user][3][1] );
        
        if ( $ress ) {
            $ress = fit_to_max( $ress, $max_ress );

            $this->raw[1][$user][3][0][0] += $ress[0];
            $this->raw[1][$user][3][0][1] += $ress[1];
            $this->raw[1][$user][3][0][2] += $ress[2];
            $this->raw[1][$user][3][0][3] += $ress[3];
            $this->raw[1][$user][3][0][4] += $ress[4];
        }
        
        if ( $robs ) {
            $robs = fit_to_max( $robs, $max_robs );
            foreach ( $robs as $i => $rob ) {
                if ( ! isset( $this->raw[1][$user][3][1][$i] ) )
                    $this->raw[1][$user][3][1][$i] = $rob;
                else
                    $this->raw[1][$user][3][1][$i] += $rob;
            }
        }
        
        $this->changed = true;
        
        return true;
    }

        function addHandel($user, $ress=false, $robs=false)
        {
            if(!$this->status || !isset($this->raw[1][$user])) return false;

            list($max_ress, $max_robs) = $this->getTransportCapacity($user);
            $max_ress -= array_sum($this->raw[1][$user][4][0]);
            $max_robs -= array_sum($this->raw[1][$user][4][1]);
            if($ress)
            {
                $ress = fit_to_max($ress, $max_ress);
                $this->raw[1][$user][4][0][0] += $ress[0];
                $this->raw[1][$user][4][0][1] += $ress[1];
                $this->raw[1][$user][4][0][2] += $ress[2];
                $this->raw[1][$user][4][0][3] += $ress[3];
                $this->raw[1][$user][4][0][4] += $ress[4];
            }

            if($robs)
            {
                $robs = fit_to_max($robs, $max_robs);
                foreach($robs as $i=>$rob)
                {
                    if(!isset($this->raw[1][$user][4][1][$i]))
                        $this->raw[1][$user][4][1][$i] = $rob;
                    else
                        $this->raw[1][$user][4][1][$i] += $rob;
                }
            }

            $this->changed = true;
            return true;
        }

        function setHandel($user, $ress=false, $robs=false)
        {
            if(!$this->status || !isset($this->raw[1][$user])) return false;

            list($max_ress, $max_robs) = $this->getTransportCapacity($user);
            if($ress !== false && is_array($ress))
            {
                if(!isset($ress[0])) $ress[0] = 0;
                if(!isset($ress[1])) $ress[1] = 0;
                if(!isset($ress[2])) $ress[2] = 0;
                if(!isset($ress[3])) $ress[3] = 0;
                if(!isset($ress[4])) $ress[4] = 0;

                $ress = fit_to_max($ress, $max_ress);

                $this->raw[1][$user][4][0] = $ress;
            }
            if($robs !== false && is_array($robs))
            {
                $robs = fit_to_max($robs, $max_robs);
                $this->raw[1][$user][4][1] = $robs;
            }

            $this->changed = true;
            return true;
        }


        function getTransport($user)
        {
            if(!$this->status || !isset($this->raw[1][$user])) return false;

            return $this->raw[1][$user][3];
        }

        function getHandel($user)
        {
            if(!$this->status || !isset($this->raw[1][$user])) return false;

            return $this->raw[1][$user][4];
        }

        function calcNeededTritium( $user )
        {
            if( !$this->status || $this->started() ) 
                return false;

            $users = array_keys( $this->raw[1] );
            $user_key = array_search( $user, $users );

            if( $user_key === false ) 
                return false;

            if( $user_key )
            {
                return $this->getTritium( $user, $this->raw[1][$user][1], $this->getCurrentTarget() )*2;
            }
            else
            {
                $tritium = 0;
                $old_target = $this->raw[1][$user][1];

                foreach( $this->raw[0] as $target => $info )
                {
                    if( substr( $target, -1 ) == 'T' ) 
                        $target = substr( $target, 0, -1 );

                    $tritium += $this->getTritium( $user, $old_target, $target );
                    $old_target = $target;
                }

                if( $old_target != $this->raw[1][$user][1] )
                    $tritium += $this->getTritium( $user, $old_target, $this->raw[1][$user][1] );

                return $tritium;
            }
        }

        function getTritium($user, $from, $to, $factor=true)
        {
            if(!$this->status || !isset($this->raw[1][$user])) 
                return false;

            $mass = 0;
            $user_obj = Classes::User($user);

            foreach($this->raw[1][$user][0] as $id=>$count)
            {
                $item_info = $user_obj->getItemInfo($id, 'schiffe');
                $mass += $item_info['mass']*$count;
            }

            $global_factors = get_global_factors();
            $add_factor = 1;
            if($factor) $add_factor = $this->raw[1][$user][2];

            return $add_factor*$global_factors['cost']*$this->getDistance($from, $to)*$mass;
        }

        function getScores($user, $from, $to)
        {
            if(!$this->status || !isset($this->raw[1][$user])) return false;

            return $this->getTritium($user, $from, $to)/1000;
        }

        function getTime($i)
        {
            if(!$this->status || (!isset($this->raw[0][$i]) && !isset($this->raw[0][$i.'T']))) return false;
            if(count($this->raw[1]) <= 0) return false;

            $keys = array_keys($this->raw[1]);
            $user = array_shift($keys);
            $from = $this->raw[1][$user][1];
            $to = $i;

            return $this->calcTime($user, $from, $to);
        }


            function calcTime($user, $from, $to)
            {
                    if(!$this->status || !isset($this->raw[1][$user]) || count($this->raw[1][$user]) <= 0) return false;

                    $speeds = array();
                   # Maximale Geschwindigkeit
                   $speed_max = 40000;
                   

            $exp = explode("/", $user);
                    $user_obj = Classes::User($exp[0]);
                    foreach($this->raw[1][$user][0] as $id=>$count)
                    {
                        $item_info = $user_obj->getItemInfo($id, 'schiffe');
                        $speeds[] = $item_info['speed'];
           
                        # Geschwindigkeitsbegrenzung auf $speed_max
                         if(min($speeds) > $speed_max && ($id == 'S7'))
                           $speed = $speed_max/10000000;
                      else                   
                            $speed = min($speeds)/10000000;
                    }

                    $time = sqrt(self::getDistance($from, $to)/$speed)*2;
                    $time /= $this->raw[1][$user][2];
                    $global_factors = get_global_factors();
                    $time *= $global_factors['time'];

                    return $time;
              }

        function callBack($user, $immediately=false)
        {
            if(!$this->status || !$this->started() || !isset($this->raw[1][$user]) || $this->isFlyingBack()) 
                return false;

            $start = $this->raw[1][$user][1];
            $keys = array_keys($this->raw[0]);
            $to = $to_t = array_shift($keys);

            if(substr($to, -1) == 'T') 
                $to = substr($to, 0, -1);

            if(count($this->raw[3]) > 0)
            {
                $keys = array_keys($this->raw[3]);
                $from = $from_t = array_pop($keys);
                if(substr($from, -1) == 'T') $from = substr($from, 0, -1);
            }
            else 
                $from = $from_t = $start;

            if($to_t == $start) 
                return false;
            
            $exp = explode("/", $user);
            $userres = $exp[0];

            if($from_t == $start) 
                $time1 = 0;
            else 
                $time1 = $this->calcTime($user, $from, $start);

            $time2 = $this->calcTime($user, $to, $start);
            $time3 = $this->calcTime($user, $from, $to);
            $holdtime = $this->getHoldTime();

            if(isset($this->raw[1][$user]['startzeit'])) 
                $timeex =(time()-$this->raw[1][$user]['startzeit']);

            $log = new Logger();
            $log->logIt( LOG_USER_FLEET, "\nCallback-User  ".$user."\nFrom  ".$from."\nTo  ".$to."\nRueckflugzeit  ".$time3."\nVerflogene Zeit  ".$timeex );

            if($immediately) $progress = 0;
            else
                    {
                        $progress = (time()-$this->raw[2])/$time3;
                        if($progress > 1) $progress = 1;
                    }

            /* Dreieck ABC:
            A: $start
            B: $to
            C: $from
            AB: $time2
            BC: $time3
            AC: $time1
            D teilt CB in die Anteile $progress und 1-$progress
            Gesucht: AD: $back_time */

            $time1_sq = pow($time1, 2);
            $time3_sq = pow($time3, 2);
            $back_time = round(sqrt($time1_sq - $progress*$time1_sq + $progress*pow($time2,2) - $progress*$time3_sq + pow($progress,2)*$time3_sq));

            $tritium3 = $this->getTritium($user, $from, $to);
            $back_tritium = $tritium3*(1-$progress);
            $prev_t = $to;
            foreach($keys as $next_t)
            {
                if(substr($next_t, -1) == 'T') $next_t = substr($next_t, 0, -1);
                $back_tritium += $this->getTritium($userres, $prev_t, $next_t);
            }
            if($from_t == $start) $tritium1 = 0;
            else $tritium1 = $this->getTritium($user, $from, $start);
            $tritium2 = $this->getTritium($user, $to, $start);

            $tritium1_sq = pow($tritium1, 2);
            $tritium3_sq = pow($tritium3, 2);
            $needed_back_tritium = round(sqrt($tritium1_sq - $progress*$tritium1_sq + $progress*pow($tritium2,2) - $progress*$tritium3_sq + pow($progress,2)*$tritium3_sq));
            $back_tritium -= $needed_back_tritium;

            # Mit Erfahrungspunkten herumhantieren
            #$this->raw[1][$user][5] += $needed_back_tritium;
            #$this->raw[1][$user][5] -= $tritium2; # Wird bei der Ankunft sowieso wieder hinzugezaehlt

            # Eventuellen Handel zurueckerstatten
            if(array_sum($this->raw[1][$user][4][0]) > 0 || array_sum($this->raw[1][$user][4][1]) > 0)
            {
                $target_split = explode(':', $to);
                $galaxy_obj = Classes::Galaxy($target_split[0]);
                $target_owner = $galaxy_obj->getPlanetOwner($target_split[1], $target_split[2]);
                $target_user_obj = Classes::User($target_owner);
                $target_user_obj->setActivePlanet($target_user_obj->getPlanetByPos($to));
                #$target_user_obj->addRess($this->raw[1][$user][4][0]);
                foreach($this->raw[1][$user][4][1] as $id=>$count)
                    $target_user_obj->changeItemLevel($id, $count, 'roboter');
                $this->raw[1][$user][4] = array(array(0,0,0,0,0), array());
            }
            if($holdtime == -1)
            {
                $target_split = explode(':', $to);
                $galaxy_obj = Classes::Galaxy($target_split[0]);
                $target_owner = $galaxy_obj->getPlanetOwner($target_split[1], $target_split[2]);
                $target_user_obj = Classes::User($target_owner);
                $target_user_obj->unsetForeignFleet($this->getName());

                $new_raw = array(
                    array($start => array($this->raw[0][$to_t][0], 1)),
                    array($userres => $this->raw[1][$user]),
                    time(),
                    array($to_t => $this->raw[0][$to_t])
                        );

            }
            else
            {
                $new_raw = array(
                    array($start => array($this->raw[0][$to_t][0], 1)),
                    array($userres => $this->raw[1][$user]),
                    (time()+$back_time)-$time2,
                    array($to_t => $this->raw[0][$to_t])
                        );
            }
            if(array_search($user, array_keys($this->raw[1])) == 0)
                $new_raw[3] = array_merge($this->raw[3], $new_raw[3]);
            $new_raw[1][$userres][3][2] += $back_tritium;
            #Flotte aus dem Raw loeschen
            unset($this->raw[1][$user]);
            #Gesamte Flotte loeschen
            if(count($this->raw[1]) <= 0)
            {
                # Aus der Eventdatei entfernen
                $event_obj = Classes::EventFile();
                $event_obj->removeCanceledFleet($this->getName());

                unlink($this->filename);
                            
                $log = new Logger();
                $log->logIt( LOG_USER_FLEET, "callBack() -- fleet ".$this->filename." deleted." );
                
                $this->status = false;
                $this->changed = false;
            }

            $new = Classes::Fleet();
            $new->create();
            $new->setRaw($new_raw);
            
            #Ankunftszeit regulaere Einzelflotten
            if(($holdtime !== -1) && ($timeex < $time3)) 
            {
                $new->raw[7][0] = (time()+$timeex);
            }
            #Ankunftszeit Halteflotten und schnellere Sub-Verbandsflotten
            else 
            {
                $new->raw[7][0] = (time()+$time3);
            }
            
            $new->createNextEvent();

            if(count($this->raw[1]) > 0)
            {
                $users = $this->getUsersList();
                foreach($users as $nextuser)
                {
                    $exp = explode("/", $nextuser);
                    if($user == $exp[0] || $user == $nextuser)
                    {
                        $that = Classes::Fleet($this->getName());
                        $user_obj = Classes::User($exp[0]);
                        $user_obj->addFleet($new->getName());
                        $that->callBack($nextuser);
                    }
                    
                }
            }
            $user_obj = Classes::User($userres);
            $user_obj->unsetFleet($this->getName());
            $user_obj->addFleet($new->getName());
    
            if(count($this->raw[1]) <= 0)
                return true;
            $this->changed = true;
                return true;
        }

        function setRaw($raw)
        {
            if(!$this->status) return false;

            $this->raw = $raw;
            $this->changed = true;
            return true;
        }
        
        function getRaw()
        {
            if(!$this->status) return false;
            
            return $this->raw;
        }

        function factor($user, $factor=false)
        {
            if(!$this->status || $this->started() || !isset($this->raw[1][$user])) return false;

            if(!$factor) return $this->raw[1][$user][2];

            $this->raw[1][$user][2] = $factor;
            $this->changed = true;
            return true;
        }

        function getFleetList($user)
        {
            if(!$this->status || !isset($this->raw[1][$user])) 
                return false;

            return $this->raw[1][$user][0];
        }

        function getUsersList()
        {
            if(!$this->status) return false;

            return array_keys($this->raw[1]);
        }

        function from($user)
        {
            if(!$this->status || !isset($this->raw[1][$user])) return false;

            return $this->raw[1][$user][1];
        }
        
        function getFleetContent()
        {
        	return $this->raw[1];
        }

        function isATarget($target)
        {
            if(!$this->status) return false;

            return (isset($this->raw[0][$target]) || isset($this->raw[0][$target.'T']));
        }

        function renameUser($old_name, $new_name)
        {
            if(!$this->status) return false;

            if(!isset($this->raw[1][$old_name])) return true;
            if($old_name == $new_name) return 2;

            $this->raw[1][$new_name] = $this->raw[1][$old_name];
            unset($this->raw[1][$old_name]);
            $this->changed = true;
            return true;
        }

        function checkBndRaid($bndfleet)
        {
        $keys = array_keys($this->raw[1]);
            $user = array_shift($keys);
            

            if(array_sum($this->raw[1][$user][0]) <= 0) return false;

            # Geschwindigkeitsfaktoren der eigenen Teilnehmer abstimmen
            $koords = array_keys($this->raw[0]);
            $koords = $koords_t = array_shift($koords);
            if(substr($koords, -1) == 'T') $koords = substr($koords, 0, -1);
            $time = $this->calcTime($user, $this->raw[1][$user][1], $koords);

            if(count($keys) > 1)
            {
                foreach($keys as $key)
                {
                    $this_time = calcTime($key, $this->raw[1][$key][1], $koords);
                    $this->raw[1][$key][2] = $this_time/$time;

                }
            }

            $this->raw[2] = time();
            $arrival = round($this->getNextArrival());
            
            #Ankunftszeit der Leitflotte
            $that = Classes::Fleet($bndfleet);
            $arrivalbnd = $that->getArrivalTime();

            #Zeitabgleich. Nicht mehr als 40% Unterschied.
            $remtimebnd = $arrivalbnd - time();
            $remtimeown = $arrival - time();
            
            if( $remtimeown > ( $remtimebnd * 1.4 ) )
            {
                $this->destroy();
                return false;
            }

            if( $remtimeown > $remtimebnd )
            {
                
                $newtime = $remtimeown + time();
                $that->raw[7][0] = $newtime;
                $conn = sqlite_open(global_setting("EVENT_FILE"), 0666);

                        $gta = $bndfleet;

                        $q = "UPDATE events SET time = '".$newtime."' WHERE fleet = '".$gta."'" or die ('ERROR');

                        return sqlite_query($conn, $q);
                        
                 sqlite_close($conn);
                         $this->changed = true;    
            }

            $this->destroy();
            return true;
        }

        function start()
        {
            if(!$this->status || $this->started()) return false;
            if(count($this->raw[1]) <= 0 || count($this->raw[0]) <= 0) return false;
            
            $keys = array_keys($this->raw[1]);
            $user = array_shift($keys);
            
            $log = new Logger();
            $log->logIt( LOG_USER_FLEET, "start() -- Fleet Start. User: ".$user."  Fleet-ID: ".$this->getName());
                        
            if(array_sum($this->raw[1][$user][0]) <= 0)
            {
                return false;
            }

            # Geschwindigkeitsfaktoren der anderen Teilnehmer abstimmen
            $koords = array_keys($this->raw[0]);
            $koords = $koords_t = array_shift($koords);
            if(substr($koords, -1) == 'T') $koords = substr($koords, 0, -1);
            $time = $this->calcTime($user, $this->raw[1][$user][1], $koords);

            if(count($keys) > 1)
            {
                foreach($keys as $key)
                {
                    $this_time = calcTime($key, $this->raw[1][$key][1], $koords);
                    $this->raw[1][$key][2] = $this_time/$time;

                }
            }
            $this->raw[2] = time();
            $arrival = round($this->getNextArrival());
            $hold = round($this->getHoldTime());
            $this->raw[6][0] = $hold;
            $this->raw[7][0] = $arrival;
            $this->raw[7][1] = ($hold + $arrival);
            
            # In Eventdatei eintragen
            $this->createNextEvent();
            $this->changed = true;
        }


        function started()
        {
            if( !$this->status )
                return false;

            return ($this->raw[2] !== false);
        }

        function getDistance($start, $target)
        {
             __autoload('Galaxy');
             
            $this_pos = explode(':', $start);
            $that_pos = explode(':', $target);

            # Entfernung berechnen
            if($this_pos[0] == $that_pos[0]) # Selbe Galaxie
            {
                if($this_pos[1] == $that_pos[1]) # Selbes System
                {
                    if($this_pos[2] == $that_pos[2]) # Selber Planet
                        $distance = 0.001;
                    else # Anderer Planet
                        $distance = 0.1*diff($this_pos[2], $that_pos[2]);
                }
                else
                {
                    # Anderes System

                    $this_x_value = $this_pos[1]-($this_pos[1]%100);
                    $this_y_value = $this_pos[1]-$this_x_value;
                    $this_y_value -= $this_y_value%10;
                    $this_z_value = $this_pos[1]-$this_x_value-$this_y_value;
                    $this_x_value /= 100;
                    $this_y_value /= 10;

                    $that_x_value = $that_pos[1]-($that_pos[1]%100);
                    $that_y_value = $that_pos[1]-$that_x_value;
                    $that_y_value -= $that_y_value%10;
                    $that_z_value = $that_pos[1]-$that_x_value-$that_y_value;
                    $that_x_value /= 100;
                    $that_y_value /= 10;

                    $x_diff = diff($this_x_value, $that_x_value);
                    $y_diff = diff($this_y_value, $that_y_value);
                    $z_diff = diff($this_z_value, $that_z_value);

                    $distance = sqrt(pow($x_diff, 2)+pow($y_diff, 2)+pow($z_diff, 2));
                }
            }
            else # Andere Galaxie
            {
                $galaxy_count = getGalaxiesCount();

                $galaxy_diff_1 = diff($this_pos[0], $that_pos[0]);
                $galaxy_diff_2 = diff($this_pos[0]+$galaxy_count, $that_pos[0]);
                $galaxy_diff_3 = diff($this_pos[0], $that_pos[0]+$galaxy_count);
                $galaxy_diff = min($galaxy_diff_1, $galaxy_diff_2, $galaxy_diff_3);

                $radius = (30*$galaxy_count)/(2*pi());
                $distance = sqrt(2*pow($radius, 2)-2*$radius*$radius*cos(($galaxy_diff/$galaxy_count)*2*pi()));
            }

            $distance = round($distance*1000);

            return $distance;
        }

        function arriveAtNextTarget()
        {
            global $types_message_types;
            
            // Logs for arriveAtNextTarget() - mostly fleet stuff
            $log = new Logger();
            $log->logIt( LOG_USER_FLEET, "arriveAtNextTarget() -- Arrive at Next Target. Flotten-ID: ".$this->getName() );

            if($this->status != 1) 
                return false;
            
            $log->logIt( LOG_USER_FLEET, "arriveAtNextTarget() -- Arrive at Next Target nach Status. Flotten-ID:  ".$this->getName() );

            $keys = array_keys($this->raw[0]);
            $next_target = $next_target_nt = array_shift($keys);
            
            if(substr($next_target_nt, -1) == 'T') 
                $next_target_nt = substr($next_target_nt, 0, -1);
            
            $keys2 = array_keys($this->raw[1]);
            $first_user = array_shift($keys2);

            $type = $this->raw[0][$next_target][0];
            $back = $this->raw[0][$next_target][1];

            $besiedeln = false;
            $log->logIt( LOG_USER_FLEET, "arriveAtNextTarget() -- Arrive at Next Target Zuordungen End. Flotten-ID:  ".$this->getName()." User:".$first_user);

            if($type == 1 && !$back)
            {
                $log->logIt( LOG_USER_FLEET, "arriveAtNextTarget() -- Besiedeln. Flotten-ID:  ".$this->getName() );               

                # Besiedeln
                $target = explode(':', $next_target_nt);
                $target_galaxy = Classes::Galaxy($target[0]);
                $target_owner = $target_galaxy->getPlanetOwner($target[1], $target[2]);

                if($target_owner)
                {
                    # Planet ist bereits besiedelt
                    $log->logIt( LOG_USER_FLEET, "arriveAtNextTarget() -- Planet schon besiedelt. Planetenbesitzer: ".$target_owner." Flotten-ID:  ".$this->getName() );

                    $message = Classes::Message();
                    if($message->create())
                    {
                        $message->text('Ihre Flotte erreicht den Planeten '.$next_target_nt.' und will mit der Besiedelung anfangen. Jedoch ist der Planet bereits vom Spieler '.$target_owner." besetzt, und Ihre Flotte macht sich auf den R\xc3\xbcckweg.");
                        $message->subject('Besiedelung von '.$next_target_nt.' fehlgeschlagen');
                        $message->addUser($first_user, 5);
                        #if(!$message->addUser($first_user, 5)) fwrite($fo, date('Y-m-d, H:i:s')."  Planet schon besiedlet. Message addUser fehlgeschlagen. User: ".$first_user." Flotten-ID:  ".$this->getName()."\n");

                    }
                    #if(!$message->create()) fwrite($fo, date('Y-m-d, H:i:s')."  Planet schon besiedelt Message fehlgeschlagen. User: ".$first_user." Flotten-ID:  ".$this->getName()."\n");

                }
                else
                {
                    $start_user = Classes::User($first_user);
                    if(!$start_user->checkPlanetCount())
                    {
                        $log->logIt( LOG_USER_FLEET, "arriveAtNextTarget() -- Planetenlimit erreicht. Flotten-ID:  ".$this->getName() ); 

                        # Planetenlimit erreicht
                        $message = Classes::Message();
                        if($message->create())
                        {
                            $message->subject('Besiedelung von '.$next_target_nt.' fehlgeschlagen');
                            $message->text("Ihre Flotte erreicht den Planeten ".$next_target_nt." und will mit der Besiedelung anfangen. Als Sie jedoch Ihren Zentralcomputer um Bestätigung für die Besiedelung bittet, kommt dieser durcheinander, da Sie schon so viele Planeten haben und er nicht so viele gleichzeitig kontrollieren kann, und schickt in Panik Ihrer Flotte das Signal zum Rückflug.");
                            $message->addUser($first_user, 5);
                            #if(!$message->addUser($first_user, 5)) fwrite($fo, date('Y-m-d, H:i:s')."  Planetenlimit erreicht. Message addUser fehlgeschlagen Flotten-ID:  ".$this->getName()."\n");

                        }
                        #if(!$message->create()) fwrite($fo, date('Y-m-d, H:i:s')."  Planetenlimit erreicht. Message fehlgeschlagen Flotten-ID:  ".$this->getName()."\n");

                    }
                    else
                    {
                        $besiedeln = true;
                        $log->logIt( LOG_USER_FLEET, "arriveAtNextTarget() -- Besiedlen = true. Flotten-ID:  ".$this->getName() );    
                    }
                }
            }

            if($type != 6 && !$back && !$besiedeln)
            {
                $log->logIt( LOG_USER_FLEET, "arriveAtNextTarget() -- Nicht stationieren, Flotte fliegt weiter. Flotten-ID:  ".$this->getName() );

                # Nicht stationieren: Flotte fliegt weiter
                $further = true;
                
                $target = explode(':', $next_target_nt);
                $target_galaxy = Classes::Galaxy($target[0], false);
                
                if(!$target_galaxy->getStatus()) 
                {
                    return false;
                }
                
                $target_owner = $target_galaxy->getPlanetOwner($target[1], $target[2]);
                
                if($target_owner)
                {
                    $target_user = Classes::User($target_owner);
                    
                    if(!$target_user->getStatus()) 
                    {
                        return false;
                    }
                    
                    $target_user->setActivePlanet($target_user->getPlanetByPos($next_target_nt));
                }
                else 
                {
                    $target_user = false;
                }

                if(($type == 3 || $type == 4) && !$target_owner)
                {
                    # Angriff und Transport nur bei besiedelten Planeten
                    # moeglich.
                    $log->logIt( LOG_USER_FLEET, "arriveAtNextTarget() -- Angriff und Transport nur bei besiedleten Planeten. Flotten-ID:  ".$this->getName());

                    $message_obj = Classes::Message();
                    if($message_obj->create())
                    {
                        $message_obj->subject($next_target_nt.' unbesiedelt');
                        $message_obj->text("Ihre Flotte erreicht den Planeten ".$next_target_nt." und will ihren Auftrag ausf\xc3\xbchren. Jedoch wurde der Planet zwischenzeitlich verlassen und Ihre Flotte macht sich auf den weiteren Weg.");
                        foreach(array_keys($this->raw[1]) as $username)
                            $message_obj->addUser($username, $types_message_types[$type]);
                    }
                }
                else
                {
                    switch($type)
                    {
                        case 2: # Sammeln
                            $log->logIt( LOG_USER_FLEET, "arriveAtNextTarget() -- Sammeln. Flotten-ID:  ".$this->getName());

                            $ress_max = truemmerfeld::get($target[0], $target[1], $target[2]);
                            $ress_max_total = array_sum($ress_max);

                            # Transportkapazitaeten
                            $trans = array();
                            $trans_total = 0;
                            foreach($this->raw[1] as $username=>$info)
                            {
                                $this_trans_used = array_sum($info[3][0]);
                                $this_trans_tf = 0;
                                $this_trans_total = 0;

                                $this_user = Classes::User($username);
                                foreach($info[0] as $id=>$count)
                                {
                                    $item_info = $this_user->getItemInfo($id, 'schiffe');
                                    $this_trans = $item_info['trans'][0]*$count;
                                    $this_trans_total += $this_trans;
                                    if(in_array(2, $item_info['types']))
                                        $this_trans_tf += $this_trans;
                                }

                                $this_trans_free = $this_trans_total-$this_trans_used;
                                if($this_trans_free < $this_trans_tf)
                                    $this_trans_tf = $this_trans_free;
                                $trans[$username] = $this_trans_tf;
                            }
                            $trans_total = array_sum($trans);

                            if($trans_total < $ress_max_total)
                            {
                                $f = $trans_total/$ress_max_total;
                                $ress_max[0] = floor($ress_max[0]*$f);
                                $ress_max[1] = floor($ress_max[1]*$f);
                                $ress_max[2] = floor($ress_max[2]*$f);
                                $ress_max[3] = floor($ress_max[3]*$f);
                                $ress_max_total = array_sum($ress_max);
                                $diff = $trans_total-$ress_max_total;
                                $diff2 = $diff%4;
                                $each = $diff-$diff2;
                                $ress_max[0] += $each;
                                $ress_max[1] += $each;
                                $ress_max[2] += $each;
                                $ress_max[3] += $each;
                                switch($diff)
                                {
                                    case 3: $ress_max[2]++;
                                    case 2: $ress_max[1]++;
                                    case 1: $ress_max[0]++;
                                }
                            }

                            $got_ress = array();

                            foreach($trans as $user=>$cap)
                            {
                                $rtrans = array();
                                $p = $cap/$trans_total;
                                $rtrans[0] = floor($ress_max[0]*$p);
                                $rtrans[1] = floor($ress_max[1]*$p);
                                $rtrans[2] = floor($ress_max[2]*$p);
                                $rtrans[3] = floor($ress_max[3]*$p);

                                $this->raw[1][$user][3][0][0] += $rtrans[0];
                                $this->raw[1][$user][3][0][1] += $rtrans[1];
                                $this->raw[1][$user][3][0][2] += $rtrans[2];
                                $this->raw[1][$user][3][0][3] += $rtrans[3];

                                $got_ress[$username] = $rtrans;
                            }

                            # Aus dem Truemmerfeld abziehen
                            truemmerfeld::sub($target[0], $target[1], $target[2], $ress_max[0], $ress_max[1], $ress_max[2], $ress_max[3]);

                            $tr_verbl = truemmerfeld::get($target[0], $target[1], $target[2]);

                            # Nachrichten versenden
                            foreach($got_ress as $username=>$rtrans)
                            {
                                $message = Classes::Message();
                                if(!$message->create()) continue;
                                $message->subject('Abbau auf '.$next_target_nt);
                                $message->html(true);
                                
                                $messageText =
"<p>Ihre Flotte erreicht das Truemmerfeld auf {$next_target_nt} und belaedt die {$trans_total} Tonnen Sammlerkapazitaet mit folgenden Rohstoffen: $rtrans[0] Carbon, $rtrans[1] Aluminium, $rtrans[2] Wolfram und $rtrans[3] Radium.</p>
<h3>Verbleibende Rohstoffe im Truemmerfeld</h3>
<dl class=\"ress truemmerfeld-verbleibend\">
    <dt class=\"c-carbon\">Carbon</dt>
    <dd class=\"c-carbon\">$tr_verbl[0]</dd>

    <dt class=\"c-aluminium\">Aluminium</dt>
    <dd class=\"c-aluminium\">$tr_verbl[1]</dd>

    <dt class=\"c-wolfram\">Wolfram</dt>
    <dd class=\"c-wolfram\">$tr_verbl[2]</dd>

    <dt class=\"c-radium\">Radium</dt>
    <dd class=\"c-radium\">$tr_verbl[3]</dd>
</dl>";
                                                                
                                $message->text( $messageText );
                                $message->addUser($username, 4);
                            }
                            $log->logIt( LOG_USER_FLEET, "arriveAtNextTarget() -- Sammeln Ende. Flotten-ID:  ".$this->getName());

                            break;
                        case 3: # Angriff
                            $log->logIt( LOG_USER_FLEET, "arriveAtNextTarget() -- Case Angriff. Flotten-ID:  ".$this->getName());

                            $angreifer = array();
                            $urangreifer = array();
                            $verteidiger = array();
                            $urfleet = $this->raw[1];
                            $urfleetverteidiger = array();
                            #Angreiferpart                        
                            #Gemeinsame Flotten in ein Userarray bringen
                            $countangreifer = count($this->raw[1]);
                            
                            $log->logIt( LOG_USER_FLEET, "arriveAtNextTarget() -- Case Angriff. Angreifer Count:  ".$countangreifer);

                            if($countangreifer > 1)
                            {
                                $log->logIt( LOG_USER_FLEET, "arriveAtNextTarget() -- Case Angriff. Angreiferflotten in ein Raw bringen-Anfang. Flotten-ID:  ".$this->getName());
        
                                foreach($this->raw[1] as $user1=>$info)
                                {
                                    $log->logIt( LOG_USER_FLEET, "  User:  ".$user1);
                                    $urangreifer[$user1] = $info[0];
                                    $exp = explode("/", $user1);    
                                                                
                                    foreach($this->raw[1][$user1][0] as $id=>$anzahl)
                                    {
                                        $log->logIt( LOG_USER_FLEET, "\nAngriff-Schiffe  ".$id."  Anzahl  ".$anzahl);

                                        if($exp[0] == $user1) 
                                        {
                                            continue;
                                        }
                                        
                                        if(isset($this->raw[1][$exp[0]])) 
                                        {
                                            $this->addFleet($id, $anzahl, $exp[0]);
                                        }
                                        
                                        if($exp[0] !== $user1) 
                                        {
                                            unset($this->raw[1][$user1]);
                                        }
                                    }
                                }
                                $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Case Angriff. Angreiferflotten in ein Raw bringen-Ende. Flotten-ID:  ".$this->getName());
                            }

                            #Ende gemeinsame Flotten in ein Userarray bringen
                            foreach($this->raw[1] as $username=>$info)
                            {
                                $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Angreifer Raw Einzeluser User:  ".$username);
                                $urangreifer[$username] = $info[0];
                                $angreifer[$username] = $info[0];
                            }
                            
                            $target1 = $this->getTargetsList();
                            
                            #Verteidigerpart

                            $verteidiger[$target_owner] = array();
                            $urverteidiger[$target_owner] = array();
                            
                            #Fremdverteidiger
                            $foreign_users = $target_user->getForeignFleetsArray();
                            $countforeign = count($foreign_users);
        
                            $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Case Angriff. Fremdverteidiger Count:  ".$countforeign);
                
                            if($countforeign > 0)
                            {
                                $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Case Angriff. Fremdverteidigerflotten in ein Raw bringen-Anfang. Flotten-ID:  ".$this->getName());

                                foreach($foreign_users as $fleets1)
                                {
                                    $that = Classes::Fleet($fleets1);
                                    $target2 = $that->getTargetsList();
                                    if($target1[0] == $target2[0])
                                    {
                                        $urfleetverteidiger[$fleets1] = $that->raw[1];    
    
                                        foreach($that->raw[1] as $username1=>$info)
                                        {
                                            $urverteidiger[$username1] = $info[0];
                                            $urverteidiger[$username1][1] = $that->getName();
                                            #In ein Array bringen
                                            $exp = explode("/", $username1);                                
                                            foreach($that->raw[1][$username1][0] as $id=>$anzahl)
                                            {
                                                $log->logIt( LOG_USER_FLEET, "\n arriveAtNextTarget() -- Angriff-Schiffe  ".$id."  Anzahl  ".$anzahl);
                                                
                                                if($exp[0] == $target_owner)
                                                {
                                                    $p = 0;
                                                    $target_owner_obj = Classes::User($target_owner);
                                                    $target_owner_obj->changeItemLevel($id, $anzahl, 'schiffe', $p);
                                                    unset($that->raw[1][$username1]);                    
                                                    #if(isset($schiffe_own[$id])) $schiffe_own[$id] += $count;
                                                    #else $schiffe_own[$id] = $count;
                                                    if(count($that->raw[1] < 1)) $that->destroy();
                                                }
                                                else
                                                {
                                                    if($exp[0] == $username1) continue;
                                                    if(!isset($that->raw[1][$exp[0]])) $that->raw[1][$exp[0]] = $that->raw[1][$username1];
                                                    else $that->addFleet($id, $anzahl, $exp[0]);
                                                    if($exp[0] !== $username1) unset($that->raw[1][$username1]);
                                                }
                                            }
                                        }
                                        foreach($that->raw[1] as $username=>$info)
                                        {
                                            $verteidiger[$username] = $info[0];
                                        }
                                    }
                                }
                                $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Case Angriff. Fremdverteidigerflotten in ein Raw bringen-Ende. Flotten-ID:  ".$this->getName());
                            }
                            #Verteidiger Planetenbesitzer
                            $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Case Angriff. Planetenbesitzer Verteidigung ermitteln-Anfang. Flotten-ID:  ".$this->getName());

                            foreach($target_user->getItemsList('schiffe') as $item)
                            {
                                $level = $target_user->getItemLevel($item, 'schiffe');
                                if($level <= 0) continue;
                                $verteidiger[$target_owner][$item] = $level;
                                $urverteidiger[$target_owner][$item] = $level;
                            }
                            
                            foreach($target_user->getItemsList('verteidigung') as $item)
                            {
                                $level = $target_user->getItemLevel($item, 'verteidigung');
                                if($level <= 0) continue;
                                $verteidiger[$target_owner][$item] = $level;
                                $urverteidiger[$target_owner][$item] = $level;
                            }
                            
                            $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Case Angriff. Planetenbesitzer Verteidigung ermitteln-Ende. Flotten-ID:  ".$this->getName());
                            $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Case Angriff. Funktion Battle Übergabe. Flotten-ID:  ".$this->getName());

                            list($winner, $angreifer2, $verteidiger2, $nachrichten_text, $verteidiger_ress, $truemmerfeld) = $this->battle($angreifer, $verteidiger);
                            $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Case Angriff. Funktion Battle R�ckgabe. Flotten-ID:  ".$this->getName());
                            
                            if(array_sum($truemmerfeld) > 0)
                            {
                                truemmerfeld::add($target[0], $target[1], $target[2], $truemmerfeld[0], $truemmerfeld[1], $truemmerfeld[2], $truemmerfeld[3]);
                                $nachrichten_text .= "<p>\n";
                                $nachrichten_text .= "\tFolgende Tr\xc3\xbcmmer zerst\xc3\xb6rter Schiffe sind durch dem Kampf in die Umlaufbahn des Planeten gelangt: ".ths($truemmerfeld[0])."&nbsp;Carbon, ".ths($truemmerfeld[1])."&nbsp;Aluminium, ".ths($truemmerfeld[2])."&nbsp;Wolfram und ".ths($truemmerfeld[3])."&nbsp;Radium.\n";
                                $nachrichten_text .= "</p>\n";
                            }
                            
                            $count = (count($angreifer2));
                            $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Case Angriff. Angreifer Count:  ".$count);

                            if($count > 0)
                            {
                                $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Case Angriff. Urflotte Angreifer wieder einsetzen-Anfang. Flotten-ID:  ".$this->getName());

                                #Urflotten wieder einsetzen
                                $this->raw[1] = $urfleet;
                                #Kampfflotte IDs und Anzahl holen
                                $angreifer3 = array();
                                foreach($this->raw[1] as $username3=>$info)
                                {
                                    $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Case Angriff. Urflotte Angreifer3. User:  ".$username3);            

                                    $angreifer3[$username3] = $info[0];
                                }
                                $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Case Angriff. Urflotte Angreifer wieder einsetzen-Ende. Flotten-ID:  ".$this->getName());

                                #Flottenverluste uebertragen
                                foreach($angreifer2 as $username2=>$ida2)
                                {
                                    $log->logIt( LOG_USER_FLEET," arriveAtNextTarget() -- Case Angriff. Angreifer Flottenverluste Übertragen-Anfang. Flotten-ID:  ".$this->getName()."  User:  ".$username2);

                                    $gesamt3 = 0;
                                    foreach($ida2 as $id2=>$anzahl2)
                                    {
                                        $usernamearray[$username2][$id2] = array();

                                        #Anzahl Einheiten Angreifer2 zaehlen
                                        $countid2[$id2] = $anzahl2;
                                        $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Nach Kampf-Flotten-ID  ".$id2."\nAnzahl  ".$anzahl2."\nUsername  ".$username2);
                                        #Anzahl Einheiten Angreifer3 und Diff vorbereiten;
                                        $gesamtid2 = 0;
                                        $gesamtangreifer3[$username2][$id2] = $gesamtid2;
                                        $diffid = 0;
                                        $diff = array();
                                        #$diff[$id2] = array();
                                        $diff[$id2] = $diffid;
                                    }
                                    
                                    foreach($angreifer3 as $username3=>$ida3)
                                    {
                                        $exp = explode("/", $username3);
                                        #$trueuser = array_key_exists($exp[0], $angreifer2);
                                        #Alle Sub-User aus angreifer3 austragen, wenn User nicht in angreifer2
                                        if(!array_key_exists($exp[0], $angreifer2)) unset($angreifer3[$username3]);
                                        #Falls angreifer3 in angreifer2, dann weiter
                                        if($exp[0] == $username2)
                                        {
                                            #Nur User, die aktuelle Schleife Angreifer2 sind, bearbeiten 
                                            foreach($ida3 as $id3=>$anzahl3)
                                            {
                                                $usernamearray[$username2][$id3][] = $username3;

                                                #Wenn ID3 nicht im ID-Array angreifer2, dann ID3 loeschen
                                                if(!array_key_exists($id3, $angreifer2[$exp[0]])) unset($angreifer3[$username3][$id3]);
                                                    
                                                #Anzahl der verschiedenen IDs zusammenzaehlen
                                                if(isset($angreifer3[$username3][$id3])) $gesamtangreifer3[$username2][$id3] += $anzahl3;
                                            }
                                        }
                                    }
                                    foreach($gesamtangreifer3[$username2] as $id3=>$anzahl)
                                    {
                                            #Differenz berechnen
                                            $diff = $gesamtangreifer3[$username2][$id3]-$countid2[$id3];
                                            if($diff > 0)
                                            {
                                                $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Angreifer Zufallsschleife Anfang. User:  ".$username2."  ID:  ".$id3);

                                                #Diff einzeln bei den Users abzaehlen
                                                $i = 0;
                                                while($i < $diff)
                                                {
                                                    #User zufaellig auswaehlen
                                                    $rand_keys = array_rand($usernamearray[$username2][$id3], 1);
                                                    $userabzug = $usernamearray[$username2][$id3][$rand_keys];
                                                    $varziff = array('1', '2', '3', '4', '5', '6', '7', '8', '9', '0');
                                                    $rand_varziff = array_rand($varziff,1);
                                                    if(isset($angreifer3[$userabzug][$id3]))
                                                    {
                                                        $remainingdiff = ($diff-$i);
                                                        if(($angreifer3[$userabzug][$id3] > 10000000) && ($remainingdiff >= 10000000))
                                                        {
                                                            $minus = (3333333+$rand_varziff);
                                                            $angreifer3[$userabzug][$id3] -= $minus;
                                                            $i += $minus;
                                                        }
                                                        elseif(($angreifer3[$userabzug][$id3] > 1000000) && ($remainingdiff >= 1000000))
                                                        {
                                                            $minus = (333333+$rand_varziff);
                                                            $angreifer3[$userabzug][$id3] -= $minus;
                                                            $i += $minus;
                                                        }
                                                        elseif(($angreifer3[$userabzug][$id3] > 100000) && ($remainingdiff >= 100000))
                                                        {
                                                            $minus = (33333+$rand_varziff);
                                                            $angreifer3[$userabzug][$id3] -= $minus;
                                                            $i += $minus;
                                                        }
                                                        elseif(($angreifer3[$userabzug][$id3] > 10000) && ($remainingdiff >= 10000))
                                                        {
                                                            $minus = (3333+$rand_varziff);
                                                            $angreifer3[$userabzug][$id3] -= $minus;
                                                            $i += $minus;
                                                        }
                                                        elseif(($angreifer3[$userabzug][$id3] > 1000) && ($remainingdiff >= 1000))
                                                        {
                                                            $minus = (333+$rand_varziff);
                                                            $angreifer3[$userabzug][$id3] -= $minus;
                                                            $i += $minus;
                                                        }
                                                        elseif(($angreifer3[$userabzug][$id3] > 100) && ($remainingdiff >= 100))
                                                        {
                                                            $minus = (33+$rand_varziff);
                                                            $angreifer3[$userabzug][$id3] -= $minus;
                                                            $i += $minus;
                                                        }
                                                        elseif(($angreifer3[$userabzug][$id3] > 10) && ($remainingdiff >= 10))
                                                        {
                                                            $angreifer3[$userabzug][$id3] -= 7;
                                                            $i += 7;
                                                        }
                                                        elseif(($angreifer3[$userabzug][$id3] > 5) && ($remainingdiff >= 5))
                                                        {
                                                            $angreifer3[$userabzug][$id3] -= 3;
                                                            $i += 3;
                                                        }
                                                        elseif(($angreifer3[$userabzug][$id3] > 2) && ($remainingdiff >= 2))
                                                        {
                                                            $angreifer3[$userabzug][$id3] -= 2;
                                                            $i += 2;
                                                        }
                                                        else
                                                        {
                                                            $angreifer3[$userabzug][$id3] -= 1;
                                                            $i++;
                                                        }
                                                    }
                                                    #Falls alle Schiffe von Zufallsuser weg User aus Raws loeschen
                                                    if($angreifer3[$userabzug][$id3] == 0)
                                                    {
                                                        $x = array_search ($userabzug, $usernamearray[$username2][$id3]);
                                                        unset($usernamearray[$username2][$id3][$x]);    
                                                        $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Angreifer Zufallsschleife. User aus Zufallsraw löschen User:  ".$userabzug."  ID:  ".$id3);
                                                    }

                                                }
                                                $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Angreifer Zufallsschleife Ende. User:  ".$username2."  ID:  ".$id3."  Flotten abgezogen:  ");
                                            }    
                                    }
                                    #Angreifer 3 ohne gef�llte Id-Raws l�schen
                                    foreach($angreifer3 as $deleteusername=>$info)
                                    {
                                        $exp1 = explode("/", $deleteusername);
                                        $checkcount = 0;
                                        foreach($angreifer3[$deleteusername] as $id=>$anzahlcheck)                                                                                                        
                                        {
                                            $checkcount += $anzahlcheck;
                                        }
                                        if($checkcount == 0)
                                        {
                                            unset($angreifer3[$deleteusername]);
                                            $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Angreifer nicht mehr vorhanden , deshalb L�schung. User:  ".$deleteusername);
                                        }
                                        #Falls der richtige Username nicht mehr in Angreifer 3, Userraw mit naechstem Imploded User fuellen
                                        if(!isset($angreifer3[$exp1[0]]))
                                        {
                                            $angreifer3[$exp1[0]] = $angreifer3[$deleteusername];
                                            unset($angreifer3[$deleteusername]);
                                        }

                                    }
                                    $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Case Angriff. Angreifer Flottenverluste Übertragen-Ende. Flotten-ID:  ".$this->getName());
                                }
                                $angreifer2 = $angreifer3;
                            }
                            
                            # Nachrichten aufteilen
                            $angreifer_keys = array_keys($angreifer);
                            $verteidiger_keys = array_keys($verteidiger);
                            $users_keys = array_merge($angreifer_keys, $verteidiger_keys);
                            $messages = array();
                            foreach($users_keys as $username)
                                $messages[$username] = $nachrichten_text;


                            # Rohstoffe stehlen
                            if($winner == 1)
                            {
                                $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Case Angriff. Angreifer Rohstoffe stehlen. Flotten-ID:  ".$this->getName());

                                # Angreifer haben gewonnen

                                # Maximal die Haelfte der vorhandenen Rohstoffe
                                $ress_max = $target_user->getRess();
                                $ress_max[0] = floor($ress_max[0]*.5);
                                $ress_max[1] = floor($ress_max[1]*.5);
                                $ress_max[2] = floor($ress_max[2]*.5);
                                $ress_max[3] = floor($ress_max[3]*.5);
                                $ress_max[4] = floor($ress_max[4]*.5);
                                unset($ress_max[5]);
                                $ress_max_total = array_sum($ress_max);

                                # Transportkapazitaeten der Angreifer
                                $trans = array();
                                $trans_total = 0;
                                foreach($angreifer2 as $username=>$fleet)
                                {
                                    $exp = explode("/", $username);
                                    $trans[$username] = -array_sum($this->raw[1][$username][3][0]);
                                    $this_user = Classes::User($exp[0]);
                                    foreach($fleet as $id=>$count)
                                    {
                                        $item_info = $this_user->getItemInfo($id, 'schiffe');
                                        #if($item_info) fwrite($fo, date('Y-m-d, H:i:s')."  Case Angriff. Angreifer Rohstoffe stehlen. Item_Info erfolgreich. Flotten-ID:  ".$this->getName()." User: ".$exp[0]."\n");
                                        #if(!$item_info) fwrite($fo, date('Y-m-d, H:i:s')."  Case Angriff. Angreifer Rohstoffe stehlen. Item_Info fehlgeschlagen!!!!!!! Flotten-ID:  ".$this->getName()." User: ".$exp[0]."\n");

                                        if($id != 'S5') $this_trans = $item_info['trans'][0]*$count;
                                        else $this_trans = 0.00000000001;
                                        #if($this_trans) fwrite($fo, date('Y-m-d, H:i:s')."  Case Angriff. Angreifer Rohstoffe stehlen. This_Trans erfolgreich. Flotten-ID:  ".$this->getName()." User: ".$exp[0]."\n");
                                        #if(!$this_trans) fwrite($fo, date('Y-m-d, H:i:s')."  Case Angriff. Angreifer Rohstoffe stehlen. This_Trans fehlgeschlagen!!!!!!! Flotten-ID:  ".$this->getName()." User: ".$exp[0]."\n");

                                        $trans[$username] += $this_trans;
                                        $trans_total += $this_trans;
                                    }
                                }
                                $ress_max_total = array_sum($ress_max);

                                if($trans_total < $ress_max_total)
                                {
                                    $f = $trans_total/$ress_max_total;
                                    $ress_max[0] = floor($ress_max[0]*$f);
                                    $ress_max[1] = floor($ress_max[1]*$f);
                                    $ress_max[2] = floor($ress_max[2]*$f);
                                    $ress_max[3] = floor($ress_max[3]*$f);
                                    $ress_max[4] = floor($ress_max[4]*$f);
                                    $ress_max_total = array_sum($ress_max);
                                    $diff = $trans_total-$ress_max_total;
                                    $diff2 = $diff%5;
                                    $each = $diff-$diff2;
                                    $ress_max[0] += $each;
                                    $ress_max[1] += $each;
                                    $ress_max[2] += $each;
                                    $ress_max[3] += $each;
                                    $ress_max[4] += $each;
                                    switch($diff)
                                    {
                                        case 4: $ress_max[3]++;
                                        case 3: $ress_max[2]++;
                                        case 2: $ress_max[1]++;
                                        case 1: $ress_max[0]++;
                                    }
                                }

                                foreach($trans as $user=>$cap)
                                {
                                    $exp1 = explode("/", $user);
                                    $rtrans = array();
                                    $p = $cap/$trans_total;
                                    $rtrans[0] = floor($ress_max[0]*$p);
                                    $rtrans[1] = floor($ress_max[1]*$p);
                                    $rtrans[2] = floor($ress_max[2]*$p);
                                    $rtrans[3] = floor($ress_max[3]*$p);
                                    $rtrans[4] = floor($ress_max[4]*$p);
                                    $this->raw[1][$user][3][0][0] += $rtrans[0];
                                    $this->raw[1][$user][3][0][1] += $rtrans[1];
                                    $this->raw[1][$user][3][0][2] += $rtrans[2];
                                    $this->raw[1][$user][3][0][3] += $rtrans[3];
                                    $this->raw[1][$user][3][0][4] += $rtrans[4];
                                    $messages[$exp1[0]] .= "\n<p class=\"rohstoffe-erbeutet selbst\">Sie haben ".ths($rtrans[0])." Carbon, ".ths($rtrans[1])." Aluminium, ".ths($rtrans[2])." Wolfram, ".ths($rtrans[3])." Radium und ".ths($rtrans[4])." Tritium erbeutet.</p>\n";
                                }

                                $target_user->subtractRess($ress_max, false);

                                foreach($users_keys as $username)
                                {
                                    if(isset($angreifer2[$username])) continue;
                                    $exp2 = explode("/", $username);
                                    if($exp2[0] == $username)
                                    {
                                        $messages[$username] .= "\n<p class=\"rohstoffe-erbeutet andere\">Die überlebenden Angreifer haben ".ths($ress_max[0])." Carbon, ".ths($ress_max[1])." Aluminium, ".ths($ress_max[2])." Wolfram, ".ths($ress_max[3])." Radium und ".ths($ress_max[4])." Tritium erbeutet.</p>\n";
                                    }
                                }
                            }
                            
                            $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Case Angriff. Angreifer Rohstoffe stehlen-Ende. Flotten-ID:  ".$this->getName());
                            $angreifer_keys = array_keys($urangreifer);
                            #if(count($angreifer_keys ) == 0) fwrite($fo, date('Y-m-d, H:i:s')."  Case Angriff. Angreifer Keys == 0! Flotten-ID:  ".$this->getName()."\n");
                            #if(count($angreifer_keys ) > 0) fwrite($fo, date('Y-m-d, H:i:s')."  Case Angriff. Angreifer Keys  > 0 Flotten-ID:  ".$this->getName()."\n");

                            $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Case Angriff. Angreifer Flotte oder Flottenteile loeschen-Gesamtanfang.");

                            foreach($angreifer_keys as $username)
                            {
                                $exp = explode("/", $username);
                                $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Case Angriff. Angreifer Flotte oder Flottenteile loeschen-Anfang. User:  ".$username);

                                if(!isset($angreifer2[$username]))
                                {
                                    
                                    #Flotten des Sub-Angreifers wurden zerstoert
                                    $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Case Angriff. Sub-Angreifer Raw loeschen. User: ".$username);

                                    unset($this->raw[1][$username]);
                                    $agcount = count($this->raw[1]);
                                    if(!isset($this->raw[1][$username]) && !isset($this->raw[1][$exp[0]]))
                                    {
                                        $user_obj = Classes::User($exp[0]);
                                        $user_obj->unsetFleet($this->getName());
                                        $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Case Angriff. Sub-Angreifer aus User-Raw loeschen. User: ".$username);
                                    }
                                    if($agcount == 0)
                                    {
                                        $further = false;
                                        #$user_obj = Classes::User($exp[0]);
                                        #$user_obj->unsetFleet($this->getName());
                                        $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Case Angriff. Angreifer Flotte Further False. Flotten-ID:  ".$this->getName()."  User: ".$username);
                                    }
                                }
                                else
                                {            
                                    $this->raw[1][$username][0] = $angreifer2[$username];
                                }
                        
                                $user_obj = Classes::User($exp[0]);                                
                                $user_obj->recalcHighscores(false, false, false, true, false);
                                
                                $user_obj->unsetVerbFleet($this->getName());
                                $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Case Angriff. Angreifer Flotte oder Flottenteile loeschen-Ende. User:  ".$username);  
                            }
                            $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Case Angriff. Angreifer Flotte oder Flottenteile loeschen-Gesamtende.");

                            $messagevertaufbau = false;

                            #Planetenbesitzer und vernichtete Flotten aus Urverteidiger austragen
                            if(isset($urverteidiger[$target_owner])) unset($urverteidiger[$target_owner]);
                        
                            foreach($verteidiger_keys as $username)
                            {
                                $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Case Angriff. Verteidiger Flotte Anzahl übertragen in Urverteidiger-Anfang. Flotten-ID:  ".$this->getName());

                                #Einheiten vor dem Kampf holen
                                #$count ist die Anzahl der zusammengefassen Usereinheiten vor dem Kampf
                                foreach($verteidiger[$username] as $id=>$count)
                                {
                                    $count2 = 0;
                                    #Uebriggebliebene Einheiten ermitteln
                                    if(isset($verteidiger2[$username]) && isset($verteidiger2[$username][$id]))
                                        $count2 = $verteidiger2[$username][$id];
                                    #Abziehen der Einheiten
                                    if($count2 != $count)
                                    {
                                        $p = 0;
                                        $user_objv = Classes::User($username);
                                        #Abziehen der Einheiten beim Planetenbesitzer
                                        if($username == $target_owner)
                                        {
                                            if($id == "V0" || $id == "V1" || $id == "V2" || $id == "V3" || $id == "V4" || $id == "V5" || $id == "V6")
                                            {
                                                $count3 = ($count2-$count)*0.25;
                                                $count3 = round($count3);
                                                if($count3 > -1)
                                                {
                                                    $count3 = -1;
                                                }
                                                $user_objv->changeItemLevel($id, $count3, 'verteidigung', $p);
                                                if($count3 !== -1)$messagevertaufbau = true;
                                            }
                                            else
                                            {
                                                $user_objv->changeItemLevel($id, $count2-$count, 'schiffe', $p);
                                            }
                                        }
                                        else
                                        {
                                            #Differenz berechnen (positiver Wert fuer Z�hlschleife)
                                            $diff = ($count-$count2);
                                
                                            #Array mit zum Usernamen gehoerenden Users fuellen
                                            $urusernamearray[$username] = array();
                                            foreach($urverteidiger as $usernameur=>$info)
                                            {
                                                $exp = explode("/", $usernameur);
                                                if($exp[0] == $username) $urusernamearray[$username][] = $usernameur;
                                            }
                                            #Diff einzeln bei den Users abzaehlen
                                            $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Verteidiger-Zufallsschleife Anfang. ID: ".$id." User:  ".$username);
    
                                            $i = 0;
                                            while($i < $diff)
                                            {
                                                $remainingdiff = ($diff-$i);
                                                #User zufaellig auswaehlen
                                                $rand_keys = array_rand($urusernamearray[$username], 1);
                                                $uruserabzug = $urusernamearray[$username][$rand_keys];
                                                $varziff = array('1', '2', '3', '4', '5', '6', '7', '8', '9', '0');
                                                $rand_varziff = array_rand($varziff,1);
                                                if(isset($urverteidiger[$uruserabzug][$id]) && ($urverteidiger[$uruserabzug][$id] !== 0))
                                                {
                                                        $remainingdiff = ($diff-$i);
                                                        if(($urverteidiger[$uruserabzug][$id] > 10000000) && ($remainingdiff >= 10000000))
                                                        {
                                                            $minus = (3333333+$rand_varziff);
                                                            $urverteidiger[$uruserabzug][$id] -= $minus;
                                                            $i += $minus;
                                                        }
                                                        elseif(($urverteidiger[$uruserabzug][$id] > 1000000) && ($remainingdiff >= 1000000))
                                                        {
                                                            $minus = (333333+$rand_varziff);
                                                            $urverteidiger[$uruserabzug][$id] -= $minus;
                                                            $i += $minus;
                                                        }
                                                        elseif(($urverteidiger[$uruserabzug][$id] > 100000) && ($remainingdiff >= 100000))
                                                        {
                                                            $minus = (33333+$rand_varziff);
                                                            $urverteidiger[$uruserabzug][$id] -= $minus;
                                                            $i += $minus;
                                                        }
                                                        elseif(($urverteidiger[$uruserabzug][$id] > 10000) && ($remainingdiff >= 10000))
                                                        {
                                                            $minus = (3333+$rand_varziff);
                                                            $urverteidiger[$uruserabzug][$id] -= $minus;
                                                            $i += $minus;
                                                        }
                                                        elseif(($urverteidiger[$uruserabzug][$id] > 1000) && ($remainingdiff >= 1000))
                                                        {
                                                            $minus = (333+$rand_varziff);
                                                            $urverteidiger[$uruserabzug][$id] -= $minus;
                                                            $i += $minus;
                                                        }
                                                        elseif(($urverteidiger[$uruserabzug][$id] > 100) && ($remainingdiff >= 100))
                                                        {
                                                            $minus = (33+$rand_varziff);
                                                            $urverteidiger[$uruserabzug][$id] -= $minus;
                                                            $i += $minus;
                                                        }
                                                        elseif(($urverteidiger[$uruserabzug][$id] > 10) && ($remainingdiff >= 10))
                                                        {
                                                            $urverteidiger[$uruserabzug][$id] -= 7;
                                                            $i += 7;
                                                        }
                                                        elseif(($urverteidiger[$uruserabzug][$id] > 5) && ($remainingdiff >= 5))
                                                        {
                                                            $urverteidiger[$uruserabzug][$id] -= 3;
                                                            $i += 3;
                                                        }
                                                        elseif(($urverteidiger[$uruserabzug][$id] > 2) && ($remainingdiff >= 2))
                                                        {
                                                            $urverteidiger[$uruserabzug][$id] -= 2;
                                                            $i += 2;
                                                        }
                                                        else
                                                        {
                                                            $urverteidiger[$uruserabzug][$id] -= 1;
                                                            $i++;
                                                        }
                                                }
                                                if(isset($urverteidiger[$uruserabzug][$id]) && ($urverteidiger[$uruserabzug][$id] == 0))
                                                {

                                                    unset($urverteidiger[$uruserabzug][$id]);
                                                }
                                            }
                                            $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Verteidiger Zufallsschleife Ende. ID: ".$id." User:  ".$username." Flotten abgezogen ".$i);
                                        }

                                    }    
                                }
                                $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Case Angriff. Verteidiger Flotte Anzahl �bertragen in Urverteidiger-Ende. Flotten-ID:  ".$this->getName());

                            }
                            
                            if($messagevertaufbau == true)
                            {
                                $messages[$target_owner] .= "\n<p class=\"verteidigung-wiederverwertung\">Es konnten 75% der Verteidigungsanlagen wiederhergestellt werden.</p>\n";
                            }
                            
                            $user_obj = Classes::User($username);
                            $user_obj->recalcHighscores(false, false, false, true, true);

                            #$Urverteidiger in Urflleet einordnen
                            $exist = array();
                            foreach($urverteidiger as $username=>$info)
                            {
                                $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Urverteidiger in Urfleet einordnen Anfang. User:  ".$username);
                                $fleet = $urverteidiger[$username][1];
                                unset($urverteidiger[$username][1]);
                                $urfleetverteidiger[$fleet][$username][0] = $urverteidiger[$username];

                                $that = Classes::Fleet($fleet);
                                    
                                #Urraws in Fleets einsetzen
                                $that->raw[1][$username] = $urfleetverteidiger[$fleet][$username];
                                $countur = (count($urverteidiger[$username]));
                                
                                if($countur == true)
                                {
                                    $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Urverteidiger existiert noch, daher keine Loeschung. Flotten-ID:  ".$this->getName());
                                    $exist[] = $username;
                                }
                                #else fwrite($fo, "\n".time()."  Urverteidiger existiert nicht mehr, daher Loeschung. User:  ".$username);

                            
                                $that->changed = true;
                                $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Urverteidiger in Urfleet einordnen Ende. User:  ".$username);
                            }
                            
                            #Fleets, die nicht in Exist sind, loeschen
                            $foreign_users = $target_user->getForeignFleetsArray();
                            foreach($foreign_users as $fleet1)
                            {
                                $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Urfleetverteidiger loeschen Anfang. Flotten-ID:  ".$fleet1);
                                $that = Classes::Fleet($fleet1);
                                $target = $that->getTargetsList();
                                
                                if($target[0] == $target1[0])
                                {    
                                    $countraw = 0;
                                    foreach($that->raw[1] as $username=>$info)
                                    {
                                        if(!in_array($username, $exist))
                                        {
                                            $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Verteidigerfleet, einzelnes Userraw loeschen. ID:  ".$fleet1."  Username:  ".$username);
                                            unset($that->raw[1][$username]);
                                            
                                            if(count($that->raw[1]) > 0)
                                            {
                                                    $i = 0;
                                                    foreach($that->raw[1] as $username1=>$info)
                                                    {
                                                        while($i < 1)
                                                        {
                                                            $exp = explode("/", $username1);
                                                            if($username1 != $exp[0] && in_array($username1, $exist))
                                                            {
                                                                $that->raw[1][$exp[0]] = $that->raw[1][$username1];
                                                                unset($that->raw[1][$username1]);
                                                                $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Verteidigerfleet, noch ein Userraw vorhanden. Userarray tauschen. ID:  ".$fleet1."  Username:  ".$username1);
                                                            }
                                                            $i++;
                                                        }
                                                    }        
                                            }

                                            if(count($that->raw[1]) < 1)
                                            {
                                                $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Verteidigerfleet, kein Userraw mehr vorhanden. Fleet loeschen. ID:  ".$fleet1."  Username:  ".$username);
                                                $that->destroy();
                                            }
                                            $that->changed = true;
                                        }
                                    }
                                }
                                
                                $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Urfleetverteidiger loeschen Ende. Flotten-ID:  ".$fleet1);      
                            }    
                            
                            #if($messagevertaufbau == true)
                            #$messages[$target_owner] .= "\n<p class=\"verteidigung-wiederverwertung\">Es konnten 75% der Verteidigungsanlagen wiederhergestellt werden.</p>\n";
                            #$user_obj = Classes::User($username);
                            #$user_obj->recalcHighscores(false, false, false, true, true);
                            
                            # Nachrichten zustellen
                            foreach($messages as $username=>$text)
                            {
                                $exp = explode("/", $username);
                                
                                if($exp[0] == $username)
                                {
                                    $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Nachrichten versenden. Anfang. User:  ".$username);                                    
                                    #echo("Fleet.php Message versenden :".date('Y-m-d, H:i:s')."\n");
                                    $message = Classes::Message();
                                    if(!$message->create()) continue;
                                    $message->from($target_owner);
                                    $message->to($username);
                                    $message->html(true);
                                    $message->text($text);
                                    $message->subject("Kampf auf ".$next_target_nt);
                                    $message->addUser($exp[0], 1);
                                    $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Nachrichten versenden. Ende. User:  ".$username);
                                }                        
                            }
                            $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Case Angriff Ende.");

                            break;

                        case 4: # Transport
                            $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Transport. Flotten-ID:  ".$this->getName());
                            
                            if(!isset($this->raw[6][1]))
                                $message_text = array(
                                $target_owner => "Ein Transport erreicht Ihren Planeten \xe2\x80\x9e".$target_user->planetName()."\xe2\x80\x9c (".$next_target_nt."). Folgende Spieler liefern Güter ab:\n"
                                );
                            else
                            {
                                $message_text = array(
                                    $target_owner => "Ein Transport erreicht Ihren Planeten \xe2\x80\x9e".$target_user->planetName()."\xe2\x80\x9c (".$next_target_nt."). Die Flotte hat das Transportgut an Board behalten.\n",
                                    $first_user => "Ein Transport erreicht den Planeten \xe2\x80\x9e".$target_user->planetName()."\xe2\x80\x9c (".$next_target_nt."). Die Flotte hat das Transportgut an Board behalten.\n"
                                );
                            }

                            if(!isset($this->raw[6][0])) $this->raw[6][0] = 0;
                                           if($this->raw[6][0] !== -1 && !isset($this->raw[6][1]))
                                           {
                                           # Rohstoffe abliefern, Handel
                                $handel = array();
                                $make_handel_message = false;
                                foreach($this->raw[1] as $username=>$data)
                                {
                                    $write_this_username = ($username != $target_owner);
                                    if($write_this_username) $message_text[$username] = "Ihre Flotte erreicht den Planeten \xe2\x80\x9e".$target_user->planetName()."\xe2\x80\x9c (".$next_target_nt.", Eigent\xc3\xbcmer: ".$target_owner.") und liefert folgende Güter ab:\n";
                                    $message_text[$target_owner] .= $username.": ";
                                    if($write_this_username) $message_text[$username] .= "Carbon: ".ths($data[3][0][0], true).", Aluminium: ".ths($data[3][0][1], true).", Wolfram: ".ths($data[3][0][2], true).", Radium: ".ths($data[3][0][3], true).", Tritium: ".ths($data[3][0][4], true);
                                    $message_text[$target_owner] .= "Carbon: ".ths($data[3][0][0], true).", Aluminium: ".ths($data[3][0][1], true).", Wolfram: ".ths($data[3][0][2], true).", Radium: ".ths($data[3][0][3], true).", Tritium: ".ths($data[3][0][4], true);
                                    $target_user->addRess($data[3][0]);
                                    $this->raw[1][$username][3][0] = array(0,0,0,0,0);
                                    if($target_owner == $username && array_sum($data[3][1]) > 0)
                                    {
                                        $items_string = makeItemsString($data[3][1]);
                                        if($write_this_username) $message_text[$username] .= "\n".$items_string;
                                        $message_text[$target_owner] .= "; ".$items_string;
                                        foreach($data[3][1] as $id=>$anzahl)
                                            $target_user->changeItemLevel($id, $anzahl, 'roboter');
                                        $this->raw[1][$username][3][1] = array();
                                    }
                                    if($write_this_username) $message_text[$username] .= "\n";
                                    $message_text[$target_owner] .= "\n";
                                    if(array_sum_r($data[4]) > 0)
                                    {
                                                        $ress_max = $target_user->getRess();
                                            $ress_max[0] = floor($ress_max[0]);
                                            $ress_max[1] = floor($ress_max[1]);
                                            $ress_max[2] = floor($ress_max[2]);
                                            $ress_max[3] = floor($ress_max[3]);
                                            $ress_max[4] = floor($ress_max[4]);
                                            unset($ress_max[5]);
                                                            $ress_max_total = array_sum($ress_max);
                                                        $handel[$username] = $data[4];
                                                        if($data[4][0][0] > $ress_max[0])
                                                        {
                                                         unset($data[4][0][0]);
                                                             $data[4][0][0] = $ress_max[0];
                                                        }
                                                        if($data[4][0][1] > $ress_max[1])
                                                        {
                                                             unset($data[4][0][1]);
                                                             $data[4][0][1] = $ress_max[1];
                                                        }
                                                        if($data[4][0][2] > $ress_max[2])
                                                        {
                                                            unset($data[4][0][2]);
                                                            $data[4][0][2] = $ress_max[2];
                                                        }
                                                        if($data[4][0][3] > $ress_max[3])
                                                        {
                                                            unset($data[4][0][3]);
                                                            $data[4][0][3] = $ress_max[3];
                                                        }
                                                        if($data[4][0][4] > $ress_max[4])
                                                        {
                                                            unset($data[4][0][2]);
                                                            $data[4][0][4] = $ress_max[4];
                                                        }
                                                       $handel[$username] = $data[4];
                                                       $target_user->subtractRess($data[4][0]); #Ress beim Handelspartner bei Ankunft abziehen
                                                       $this->raw[1][$username][3][0] = $data[4][0];
                                        #$this->raw[1][$username][3][1] = $data[4][1];
                                        $this->raw[1][$username][4] = array(array(0,0,0,0,0),array());
                                                       $make_handel_message = true;
                                    }
                                }
                                if($make_handel_message)
                                {
                                    $message_text[$target_owner] .= "\nFolgender Handel wird durchgef\xc3\xbchrt:\n";
                                    foreach($handel as $username=>$h)
                                    {
                                        $write_this_username = ($username != $target_owner);
                                        if($write_this_username)
                                        {
                                            $message_text[$username] .= "\nFolgender Handel wird durchgef\xc3\xbchrt:\n";
                                            $message_text[$username] .= "Carbon: ".ths($h[0][0], true).", Aluminium: ".ths($h[0][1], true).", Wolfram: ".ths($h[0][2], true).", Radium: ".ths($h[0][3], true).", Tritium: ".ths($h[0][4], true);
                                        }
                                        $message_text[$target_owner] .= $username.": Carbon: ".ths($h[0][0], true).", Aluminium: ".ths($h[0][1], true).", Wolfram: ".ths($h[0][2], true).", Radium: ".ths($h[0][3], true).", Tritium: ".ths($h[0][4], true);
                                        if(array_sum($h[1]) > 0)
                                        {
                                            if($write_this_username) $message_text[$username] .= "\n";
                                            $message_text[$target_owner] .= "; ";
                                            $items_string = makeItemsString($h[1]);
                                            if($write_this_username) $message_text[$username] .= $items_string;
                                            $message_text[$target_owner] .= $items_string;
                                        }
                                        if($write_this_username) $message_text[$username] .= "\n";
                                        $message_text[$target_owner] .= "\n";
                                    }
                                }
                            }
                            foreach($message_text as $username=>$text)
                            {
                                $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Message Transport Anfang. User:  ".$username);

                                $message_obj = Classes::Message();
                                if($message_obj->create())
                                {
                                    if($username == $target_owner && !isset($this->raw[1][$username]))
                                    {
                                        $message_obj->subject('Ankunft eines fremden Transportes auf '.$next_target_nt);
                                        $users = array_keys($this->raw[1]);
                                        $message_obj->from(array_shift($users));
                                        $message_obj->to($target_owner);
                                    }
                                    else
                                    {
                                        $message_obj->subject('Ankunft Ihres Transportes auf '.$next_target_nt);
                                        $message_obj->from($target_owner);
                                        $message_obj->to($username);
                                    }
                                    $message_obj->text($text);
                                    $message_obj->addUser($username, $types_message_types[$type]);
                                }
                                $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Message Transport Ende. User:  ".$username);                    
                            }    
                            $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Transport Ende. Flotten-ID:  ".$this->getName());

                            break;
                        case 5: # Spionage
                            $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- CASE SPIONAGE. Fleet ID: ".$this->getName());

                            if(!$target_owner)
                            {
                                $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- CASE SPIONAGE. Unbesiedelter Planet.");

                                # Zielplanet ist nicht besiedelt
                                $message_text = "<h3>Spionagebericht des Planeten ".utf8_htmlentities($next_target_nt)."</h3>\n";
                                $message_text .= "<div id=\"spionage-planet\">\n";
                                $message_text .= "\t<h4>Planet</h4>\n";
                                $message_text .= "\t<dl class=\"planet_".$target_galaxy->getPlanetClass($target[1], $target[2])."\">\n";
                                $message_text .= "\t\t<dt class=\"c-felder\">Felder</dt>\n";
                                $message_text .= "\t\t<dd class=\"c-felder\">".ths($target_galaxy->getPlanetSize($target[1], $target[2]))."</dd>\n";
                                $message_text .= "\t</dl>\n";
                                $message_text .= "</div>";

                                $message_text .= "\n<p class=\"besiedeln\">";
                                $message_text .= "\n\t<a href=\"flotten.php?action=besiedeln&amp;action_galaxy=".htmlentities(urlencode($target[0]))."&amp;action_system=".htmlentities(urlencode($target[1]))."&amp;action_planet=".htmlentities(urlencode($target[2]))."\" title=\"Schicken Sie ein Besiedelungsschiff zu diesem Planeten\">Besiedeln</a>";
                                $message_text .= "\n</p>";
 
                                $message = Classes::Message();

                                if($message->create())
                                {
                                    $message->html(true);
                                    $message->text($message_text);
                                    $message->subject('Spionage des Planeten '.$next_target_nt);
                                    
                                    foreach(array_keys($this->raw[1]) as $username)
                                    {
                                        $message->addUser($username, $types_message_types[$type]);
                                    }
                                }
                            }
                            else
                            {
                                # Zielplanet ist besiedelt
                                $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- CASE SPIONAGE. Besiedelter Planet.");
                                $users = array_keys($this->raw[1]);
                                $verbuendet = true;
                                
                                foreach($users as $username)
                                {
                                    if(!$target_user->isVerbuendet($username))
                                    {
                                        $verbuendet = false;
                                        break;
                                    }
                                }
                                
                                if(!$verbuendet)
                                {
                                    # Spionagetechnikdifferenz ausrechnen
                                    $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- CASE SPIONAGE. Spionagetechnik Anfang.");

                                    $owner_level = $target_user->getItemLevel('F1', 'forschung');
                                    $others_level = 0;
                                    foreach($users as $username)
                                    {
                                        if(isset($this->raw[1][$username][0]['S5']))
                                            $others_level += $this->raw[1][$username][0]['S5'];
                                    }
                                    $others_level -= count($users);
                                    if($others_level < 0) $others_level = 0;

                                    $max_f1 = 0;
                                    foreach($users as $username)
                                    {
                                        $user = Classes::User($username);
                                        $this_f1 = $user->getItemLevel('F1', 'forschung');
                                        if($this_f1 > $max_f1) $max_f1 = $this_f1;
                                    }
                                    $others_level += $max_f1;

                                    if($owner_level == 0) $diff = 5;
                                    else $diff = floor(pow($others_level/$owner_level, 2));
                                    $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- CASE SPIONAGE. Spionagetechnik Ende.");

                                }
                                else # Spionierter Planet liefert alle Daten aus, wenn alle Spionierenden verbuendet sind
                                    $diff = 5;

                                if($diff > 5)
                                    $diff = 5;

                                $message_text = "<h3>Spionagebericht des Planeten \xe2\x80\x9e".utf8_htmlentities($target_galaxy->getPlanetName($target[1], $target[2]))."\xe2\x80\x9c (<a href=\"flotten.php?action_galaxy=".htmlentities(urlencode($target[0]))."&amp;action_system=".htmlentities(urlencode($target[1]))."&amp;action_planet=".htmlentities(urlencode($target[2]))."\" title=\"Koordinaten ins Flottenmenü übernehmen\">".utf8_htmlentities($next_target_nt)."</a>, Eigent\xc3\xbcmer: ".utf8_htmlentities($target_owner).")</h3>\n";
                                $message_text .= "<div id=\"spionage-planet\">\n";
                                $message_text .= "\t<h4>Planet</h4>\n";
                                $message_text .= "\t<dl class=\"planet_".$target_galaxy->getPlanetClass($target[1], $target[2])."\">\n";
                                $message_text .= "\t\t<dt class=\"c-felder\">Felder</dt>\n";
                                $message_text .= "\t\t<dd class=\"c-felder\">".$target_user->getTotalFields()."</dd>\n";
                                $message_text .= "\t</dl>\n";
                                $message_text .= "</div>";

                                $message_text2 = array();
                                switch($diff)
                                {
                                    case 5: # Roboter und Fremdflotte zeigen
                                        $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- CASE SPIONAGE. Roboter und Fremdflotte.");
                                        $next = &$message_text2[];
                                        $next = "\n<div id=\"spionage-fremdschiffe\">";
                                        $next .= "\n\t<h4>Fremdflotte</h4>";
                                        $next .= "\n\t<ul>";
                                        $foreign_users = $target_user->getForeignFleetsArray();
                                        $target1 = $this->getTargetsList();
                                        foreach($foreign_users as $flotte)
                                        {

                                                                            $that = Classes::Fleet($flotte);
                                            $target = $that->getTargetsList();
                                            if($target[0] == $target1[0])
                                            {
                                                $vert = array();
                                                foreach($that->raw[1] as $usernamevert=>$info)
                                                {
                                                    $exp = explode("/", $usernamevert);
                                                    $vert[$usernamevert] = $info[0];
                                                }
                                                    $next .= "\n\t<h4>".$exp[0]."</h4>";
                                                
                                                foreach($vert[$usernamevert] as $id=>$anzahl)
                                                {
                                                    $vert[$exp[0]][$id] += $anzahl;
                                                }
                                                foreach($vert[$exp[0]] as $id=>$anzahl)
                                                {

                                                    if($id =='S0') $id = 'Kleiner Transporter';
                                                    if($id =='S1') $id = 'Grosser Transporter';
                                                    if($id =='S2') $id = 'Transcube';
                                                    if($id =='S3') $id = 'Sammler';
                                                    if($id =='S5') $id = 'Spionagesonde';
                                                    if($id =='S6') $id = 'Besiedlungsschiff';
                                                    if($id =='S7') $id = 'Kampfkapsel';
                                                    if($id =='S8') $id = 'Leichter J�ger';
                                                    if($id =='S9') $id = 'Schwerer J�ger';
                                                    if($id =='S10') $id = 'Leichte Fregatte';
                                                    if($id =='S11') $id = 'Schwere Fregatte';
                                                    if($id =='S12') $id = 'Leichter Kreuzer';
                                                    if($id =='S13') $id = 'Schwerer Kreuzer';
                                                    if($id =='S14') $id = 'Schlachtschiff';
                                                    if($id =='S15') $id = 'Zerstörer';
                                                    if($id =='S16') $id = 'Warcube';
                                                    $next .= "\n\t\t<li>".$id." <span class=\"anzahl\">(".ths($anzahl).")</span></li>";
                                                }
                                            }
                                        }
                                        $next .= "\n\t</ul>";
                                        $next .= "\n</div>";
                                        unset($next);


                                        $next = &$message_text2[];
                                        $next = "\n<div id=\"spionage-roboter\">";
                                        $next .= "\n\t<h4>Roboter</h4>";
                                        $next .= "\n\t<ul>";
                                        foreach($target_user->getItemsList('roboter') as $id)
                                        {
                                            if($target_user->getItemLevel($id, 'roboter') <= 0) continue;
                                            $item_info = $target_user->getItemInfo($id, 'roboter');
                                            $next .= "\n\t\t<li>".$item_info['name']." <span class=\"anzahl\">(".ths($item_info['level']).")</span></li>";
                                        }
                                        $next .= "\n\t</ul>";
                                        $next .= "\n</div>";


                                        unset($next);
                                    case 4: # Forschung zeigen

                                        $log->logIt( LOG_USER_FLEET, "  CASE SPIONAGE. Forschung.");

                                        $next = &$message_text2[];
                                        $next = "\n<div id=\"spionage-forschung\">";
                                        $next .= "\n\t<h4>Forschung</h4>";
                                        $next .= "\n\t<ul>";
                                        
                                        foreach($target_user->getItemsList('forschung') as $id)
                                        {
                                            if($target_user->getItemLevel($id, 'forschung') <= 0) continue;
                                            $item_info = $target_user->getItemInfo($id, 'forschung');
                                            $next .= "\n\t\t<li>".$item_info['name']." <span class=\"stufe\">(Level&nbsp;".ths($item_info['level']).")</span>";
                                        }
                                        
                                        $next .= "\n\t</ul>";
                                        $next .= "\n</div>";
                                        unset($next);
                                    case 3: # Schiffe und Verteidigungsanlagen anzeigen
                                        $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- CASE SPIONAGE. Schiffe und Verteidigung");

                                        $next = &$message_text2[];
                                        $next = "\n<div id=\"spionage-schiffe\">";
                                        $next .= "\n\t<h4>Schiffe</h4>";
                                        $next .= "\n\t<ul>";
                                        foreach($target_user->getItemsList('schiffe') as $id)
                                        {
                                            if($target_user->getItemLevel($id, 'schiffe') <= 0) continue;
                                            $item_info = $target_user->getItemInfo($id, 'schiffe');
                                            $next .= "\n\t\t<li>".$item_info['name']." <span class=\"anzahl\">(".ths($item_info['level']).")</span></li>";
                                        }
                                        $next .= "\n\t</ul>";
                                        $next .= "\n</div>";

                                        unset($next);

                                        $next = &$message_text2[];
                                        $next = "\n<div id=\"spionage-verteidigung\">";
                                        $next .= "\n\t<h4>Verteidigung</h4>";
                                        $next .= "\n\t<ul>";
                                        foreach($target_user->getItemsList('verteidigung') as $id)
                                        {
                                            if($target_user->getItemLevel($id, 'verteidigung') <= 0) continue;
                                            $item_info = $target_user->getItemInfo($id, 'verteidigung');
                                            $next .= "\n\t\t<li>".$item_info['name']." <span class=\"anzahl\">(".ths($item_info['level']).")</span></li>";
                                        }
                                        $next .= "\n\t</ul>";
                                        $next .= "\n</div>";
                                        unset($next);

                                    case 2: # Gebaeude anzeigen
                                        $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- CASE SPIONAGE. Gebäude.");

                                        $next = &$message_text2[];
                                        $next = "\n<div id=\"spionage-gebaeude\">";
                                        $next .= "\n\t<h4>Geb\xc3\xa4ude</h4>";
                                        $next .= "\n\t<ul>";
                                        foreach($target_user->getItemsList('gebaeude') as $id)
                                        {
                                            if($target_user->getItemLevel($id, 'gebaeude') <= 0) continue;
                                            $item_info = $target_user->getItemInfo($id, 'gebaeude');
                                            $next .= "\n\t\t<li>".$item_info['name']." <span class=\"stufe\">(Stufe&nbsp;".ths($item_info['level']).")</span></li>";
                                        }
                                        $next .= "\n\t</ul>";
                                        $next .= "\n</div>";
                                        unset($next);
                                    case 1: # Rohstoffe anzeigen
                                        $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- CASE SPIONAGE. Rohstoffe.");

                                        $next = &$message_text2[];
                                        $next = "\n<div id=\"spionage-rohstoffe\">";
                                        $next .= "\n\t<h4>Rohstoffe</h4>";
                                        $next .= "\n\t".format_ress($target_user->getRess(), 1, true);
                                        $next .= "</div>";
                                        unset($next);
                                }
                                $message_text .= implode('', array_reverse($message_text2));
                                $message = Classes::Message();
                                if($message->create())
                                {
                                    $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- CASE SPIONAGE. Message Spion.");
                                    $message->html(true);
                                    $message->subject('Spionage des Planeten '.$next_target_nt);
                                    $message->text($message_text);
                                    $message->from($target_owner);
                                    $message->to($first_user);
                                    foreach($users as $username)
                                        $message->addUser($username, $types_message_types[$type]);
                                }

                                $message = Classes::Message();
                                if($message->create())
                                {
                                    $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- CASE SPIONAGE. Message Opfer.");

                                    $message->subject('Fremde Flotte auf dem Planeten '.$next_target_nt);
                                    $first_user = array_shift($users);
                                    $from_pos_str = $this->raw[1][$first_user][1];
                                    $from_pos = explode(':', $from_pos_str);
                                    $from_galaxy = Classes::Galaxy($from_pos[0]);
                                    $message->text("Eine fremde Flotte vom Planeten \xe2\x80\x9e".$from_galaxy->getPlanetName($from_pos[1], $from_pos[2])."\xe2\x80\x9c (".$from_pos_str.", Eigent\xc3\xbcmer: ".$first_user.") wurde von Ihrem Planeten \xe2\x80\x9e".$target_user->planetName()."\xe2\x80\x9c (".$next_target_nt.") aus bei der Spionage gesichtet.");
                                    $message->from($first_user);
                                    $message->to($target_owner);
                                    $message->addUser($target_owner, $types_message_types[$type]);
                                }
                            }
                    }
                }
                # Weiterfliegen
                $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Weiterfliegen Anfang. Flotten-ID:  ".$this->getName()."");
                $users = array_keys($this->raw[1]);
                if($further)
                {
                    $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Weiterfliegen mit further = true. Flotten-ID:  ".$this->getName());
                    
                    #In die Halteschleife schicken
                    #echo("\nRaw6 ".$this->raw[6][0]."\n");
                    if(isset($this->raw[6][0]) && $this->raw[6][0] > 0)
                    {
                        $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Weiterfliegen in Halteschleife. Flotten-ID:  ".$this->getName());
                        $owner_obj = Classes::User($target_owner);
                        $foreign_fleet = $owner_obj->getForeignFleetsArray();
                        $foreigncount = 0;
                        $usersonhold = array();
                        
                        #Fremdflotten durch Zielabgleich aussortieren, nur Flotten am Ziel werden gez�hlt
                        foreach($foreign_fleet as $fleet1)
                        {
                            $that = Classes::Fleet($fleet1);
                            $target = $that->getTargetsList();
                            $target1 = $this->getTargetsList();
                            if($target[0] == $target1[0])
                            {
                                $foreigncount += count($fleet1);
                            
                                foreach($that->raw[1] as $usernameonhold=>$info)
                                {
                                    $usersonhold[] = $usernameonhold;
                                    if($usernameonhold == $users[0])
                                    {
                                         $fleetid = $fleet1;
                                    }
                                }
                            }
                        }
                        $permitjoin = in_array($users[0], $usersonhold);
                        #var_dump($permitjoin);
                        if($permitjoin == true)
                        {
                            $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Erlaubnis Eindocken in vorhandene Halteflotte. Flotten-ID:  ".$this->getName());

                            $time = $this->getHoldTime();
                            $that = Classes::Fleet($fleetid);
                            $from = $this->raw[1][$users[0]][1];
                            $factor = $this->raw[1][$users[0]][2];
                            $adduser = $that->addUser($users[0], $from, $factor);
                            $that->addNewHoldTime($time, $fleetid);
                            foreach($this->raw[1][$users[0]][0] as $id=>$anzahl)
                            {                                        
                                $that->addFleet($id, $anzahl, $adduser);
                                $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Eindocken von Flotte. Flotten-ID:  ".$this->getName()." in Flotte: ".$that->getName());    
                            }
                            $further = false;;
                        }                                            
    
                        if($foreigncount <= 4)
                        {
                            $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Eindocken in Halteschleife am Plani. Flotten-ID:  ".$this->getName());    

                            $keys = array_keys($this->raw[0]);
                            $to = $to_t = array_shift($keys);
                            $target_user_obj = Classes::User($target_owner);
                            $target_user_obj->addForeignFleet($this->getName());
                            $first_user = array_shift($users);
                            $holdtime = $this->getHoldTime();
                            $duration = $this->getNextDuration();
                            $holdtime1 = ((time() + $holdtime)-$duration);
                            $this->raw[2] = $holdtime1;
                            $newholdtime = -1;
                            
                            unset($this->raw[6][0]);
                            
                            $this->raw[6][0] = $newholdtime;
                            $this->createNextEvent();
                            
                            #Tritiumverbrauch anpassen
                            #$this->raw[1][$username][3][2] -= $this->getTritium($username, $this->raw[1][$username][1], $next_target_nt);
                            #Flugerfahrung anpassen
                            $this->raw[1][$first_user][5] -= $this->getTritium($first_user, $this->raw[1][$first_user][1], $next_target_nt);
                        }
                        $this->changed= true;
                    }
                    else
                    {
                        $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Weiterfliegen ohne Halteschleife. Flotten-ID:  ".$this->getName());
                            
                        $first_user = array_shift($users);
                        $this->raw[3][$next_target] = array_shift($this->raw[0]);
                        $this->raw[2] = time();
                        
                        if(isset($this->raw[6][0]) && $this->raw[6][0] == -1)
                        {
                            $target_user->unsetForeignFleet($this->getName());
                        }
                        
                        if(isset($this->raw[6][0]) && $this->raw[6][0] == -1) 
                        {
                            $this->raw[6][0] = 0;
                        }
                        
                        $this->raw[7][0] = $this->getNextArrival();
                        $this->createNextEvent();        
                                        
                        # Vom Target entfernen
                        if($target_user && $target_owner != $first_user)
                        {
                            $target_user->unsetFleet($this->getName());
                        }
                        
                        $this->changed = true;
                    }        
                    # Flugerfahrung
                    $last_targets = array_keys($this->raw[3]);
                    
                    if(count($last_targets) <= 0) 
                    {
                        $last_target = false;
                    }
                    else
                    {
                        $last_target = array_pop($last_targets);
                        if(substr($last_target, -1) == 'T') $last_target = substr($last_target, 0, -1);
                    }

                    # Flugerfahrung fuer den ersten Benutzer
                    $this_last_target = (($last_target === false) ? $this->raw[1][$first_user][1] : $last_target);
                    $this->raw[1][$first_user][5] += $this->getTritium($first_user, $this_last_target, $next_target);
                    
                    foreach($users as $user)
                    {
                        $exp = explode("/" , $user);                     
                        $user_obj = Classes::User($exp[0]);
                        if($exp[0] != $first_user) $user_obj->unsetFleet($this->getName());
                        $new_fleet = Classes::Fleet();
                        # Flugerfahrung
                        $this_last_target = (($last_target === false) ? $this->raw[1][$user][1] : $last_target);
                        $this->raw[1][$user][5] += $this->getTritium($user, $this_last_target, $next_target);

                        if($new_fleet->create() && $further == true)
                        {
                            $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Neue Flotte.");

                            $that = Classes::Fleet();
                            $that->raw[1][$exp[0]] = $this->raw[1][$user];
                            $new_fleet->setRaw(array(
                            array($that->raw[1][$exp[0]][1] => array($type, true)),
                            array($exp[0] => $this->raw[1][$user]),
                            time(),
                            array($next_target => array($type, false))
                            ));
                            $new_fleet->raw[7][0] = round($new_fleet->getNextArrival());
                            $user_obj->addFleet($new_fleet->getName());
                            $new_fleet->createNextEvent();
                            $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Neue Flotte. ID: ".$new_fleet->getName());

                        }
                        unset($this->raw[1][$user]);
                    }
                    $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Ende Neue Flotte oder Ende Weiterflug.");

            
                }
                if(!$further)
                {
                    $this->destroy();
                    $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Flotte Further = false. Destroy. Lösche Flotten-ID:  ".$this->getName());
                }
            }
            else
            {
                $log->logIt( LOG_USER_FLEET, " arriveAtNextTarget() -- Stationieren.");
    
                # Stationieren
                $target = explode(':', $next_target_nt);
                $target_galaxy = Classes::Galaxy($target[0], $besiedeln);
                
                if(($besiedeln && $target_galaxy->getStatus() != 1) || !$target_galaxy->getStatus())
                {
                    return false;
                }
                
                $owner = $target_galaxy->getPlanetOwner($target[1], $target[2]);
                
                if($besiedeln || $owner == $first_user)
                {
                    # Ueberschuessiges Tritium
                    if(!isset($this->raw[1][$first_user][3][2])) 
                    {
                        $this->raw[1][$first_user][3][2] = $this->getTritium($first_user, $this->raw[1][$first_user][1], $next_target_nt);
                    }
                    else 
                    {
                        $this->raw[1][$first_user][3][2] += $this->getTritium($first_user, $this->raw[1][$first_user][1], $next_target_nt);
                    }
                }

                if($besiedeln)
                {
                    $user_obj = Classes::User($first_user);
                    
                    if($user_obj->registerPlanet($next_target_nt) === false)
                    {
                        return false;
                    }
                    
                    if(isset($this->raw[1][$first_user][0]['S6']))
                    {
                        $this->raw[1][$first_user][0]['S6']--;
                        $active_planet = $user_obj->getActivePlanet();
                        $user_obj->setActivePlanet($user_obj->getPlanetByPos($next_target_nt));
                        $item_info = $user_obj->getItemInfo('S6', 'schiffe');
                        $besiedelung_ress = $item_info['ress'];
                        $besiedelung_ress[0] *= .4;
                        $besiedelung_ress[1] *= .4;
                        $besiedelung_ress[2] *= .4;
                        $besiedelung_ress[3] *= .4;
                        $user_obj->addRess($besiedelung_ress);
                    }
                    $owner = $first_user;                    
                }

                if( !$owner )
                {
                    $this->destroy();

                    return false;
                }

                $owner_obj = Classes::User( $owner );
                
                if( !$owner_obj->getStatus() ) 
                    return false;

                $planet_index = $owner_obj->getPlanetByPos($next_target_nt);
                
                if($planet_index === false)
                {
                    return false;
                }

                $owner_obj->setActivePlanet($planet_index);

                $ress = array(0, 0, 0, 0, 0);
                $robs = array();
                $schiffe_own = array();
                $schiffe_other = array();

                # Flugerfahrung                
                $last_targets = array_keys($this->raw[3]);
                if(count($last_targets) <= 0) 
                {
                    $last_target = false;
                }
                else
                {
                    $last_target = array_pop($last_targets);
                    
                    if(substr($last_target, -1) == 'T') 
                    {
                        $last_target = substr($last_target, 0, -1);
                    }
                }
                
                foreach($this->raw[1] as $username=>$move_info)
                {

                    
                    $ress[0] += $move_info[3][0][0];
                    $ress[1] += $move_info[3][0][1];
                    $ress[2] += $move_info[3][0][2];
                    $ress[3] += $move_info[3][0][3];
                    $ress[4] += $move_info[3][0][4];

                    foreach($move_info[3][1] as $id=>$count)
                    {
                        if(isset($robs[$id])) $robs[$id] += $count;
                        else $robs[$id] = $count;
                    }

                    if($username == $owner)
                    {
                        # Stationieren
                        foreach($move_info[0] as $id=>$count)
                        {
                            if(isset($schiffe_own[$id])) $schiffe_own[$id] += $count;
                            else $schiffe_own[$id] = $count;
                        }

                        if($username != $first_user)
                        {
                            $this->raw[1][$username][3][2] += $this->getTritium($username, $this->raw[1][$username][1], $next_target_nt);
                        }
                    }
                    else
                    {
                        if(!$besiedeln)
                        {
                            # Fremdstationieren
                            if(!isset($schiffe_other[$username]))
                                $schiffe_other[$username] = array();
                            foreach($move_info[0] as $id=>$count)
                            {
                                if(isset($schiffe_other[$username][$id])) $schiffe_other[$username][$id] += $count;
                                else $schiffe_other[$username][$id] = $count;
                            }
                        }
                    }

                    # Flugerfahrung
                    if($besiedeln) 
                    {
                        $this->raw[1][$first_user][0]['S6'] = 1;
                    }
                    
                    $this_last_target = (($last_target === false) ? $this->raw[1][$username][1] : $last_target);
                    $this->raw[1][$username][5] += $this->getTritium($username, $this_last_target, $next_target);
                    
                    if($besiedeln) 
                    {
                        $this->raw[1][$first_user][0]['S6']--;
                    }

                    $user_obj = Classes::User($username);
                    $user_obj->addScores(5,$this->raw[1][$username][5]/1000*2);
                    $user_obj->addScores(5,$this->raw[1][$first_user][3][2]*-0.001);

                    #Behebung der bestehenden -1 Eintr�ge vom Flugerfahrungsbug
                    if($user_obj->raw['punkte'][5] < 0) 
                    {
                        $user_obj->raw['punkte'][5] = ($user_obj->raw['punkte'][5] *-1);
                    }
                }            
                
                if($besiedeln)
                {
                    $message_text = "Ihre Flotte erreicht den Planeten ".$next_target_nt." und beginnt mit seiner Besiedelung.";

                    if(isset($besiedelung_ress))
                    {
                        $message_text .= " Durch den Abbau eines Besiedelungsschiffs konnten folgende Rohstoffe wiederhergestellt werden: ".ths($besiedelung_ress[0], true)." Carbon, ".ths($besiedelung_ress[1], true)." Aluminium, ".ths($besiedelung_ress[2], true)." Wolfram, ".ths($besiedelung_ress[3], true)." Radium.";
                    }
                    
                    $message_text .= "\n";
                }
                else
                {                                    
                    $message_text = "Eine Flotte erreicht den Planeten \xe2\x80\x9e".$owner_obj->planetName()."\xe2\x80\x9c (".$owner_obj->getPosString().", Eigent\xc3\xbcmer: ".$owner_obj->getName().").\n";
                }
                
                if(array_sum($schiffe_own) > 0)
                {
                    $message_text .= "Die Flotte besteht aus folgenden Schiffen: ".makeItemsString($schiffe_own, false)."\n";
                    
                    foreach($schiffe_own as $id=>$anzahl)
                    {
                        $owner_obj->changeItemLevel($id, $anzahl);
                    }                                               
                }
                
                if(array_sum_r($schiffe_other) > 0)
                {
                    $message_text .= "Folgende Schiffe werden fremdstationiert:\n";
                    
                    foreach($schiffe_other as $user=>$schiffe)
                    {
                        $message_text .= $user.": ".makeItemsString($schiffe, false)."\n";
                        #$owner_obj->addForeignFleet();
                    }
                }

                $message_text .= "\nFolgende G\xc3\xbcter werden abgeliefert:\n";
                $message_tet .= ths($ress[0], true).' Carbon, '.ths($ress[1], true).' Aluminium, '.ths($ress[2], true).' Wolfram, '.ths($ress[3], true).' Radium, '.ths($ress[4], true)." Tritium.";
                
                if(array_sum($robs) > 0)
                    $message_text .= "\n".makeItemsString($robs, false)."\n";
                foreach($robs as $id=>$anzahl)
                    $owner_obj->changeItemLevel($id, $anzahl, 'roboter');

                if($this->raw[1][$first_user][3][2] > 0)
                {
                    $message_text .= "\n\nFolgender \xc3\xbcbersch\xc3\xbcssiger Treibstoff wird abgeliefert: ".ths($this->raw[1][$first_user][3][2], true)." Tritium.";
                    $ress[4] += $this->raw[1][$first_user][3][2];
                }
                $owner_obj->addRess($ress);
          
                        $user_obj = Classes::User($username);
                        $user_obj->addScores(11, ($this->raw[1][$first_user][3][2]*-1));
                
            
                $message_users = array();
                foreach($this->raw[1] as $username=>$move_info)
                {
                    $message_user_obj = Classes::User($username);
                    $receive = $message_user_obj->checkSetting('receive');
                    if(!isset($receive[$types_message_types[$this->raw[0][$next_target][0]]][$this->raw[0][$next_target][1]]) || $receive[$types_message_types[$this->raw[0][$next_target][0]]][$this->raw[0][$next_target][1]])
                    {
                        # Will Nachricht erhalten
                        $message_users[] = $username;
                    }
                }

                if(count($message_users) > 0)
                {
                    $message_obj = Classes::Message();
                    if($message_obj->create())
                    {
                        $message_obj->text($message_text);
                        if($besiedeln)
                            $message_obj->subject("Besiedelung von ".$next_target_nt);
                        else
                            $message_obj->subject("Stationierung auf ".$owner_obj->getPosString());
                        $message_obj->from($owner_obj->getName());
                        foreach($message_users as $username)
                            $message_obj->addUser($username, $types_message_types[$this->raw[0][$next_target][0]]);
                    }
                }
                $this->destroy();
            }
            return true;
        }
        
        function createNextEvent()
        {
            if(!$this->status) return false;
            $event_obj = Classes::EventFile();
            return $event_obj->addNewFleet($this->getArrivalTime(), $this->getName());
        }
        
        
        protected function getDataFromRaw(){}
        protected function getRawFromData(){}
          
        function battle($angreifer, $verteidiger)
		{
			
			if(count($angreifer) < 0 || count($verteidiger) < 0) return false;
			$angreifervorkampf = $angreifer;
			$angreifer_anfang = $angreifer;
			$verteidigervorkampf = $verteidiger;
			$verteidiger_anfang = $verteidiger;

			$users_angreifer = array();
			$users_verteidiger = array();
			foreach($angreifer as $username=>$i)
			{
				$expa = explode("/", $username);
				$expusera = $expa[0];
				$users_angreifer[$username] = Classes::User($expusera);
			}
			foreach($verteidiger as $username=>$i)
			{
				$exp = explode("/", $username);
				$expuser = $exp[0];
				$users_verteidiger[$username] = Classes::User($expuser);
			}

			# Spionagetechnik fuer Erstschlag
			$angreifer_spiotech = 0;
			foreach($users_angreifer as $user) {
				$angreifer_spiotech += $user->getItemLevel('F1', 'forschung');
			}
			$angreifer_spiotech /= count($users_angreifer);

			$verteidiger_spiotech = 0;
			foreach($users_verteidiger as $user) {
				$verteidiger_spiotech += $user->getItemLevel('F1', 'forschung');
			}
			$verteidiger_spiotech /= count($users_verteidiger);
			
			$angreifer_waffentech = 0;
			foreach($users_angreifer as $user) {
				$angreifer_waffentech += $user->getItemLevel('F4', 'forschung');
			}
			$angreifer_waffentech /= count($users_angreifer);

			$verteidiger_waffentech = 0;
			foreach($users_verteidiger as $user) {
				$verteidiger_waffentech += $user->getItemLevel('F4', 'forschung');
			}
			$verteidiger_waffentech /= count($users_verteidiger);
			
			$angreifer_deftech = 0;
			foreach($users_angreifer as $user) {
				$angreifer_deftech += $user->getItemLevel('F5', 'forschung');
			}
			$angreifer_deftech /= count($users_angreifer);

			$verteidiger_deftech = 0;
			foreach($users_verteidiger as $user) {
				$verteidiger_deftech += $user->getItemLevel('F5', 'forschung');
			}
			$verteidiger_deftech /= count($users_verteidiger);
			
			$angreifer_schildtech = 0;
			foreach($users_angreifer as $user) {
				$angreifer_schildtech += $user->getItemLevel('F10', 'forschung');
			}
			$angreifer_schildtech /= count($users_angreifer);

			$verteidiger_schildtech = 0;
			foreach($users_verteidiger as $user) {
				$verteidiger_schildtech += $user->getItemLevel('F10', 'forschung');
			}
			$verteidiger_schildtech /= count($users_verteidiger);


			# Kampferfahrung
			$angreifer_erfahrung = 0;
			foreach($users_angreifer as $user) {
				$angreifer_erfahrung += $user->getScores('kampferfahrung');
			}
			$angreifer_erstschlag_erfahrung = $angreifer_erfahrung / count($users_angreifer);
			
			$verteidiger_erfahrung = 0;
			foreach($users_verteidiger as $user) {
				$verteidiger_erfahrung += $user->getScores('kampferfahrung');
			}
			$verteidiger_erstschlag_erfahrung = $verteidiger_erfahrung / count($users_verteidiger);
			
			$angreifer_erstschlag = $angreifer_spiotech + $angreifer_waffentech + $angreifer_deftech + $angreifer_schildtech + $angreifer_erstschlag_erfahrung;
			$verteidiger_erstschlag = $verteidiger_spiotech + $verteidiger_waffentech + $verteidiger_deftech + $verteidiger_schildtech + $verteidiger_erstschlag_erfahrung;


			# Nachrichtentext
			$nachrichten_text = '';
			if(count($angreifer) > 1) {
				$nachrichten_text .= "<h3>Flotten der Angreifer</h3>";
			} else {
				$nachrichten_text .= "<h3>Flotten des Angreifers</h3>";
			}

			$nachrichten_text .= "<table>\n";
			$nachrichten_text .= "\t<thead>\n";
			$nachrichten_text .= "\t\t<tr>\n";
			$nachrichten_text .= "\t\t\t<th class=\"c-schiffstyp\">Schiffstyp</th>\n";
			$nachrichten_text .= "\t\t\t<th class=\"c-anzahl\">Anzahl</th>\n";
			$nachrichten_text .= "\t\t\t<th class=\"c-gesamtstaerke\">Gesamtst\xc3\xa4rke</th>\n";
			$nachrichten_text .= "\t\t\t<th class=\"c-gesamtschild\">Gesamtschild</th>\n";
			$nachrichten_text .= "\t\t</tr>\n";
			$nachrichten_text .= "\t</thead>\n";
			$nachrichten_text .= "\t<tbody>\n";

			$ges_anzahl = $ges_staerke = $ges_schild = 0;

			# KK Kampfvorbereitungen
			$kk_kampf = 0;
			$kk_max = 4000;

			foreach($angreifer as $name=>$flotten)
			{
					$expa = explode("/", $name);
					$expnamea = $expa[0];
				
				$nachrichten_text .= "\t\t<tr class=\"benutzername\">\n";
				$nachrichten_text .= "\t\t\t<th colspan=\"4\"><span class=\"angreifer-name\">".utf8_htmlentities($expnamea)."</span></th>\n";
				$nachrichten_text .= "\t\t</tr>\n";

				$this_ges_anzahl = $this_ges_staerke = $this_ges_schild = 0;
				foreach($flotten as $id=>$anzahl)
				{



					$item_info = $users_angreifer[$name]->getItemInfo($id);
					$sepstaerke[$id] = $item_info['att']*$anzahl;
					$staerke = $item_info['att']*$anzahl;
					$schild = $item_info['def']*$anzahl;

					$nachrichten_text .= "\t\t<tr>\n";
					$nachrichten_text .= "\t\t\t<td class=\"c-schiffstyp\"><a href=\"help/description.php?id=".htmlentities(urlencode($id))."\" title=\"Genauere Informationen anzeigen\">".utf8_htmlentities($item_info['name'])."</a></td>\n";
					$nachrichten_text .= "\t\t\t<td class=\"c-anzahl\">".ths($anzahl)."</td>\n";
					$nachrichten_text .= "\t\t\t<td class=\"c-gesamtstaerke\">".ths($staerke)."</td>\n";
					$nachrichten_text .= "\t\t\t<td class=\"c-gesamtschild\">".ths($schild)."</td>\n";
					$nachrichten_text .= "\t\t</tr>\n";

					$this_ges_anzahl += $anzahl;
					$this_ges_staerke += $staerke;
					$this_ges_schild += $schild;

					# KK �berpr�fung: Nur wenn KK und kein anderer Typ au�er KK und Spios da sind ist es ein KK Kampf
						 if($id != "S7" && $id != "S5" )
								$kk_kampf = 2;
						 elseif($id == "S7" && $kk_kampf != 2)
							$kk_kampf = 1;
				}
					#var_dump($sepstaerke);
		
				$nachrichten_text .= "\t\t<tr class=\"gesamt\">\n";
				$nachrichten_text .= "\t\t\t<td class=\"c-schiffstyp\">Gesamt</td>\n";
				$nachrichten_text .= "\t\t\t<td class=\"c-anzahl\">".ths($this_ges_anzahl)."</td>\n";
				$nachrichten_text .= "\t\t\t<td class=\"c-gesamtstaerke\">".ths($this_ges_staerke)."</td>\n";
				$nachrichten_text .= "\t\t\t<td class=\"c-gesamtschild\">".ths($this_ges_schild)."</td>\n";
				$nachrichten_text .= "\t\t</tr>\n";

				$ges_anzahl += $this_ges_anzahl;
				$ges_staerke += $this_ges_staerke;
				$ges_schild += $this_ges_schild;
			}

			$nachrichten_text .= "\t</tbody>\n";

			if(count($angreifer) > 1)
			{
				$nachrichten_text .= "\t<tfoot>\n";
				$nachrichten_text .= "\t\t<tr>\n";
				$nachrichten_text .= "\t\t\t<td class=\"c-schiffstyp\">Gesamt</td>\n";
				$nachrichten_text .= "\t\t\t<td class=\"c-anzahl\">".ths($ges_anzahl)."</td>\n";
				$nachrichten_text .= "\t\t\t<td class=\"c-gesamtstaerke\">".ths($ges_staerke)."</td>\n";
				$nachrichten_text .= "\t\t\t<td class=\"c-gesamtschild\">".ths($ges_schild)."</td>\n";
				$nachrichten_text .= "\t\t</tr>\n";
				$nachrichten_text .= "\t</tfoot>\n";
			}
			$nachrichten_text .= "</table>\n";

			# Unformartierter Text zur Info das es ein KK Kampf ist und nur $kk_max KKs angreifen
				if($kk_kampf == 1)
					$nachrichten_text .= "\t\tDies ist ein KK Kampf und nur ".ths($kk_max)." greifen maximal pro Runde an!\n";


			if(count($verteidiger) > 1)
				$nachrichten_text .= "<h3>Flotten der Verteidiger</h3>";
			else
				$nachrichten_text .= "<h3>Flotten des Verteidigers</h3>";

			$nachrichten_text .= "<table>\n";
			$nachrichten_text .= "\t<thead>\n";
			$nachrichten_text .= "\t\t<tr>\n";
			$nachrichten_text .= "\t\t\t<th class=\"c-schiffstyp\">Schiffstyp</th>\n";
			$nachrichten_text .= "\t\t\t<th class=\"c-anzahl\">Anzahl</th>\n";
			$nachrichten_text .= "\t\t\t<th class=\"c-gesamtstaerke\">Gesamtst\xc3\xa4rke</th>\n";
			$nachrichten_text .= "\t\t\t<th class=\"c-gesamtschild\">Gesamtschild</th>\n";
			$nachrichten_text .= "\t\t</tr>\n";
			$nachrichten_text .= "\t</thead>\n";
			$nachrichten_text .= "\t<tbody>\n";

			$ges_anzahl = $ges_staerke = $ges_schild = 0;
			foreach($verteidiger as $name=>$flotten)
			{
					$exp = explode("/", $name);
					$expname = $exp[0];

				$nachrichten_text .= "\t\t<tr class=\"benutzername\">\n";
				$nachrichten_text .= "\t\t\t<th colspan=\"4\"><span class=\"verteidiger-name\">".utf8_htmlentities($expname)."</span></th>\n";
				$nachrichten_text .= "\t\t</tr>\n";

				$this_ges_anzahl = $this_ges_staerke = $this_ges_schild = 0;
				$one = false;
				foreach($flotten as $id=>$anzahl)
				{
					$item_info = $users_verteidiger[$name]->getItemInfo($id);

					$staerke = $item_info['att']*$anzahl;
					$schild = $item_info['def']*$anzahl;

					if($anzahl > 0)
					{
						$nachrichten_text .= "\t\t<tr>\n";
						$nachrichten_text .= "\t\t\t<td class=\"c-schiffstyp\"><a href=\"help/description.php?id=".htmlentities(urlencode($id))."\" title=\"Genauere Informationen anzeigen\">".utf8_htmlentities($item_info['name'])."</a></td>\n";
						$nachrichten_text .= "\t\t\t<td class=\"c-anzahl\">".ths($anzahl)."</td>\n";
						$nachrichten_text .= "\t\t\t<td class=\"c-gesamtstaerke\">".ths($staerke)."</td>\n";
						$nachrichten_text .= "\t\t\t<td class=\"c-gesamtschild\">".ths($schild)."</td>\n";
						$nachrichten_text .= "\t\t</tr>\n";
						$one = true;
					}

					$this_ges_anzahl += $anzahl;
					$this_ges_staerke += $staerke;
					$this_ges_schild += $schild;
				}

				if(!$one)
				{
					$nachrichten_text .= "\t\t<tr class=\"keine\">\n";
					$nachrichten_text .= "\t\t\t<td colspan=\"4\">Keine.</td>\n";
					$nachrichten_text .= "\t\t</tr>\n";
				}
				else
				{
					$nachrichten_text .= "\t\t<tr class=\"gesamt\">\n";
					$nachrichten_text .= "\t\t\t<td class=\"c-schiffstyp\">Gesamt</td>\n";
					$nachrichten_text .= "\t\t\t<td class=\"c-anzahl\">".ths($this_ges_anzahl)."</td>\n";
					$nachrichten_text .= "\t\t\t<td class=\"c-gesamtstaerke\">".ths($this_ges_staerke)."</td>\n";
					$nachrichten_text .= "\t\t\t<td class=\"c-gesamtschild\">".ths($this_ges_schild)."</td>\n";
					$nachrichten_text .= "\t\t</tr>\n";
				}

				$ges_anzahl += $this_ges_anzahl;
				$ges_staerke += $this_ges_staerke;
				$ges_schild += $this_ges_schild;
			}

			$nachrichten_text .= "\t</tbody>\n";

			if(count($verteidiger) > 1)
			{
				$nachrichten_text .= "\t<tfoot>\n";
				$nachrichten_text .= "\t\t<tr>\n";
				$nachrichten_text .= "\t\t\t<td class=\"c-schiffstyp\">Gesamt</td>\n";
				$nachrichten_text .= "\t\t\t<td class=\"c-anzahl\">".ths($ges_anzahl)."</td>\n";
				$nachrichten_text .= "\t\t\t<td class=\"c-gesamtstaerke\">".ths($ges_staerke)."</td>\n";
				$nachrichten_text .= "\t\t\t<td class=\"c-gesamtschild\">".ths($ges_schild)."</td>\n";
				$nachrichten_text .= "\t\t</tr>\n";
				$nachrichten_text .= "\t</tfoot>\n";
			}
			$nachrichten_text .= "</table>\n";

			if(count($angreifer_anfang) > 1)
			{
				$angreifer_nominativ = 'die Angreifer';
				$angreifer_praedikat = 'sind';
				$angreifer_praedikat2 = 'haben';
				$angreifer_genitiv = 'der Angreifer';
				$angreifer_dativ = 'ihnen';
			}
			else
			{
				$angreifer_nominativ = 'der Angreifer';
				$angreifer_praedikat = 'ist';
				$angreifer_praedikat2 = 'hat';
				$angreifer_genitiv = 'des Angreifers';
				$angreifer_dativ = 'ihm';
			}
			if(count($verteidiger_anfang) > 1)
			{
				$verteidiger_nominativ = 'die Verteidiger';
				$verteidiger_praedikat = 'sind';
				$verteidiger_praedikat2 = 'haben';
				$verteidiger_nominativ_letzt = 'letztere';
				$verteidiger_genitiv = 'der Verteidiger';
			}
			else
			{
				$verteidiger_nominativ = 'der Verteidiger';
				$verteidiger_praedikat = 'ist';
				$verteidiger_praedikat2 = 'hat';
				$verteidiger_nominativ_letzt = 'letzterer';
				$verteidiger_genitiv = 'des Verteidigers';
			}

			# Erstschlag
			# Neuer KK Erstchlag Text (Zu Testzwecken)
			if($kk_kampf == 1)
			{
				$runde_starter = 'verteidiger';
				$runde_anderer = 'angreifer';

				$nachrichten_text .= "\t<p class=\"erstschlag verteidiger\">\n";
				$nachrichten_text .= "\t\tNur auf die schnelle Ankunft achtend müssen die Kampfkapseln nun die Situation checken.\n";
				$nachrichten_text .= "\t\tDadurch hat ".$verteidiger_nominativ." den Erstschlag\n";
				$nachrichten_text .= "\t</p>\n";
			}
			elseif($angreifer_erstschlag > $verteidiger_erstschlag)
			{
				$runde_starter = 'angreifer';
				$runde_anderer = 'verteidiger';

				$nachrichten_text .= "<p class=\"erstschlag angreifer\">\n";
				$nachrichten_text .= "\tDie Summe der Kampftechniken ".$angreifer_genitiv." ist gr&ouml;&szlig;er als die ".$verteidiger_genitiv." und ermöglichen es ".$angreifer_dativ.", den Erstschlag auszuf\xc3\xbchren.\n";
				$nachrichten_text .= "</p>\n";
			}
			else
			{
				$runde_starter = 'verteidiger';
				$runde_anderer = 'angreifer';

				$nachrichten_text .= "<p class=\"erstschlag verteidiger\">\n";
				$nachrichten_text .= "\tDie Summe der Kampftechniken ".$angreifer_genitiv." sind kleiner als die ".$verteidiger_genitiv.", weshalb ".$verteidiger_nominativ_letzt." den Erstschlag ausf\xc3\xbchrt.\n";
				$nachrichten_text .= "</p>\n";
			}

			$verteidiger_no_fleet = true;
			foreach($verteidiger as $name=>$ids)
			{
				if(array_sum($ids) > 0)
				{
					$verteidiger_no_fleet = false;
					break;
				}
			}
			if($verteidiger_no_fleet)
			{
				$runde_starter = 'angreifer';
				$runde_anderer = 'verteidiger';
			}

			#Variablen zur Zwischenspeicherung des �berhangs bei den besch�digten Schiffen
			$floordiffgerundet = array();
			$floordiffungerundet = array();

			foreach($angreifer as $name=>$ids)
			{
				$floordiffgerundet[$name] = array();
				$floordiffungerundet[$name] = array();

				foreach($ids as $id=>$anzahl)
				{
					$floordiffgerundet[$name][$id] = 0;
					$floordiffungerundet[$name][$id] = 0;

					if($anzahl <= 0) unset($ids[$id]);
				}


				if(count($ids) <= 0) unset($angreifer[$name]);
				else $angreifer[$name] = $ids;
			}
			foreach($verteidiger as $name=>$ids)
			{
				$floordiffgerundet[$name] = array();
				$floordiffungerundet[$name]  = array();

				foreach($ids as $id=>$anzahl)
				{
					$floordiffgerundet[$name][$id] = 0;
					$floordiffungerundet[$name][$id] = 0;

					if($anzahl <= 0) unset($ids[$id]);
				}
				if(count($ids) <= 0) unset($verteidiger[$name]);
				else $verteidiger[$name] = $ids;
			}

			# Einzelne Runden
			for($runde = 1; $runde <= 20; $runde++)
			{
				if(count($angreifer) <= 0 || count($verteidiger) <= 0) break;

				$a = & ${$runde_starter};
				$d = & ${$runde_anderer};
				$a_objs = & ${'users_'.$runde_starter};
				$d_objs = & ${'users_'.$runde_anderer};

				if($runde%2)
				{
					$nachrichten_text .= "<div class=\"runde\">\n";
					$nachrichten_text .= "\t<h3>Runde ".(($runde+1)/2)."</h3>\n";
				}

				# Flottengesamtstaerke
				$staerke = 0;

				$arr_staerke = array();


				foreach($a as $name=>$items)
				{
					foreach($items as $id=>$anzahl)
					{
						$item_info = $a_objs[$name]->getItemInfo($id);
						if(!$item_info) continue;
						# KK Reduzierung pro Runde auf $kk_max
									if($kk_kampf == 1 && $anzahl > $kk_max && $id == "S7" && $a == $angreifer) {
										$staerke += $item_info['att']*$kk_max;
							$arr_staerke[$id] += $item_info['att']*$kk_max;
									} else {
										$staerke += $item_info['att']*$anzahl;
							$arr_staerke[$id] += $item_info['att']*$anzahl;
						}
					}
				}

				$nachrichten_text .= "\t<h4>".ucfirst(${$runde_starter.'_nominativ'})." ".${$runde_starter.'_praedikat'}." am Zug (Gesamtst\xc3\xa4rke ".round($staerke).")</h4>\n";
				$nachrichten_text .= "\t<ol>\n";
				while(count($arr_staerke) > 0)
				{
					$staerke_id = $this->getRandomShipID($arr_staerke);
					//$staerke_id = array_rand($arr_staerke);
					$one_staerke = $arr_staerke[$staerke_id];			

					$att_user = array_rand($d);
					$att_id = $this->getRandomShipID($d[$att_user]);
					//$att_id = array_rand($d[$att_user]);
					
					$item_info = ${'users_'.$runde_anderer}[$att_user]->getItemInfo($att_id);
					$this_shield = $item_info['def']*$d[$att_user][$att_id];

					$schild_f = pow(0.95, ${'users_'.$runde_anderer}[$att_user]->getItemLevel('F10', 'forschung'));
					#echo("Angriffsst�rke Angreifer vor Forschung Schildtechnik: ".$staerke."\n");
					$aff_staerke = $one_staerke*$schild_f;
					#echo("Angriffsst�rke Angreifer nach Forschung Schildtechnik: ".$aff_staerke."\n");
			
					if($this_shield > $aff_staerke) #
					{
						$this_shield -= $aff_staerke;
						$before = $d[$att_user][$att_id];
						$d[$att_user][$att_id] = $this_shield/$item_info['def'];
						$diff = $before-$d[$att_user][$att_id];
						$floor_diff = floor($diff);
						#echo($before."\n");
						#echo($floor_diff."\n");
						$floordiffungerundet[$att_user][$att_id] += $diff;
						$floordiffgerundet[$att_user][$att_id] += $floor_diff;
						$remdiff = $floordiffungerundet[$att_user][$att_id] - $floordiffgerundet[$att_user][$att_id];
						if($remdiff >=1) $floor_diff += floor($remdiff);
						if($remdiff >=1) $floordiffgerundet[$att_user][$att_id] += floor($remdiff);

						#echo("Gesamtabzugungerundet ID".$id." Anzahl ".$floordiffungerundet[$att_user][$id]."\n");
						#echo("Gesamtabzuggerundet ID".$id." Anzahl ".$floordiffgerundet[$att_user][$id]."\n");

						$nachrichten_text .= "\t\t<li>";
						#Namen aufloesen fuer Messages
						$exp1 = explode("/", $att_user);
						$expname1 = $exp1[0];

						if($floor_diff == 0)
							$nachrichten_text .= "Eine Einheit des Typs ".utf8_htmlentities($item_info['name'])." (<span class=\"".$runde_anderer."-name\">".utf8_htmlentities($expname1)."</span>) wird angeschossen.</li>\n";
						else
						{
							if($floor_diff == 1)
							$nachrichten_text .= "Eine Einheit des Typs ".utf8_htmlentities($item_info['name'])." (<span class=\"".$runde_anderer."-name\">".utf8_htmlentities($expname1)."</span>) wird zerst\xc3\xb6rt. ".ths(ceil($d[$att_user][$att_id]))." verbleiben.</li>\n";

							if($floor_diff != 1)
							{
								$nachrichten_text .= ths($floor_diff)."&nbsp;Einheiten";

								$nachrichten_text .= " des Typs ".utf8_htmlentities($item_info['name'])." (<span class=\"".$runde_anderer."-name\">".utf8_htmlentities($expname1)."</span>) werden zerst\xc3\xb6rt. ".ths(ceil($d[$att_user][$att_id]))." verbleiben.</li>\n";
							}				
						}
						$staerke = 0;
						$arr_staerke[$staerke_id] = 0;					
					}
					else
					{
						$exp1 = explode("/", $att_user);
						$expname1 = $exp1[0];
						$nachrichten_text .= "\t\t<li>Alle Einheiten des Typs ".utf8_htmlentities($item_info['name'])." (".ths(ceil($d[$att_user][$att_id])).") (<span class=\"".$runde_anderer."-name\">".utf8_htmlentities($expname1)."</span>) werden zerst\xc3\xb6rt.</li>\n";
						$aff_staerke = $this_shield;
						unset($d[$att_user][$att_id]);
						if(count($d[$att_user]) <= 0) unset($d[$att_user]);
						$staerke -= $aff_staerke/$schild_f;
						$arr_staerke[$staerke_id] -= $aff_staerke/$schild_f;
					}
					if($arr_staerke[$staerke_id]<=0) {
						unset($arr_staerke[$staerke_id]);
					}
					if(count($angreifer) <= 0 || count($verteidiger) <= 0) break;
				}

				$nachrichten_text .= "\t</ol>\n";
				if(!$runde%2)
					$nachrichten_text .= "</div>\n";
				# Vertauschen
				list($runde_starter, $runde_anderer) = array($runde_anderer, $runde_starter);
				unset($a);
				unset($d);
				unset($a_objs);
				unset($d_objs);
			}
			$nachrichten_text .= "<p>\n";
			$nachrichten_text .= "\tDer Kampf ist vor\xc3\xbcber. ";
			if(count($angreifer) == 0)
			{
				$nachrichten_text .= "Gewinner ".$verteidiger_praedikat." ".$verteidiger_nominativ.".";
				$winner = -1;
			}
			elseif(count($verteidiger) == 0)
			{
				$nachrichten_text .= "Gewinner ".$angreifer_praedikat." ".$angreifer_nominativ.".";
				$winner = 1;
			}
			else
			{
				$nachrichten_text .= "Er endet unentschieden.";
				$winner = 0;
			}
			$nachrichten_text .= "\n";
			$nachrichten_text .= "</p>\n";

			# Flottenbestaende aufrunden
			foreach($angreifer as $name=>$ids)
			{
				foreach($ids as $id=>$anzahl)
					$angreifer[$name][$id] = ceil($anzahl);
					#echo("Angreiferflotte ungerundet :".$anzahl."\n");

					#echo("Angreiferflotte gerundet :".ceil($anzahl)."\n");
			}
			foreach($verteidiger as $name=>$ids)
			{
				foreach($ids as $id=>$anzahl)
					$verteidiger[$name][$id] = ceil($anzahl);
					#echo("Verteidigerflotte ungerundet :".$anzahl."\n");

					#echo("Verteidigerflotte gerundet :".ceil($anzahl)."\n");

			}
			$truemmerfeld = array(0, 0, 0, 0);
			$verteidiger_ress = array();
			$angreifer_punkte = array();
			$verteidiger_punkte = array();
			if(count($angreifer_anfang) > 1)
				$nachrichten_text .= "<h3>Flotten der Angreifer</h3>";
			else
				$nachrichten_text .= "<h3>Flotten des Angreifers</h3>";
			$nachrichten_text .= "<table>\n";
			$nachrichten_text .= "\t<thead>\n";
			$nachrichten_text .= "\t\t<tr>\n";
			$nachrichten_text .= "\t\t\t<th class=\"c-schiffstyp\">Schiffstyp</th>\n";
			$nachrichten_text .= "\t\t\t<th class=\"c-anzahl\">Anzahl</th>\n";
			$nachrichten_text .= "\t\t\t<th class=\"c-gesamtstaerke\">Gesamtst\xc3\xa4rke</th>\n";
			$nachrichten_text .= "\t\t\t<th class=\"c-gesamtschild\">Gesamtschild</th>\n";
			$nachrichten_text .= "\t\t</tr>\n";
			$nachrichten_text .= "\t</thead>\n";
			$nachrichten_text .= "\t<tbody>\n";
			$ges_anzahl = $ges_staerke = $ges_schild = 0;
			foreach($angreifer_anfang as $name=>$flotten)
			{
					$expa = explode("/", $name);
					$expnamea = $expa[0];
				$nachrichten_text .= "\t\t<tr class=\"benutzername\">\n";
				$nachrichten_text .= "\t\t\t<th colspan=\"4\"><span class=\"angreifer-name\">".utf8_htmlentities($expnamea)."</span></th>\n";
				$nachrichten_text .= "\t\t</tr>\n";

				$this_ges_anzahl = $this_ges_staerke = $this_ges_schild = 0;
				$angreifer_punkte[$name] = 0;
				$one = false;
				foreach($flotten as $id=>$old_anzahl)
				{
					$item_info = $users_angreifer[$name]->getItemInfo($id, false, true, true);

					if(isset($angreifer[$name]) && isset($angreifer[$name][$id]))
						$anzahl = $angreifer[$name][$id];
					else
						$anzahl = 0;

					$diff = $old_anzahl-$anzahl;
					$truemmerfeld[0] += $item_info['ress'][0]*$diff*.4;
					$truemmerfeld[1] += $item_info['ress'][1]*$diff*.4;
					$truemmerfeld[2] += $item_info['ress'][2]*$diff*.4;
					$truemmerfeld[3] += $item_info['ress'][3]*$diff*.4;
					$angreifer_punkte[$name] += $item_info['simple_scores']*$diff;

					$staerke = $item_info['att']*$anzahl;
					$schild = $item_info['def']*$anzahl;

					if($anzahl > 0)
					{
						$nachrichten_text .= "\t\t<tr>\n";
						$nachrichten_text .= "\t\t\t<td class=\"c-schiffstyp\"><a href=\"help/description.php?id=".htmlentities(urlencode($id))."\" title=\"Genauere Informationen anzeigen\">".utf8_htmlentities($item_info['name'])."</a></td>\n";
						$nachrichten_text .= "\t\t\t<td class=\"c-anzahl\">".ths($anzahl)."</td>\n";
						$nachrichten_text .= "\t\t\t<td class=\"c-gesamtstaerke\">".ths($staerke)."</td>\n";
						$nachrichten_text .= "\t\t\t<td class=\"c-gesamtschild\">".ths($schild)."</td>\n";
						$nachrichten_text .= "\t\t</tr>\n";
						$one = true;
					}

					$this_ges_anzahl += $anzahl;
					$this_ges_staerke += $staerke;
					$this_ges_schild += $schild;
				}
				if(!$one)
				{
					$nachrichten_text .= "\t\t<tr class=\"keine\">\n";
					$nachrichten_text .= "\t\t\t<td colspan=\"4\">Keine.</td>\n";
					$nachrichten_text .= "\t\t</tr>\n";
				}
				else
				{
					$nachrichten_text .= "\t\t<tr class=\"gesamt\">\n";
					$nachrichten_text .= "\t\t\t<td class=\"c-schiffstyp\">Gesamt</td>\n";
					$nachrichten_text .= "\t\t\t<td class=\"c-anzahl\">".ths($this_ges_anzahl)."</td>\n";
					$nachrichten_text .= "\t\t\t<td class=\"c-gesamtstaerke\">".ths($this_ges_staerke)."</td>\n";
					$nachrichten_text .= "\t\t\t<td class=\"c-gesamtschild\">".ths($this_ges_schild)."</td>\n";
					$nachrichten_text .= "\t\t</tr>\n";
				}

				$ges_anzahl += $this_ges_anzahl;
				$ges_staerke += $this_ges_staerke;
				$ges_schild += $this_ges_schild;
			}

			$nachrichten_text .= "\t</tbody>\n";

			if(count($angreifer_anfang) > 1)
			{
				$nachrichten_text .= "\t<tfoot>\n";
				$nachrichten_text .= "\t\t<tr>\n";
				$nachrichten_text .= "\t\t\t<td class=\"c-schiffstyp\">Gesamt</td>\n";
				$nachrichten_text .= "\t\t\t<td class=\"c-anzahl\">".ths($ges_anzahl)."</td>\n";
				$nachrichten_text .= "\t\t\t<td class=\"c-gesamtstaerke\">".ths($ges_staerke)."</td>\n";
				$nachrichten_text .= "\t\t\t<td class=\"c-gesamtschild\">".ths($ges_schild)."</td>\n";
				$nachrichten_text .= "\t\t</tr>\n";
				$nachrichten_text .= "\t</tfoot>\n";
			}
			$nachrichten_text .= "</table>\n";


			if(count($verteidiger_anfang) > 1)
				$nachrichten_text .= "<h3>Flotten der Verteidiger</h3>";
			else
				$nachrichten_text .= "<h3>Flotten des Verteidigers</h3>";

			$nachrichten_text .= "<table>\n";
			$nachrichten_text .= "\t<thead>\n";
			$nachrichten_text .= "\t\t<tr>\n";
			$nachrichten_text .= "\t\t\t<th class=\"c-schiffstyp\">Schiffstyp</th>\n";
			$nachrichten_text .= "\t\t\t<th class=\"c-anzahl\">Anzahl</th>\n";
			$nachrichten_text .= "\t\t\t<th class=\"c-gesamtstaerke\">Gesamtst\xc3\xa4rke</th>\n";
			$nachrichten_text .= "\t\t\t<th class=\"c-gesamtschild\">Gesamtschild</th>\n";
			$nachrichten_text .= "\t\t</tr>\n";
			$nachrichten_text .= "\t</thead>\n";
			$nachrichten_text .= "\t<tbody>\n";

			$ges_anzahl = $ges_staerke = $ges_schild = 0;
			foreach($verteidiger_anfang as $name=>$flotten)
			{
					$exp = explode("/", $name);
					$expname = $exp[0];

				$nachrichten_text .= "\t\t<tr class=\"benutzername\">\n";
				$nachrichten_text .= "\t\t\t<th colspan=\"4\"><span class=\"verteidiger-name\">".utf8_htmlentities($expname)."</span></th>\n";
				$nachrichten_text .= "\t\t</tr>\n";

				$this_ges_anzahl = $this_ges_staerke = $this_ges_schild = 0;
				$verteidiger_punkte[$name] = 0;
				$verteidiger_ress[$name] = array(0, 0, 0, 0);
				$one = false;
				foreach($flotten as $id=>$anzahl_old)
				{
					$item_info = $users_verteidiger[$name]->getItemInfo($id, false, true, true);

					if(isset($verteidiger[$name]) && isset($verteidiger[$name][$id]))
						$anzahl = $verteidiger[$name][$id];
					else $anzahl = 0;

					$diff = $anzahl_old-$anzahl;
					if($item_info['type'] == 'schiffe')
					{
						$truemmerfeld[0] += $item_info['ress'][0]*$diff*.4;
						$truemmerfeld[1] += $item_info['ress'][1]*$diff*.4;
						$truemmerfeld[2] += $item_info['ress'][2]*$diff*.4;
						$truemmerfeld[3] += $item_info['ress'][3]*$diff*.4;
					}
					elseif($item_info['type'] == 'verteidigung')
					{
						$verteidiger_ress[$name][0] += $item_info['ress'][0]*.2;
						$verteidiger_ress[$name][1] += $item_info['ress'][1]*.2;
						$verteidiger_ress[$name][2] += $item_info['ress'][2]*.2;
						$verteidiger_ress[$name][3] += $item_info['ress'][3]*.2;
					}

					$verteidiger_punkte[$name] += $diff*$item_info['simple_scores'];

					$staerke = $item_info['att']*$anzahl;
					$schild = $item_info['def']*$anzahl;

					if($anzahl > 0)
					{
						$nachrichten_text .= "\t\t<tr>\n";
						$nachrichten_text .= "\t\t\t<td class=\"c-schiffstyp\"><a href=\"help/description.php?id=".htmlentities(urlencode($id))."\" title=\"Genauere Informationen anzeigen\">".utf8_htmlentities($item_info['name'])."</a></td>\n";
						$nachrichten_text .= "\t\t\t<td class=\"c-anzahl\">".ths($anzahl)."</td>\n";
						$nachrichten_text .= "\t\t\t<td class=\"c-gesamtstaerke\">".ths($staerke)."</td>\n";
						$nachrichten_text .= "\t\t\t<td class=\"c-gesamtschild\">".ths($schild)."</td>\n";
						$nachrichten_text .= "\t\t</tr>\n";
						$one = true;
					}

					$this_ges_anzahl += $anzahl;
					$this_ges_staerke += $staerke;
					$this_ges_schild += $schild;
				}

				if(!$one)
				{
					$nachrichten_text .= "\t\t<tr class=\"keine\">\n";
					$nachrichten_text .= "\t\t\t<td colspan=\"4\">Keine.</td>\n";
					$nachrichten_text .= "\t\t</tr>\n";
				}
				else
				{
					$nachrichten_text .= "\t\t<tr class=\"gesamt\">\n";
					$nachrichten_text .= "\t\t\t<td class=\"c-schiffstyp\">Gesamt</td>\n";
					$nachrichten_text .= "\t\t\t<td class=\"c-anzahl\">".ths($this_ges_anzahl)."</td>\n";
					$nachrichten_text .= "\t\t\t<td class=\"c-gesamtstaerke\">".ths($this_ges_staerke)."</td>\n";
					$nachrichten_text .= "\t\t\t<td class=\"c-gesamtschild\">".ths($this_ges_schild)."</td>\n";
					$nachrichten_text .= "\t\t</tr>\n";
				}

				$ges_anzahl += $this_ges_anzahl;
				$ges_staerke += $this_ges_staerke;
				$ges_schild += $this_ges_schild;
			}

			$nachrichten_text .= "\t</tbody>\n";

			if(count($verteidiger) > 1)
			{
				$nachrichten_text .= "\t<tfoot>\n";
				$nachrichten_text .= "\t\t<tr>\n";
				$nachrichten_text .= "\t\t\t<td class=\"c-schiffstyp\">Gesamt</td>\n";
				$nachrichten_text .= "\t\t\t<td class=\"c-anzahl\">".ths($ges_anzahl)."</td>\n";
				$nachrichten_text .= "\t\t\t<td class=\"c-gesamtstaerke\">".ths($ges_staerke)."</td>\n";
				$nachrichten_text .= "\t\t\t<td class=\"c-gesamtschild\">".ths($ges_schild)."</td>\n";
				$nachrichten_text .= "\t\t</tr>\n";
				$nachrichten_text .= "\t</tfoot>\n";
			}
			$nachrichten_text .= "</table>\n";

			$nachrichten_text .= "<ul class=\"angreifer-punkte\">\n";
			foreach($angreifer_anfang as $a=>$i)
			{
				$exp = explode("/", $a);
					$expa = $exp[0];

				$p = 0;
				if(isset($angreifer_punkte[$a])) $p = $angreifer_punkte[$a];
				$nachrichten_text .= "\t<li>Der Angreifer <span class=\"koords\">".utf8_htmlentities($expa)."</span> hat ".ths($p)."&nbsp;Punkte verloren.</li>\n";
			}
			$nachrichten_text .= "</ul>\n";
			$nachrichten_text .= "<ul class=\"verteidiger-punkte\">\n";
			foreach($verteidiger_anfang as $v=>$i)
			{
					$exp = explode("/", $v);
					$expv = $exp[0];

				$p = 0;
				if(isset($verteidiger_punkte[$v])) $p = $verteidiger_punkte[$v];
				$nachrichten_text .= "\t<li>Der Verteidiger <span class=\"koords\">".utf8_htmlentities($expv)."</span> hat ".ths($p)."&nbsp;Punkte verloren.</li>\n";
			}
			$nachrichten_text .= "</ul>\n";

			if(array_sum($truemmerfeld) > 0)
			{
				# Truemmerfeld

				$truemmerfeld[0] = round($truemmerfeld[0]);
				$truemmerfeld[1] = round($truemmerfeld[1]);
				$truemmerfeld[2] = round($truemmerfeld[2]);
				$truemmerfeld[3] = round($truemmerfeld[3]);
			}

			# Kampferfahrung
			$angreifer_new_erfahrung = array_sum($verteidiger_punkte)/10000;
			$verteidiger_new_erfahrung = array_sum($angreifer_punkte)/10000;

			#Kampferfahrung Angreifer Punkteverteilung nach verbliebenen Einheiten
			$angreifergesamt = array();
			$angreiferkomplett = 0;
			
			foreach($angreifervorkampf as $name=>$ids)
			{
				foreach($ids as $id=>$anzahl)
					$angreifergesamt[$name] =+ ceil($anzahl);
			}
			foreach($angreifergesamt as $name=>$anzahl)
				$angreiferkomplett += $anzahl;

			if($angreiferkomplett == 0) $angreiferkomplett = 1;
			foreach($angreifergesamt as $name=>$anzahl)
				$angreiferfaktor[$name] = ($angreifergesamt[$name]/$angreiferkomplett);

			foreach($angreifergesamt as $user=>$info)
			{
				$user_obj = Classes::User($user);
				$user_obj->addScores('kampferfahrung', $angreifer_new_erfahrung);
			}

			#Kampferfahrung Verteidiger Punkteverteilung nach verbliebenen Einheiten
			$verteidigergesamt = array();
			$verteidigerkomplett = 0;
			
			foreach($verteidigervorkampf as $name=>$ids)
			{
				foreach($ids as $id=>$anzahl)
					$verteidigergesamt[$name] =+ ceil($anzahl);
			}
			foreach($verteidigergesamt as $name=>$anzahl)
				$verteidigerkomplett += $anzahl;

			if($verteidigerkomplett == 0) $verteidigerkomplett = 1;
			foreach($verteidigergesamt as $name=>$anzahl)
				$verteidigerfaktor[$name] = ($verteidigergesamt[$name]/$verteidigerkomplett);

			foreach($verteidigergesamt as $user=>$info)
			{
				$user_obj = Classes::User($user);
				$user_obj->addScores('kampferfahrung', $verteidiger_new_erfahrung);
			}
				
			$nachrichten_text .= "<ul class=\"kampferfahrung\">\n";
			$nachrichten_text .= "\t<li class=\"c-angreifer\">".ucfirst($angreifer_nominativ)." ".$angreifer_praedikat2." ".ths($angreifer_new_erfahrung,false,2)."&nbsp;Kampferfahrungspunkte gesammelt.</li>\n";
			$nachrichten_text .= "\t<li class=\"c-verteidiger\">".ucfirst($verteidiger_nominativ)." ".$verteidiger_praedikat2." ".ths($verteidiger_new_erfahrung,false,2)."&nbsp;Kampferfahrungspunkte gesammelt.</li>\n";
			$nachrichten_text .= "</ul>\n";

			#foreach($users_angreifer as $user)
				#$user->addScores('kampferfahrung', $angreifer_new_erfahrung);

			#foreach($users_verteidiger as $user)
				#$user->addScores('kampferfahrung', $verteidiger_new_erfahrung);


			# $winner:  1: Angreifer gewinnt
			#           0: Unentschieden
			#          -1: Verteidiger gewinnt
			#
			# $angreifer: Wie uebergeben, Flotten nach der Schlacht
			# $verteidiger: Wie uebergeben, Flotten nach der Schlacht
			#
			# $nachrichten_text: Kampfbericht, es muessen noch fuer jeden Benutzer die regenerierten
			#                    Verteidigungsrohstoffe aus $verteidiger_ress angehaengt werden.
			#
			# $truemmerfeld: Das Truemmerfeld, das entstehen wird

			return array($winner, $angreifer, $verteidiger, $nachrichten_text, $verteidiger_ress, $truemmerfeld);
		}
    }

    function array_sum_r($array)
    {
        $sum = 0;
        foreach($array as $val)
            $sum += array_sum($val);
        return $sum;
    }
    
    function isMilitaryShip($value) {
    	switch($value) {
    		case 'S7':
    		case 'S8':
    		case 'S9':
    		case 'S10':
    		case 'S11':
    		case 'S12':
    		case 'S13':
    		case 'S14':
    		case 'S15':
    		case 'S16':
    			return true;
    		default:
    			return false;
    	}
    } 
?>
