<?php
    require_once( '../include/config_inc.php' );
    require_once( TBW_ROOT.'admin/include.php' );

   	/**
	 * check for access to that page
	 * @extern $adminObj
	 */
	if( !isset($adminObj) || !$adminObj->can(ADMIN_LISTUSERS))
	{
		die('No access.');
	}

    admin_gui::html_head();

    $sort = (isset($_GET['sort']) && $_GET['sort']);
?>
<h2>Benutzerliste &ndash; <?php echo $sort ? 'sortiert' : 'unsortiert'?></h2>
<?php
    if($sort)
    {
        $unames = array();
        $dh = opendir(global_setting("DB_PLAYERS"));
        while(($uname = readdir($dh)) !== false)
        {
            if(!is_file(global_setting("DB_PLAYERS").'/'.$uname) || !is_readable(global_setting("DB_PLAYERS").'/'.$uname))
                continue;
            $unames[] = urldecode($uname);
        }
        closedir($dh);

        natcasesort($unames);
?>
<ol>
<?php
        foreach($unames as $uname)
        {
?>
    <li><?php echo utf8_htmlentities($uname)?></li>
<?php
            flush();
        }
?>
</ol>
<?php
    }
    else
    {
?>
<ul>
<?php
        $dh = opendir(global_setting("DB_PLAYERS"));
        while(($uname = readdir($dh)) !== false)
        {
            if(!is_file(global_setting("DB_PLAYERS").'/'.$uname) || !is_readable(global_setting("DB_PLAYERS").'/'.$uname))
                continue;
?>
    <li><?php echo utf8_htmlentities(urldecode($uname))?></li>
<?php
            flush();
        }
        closedir($dh);
?>
</ul>
<?php
    }

    admin_gui::html_foot();
?>