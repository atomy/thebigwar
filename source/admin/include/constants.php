<?php

if(!isset($_SERVER['DOCUMENT_ROOT']) || strlen($_SERVER['DOCUMENT_ROOT']) <= 0)
    $_SERVER['DOCUMENT_ROOT'] = getcwd()."/..";
    
require_once($_SERVER['DOCUMENT_ROOT'].'/include/config_inc.php');
require_once $_SERVER['DOCUMENT_ROOT'].'/admin/include/cadminpermission.php';

$ADMINPERMISSIONS = array();
$ADMINPERMISSIONS[] = new CAdminPermission(0, 'ADMIN_LISTUSERS', 'Allowed to List All existing Users' );
$ADMINPERMISSIONS[] = new CAdminPermission(1, 'ADMIN_GHOSTMODE', 'Allowed to login user accounts without using password' );
$ADMINPERMISSIONS[] = new CAdminPermission(2, 'ADMIN_FIXSTUCKFLEET', 'Allowed to fix stucked fleets on a given Useraccount' );
$ADMINPERMISSIONS[] = new CAdminPermission(3, 'ADMIN_SETNOOBPROTECT', 'Allowed to enable/disable noob protection in the Universe' );
$ADMINPERMISSIONS[] = new CAdminPermission(4, 'ADMIN_EDITPRANGER', 'Allowed to edit the Pranger' );
$ADMINPERMISSIONS[] = new CAdminPermission(5, 'ADMIN_SENDMESSAGES', 'Allowed to send messages to specific or all users within the Universe' );
$ADMINPERMISSIONS[] = new CAdminPermission(6, 'ADMIN_VIEWLOGS', 'Allowed to view Admin-Activities' );
$ADMINPERMISSIONS[] = new CAdminPermission(7, 'ADMIN_MANAGEADMINS', 'Allowed to edit/add/delete Admin Accounts' );
$ADMINPERMISSIONS[] = new CAdminPermission(8, 'ADMIN_SETMAINTENANCE', 'Allowed to set the Universe to maintenance mode' );
$ADMINPERMISSIONS[] = new CAdminPermission(9, 'ADMIN_DISABLEGAME', 'Allowed to disable the Game' );
$ADMINPERMISSIONS[] = new CAdminPermission(10, 'ADMIN_TICKETSYSTEM', 'Allowed to access the Ticketsystem' );
$ADMINPERMISSIONS[] = new CAdminPermission(11, 'ADMIN_SETUSERSPASS', 'Allowed to reset/set a password for the given Useraccount' );
$ADMINPERMISSIONS[] = new CAdminPermission(12, 'ADMIN_DELETEUSERS', 'Allowed to delete Useraccounts' );
$ADMINPERMISSIONS[] = new CAdminPermission(13, 'ADMIN_DISABLEUSERS', 'Allowed to disable Useraccounts' );
$ADMINPERMISSIONS[] = new CAdminPermission(14, 'ADMIN_RENAMEUSERS', 'Allowed to rename Useraccounts' );
$ADMINPERMISSIONS[] = new CAdminPermission(15, 'ADMIN_DISABLEFLEETS', 'Allowed to restrict all fleet activities' );
$ADMINPERMISSIONS[] = new CAdminPermission(16, 'ADMIN_EDITITEMS', 'Allowed to edit users account, add/remove items' );

/*
 * use within files that include admin/include.php
 * 
 *	if( !isset($adminObj) || !$adminObj->can(ADMIN_MANAGEADMINS))
 *	{
 *		die('No access.');
 *	}
 */

function getPermissionWithID( $id = false )
{
    if ( $id === false )
    {
        throw new Exception( __METHOD__." error, invalid parameter given!" );
    }  

    foreach( $GLOBALS['ADMINPERMISSIONS'] as $permObj )
    {
        if ( $permObj->getId() == $id )
        {
            return $permObj;
        }
    }
    
    return false;
}

function getPermissionCount()
{
    if(!isset($GLOBALS['ADMINPERMISSIONS']))
    {
        throw new Exception( __METHOD__." error, no global admin permissions found!" );
    }
    
    return count($GLOBALS['ADMINPERMISSIONS']);
}