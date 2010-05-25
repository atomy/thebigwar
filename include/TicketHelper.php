<?php

require_once TBW_ROOT.'ticketsystem/TicketManager.php';

/**
 * this class helps ticket management
 * @author atomy
 *
 */
class TicketHelper
{

    /**
     * creates a ticket with the given parameters
     * @param unknown_type $subject
     * @param unknown_type $text
     */
    public static function createTicket( $username, $subject, $text )
    {
        if ( strlen( $subject ) > MAX_SUBJECT_LEN )
        {
            ?>
<div id="ticketError">Fehler: Betreff ist zu lang!</div>
<?
            return;
        }
        
        if ( strlen( $text ) > MAX_MESSAGE_LEN )
        {
            ?>
<div id="ticketError">Fehler: Text ist zu lang!</div>
<?
            return;
        }        
        
        $tManager = ticketManager::getInstance();
        
        if ($tManager->getTicketNumByStatusForUser( TICKET_STATUS_NEW, $username ) >= MAX_NEW_TICKETS)
        {
            ?>
<div id="ticketError">Fehler: Du hast die maximale Anzahl von offenen Tickets ereicht!</div>
<?
            return;            
        }
        
        $pText = mb_convert_encoding( $text, 'UTF-8', 'UTF-8' );
        unset( $text );
        $pText = strip_tags( $pText );
        $pText = nl2br( $pText );
        $pText = wordwrap( $pText, MAX_MESSAGE_TEXTTILLWRAP, "<br />", true );
        $lineCount = substr_count( $pText, "<br />" );
        
        if ( $lineCount >= MAX_MESSAGE_LINES )
        {
            ?>
<div id="ticketError">Fehler: Text ist zu lang!</div>
<?
            return;
        }
        
        $pSubject = mb_convert_encoding( $subject, 'UTF-8', 'UTF-8' );
        unset( $subject );
        $pSubject = strip_tags( $pSubject );
        
        $tObj = new Ticket();
        // extern $me
        $tId = $tObj->create( $username, $pText, $pSubject );
        ?>

<div id="addMsg">
    <?
        if ( $tId >= 0 )
        {
            ?>
        <div id="addMsgSuccess">Ticket hinzugef&uuml;gt<br />
</div>
<a
	href="ticketsystem.php?<?=htmlentities( session_name() . '=' . urlencode( session_id() ) )?>">Zurück
zur Ticketübersicht</a> <a
	href="ticketsystem.php?<?=htmlentities( session_name() . '=' . urlencode( session_id() ) . '&ticketid=' . $tId )?>">Zum erstellten Ticket #<?=$tId?></a> 
    	<?
        }
        else
        {
            ?>
        <div id="ticketError">Fehler beim Erstellen des Tickets!</div>
<a
	href="ticketsystem.php?<?=htmlentities( session_name() . '=' . urlencode( session_id() ) )?>">Zurück
zur Ticketübersicht</a>      
        <?
        }
        ?>   
    </div>
<!-- /addMsg -->
<?
    }

    /**
     * shows form for submitting new tickets
     */
    public static function showNewTicketForm( )
    {
        ?>
<form
	action="ticketsystem.php?<?=htmlentities( session_name() . '=' . urlencode( session_id() ) )?>"
	method="post">
<dl>
	<dt>Betreff</dt>
	<dd><input id="input_subject" type="text" name="subject" width="50" /></dd>
	<dt>Deine Anfrage:</dt>
	<dd><textarea name="text" rows="20" cols="50"></textarea></dd>
</dl>
<p><input id="submit_button" type="submit" value="Ticket absenden" /> <input
	id="reset_button" type="reset" value="Abbrechen" /> <input
	type="hidden" name="newTicket" value="1" /></p>
</form>
<?php
    }

