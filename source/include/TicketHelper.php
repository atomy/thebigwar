<?php

if(!isset($_SERVER['DOCUMENT_ROOT']) || strlen($_SERVER['DOCUMENT_ROOT']) <= 0)
    $_SERVER['DOCUMENT_ROOT'] = getcwd()."/..";
    
require_once($_SERVER['DOCUMENT_ROOT'].'/include/config_inc.php');
require_once $_SERVER['DOCUMENT_ROOT'].'/ticketsystem/TicketManager.php';

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
<?php
            return;
        }
        
        if ( strlen( $text ) > MAX_MESSAGE_LEN )
        {
            ?>
<div id="ticketError">Fehler: Text ist zu lang!</div>
<?php
            return;
        }
        
        $tManager = ticketManager::getInstance();
        
        if ( $tManager->getTicketNumByStatusForUser( TICKET_STATUS_NEW, $username ) >= MAX_NEW_TICKETS )
        {
?>
<div id="ticketError">Fehler: Du hast die maximale Anzahl von
möglichen offenen Tickets ereicht!</div>
<?php
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
<?php
            return;
        }
        
        $pSubject = mb_convert_encoding( $subject, 'UTF-8', 'UTF-8' );
        unset( $subject );
        $pSubject = strip_tags( $pSubject );
        
        // extern $me
        $tId = $tManager->newTicket($username, $pText, $pSubject);
?>
<div id="addMsg">
<?php
        if ( $tId >= 0 )
        {
?>
  <div id="addMsgSuccess">Ticket hinzugefügt<br />
  </div>
  <a href="ticketsystem.php?<?php echo htmlentities( session_name() . '=' . urlencode( session_id() ) )?>">Zurück zur Ticketübersicht</a> 
  <a href="ticketsystem.php?<?php echo htmlentities( session_name() . '=' . urlencode( session_id() ) . '&ticketid=' . $tId )?>">Zu erstelltem Ticket #<?php echo $tId?></a> 
<?php
        }
        else
        {
?>
  <div id="ticketError">Fehler beim Erstellen des Tickets!</div>
  <a href="ticketsystem.php?<?php echo htmlentities( session_name() . '=' . urlencode( session_id() ) )?>">Zurück zur Ticketübersicht</a>      
<?php
        }
?>   
</div> <!-- /addMsg -->
<?php
    }

    /**
     * shows form for submitting new tickets
     */
    public static function showNewTicketForm( )
    {
?>
<form action="ticketsystem.php?<?php echo htmlentities( session_name() . '=' . urlencode( session_id() ) )?>" method="post">
  <dl>
	<dt>Betreff</dt>
	<dd><input id="input_subject" type="text" name="subject" width="50" /></dd>
  	<dt>Deine Anfrage:</dt>
  	<dd><textarea name="text" rows="20" cols="50"></textarea></dd>
  </dl>
  <p>
    <input id="submit_button" type="submit" value="Ticket absenden" /> 
    <input id="reset_button" type="reset" value="Abbrechen" />
    <input type="hidden" name="newTicket" value="1" />
  </p>
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
<h1>Ticket #<?php echo $tObj->getId()?></h1>
<div id="ticketDetail">
  <div id="ticketDetailSubject">
    <h2>&#91;<?php echo $tObj->getStatusString()?>&#93; <?php echo $tObj->getSubject()?></h2>
  </div>
  <div id="ticketDetailMain"><?php echo $msgObj->getText()?></div>
  <div id="ticketDetailSideBar">
    <div id="ticketDetailReporter"><?php echo $tObj->getReporter()?></div>
    <div id="ticketDetailCreatedTime"><?php echo $tObj->getTimeCreated()?></div>
  </div>
</div><!-- ticketDetail -->

<?php
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
  <div id="ticketDetailMain"><?php echo $msgObj->getText()?></div>
  <div id="ticketDetailSideBar">
<?php
            if ( $msgObj->isGameoperator() )
            {
                ?>
    <div id="ticketDetailReporterGO"><?php echo $msgObj->getUsername()?> (Gameoperator)</div>   
<?php
            }
            else
            {
?>
    <div id="ticketDetailReporter"><?php echo $msgObj->getUsername()?></div>
<?php
            }
            ?>
    <div id="ticketDetailCreatedTime"><?php echo $msgObj->getTimeCreated()?></div>
  </div>
</div><!-- ticketDetail -->
<?php
            $i++;
        }
