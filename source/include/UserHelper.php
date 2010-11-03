<?php
require_once '../include/config_inc.php';
require_once TBW_ROOT.'include/DBHelper.php';
require_once TBW_ROOT.'engine/classes/user.php';

class UserHelper
{
	/**
	 * convert given username to an userid
	 * @param $szName - UNSAVE username
	 * @return unknown_type
	 * - user exists (old database)
	 * - resolve userid for username (new mapping database) \o/
	 */
	public static function GetUserIdOfUsername( $szName )
	{	
		$uID = -1;
		$dbHelper = DBHelper::getInstance();
		
		if ( !$dbHelper )
			throw new Exception("UserHelper::getUserIdOfUsername() db connection inactive");
			
		$dbSave_szName = $dbHelper->EscapeString( $szName );
		$shellSave_szName = escapeshellarg($szName);
		
		if (!User::userExists($dbSave_szName))
			throw new Exception("UserHelper::getUserIdOfUsername() user doesnt exists!");
		
		$sql = "SELECT userid FROM `tbw_map_username_to_userid` WHERE username = '".$dbSave_szName."';";			
		$result = &$dbHelper->DoQuery($sql);
		
		if ($dbHelper->getAffectedRows() == 0)
		{			
			$sql = "INSERT INTO `tbw_map_username_to_userid` (username) VALUES ('".$dbSave_szName."');";
			$dbHelper->DoQuery($sql);
			$uID = $dbHelper->GetInsertID();
		}
		else
		{
			$output = $result->fetch_assoc();
			$uID = $output['userid'];
		}
		
		$result->free();
		
		return $uID;
	}
	
	/**
	 * 
	 * @param $uID
	 * @return unknown_type
	 */
	public static function GetUserNameOfUserID( $userID )
	{
		$dbsave_uID = intval($userID);
		$dbHelper = DBHelper::getInstance();
		$sql = "SELECT username FROM `tbw_map_username_to_userid` WHERE userid = '".$dbsave_uID."';";
		$result = &$dbHelper->DoQuery($sql);
		$output = $result->fetch_assoc();
		
		return $output['username'];
	}
	
    /**
     * tells the uname<->uid mapping that a username was changed
     * @param $from
     * @param $to
     * @return unknown_type
     */
    public static function RenameUser( $from, $to )
    {    
    	//TODO, fill me!
    }
    
    /**
     * tells the uname<->uid mapping that a username was deleted
     * @param $uName
     * @return unknown_type
     */
    public static function DeleteUser( $uName )
    {
    	//TODO, fill me with creaam err well, code i mean
    }
}