    /**
     * show details of the given ticketid
     * @param unknown_type $ticketid
     */
    public static function showTicketDetails( $ticketid, $showGoOptions = false )
    {
        $tObj = new Ticket( $ticketid );
        if ( ! $tObj->isValid() )
        {
            throw new Exception( __METHOD__ . " ERROR unable to get ticket from database\n" );
        }
        
        $msgIDs = $tObj->getMessages();
        $msgObj = new TicketMessage( $msgIDs[0] );
        ?>
<h1>Ticket #<?=$tObj->getId()?></h1>
<div id="ticketDetail">
<div id="ticketDetailSubject">
<h2>&#91;<?=$tObj->getStatusString()?>&#93; <?=$tObj->getSubject()?></h2>
</div>
<div id="ticketDetailMain"><?=$msgObj->getText()?></div>
<div id="ticketDetailSideBar">
<div id="ticketDetailReporter"><?=$tObj->getReporter()?></div>
<div id="ticketDetailCreatedTime"><?=$tObj->getTimeCreated()?></div>
</div>
</div>
<!-- ticketDetail -->

<?
        
        $i = 1;
        // loop through all linked messageIDs and create their objects for output
        while ( isset( $msgIDs[$i] ) )
        {
            $msgObj = new TicketMessage( $msgIDs[$i] );
            if ( ! $msgObj->isValid() )
            {
                break;
            }
            ?>
<div id="ticketDetail">
<div id="ticketDetailMain"><?=$msgObj->getText()?></div>
<div id="ticketDetailSideBar">
<? 
if ( $msgObj->isGameoperator() )
{
?>
<div id="ticketDetailReporterGO"><?=$msgObj->getUsername()?> (Gameoperator)</div>   
<? 
}
else
{
?>
<div id="ticketDetailReporter"><?=$msgObj->getUsername()?></div>
<?
} 
?>
<div id="ticketDetailCreatedTime"><?=$msgObj->getTimeCreated()?></div>
</div>
</div>
<!-- ticketDetail -->
<?
            $i ++;
        }
        ?>
<form
	action="ticketsystem.php?<?=htmlentities( session_name() . '=' . urlencode( session_id() ) )?>"
	method="post">
<dl>
	<dt>Neue Nachricht hinzuf&uuml;gen</dt>
	<dd><textarea name="text" rows="20" cols="50"></textarea></dd>
</dl>
<input id="ticketAddMessage" type="submit" value="Nachricht absenden" />
<?
if ( $showGoOptions )
{
?>
<input id="ticketResolve" type="submit" name="resolve" value="Ticket als bearbeitet markieren" />
<?
}
?>
<input type="hidden" name="ticketid" value="<?=$ticketid?>" /></form>
<a href="ticketsystem.php?<?=htmlentities( session_name() . '=' . urlencode( session_id() ) )?>">Zurück
zur Ticketübersicht</a>
<?
    }

    /**
     * show all tickets which i'm reporter of
     */
    public static function showMyTickets( $username )
    {
        ?>

<div id="ticketoptions"></div>

<div id="ticketlist">

<?php
        $ticketManager = TicketManager::getInstance();
        $tickets = $ticketManager->getNumMyTickets( $username, 50 );
        ?>
<div id="ticketHeadline">
<h1>Meine Tickets</h1>
</div>
<table id="ticketTable">
	<tr>
		<th>Status</th>
		<th>Betreff</th>
		<th>Datum</th>
	</tr>
<?php
        $i = 0;
        foreach ( $tickets as $ticketId )
        {
            $tObj = new Ticket( $ticketId );
            
            // alternate background colors
            $cssID = "ticketTableEntryA";
            if ( $i % 2 )
                $cssID = "ticketTableEntryB";
            
            if ( $i % 2 )
            {
                ?>       
        <tr id="<?=$cssID?>"
		onclick="goTo('ticketsystem.php?<?=htmlentities( session_name() . '=' . urlencode( session_id() ) . '&ticketid=' . $ticketId )?>')"
		onmouseover="style.backgroundColor='#667788'"
		onmouseout="style.backgroundColor='#778899'">
    <?php
            }
            else
            {
                ?>
        	<tr id="<?=$cssID?>"
			onclick="goTo('ticketsystem.php?<?=htmlentities( session_name() . '=' . urlencode( session_id() ) . '&ticketid=' . $ticketId )?>')"
			onmouseover="style.backgroundColor='#667788'"
			onmouseout="style.backgroundColor='#7788AA'">
            <?php
            }
            ?>
    	<td id="ticketTableStatus"><?=$tObj->getStatusString()?></td>
			<td id="ticketTableSubject"><?=$tObj->getSubject()?></td>
			<td id="ticketTableCreatedTime"><?=$tObj->getLastActivity()?></td>
		</tr>
        <?
            $i ++;
        }
        
        // no tickets, generate a dummy entry
        if ( $i == 0 )
        {
            ?>
   		<tr id="ticketEntryA">
			<td id="ticketStatus"></td>
			<td id="ticketSubject">Keine Tickets vorhanden.</td>
			<td id="ticketCreatedTime"></td>
		</tr>
        <?php
        }
        ?>
</table>

</div>
<!-- /ticketlist -->

<form
	action="ticketsystem.php?<?=htmlentities( session_name() . '=' . urlencode( session_id() ) )?>"
	method="post"><input id="newticketButton" type="submit"
	value="Neue Anfrage erstellen"> <input type="hidden"
	name="newTicketForm" value="1" /></form>
<?php
    }