?>
<form action="ticketsystem.php?<?php echo htmlentities( session_name() . '=' . urlencode( session_id() ) )?>"  method="post">
<dl>
	<dt>Neue Nachricht hinzufügen</dt>
	<dd><textarea name="text" rows="20" cols="50"></textarea></dd>
</dl>
<input id="ticketAddMessage" type="submit" value="Nachricht absenden" />
<?php
        if ( $showGoOptions )
        {
            ?>
<input id="ticketResolve" type="submit" name="resolve"
	value="Ticket als bearbeitet markieren" />
<?php
        }
        ?>
<input type="hidden" name="ticketid" value="<?php echo $ticketid?>" /></form>
<a
	href="ticketsystem.php?<?php echo htmlentities( session_name() . '=' . urlencode( session_id() ) )?>">Zurück
zur Ticketübersicht</a>
<?php
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
        $tickets = $ticketManager->getMyTickets( $username );
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
        <tr id="<?php echo $cssID?>"
		onclick="goTo('ticketsystem.php?<?php echo htmlentities( session_name() . '=' . urlencode( session_id() ) . '&ticketid=' . $ticketId )?>')"
		onmouseover="style.backgroundColor='#667788'"
		onmouseout="style.backgroundColor='#778899'">
    <?php
            }
            else
            {
                ?>
        	<tr id="<?php echo $cssID?>"
			onclick="goTo('ticketsystem.php?<?php echo htmlentities( session_name() . '=' . urlencode( session_id() ) . '&ticketid=' . $ticketId )?>')"
			onmouseover="style.backgroundColor='#667788'"
			onmouseout="style.backgroundColor='#7788AA'">
            <?php
            }
            ?>
    	<td id="ticketTableStatus"><?php echo $tObj->getStatusString()?></td>
			<td id="ticketTableSubject"><?php echo $tObj->getSubject()?></td>
			<td id="ticketTableCreatedTime"><?php echo $tObj->getLastActivity()?></td>
		</tr>
        <?php
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
	action="ticketsystem.php?<?php echo htmlentities( session_name() . '=' . urlencode( session_id() ) )?>"
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
<?php
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
<?php
            return;
        }
        
        ?>
<div id="addMsg">
    <?php
        if ( $tObj->addMessage( $username, $pText, $gameoperator ) )
        {
            ?>
        <div id="addMsgSuccess">Nachricht hinzugefügt<br />
</div>        
    	<?php
        }
        else
        {
            ?>
        <div id="ticketError">Fehler beim Hinzufügen der Nachricht!</div> 
        <?php
        }
        // announce new messages to irc
        $url = 'http://'.$_SERVER['HTTP_HOST'].'/admin/ticketsystem.php?ticketid='.urlencode($tObj->getId());
        if($gameoperator)
            phpbb2egg("\00304Neue Nachricht in #".$tObj->getId()." von '".$username."' (GO) mit Betreff '".$tObj->getSubject()."' -- $url", "tbwsupport" );
        else
            phpbb2egg("\00304Neue Nachricht in #".$tObj->getId()." von '".$username."' mit Betreff '".$tObj->getSubject()."' -- $url", "tbwsupport" );      
            
        ?>
    <a
	href="ticketsystem.php?<?php echo htmlentities( session_name() . '=' . urlencode( session_id() ) )?>">Zurück
