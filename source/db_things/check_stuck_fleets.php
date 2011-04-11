#!/usr/bin/php
<?php
        $USE_OB = false;
        require('../engine/include.php');

        if(!isset($_SERVER['argv'][1]))
        {
                echo "Usage: ".$_SERVER['argv'][0]." <Database ID>\n";
                exit(1);
        }
        else
        {
                $databases = get_databases();
                if(!isset($databases[$_SERVER['argv'][1]]))
                {
                        echo "Unknown database.\n";
                        exit(1);
                }
                else
                        define_globals($_SERVER['argv'][1], true);
        }

        $eventfile = Classes::EventFile();
        $eventfile->_empty();

        $dh = opendir(global_setting("DB_FLEETS"));
        while(($fname = readdir($dh)) !== false)
        {
                if($fname == '.' || $fname == '..') continue;
                $fleet_id = urldecode($fname);
                $fleet = Classes::Fleet($fleet_id);
                if(!$fleet->getStatus()) continue;
                if($fleet->getArrivalTime() > 0) continue;

                echo "fleet-id: '".$fleet->getName()."'\n";
                echo " arrival time: '".$fleet->getArrivalTime()."'\n";
                echo " users: '".print_r($fleet->getFleetContent())."'\n";
                unset($fleet);
        }
        closedir($dh);
?>