    /**
     * add a new message to the ticket
     * @param unknown_type $username
     * @param unknown_type $ticketid
     * @param unknown_type $text
     * @param unknown_type $gameoperator
     */
    public static function addMessageToTicket( $username, $ticketid, $text, $gameoperator = false )
    {
        if ( strlen( $_REQUEST['text'] ) > MAX_MESSAGE_LEN )
        {
            ?>
<div id="ticketError">Fehler: Text ist zu lang!</div>
<?
            return;
        }
        
        $tObj = new Ticket( $_REQUEST['ticketid'] );
        if ( ! $tObj->isValid() )
        {
            throw new Exception( __METHOD__ . " ERROR unable to get ticket from database\n" );
        }
        
        $pText = mb_convert_encoding( $_REQUEST['text'], 'UTF-8', 'UTF-8' );
        unset( $_REQUEST['text'] );
        $pText = strip_tags( $pText );
        $pText = nl2br( $pText );
        $pText = wordwrap( $pText, MAX_MESSAGE_TEXTTILLWRAP, "<br />", true );
        $lineCount = substr_count( $pText, "<br />" );      
        
        if ( $lineCount >= MAX_MESSAGE_LINES )
        {
            ?>
<div id="ticketError">Fehler: Text ist zu lang!</div>
<?
            return;
        }

        ?>
<div id="addMsg">
    <?
        if ( $tObj->addMessage( $username, $pText, $gameoperator ) )
        {
            ?>
        <div id="addMsgSuccess">Nachricht hinzugef&uuml;gt<br />
</div>        
    	<?
        }
        else
        {
            ?>
        <div id="ticketError">Fehler beim Hinzufügen der Nachricht!</div> 
        <?
        }
        ?>
    <a
	href="ticketsystem.php?<?=htmlentities( session_name() . '=' . urlencode( session_id() ) )?>">Zurück
zur Ticketübersicht</a> <a
	href="ticketsystem.php?<?=htmlentities( session_name() . '=' . urlencode( session_id() ) . '&ticketid=' . $tObj->getId() )?>">Zurück zu Ticket #<?=$tObj->getId()?></a>

</div>
<!-- /addMsg -->
<?
    }
    
