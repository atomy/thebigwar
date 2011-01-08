<?php
if(!isset($_SERVER['DOCUMENT_ROOT']) || strlen($_SERVER['DOCUMENT_ROOT']) <= 0)
    $_SERVER['DOCUMENT_ROOT'] = getcwd()."/..";
    
require_once($_SERVER['DOCUMENT_ROOT'].'/include/config_inc.php');
require($_SERVER['DOCUMENT_ROOT'].'/admin/include.php');

	/**
	 * check for access to that page
	 * @extern $adminObj
	 */
	if( !isset($adminObj) || !$adminObj->can(ADMIN_MANAGEADMINS))
	{
		die('No access.');
	}

	if(!isset($_GET['action']))
	{
		$url = global_setting("PROTOCOL").'://'.$_SERVER['HTTP_HOST'].h_root.'/admin/index.php';
		header('Location: '.$url, true, 303);
		die('HTTP redirect: <a href="'.htmlentities($url).'">'.htmlentities($url).'</a>');
	}

	admin_gui::html_head();

	switch($_GET['action'])
	{
		case 'add':
			if(isset($_POST['new_admin']) && count($_POST['new_admin']) >= 2)
			{
				if(isset($admins[$_POST['new_admin'][0]]))
				{
					if(preg_match('/_([0-9]+)$/', $_POST['new_admin'][0], $match))
					{
						$i = $match[1]+1;
						$_POST['new_admin'][0] = substr($_POST['new_admin'][0], 0, -strlen($i)-1);
					}
					else
					{
						$i=0;
					}
					
					while(isset($admins[$_POST['new_admin'][0].'_'.$i]))
					{
						$i++;
					}
					
					$_POST['new_admin'][0] .= '_'.$i;
				}

				$admins[$_POST['new_admin'][0]]['password'] = md5($_POST['new_admin'][1]);
				$admins[$_POST['new_admin'][0]]['permissions'] = array();

				for($i=0; $i<=19; $i++)
				{
					$admins[$_POST['new_admin'][0]]['permissions'][$i] = (isset($_POST['new_admin'][$i+2]) ? '1' : '0');
				}

				write_admin_list($admins) && protocol("11.1", $_POST['new_admin'][0]);
			}
			else
			{
?>
<form action="usermanagement.php?action=add" method="post">
	<table border="1">
		<thead>
			<tr>
				<th rowspan="2" title="Name des Administrators">Name</th>
				<th rowspan="2" title="Passwort">Passwort</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><input type="text" size="14" name="new_admin[0]" /></td>
				<td><input type="text" size="14" name="new_admin[1]" /></td>
<?php
?>
			</tr>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="22"><button type="submit">Hinzufügen</button></td>
			</tr>
		</tfoot>
	</table>
</form>
<?php
				break;
			}

		case 'delete':
			$admin_keys = array_keys($admins);
			if(isset($_GET['delete']) && isset($admin_keys[$_GET['delete']]) && $admin_keys[$_GET['delete']] != $_SESSION['admin_username'])
			{
				unset($admins[$admin_keys[$_GET['delete']]]);
				write_admin_list($admins) && protocol("11.4", $_GET['delete']);
			}

		case 'edit':
         // old code
		    /*
			if(isset($_POST['admin_array']))
			{
				$old_admins = array_keys($admins);
				$new_admins = array();
				$session_key = array_search($_SESSION['admin_username'], $old_admins);
				foreach($_POST['admin_array'] as $no=>$admin)
				{
					if(!isset($old_admins[$no]))
					{
						continue;
					}
					
					$this_password = $admins[$old_admins[$no]]['password'];
					$this_name = $admin[0];
					
					if(isset($new_admins[$this_name]))
					{
						if(preg_match('/_([0-9]+)$/', $this_name, $match))
						{
							$i = $match[1]+1;
							$this_name = substr($this_name, 0, -strlen($i)-1);
						}
						else
							$i=0;
						while(isset($new_admins[$this_name.'_'.$i]))
							$i++;
						$this_name .= '_'.$i;
					}
					
					if($old_admins[$no] != $this_name)
					{
						protocol("11.3", $old_admins[$no], $this_name);
						if($no == $session_key)
							$_SESSION['admin_username'] = $this_name;
					}
					
					$new_admins[$this_name] = array();
					$new_admins[$this_name]['password'] = $this_password;
					$new_admins[$this_name]['permissions'] = array();
					$prot = false;
					
					for($i=0; $i<=19; $i++)
					{
						$new_admins[$this_name]['permissions'][$i] = (isset($admin[$i+1]) ? '1' : '0');
						if($admins[$this_name]['permissions'][$i] != $new_admins[$this_name]['permissions'][$i])
							$prot = true;
					}
					if($prot) protocol("11.2", $this_name);
					$new_admins[$_SESSION['admin_username']]['permissions'][11] = '1';
				}
				write_admin_list($new_admins);
				$admins = $new_admins;				
			}
			*/
?>
<form action="usermanagement.php?action=edit" method="post">
	<table border="1">
		<thead>
			<tr> 
				<th rowspan="2" title="Name des Administrators">Name</th>
			</tr>
		</thead>
		<tbody>
<?php
			$i = 0;
			foreach($admins as $name=>$settings)
			{
?>
			<tr>
				<td><input type="text" name="admin_array[<?php echo htmlentities($i)?>][0]" value="<?php echo utf8_htmlentities($name)?>" readonly /></td>			
<?php

				if($name == $_SESSION['admin_username'])
				{
?>
				<td>[bearbeiten]</td>
<?php
				}
				else
				{
?>
				<td><a href="editAdmin.php?username=<?php echo htmlentities(urlencode($name))?>">[bearbeiten]</a></td>
<?php
				}
?>
			</tr>
<?php
				$i++;
			}
?>
		</tbody>
	</table>
</form>
<?php
			break;
	}

	admin_gui::html_foot();
?>
