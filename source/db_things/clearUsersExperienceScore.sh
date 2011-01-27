#!/usr/bin/php
<?php
        if(!isset($_SERVER['DOCUMENT_ROOT']) || strlen($_SERVER['DOCUMENT_ROOT']) <= 0)
            $_SERVER['DOCUMENT_ROOT'] = getcwd()."/..";
    
	$USE_OB = false;
	require($_SERVER['DOCUMENT_ROOT'].'/engine/include.php');

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
        
        $count = 0;
        
	$dh = opendir(global_setting("DB_PLAYERS"));
	while(($fname = readdir($dh)) !== false)
	{
		$path = global_setting("DB_PLAYERS")."/".$fname;
		if(!is_file($path)) continue;

		$user = new User(urldecode($fname));
                $user->clearScore(6);
                $count++;
                
		foreach($user->getVerbuendetList() as $verb)
		{
			$verb_obj = new User($verb);
			if(!$verb_obj->isVerbuendet($user->getName()))
			{
				$user->_removeVerbuendet($verb);
				fputs(STDOUT, $user->getName().": ".$verb." isn't Verbuendet anymore.\n");
			}
			unset($verb_obj);
		}
		unset($user);
	}
	closedir($dh);
        
        echo "cleared war experience of '".$count."' users!\n";

	exit(0);
?>