zur Ticketübersicht</a> <a
	href="ticketsystem.php?<?php echo htmlentities( session_name() . '=' . urlencode( session_id() ) . '&ticketid=' . $tObj->getId() )?>">Zurück zu Ticket #<?php echo $tObj->getId()?></a>

</div>
<!-- /addMsg -->
<?php
    }

    /**
     * list all tickets which match the given status
     * @param unknown_type $status
     */
    // TODO, $status isnt used yet
    // TODO, warning, status is not safe
    public static function showTicketsWithStatus( $status )
    {
        if ( ! is_numeric( $status ) )
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
<?php
        foreach ( $GLOBALS['TICKETSTATUS'] as $statusID => $statusName )
        {
            if ( $status == $statusID )
            {
                ?>
    	<a href="ticketsystem.php?status=<?php echo $statusID?>"><b><?php echo $GLOBALS['TICKETSTATUS_DESC'][$statusID]?> (<?php echo $ticketManager->getTicketNumByStatus( $statusID )?>)</b></a>
        <?php
            }
            else
            {
                ?>
    	<font size="1"><a href="ticketsystem.php?status=<?php echo $statusID?>"><?php echo $GLOBALS['TICKETSTATUS_DESC'][$statusID]?> (<?php echo $ticketManager->getTicketNumByStatus( $statusID )?>)</a></font>
        <?php
            }
        }
        ?>
</div>
<!-- /ticketHeadline -->

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
        <tr id="<?php echo $cssID?>"
		onclick="goTo('ticketsystem.php?<?php echo htmlentities( session_name() . '=' . urlencode( session_id() ) . '&ticketid=' . $ticketId )?>')"
		onmouseover="style.backgroundColor='#667788'"
		onmouseout="style.backgroundColor='#778899'">
    <?php
            }
            else
            {
                ?>
        	<tr id="<?php echo $cssID?>"
			onclick="goTo('ticketsystem.php?<?php echo htmlentities( session_name() . '=' . urlencode( session_id() ) . '&ticketid=' . $ticketId )?>')"
			onmouseover="style.backgroundColor='#667788'"
			onmouseout="style.backgroundColor='#7788AA'">
            <?php
            }
            ?>
    		<td id="ticketTableStatus"><?php echo $tObj->getStatusString()?></td>
			<td id="ticketTableSubject"><?php echo $tObj->getSubject()?></td>
			<td id="ticketTableCreatedTime"><?php echo $tObj->getLastActivity()?></td>
		</tr>
        <?php
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
    <?php
        if ( $tObj->setStatus( TICKET_STATUS_RESOLVED ) )
        {
            ?>
        <div id="addMsgSuccess">Ticket als erledigt markiert<br />
</div>        
    	<?php
        }
        else
        {
            ?>
        <div id="ticketError">Fehler beim Setzen des Status!</div> 
        <?php
        }
        $url = 'http://'.$_SERVER['HTTP_HOST'].'/admin/ticketsystem.php?ticketid='.urlencode($tObj->getId());
        phpbb2egg("\00304Ticket #".$tObj->getId()." von '".$tObj->getReporter()."' mit Betreff '".$tObj->getSubject()."' wurde als erledigt markiert. -- $url", "tbwsupport" );   
        ?>
    <a
	href="ticketsystem.php?<?php echo htmlentities( session_name() . '=' . urlencode( session_id() ) )?>">Zurück
zur Ticketübersicht</a> <a
	href="ticketsystem.php?<?php echo htmlentities( session_name() . '=' . urlencode( session_id() ) . '&ticketid=' . $tObj->getId() )?>">Zurück zu Ticket #<?php echo $tObj->getId()?></a>

</div>
<!-- /addMsg -->
<?php
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

    public static function userRenamed( $oldname, $newname )
    {
        $ticketManager = TicketManager::getInstance();
        $ticketManager->renameUser( $oldname, $newname );
    }
}
