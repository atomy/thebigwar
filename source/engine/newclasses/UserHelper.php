<?php
require_once '../include/config_inc.php';
require_once TBW_ROOT.'include/DBHelper.php';

class UserHelper
{
	/**
	 * TODO:
	 * - user exists (old database)
	 * - resolve userid for username (new mapping database)
	 */
	public static function getUserIdOfUsername( $szName )
	{
		throw new Exception("UserHelper::getUserIdOfUsername() N/A");
		
		$dbLink = DBHelper::getInstance();		
		$result = $dbLink->query($sql);
		
		return $uId;
	}
}