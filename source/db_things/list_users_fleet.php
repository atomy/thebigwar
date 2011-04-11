#!/usr/bin/php
<?php
        $USE_OB = false;
        require('../engine/include.php');

        if(!isset($_SERVER['argv'][1]))
        {
                echo "Usage: ".$_SERVER['argv'][0]." <Database ID> <Username>\n";
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

        if(!isset($_SERVER['argv'][2])) {
                echo "Usage: ".$_SERVER['argv'][0]." <Database ID> <Username>\n";
        } else {
                if(!User::UserExists($_SERVER['argv'][2])) {
                        echo "Unknown user '".$_SERVER['argv'][2]."'\n";
                        exit(0);
                }
        }

        $eventfile = Classes::EventFile();
        #Betroffenen User eintragen
        $user_obj = Classes::User($_SERVER['argv'][2]);
        #Flotten        holen
        $flotten = $user_obj->getFleetsList();
        if($flotten == null || count($flotten) <= 0) {
                echo "User '".$user_obj->getName()."' has no fleets.\n";
                exit(0);
        }
        echo "Listing fleets of user '".$user_obj->getName()."'\n";
        foreach($flotten as $flotte)
        {
                $fl = Classes::Fleet($flotte);
                $time = $fl->getArrivalTime();
                echo "fleetid: '".$flotte."'\n";
        }
?>
