<?php

require_once( TBW_ROOT.'admin/include/constants.php' );

/**
 * class for an admin account
 * @author atomy
 *
 */
class CAdmin
{
    /**
     * permissions array holding CAdminPermission objects
     * @var array
     */
    private $permissions;
    
    /**
     * password hash
     * @var unknown_type
     */
    private $passHash;
    
    /**
     * name of that admin
     * @var unknown_type
     */
    private $username;
    
    /**
     * Array ( [password] => 21232f297a57a5a743894a0e4a801fc3 ) 
     * @param $adminArray
     */
    
    /*
     * get_admin_list() returns:
     * 
     * Array ( 
     * 		[admin] => Array ( 
     * 			[password] => 21232f297a57a5a743894a0e4a801fc3 
     * 			[permissions] => Array ( 
     * 				[0] => 1 ... [19] => 1 
     * 			)
     *  	) 
     *  	[test] => Array ( 
     *  		[password] => 098f6bcd4621d373cade4e832627b4f6 
     *		  	[permissions] => Array ( 
     *  			[0] => 1 ... [19] => 0 
     *  		) 
     *  	) 
     *  ) 
     */
    
    function __construct( $adminArray, $username )
    {     
        $this->permissions = array();
        $this->username = false;
        $this->passHash = false;

        if ( isset($adminArray['permissions']))
        {
            $this->username = $username;
            $this->parseAdminArray( $adminArray );
        }
        else
        {
            echo "Warning, failed to load admin permissions!\n";
        }
    }
    
    private function parseAdminArray( $adminArray )
    {
        if ( $adminArray === false )
        {
            throw new Exception( __METHOD__." error, invalid parameter given!" );
        }

        $this->passHash = $adminArray['password'];
        $this->parsePermissions($adminArray['permissions']);
    }
    
    private function parsePermissions( $permArray = false )
    {
        if ( $permArray === false )
        {
            throw new Exception( __METHOD__." error, invalid parameter given!" );
        }
        
        /*
         * loop through the array, for every permission set get the object and link it
         */
        foreach( $permArray as $key => $value )
        {
            if( getPermissionWithID( $key ) === false && $key < getPermissionCount() )
            {
                throw new Exception( __METHOD__." permission with id ".$key." doesnt exists!" );
            }
            
            // if enabled, add it to my list
            if ( $value > 0 )
            {
                $this->permissions[] = &getPermissionWithID( $key );
            }
        }
    }

    /**
     * return the old-format permission array
     */
    public function getPermissionArray()
    {        
        $permArray = array();       
        
        for( $i = 0; $i <= getPermissionCount(); $i++ )
        {
            if ($this->getMyPermissionWithID($i))
            {
                $permArray[$i] = 1;
            }
            else
            {
                $permArray[$i] = 0;
            }
        }
        
        return $permArray;
    }
    
    /**
     * TODO, this saves this obj, however the called func saves a list of all admins - there's some inconsistency here
     */
    public function save()
    {
        $curAdminList = get_admin_list();         
        
        $myArray = &$curAdminList[$this->username];        
        $myArray['password'] = $this->passHash;
        $myArray['permissions'] = $this->getPermissionArray();
        
        return write_admin_list($curAdminList);         
    }
    
    public function getName()
    {
        return $this->username;
    }
        
    /**
     * tests if we have the given userid, returns true if so
     * @param $permissionId can be the numerical id or name
     */
    public function can( $permissionId = false )
    {
        if ( $permissionId === false )
        {
            throw new Exception( __METHOD__." error, invalid parameter given!" );
        }  

        if (is_numeric($permissionId) && $this->getMyPermissionWithID($permissionId))
        {
            return true;
        }
        else if ($this->getMyPermissionWithID($permissionId))
        {
            return true;
        }
        
        return false;
    }    
    
    public function getDescription( $permissionId = false )
    {
        if ( $permissionId === false )
        {
            throw new Exception( __METHOD__." error, invalid parameter given!" );
        }
        
        return $permissionId."_DESC";
    }      
    
    /**
     * return permission obj with the given id
     * @param $id - can be numerical or the name
     */
    function getMyPermissionWithID( $id = false )
    {
        if ( $id === false )
        {
            throw new Exception( __METHOD__." error, invalid parameter given!" );
        }
        
        if ( is_numeric( $id ) )
        {                   
            foreach( $this->permissions as $permObj )
            {            
                if ( $permObj->getId() == $id )
                {
                    return $permObj;
                }
            }
        }
        else
        {
            foreach( $this->permissions as $permObj )
            {            
                if ( $permObj->getName() == $id )
                {
                    return $permObj;
                }
            }            
        }
    
        return false;
    }    
    
    function getPermissions()
    {
        return $this->permissions;
    }
    
    /**
     * grant the given permission id
     * @param $id
     */  
    function grant( $id = false )
    {
        if ( $id === false )
        {
            throw new Exception( __METHOD__." error, invalid parameter given!" );
        }    

        $this->permissions[] = &getPermissionWithID( $id );
    }
    
    /**
     * revoke the given permission id
     * @param $id
     */
    function revoke( $id = false )
    {
        if ( $id === false )
        {
            throw new Exception( __METHOD__." error, invalid parameter given!" );
        }        
        
        $newPermissions = array();
        
        foreach( $this->permissions as $permObj )
        {
            if ( $permObj->getId() != $id )
            {
                $newPermissions[] = &getPermissionWithID( $permObj->getId() );
            } 
        }
        
        $this->permissions = $newPermissions;
    }
}