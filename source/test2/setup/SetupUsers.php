<?
require_once '../../include/config_inc.php';
require_once TBW_ROOT.'engine/include.php';

class SetupUsers
{
    public static Setup()
    {
        $databases = get_databases();
        defin_globals(key($databases));
        
        if (!User::userExists("hans"))
            throw new Exception("SetupUsers::Setup() user already exists!");
        
        $newUserObj = Classes::User("hans");
        
        if($newUserObj->create())
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
        
        $newUserObj->setActivePlaneT($index);
        $newUserObj->addRess(array(2000000, 1000000, 750000, 500000, 200000));
        $newUserObj->setPassword($newUserObj->getName());
        
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