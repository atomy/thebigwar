#!/usr/bin/php

<?php
	$USE_OB = false;
if(!isset($_SERVER['DOCUMENT_ROOT']) || strlen($_SERVER['DOCUMENT_ROOT']) <= 0)
    $_SERVER['DOCUMENT_ROOT'] = getcwd();
    
require_once($_SERVER['DOCUMENT_ROOT'].'/include/config_inc.php');
require $_SERVER['DOCUMENT_ROOT'].'/engine/include.php';

        if(!isset($_SERVER['argv'][1]))
        {
                fputs(STDERR, "Usage: ".$_SERVER['argv'][0]." <Database ID>\n");
                exit(1);
        }
        else
        {
                $databases = get_databases();
                if(!define_globals($_SERVER['argv'][1]))
                {
                        fputs(STDERR, "Unknown database.\n");
                        exit(1);
                }
        }

        $dh = opendir(global_setting("DB_PLAYERS"));
 

	$user = new User('atomy');

	$user->setActivePlanet(0);


    $user->changeItemLevel('S0', '10', 'schiffe');
	$user->changeItemLevel('S1', '10', 'schiffe');
	$user->changeItemLevel('S2', '10', 'schiffe');
	$user->changeItemLevel('S3', '10', 'schiffe');
	$user->changeItemLevel('S4', '10', 'schiffe');
	$user->changeItemLevel('S5', '10', 'schiffe');
	$user->changeItemLevel('S6', '10', 'schiffe');
	$user->changeItemLevel('S7', '10', 'schiffe');
	$user->changeItemLevel('S8', '10', 'schiffe');
	$user->changeItemLevel('S9', '10', 'schiffe');
    $user->changeItemLevel('S10', '10', 'schiffe');
	$user->changeItemLevel('S11', '10', 'schiffe');
    $user->changeItemLevel('S12', '10', 'schiffe');
    $user->changeItemLevel('S13', '10', 'schiffe');
	$user->changeItemLevel('S14', '22500', 'schiffe');
	$user->changeItemLevel('S15', '12500', 'schiffe');
    $user->changeItemLevel('S16', '10', 'schiffe');
    
    $user->changeItemLevel('F0', '10', 'forschung');    
    $user->changeItemLevel('F1', '10', 'forschung'); 
    $user->changeItemLevel('F2', '10', 'forschung'); 
    $user->changeItemLevel('F3', '10', 'forschung'); 
    $user->changeItemLevel('F4', '10', 'forschung'); 
    $user->changeItemLevel('F5', '10', 'forschung'); 
    $user->changeItemLevel('F6', '10', 'forschung'); 
    $user->changeItemLevel('F7', '10', 'forschung'); 
    $user->changeItemLevel('F8', '10', 'forschung'); 
    $user->changeItemLevel('F9', '10', 'forschung');
    $user->changeItemLevel('F10', '10', 'forschung'); 
    $user->changeItemLevel('F11', '10', 'forschung');  
    
    $user->changeItemLevel('B0', '20', 'gebaeude');    
    $user->changeItemLevel('B1', '20', 'gebaeude'); 
    $user->changeItemLevel('B2', '20', 'gebaeude'); 
    $user->changeItemLevel('B3', '20', 'gebaeude'); 
    $user->changeItemLevel('B4', '20', 'gebaeude'); 
    $user->changeItemLevel('B5', '20', 'gebaeude'); 
    $user->changeItemLevel('B6', '20', 'gebaeude'); 
    $user->changeItemLevel('B7', '20', 'gebaeude'); 
    $user->changeItemLevel('B8', '20', 'gebaeude'); 
    $user->changeItemLevel('B9', '20', 'gebaeude');
    $user->changeItemLevel('B10', '20', 'gebaeude');     
    
	$user->changeItemLevel('V0', '0', 'verteidigung');
	$user->changeItemLevel('V1', '0', 'verteidigung');
	$user->changeItemLevel('V2', '0', 'verteidigung');
	$user->changeItemLevel('V3', '0', 'verteidigung');
	$user->changeItemLevel('V4', '0', 'verteidigung');
	$user->changeItemLevel('V5', '0', 'verteidigung');
	$user->changeItemLevel('V6', '0', 'verteidigung');



?>
