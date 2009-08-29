<?php
	require_once( '../include/config_inc.php' );
	require_once( TBW_ROOT.'engine/include.php' );

	define_globals( $_REQUEST['database'] );
	
	# if there's no demo account, create one
	if ( !User::userExists( GLOBAL_DEMOACCNAME ) )
	{
		$user_obj = Classes::User( GLOBAL_DEMOACCNAME );
		
		if( !$user_obj->create() )
			$error = 'Datenbankfehler beim Anlegen des Benutzeraccounts.';		

		__autoload('Galaxy');
				
		# Koordinaten des Hauptplaneten bestimmen
		$koords = getFreeKoords();
				
		if( !$koords )
		{
			$error = 'Es gibt keine freien Planeten mehr.';
			$uName = $user_obj->getName();
			user_control::removeUser( $uName );
			echo $error;
			die();
		}
		else
		{
			$index = $user_obj->registerPlanet( $koords );
		
			if( $index === false )
			{
				$error = 'Der Hauptplanet konnte nicht besiedelt werden.';
				$uName = $user_obj->getName();
				user_control::removeUser( $uName );
				echo $error;
				die();
			}

			$user_obj->setActivePlanet( $index );
			$user_obj->addRess( array( 20000, 10000, 7500, 5000, 2000 ) );
			$user_obj->setPassword( GLOBAL_DEMOACCPASS );
			$user_obj->planetName( 'Hauptplanet' );
			
			# give him a good start, some awesome buildings and research
			for( $i=0; $i<=6; $i++ )
			{
				$user_obj->changeItemLevel( 'B'.$i, '20', 'gebaeude' );
			}

			for( $i=8; $i<=10; $i++ )
			{
				$user_obj->changeItemLevel( 'B'.$i, '30', 'gebaeude' );
			}	
			
			for( $i=0; $i<=7; $i++ )
			{
				$user_obj->changeItemLevel( 'F'.$i, '20', 'forschung' );
			}
			
			for( $i=8; $i<=11; $i++ )
			{
				$user_obj->changeItemLevel( 'F'.$i, '2', 'forschung' );
			}
		}
	}

	if ( !User::userExists( GLOBAL_DEMOACCNAME ) )
	{
		echo "Gast-Zugang nicht verfuegbar.";
		die;
	}


	$url = 'http://'.$_SERVER['HTTP_HOST'].h_root.'/login/index.php?username='.GLOBAL_DEMOACCNAME.'&password='.GLOBAL_DEMOACCPASS.'&database='.$_REQUEST['database'];
	header('Location: '.$url, true, 303);
    die( 'HTTP redirect: <a href="'.htmlentities($url).'">'.htmlentities($url).'</a>' );

?>
