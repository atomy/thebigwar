<?php

if ( is_file( '../include/config_inc.php' ) )
{
    require_once '../include/config_inc.php';
}
else
{
    require_once 'include/config_inc.php';
}

require_once TBW_ROOT . 'admin/include.php';
require_once TBW_ROOT.'include/TicketHelper.php';
require_once TBW_ROOT.'ticketsystem/TicketConstants.php';

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

/*
 * show tickets with the given status
 */
if ( isset($_REQUEST['status'] ))
{
    TicketHelper::showTicketsWithStatus($_REQUEST['status']);
}
// display ticket details for the given ticketid
else if ( isset( $_REQUEST['ticketid'] ) && is_numeric( $_REQUEST['ticketid'] ) && !isset($_REQUEST['text']) )
{
    TicketHelper::showTicketDetails($_REQUEST['ticketid'], true);
}
/*
 * mark ticket as resolved
 */
else if ( isset($_REQUEST['resolve']) && isset($_REQUEST['ticketid']) && is_numeric( $_REQUEST['ticketid'] ) )
{
    TicketHelper::resolveTicket( $_REQUEST['ticketid'] );
}
/*
 * add new message to given ticket id
 */
else if ( isset($_REQUEST['text']) && isset($_REQUEST['ticketid']) && is_numeric( $_REQUEST['ticketid'] ) )
{   
    TicketHelper::addMessageToTicket($adminObj->getName(), $_REQUEST['ticketid'], $_REQUEST['text'], true);
}
// show all tickets with default status NEW
else
{
    TicketHelper::showTicketsWithStatus(TICKET_STATUS_NEW);
}

admin_gui::html_foot();
?>
