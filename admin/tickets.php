<?php

if ( is_file( '../include/config_inc.php' ) )
{
    require_once ( '../include/config_inc.php' );
}
else
{
    require_once ( 'include/config_inc.php' );
}

require_once ( TBW_ROOT . 'admin/include.php' );

/**
 * check for access to that page
 * @extern $adminObj
 */
if ( ! isset( $adminObj ) || ! $adminObj->can( ADMIN_TICKETSYSTEM ) )
{
    die( 'No access.' );
}

admin_gui::html_head();
?>
<link rel="stylesheet" href="/css/ticketsystem.css" type="text/css" />
<?
admin_gui::html_foot();
?>