    /**
     * list all tickets which match the given status
     * @param unknown_type $status
     */
    // TODO, $status isnt used yet
    // TODO, warning, status is not safe
    public static function showTicketsWithStatus( $status )    
    {  
         if (!is_numeric($status))
         {
             return;
         }   
    ?>

<div id="ticketlist">

<?php
    $ticketManager = TicketManager::getInstance();
    $tickets = $ticketManager->getNumTicketsByStatus( $status, 50 );       
    ?>
    
<div id="ticketHeadline">Tickets mit Status 
<?
foreach( $GLOBALS['TICKETSTATUS'] as $statusID => $statusName)
{
    if ( $status == $statusID )
    {
        ?>
    	<a href="ticketsystem.php?status=<?=$statusID?>"><b><?=$GLOBALS['TICKETSTATUS_DESC'][$statusID]?> (<?=$ticketManager->getTicketNumByStatus($statusID)?>)</b></a>
        <?
    }
    else
    {
        ?>
    	<font size="1"><a href="ticketsystem.php?status=<?=$statusID?>"><?=$GLOBALS['TICKETSTATUS_DESC'][$statusID]?> (<?=$ticketManager->getTicketNumByStatus($statusID)?>)</a></font>
        <?        
    }    
}
?>
</div> <!-- /ticketHeadline -->

<table id="ticketTable">
	<tr>
		<th>Status</th>
		<th>Betreff</th>
		<th>Datum</th>
	</tr>
<?php
        $i = 0;
        foreach ( $tickets as $ticketId )
        {
            $tObj = new Ticket( $ticketId );
            
            // alternate background colors
            $cssID = "ticketTableEntryA";
            if ( $i % 2 )
                $cssID = "ticketTableEntryB";
            
            if ( $i % 2 )
            {
                ?>       
        <tr id="<?=$cssID?>"
		onclick="goTo('ticketsystem.php?<?=htmlentities( session_name() . '=' . urlencode( session_id() ) . '&ticketid=' . $ticketId )?>')"
		onmouseover="style.backgroundColor='#667788'"
		onmouseout="style.backgroundColor='#778899'">
    <?php
            }
            else
            {
                ?>
        	<tr id="<?=$cssID?>"
			onclick="goTo('ticketsystem.php?<?=htmlentities( session_name() . '=' . urlencode( session_id() ) . '&ticketid=' . $ticketId )?>')"
			onmouseover="style.backgroundColor='#667788'"
			onmouseout="style.backgroundColor='#7788AA'">
            <?php
            }
            ?>
    		<td id="ticketTableStatus"><?=$tObj->getStatusString()?></td>
			<td id="ticketTableSubject"><?=$tObj->getSubject()?></td>
			<td id="ticketTableCreatedTime"><?=$tObj->getLastActivity()?></td>
		</tr>
        <?
            $i ++;
        }
        
        // no tickets, generate a dummy entry
        if ( $i == 0 )
        {
            ?>
   		<tr id="ticketEntryA">
			<td id="ticketSubject" colspan=3>Keine Tickets vorhanden.</td>
		</tr>
        <?php
        }
        ?>
</table>

</div>
<!-- /ticketlist -->

<?php        
    }
    
    /**
     * resolves the ticket with the given id
     * // TODO, permissions etc.
     * @param unknown_type $ticketId
     */
    public static function resolveTicket( $ticketId )
    {       
        $tObj = new Ticket( $_REQUEST['ticketid'] );
        if ( ! $tObj->isValid() )
        {
            throw new Exception( __METHOD__ . " ERROR unable to get ticket from database\n" );
        }
        

        ?>
<div id="addMsg">
    <?
        if ( $tObj->setStatus( TICKET_STATUS_RESOLVED ) )
        {
            ?>
        <div id="addMsgSuccess">Ticket als erledigt markiert<br />
</div>        
    	<?
        }
        else
        {
            ?>
        <div id="ticketError">Fehler beim Setzen des Status!</div> 
        <?
        }
        ?>
    <a
	href="ticketsystem.php?<?=htmlentities( session_name() . '=' . urlencode( session_id() ) )?>">Zurück
zur Ticketübersicht</a> <a
	href="ticketsystem.php?<?=htmlentities( session_name() . '=' . urlencode( session_id() ) . '&ticketid=' . $tObj->getId() )?>">Zurück zu Ticket #<?=$tObj->getId()?></a>

</div>
<!-- /addMsg -->
<?        
    }

    /**
     * check if the user can view the given ticket, he can when:
     * - he has reported the ticket
     */
    public static function canUserViewTicket( $userName, $ticketId )
    {
        $tObj = new Ticket( $_REQUEST['ticketid'] );
        if ( ! $tObj->isValid() )
        {
	    return false;
        }        

	if ( $userName == $tObj->getReporter() )
        {
	    return true;
        }

        return false;
    }
}
