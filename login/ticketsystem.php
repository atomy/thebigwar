<?php
require_once '../include/config_inc.php';
require_once TBW_ROOT . 'include/DBHelper.php';
require_once TBW_ROOT . 'ticketsystem/TicketMessage.php';
require_once TBW_ROOT . 'ticketsystem/TicketManager.php';
require_once TBW_ROOT . 'login/scripts/include.php';
require_once TBW_ROOT.'include/TicketHelper.php';

login_gui::html_head( false );
?>

<link rel="stylesheet" href="/css/ticketsystem.css" type="text/css" />

<?php
// TODO, maximale anzahl offener tickets begrenzen
// TODO, permissions and shit
// create a new ticket with the given parameters
if ( isset( $_REQUEST['newTicket'] ) && isset( $_REQUEST['subject'] ) && isset( $_REQUEST['text'] ) )
{
    TicketHelper::createTicket( $me->getName(), $_REQUEST['subject'], $_REQUEST['text'] );
}
// display form for submitting new tickets
else if ( isset( $_REQUEST['newTicketForm'] ) )
{
    TicketHelper::showNewTicketForm();

}
// display ticket details for the given ticketid
else if ( isset( $_REQUEST['ticketid'] ) && is_numeric( $_REQUEST['ticketid'] ) && !isset($_REQUEST['text']) )
{
    TicketHelper::showTicketDetails($_REQUEST['ticketid']);
}
// show all tickets which belong to me
else if ( isset( $_REQUEST['showMyTickets'] ) )
{
    TicketHelper::showMyTickets( $me->getName() );
}
/*
 * add new message to given ticket id
 */
else if ( isset($_REQUEST['text']) && isset($_REQUEST['ticketid']) && is_numeric( $_REQUEST['ticketid'] ) )
{   
    TicketHelper::addMessageToTicket($me->getName(), $_REQUEST['ticketid'], $_REQUEST['text']);
}
// show all tickets with the given status // TODO, permissions
else
{
    TicketHelper::showTicketsWithStatus();
}
?>
	
<?php
login_gui::html_foot();
?>