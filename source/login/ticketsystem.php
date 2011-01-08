<?php
if(!isset($_SERVER['DOCUMENT_ROOT']) || strlen($_SERVER['DOCUMENT_ROOT']) <= 0)
    $_SERVER['DOCUMENT_ROOT'] = getcwd();
    
require_once($_SERVER['DOCUMENT_ROOT'].'/include/config_inc.php');
require_once $_SERVER['DOCUMENT_ROOT'].'/include/DBHelper.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/ticketsystem/TicketMessage.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/ticketsystem/TicketManager.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/login/scripts/include.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/include/TicketHelper.php';

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
    // check for permissions
    if ( !TicketHelper::canUserViewTicket( $me->getName(), $_REQUEST['ticketid'] ) )
    {
        die( "No Access." );
    }
    TicketHelper::showTicketDetails($_REQUEST['ticketid']);
}
/*
 * add new message to given ticket id
 */
else if ( isset($_REQUEST['text']) && isset($_REQUEST['ticketid']) && is_numeric( $_REQUEST['ticketid'] ) )
{   
    // check for permissions
    if ( !TicketHelper::canUserViewTicket( $me->getName(), $_REQUEST['ticketid'] ) )
    {
        die( "No Access." );
    }

    TicketHelper::addMessageToTicket($me->getName(), $_REQUEST['ticketid'], $_REQUEST['text']);
}
// show all tickets which belong to me
else 
{
    TicketHelper::showMyTickets( $me->getName() );
}
?>
	
<?php
login_gui::html_foot();
?>
