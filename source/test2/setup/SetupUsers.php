<?php
if(!isset($_SERVER['DOCUMENT_ROOT']) || strlen($_SERVER['DOCUMENT_ROOT']) <= 0)
    $_SERVER['DOCUMENT_ROOT'] = getcwd();
    
require_once($_SERVER['DOCUMENT_ROOT'].'/include/config_inc.php');
require_once $_SERVER['DOCUMENT_ROOT'].'/engine/include.php';

class SetupUsers
{
    public static function Setup()
    {
        $databases = get_databases();
        define_globals(key($databases));
        
        if (User::userExists("hans"))
            user_control::removeUser("hans");
        
        $newUserObj = Classes::User("hans");
        
        if(!$newUserObj->create())
            throw new Exception("SetupUsers::Setup() error creating user!");
        
        __autoload('Galaxy');
        $koords = getFreeKoords();
        
        if(!$koords)
        {
            user_control::removeUser($newUserObj->getName());
            throw new Exception("SetupUsers::Setup() no free space in galaxy!");
        }
        $index = $newUserObj->registerPlanet($koords);
        
        if($index === false)
        {
            user_control::removeUser($newUserObj->getName());
            throw new Exception("SetupUsers::Setup() couldnt register main planet!");
        }
        
        $newUserObj->setActivePlanet($index);
        $newUserObj->addRess(array(2000000, 1000000, 750000, 500000, 200000));
        $newUserObj->setPassword($newUserObj->getName());
        
        # give him a good start, some awesome buildings and research
        for( $i=0; $i<=6; $i++ )
        {
            $newUserObj->changeItemLevel( 'B'.$i, '20', 'gebaeude' );
        }

        for( $i=8; $i<=10; $i++ )
        {
            $newUserObj->changeItemLevel( 'B'.$i, '30', 'gebaeude' );
        }    
           
        for( $i=0; $i<=7; $i++ )
        {
           $newUserObj->changeItemLevel( 'F'.$i, '20', 'forschung' );
        }
           
        for( $i=8; $i<=11; $i++ )
        {
           $newUserObj->changeItemLevel( 'F'.$i, '2', 'forschung' );
        }
        
        for( $i=0; $i<=16; $i++ )
        {
           $newUserObj->changeItemLevel( 'S'.$i, '1000', 'schiffe' );
        }        
    }    
}