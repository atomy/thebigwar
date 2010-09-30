<?php
	require_once( '../include/config_inc.php' );
	require( TBW_ROOT.'admin/include.php' );
	require_once( TBW_ROOT.'admin/include/constants.php' );
	
	   	/**
	 * check for access to that page
	 * @extern $adminObj
	 */
	if( !isset($adminObj) || !$adminObj->can(ADMIN_MANAGEADMINS))
	{
		die('No access.');
	}

	/**
	 * get back to the admin page when no username is given
	 */
	if(!isset($_REQUEST['username']))
	{
		$url = global_setting("PROTOCOL").'://'.$_SERVER['HTTP_HOST'].h_root.'/admin/index.php';
		header('Location: '.$url, true, 303);
		die('HTTP redirect: <a href="'.htmlentities($url).'">'.htmlentities($url).'</a>');
	}

	admin_gui::html_head();
	
	if ( !isset($admins[$_REQUEST['username']]) )
	{
	    die("User does not exists!");
	}
	
	$admin_array = &$admins[$_REQUEST['username']];
	$editAdmin = new CAdmin($admin_array, $_REQUEST['username']);

	?>
	<div id="editAdminHeadLine">Bearbeite Admin: <span><?=$editAdmin->getName()?></span></div>
	<?
		
	if ( isset($_REQUEST['action']) && $_REQUEST['action'] == "setPermissions" )
	{
	    ?>
	    <ul>
	    <?
	    foreach($GLOBALS['ADMINPERMISSIONS'] as $permObj)
	    {	     
	        // check if the permission is set and if he doesnt have it already
	        if ( isset($_REQUEST[$permObj->getName()]) && !$editAdmin->can($permObj->getId()))	        
	        {
	            $editAdmin->grant($permObj->getId())
	            ?>
	            <li id="grantedPerm">GRANTED <?=$permObj->getName()?></li>
	            <?
	        }
	        else if ( !isset($_REQUEST[$permObj->getName()]) && $editAdmin->can($permObj->getId()))
	        {
	            $editAdmin->revoke($permObj->getId())
	            ?>
	            <li id="revokedPerm">REVOKED <?=$permObj->getName()?></li>
	            <?	           
	        }
	    }	    
	    ?>
	    </ul>
	    <?
	    if (!$editAdmin->save())
	    {
	        ?>
	        <div id="error">Fehler beim Speichern!</div>
	        <?
	    }
	    ?>
	    <a href="index.php">Zurück zum Adminmenü</a>&nbsp;&nbsp;&nbsp;
	    <a href="<?=$_SERVER['PHP_SELF']?>?username=<?=$editAdmin->getName()?>">Zurück zum Admin</a>
	    <?
	}
	else 
	{
	?>
	<div id="editPermissions">
	<div id="editPermissionsHeadline">Zugriffsrechte</div>
	<form action="<?=$_SERVER['PHP_SELF']?>" method="post">	
	<table id="permTable">
	<?
	
	foreach($GLOBALS['ADMINPERMISSIONS'] as $permObj)
	{
	    $editAdmin->getMyPermissionWithID($permObj->getId()) ? $hasFlag = true : $hasFlag = false;	    
	    ?>
	    <tr>
	    <td><?=$permObj->getDescription()?></td>
	    <?
	    if ($hasFlag)
	    {
	        ?>
	        <td id="enabled"><input type="checkbox" name="<?=$permObj->getName()?>" value="1" checked="checked" /></td>
	        <?
	    }
	    else
	    {
	        ?>
	        <td id="disabled"><input type="checkbox" name="<?=$permObj->getName()?>" /></td>
	        <?
	    }
	    ?>	    
	    </tr>
	    <?
	}
	?>
	</table>
	<input type="hidden" name="username" value="<?=$editAdmin->getName()?>"/>
	<input type="hidden" name="action" value="setPermissions" />
	<input id="submit_button" type="submit" value="Speichern" />
	</form>	
	</div> <!-- /editPermissions -->
	<?
    }
	admin_gui::html_foot();
?>
