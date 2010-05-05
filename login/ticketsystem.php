<?php
require_once '../include/config_inc.php';
require_once TBW_ROOT . 'include/DBHelper.php';
require_once TBW_ROOT . 'ticketsystem/TicketMessage.php';
require_once TBW_ROOT . 'ticketsystem/TicketManager.php';
require ( TBW_ROOT . 'login/scripts/include.php' );

login_gui::html_head( false );
?>

<style type="text/css">
#addMsg a {
	text-decoration: underline;
	font-size: 1em;
	margin: 10px;
}

#addMsgSuccess {
	background-image: url(../images/green_tick.png);
	background-repeat: no-repeat;
	background-position: center right;
	color: #FFFFFF;
	text-align: left;
	height: 50px;
	width: 40%;
	margin: 10px;
	margin-left: 30%;
	font-size: 1.8em;
	line-height: 2.5;
}

#submit_button {
	background-image: url(../images/green_tick.png);
	background-repeat: no-repeat;
	background-position: center right;
	color: #FFFFFF;
	text-align: left;
	height: 50px;
	width: 180px;
	border: 0.2em solid #AAAAAA;
	margin-left: 50px;
}

#reset_button {
	background-image: url(../images/red_cross.png);
	background-repeat: no-repeat;
	background-position: center right;
	color: #FFFFFF;
	text-align: left;
	height: 50px;
	width: 180px;
	border: 0.2em solid #AAAAAA;
	margin-left: 20px;
}

#input_subject {
	width: 420px;
	margin-bottom: 20px;
}

#ticketoptions {
	
}

#ticketlist {
	
}

#ticketHeadline {
	text-align: center;
	font-size: 1.3em;
	width: 100%;
}

#ticketEntryA {
	background-color: #778899;
}

#ticketEntryB {
	background-color: #7788AA;
}

#ticketReporter {
	width: 20%;
}

#ticketSubject {
	width: 100%;
}

#ticketCreatedTime {
	width: 20%;
	text-align: center;
}

#ticketMessage {
	
}

#ticketMessageEntry {
	
}

#ticketMessageEntryText {
	
}

#ticketMessageEntryUser {
	
}

#ticketMessageEntryTime {
	
}

#ticketBody {
	padding-left: 10px;
}

#ticketTable {
	width: 100%
}

#ticketTable th {
	font-size: 1.2em;
}

#newticketButton {
	background-image: url(../images/new_ticket.png);
	background-repeat: no-repeat;
	background-position: center right;
	color: #FFFFFF;
	text-align: left;
	height: 55px;
	width: 250px;
	border: 0.2em solid #AAAAAA;
	font-size: 1.3em;
	margin-top: 20px;
	margin-left: 250px;
	line-height: 1.7;
}

#ticketAddMessage {
	margin: 20px;
	margin-left: 40%;
}
</style>

<script type="text/javascript">
  function goTo(theUrl)
  {
  	document.location.href = theUrl;
  }
</script>

<?php
// create a new ticket with the given parameters
if ( isset( $_REQUEST['newTicket'] ) && isset( $_REQUEST['subject'] ) && isset( $_REQUEST['text'] ) )
{
    if ( strlen( $_REQUEST['subject'] ) > 64 )
    {
        echo "Betreff ist zu lang!\n";
        return;
    }
    
    if ( strlen( $_REQUEST['text'] ) > 3000 )
    {
        echo "Text ist zu lang!\n";
        return;
    }
    
    $tObj = new Ticket();
    $pText = $_REQUEST['text'];
    $pSubject = $_REQUEST['subject'];
    
    // extern $me
    $tId = $tObj->create( $me->getName(), $pText, $pSubject );
    if ( $tId >= 0 )
    {
        echo "Ticket erfolgreich erstellt! (" . $tId . ")"; // TODO, more sexier success message
    }
    else
    {
        echo "Fehler beim Erstellen des Tickets!\n";
    }
}
// display form for submitting new tickets
else if ( isset( $_REQUEST['newTicketForm'] ) )
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
// display ticket details for the given ticketid
else if ( isset( $_REQUEST['ticketid'] ) && is_numeric( $_REQUEST['ticketid'] ) && !isset($_REQUEST['text']) )
{
    $tObj = new Ticket( $_REQUEST['ticketid'] );
    if ( ! $tObj->isValid() )
    {
        throw new Exception( __METHOD__ . " ERROR unable to get ticket from database\n" );
    }
    ?>
    <h1>Ticket #<?=$tObj->getId()?></h1>
    <div id="ticketHeader">    
    	<div id="ticketSubject">Betreff: <?=htmlspecialchars( $tObj->getSubject() )?></div>
    	<div id="ticketCreatedTime">erstellt: <?=$tObj->getTimeCreated()?></div>;    
    </div> <!-- ticketHeader -->
    
    <div id="ticketBody"> <!-- ticketBody -->
    <?    
    $msgIDs = $tObj->getMessages();
    
    $i = 1;
    // loop through all linked messageIDs and create their objects for output
    while ( isset( $msgIDs[$i] ) )
    {
        $msgObj = new TicketMessage( $msgIDs[$i] );
        if ( ! $msgObj->isValid() )
        {
            break;
        }
        
        echo "<div id=\"ticketMessageEntry\">";
        echo "<div id=\"ticketMessageEntryText\">" . htmlspecialchars( $msgObj->getText() ) . "</div>";
        echo "<div id=\"ticketMessageEntryUser\">" . htmlspecialchars( $msgObj->getUsername() ) . "</div>";
        echo "<div id=\"ticketMessageEntryTime\">" . $msgObj->getTimeCreated() . "</div>";
        echo "</div>"; // ticketMessageEntry
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
	<input id="ticketAddMessage" type="submit"
		value="Nachricht absenden" />	
	<input type="hidden" name="ticketid" value="<?=$_REQUEST['ticketid']?>" />
	</form>	
	</div> <!-- ticketBody -->
    <?
}
// show all tickets which belong to me
else if ( isset( $_REQUEST['showMyTickets'] ) )
{
    ?>

<div id="ticketoptions"></div>

<div id="ticketlist">

<?php
    $ticketManager = TicketManager::getInstance();
    // extern $me
    $tickets = $ticketManager->getNumMyTickets( $me->getName(), 50 );
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
        // TODO, output through htmlentities()
        // alternate background colors
        $cssID = "ticketEntryA";
        if ( $i % 2 )
            $cssID = "ticketEntryB";
        
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
    	<td id="ticketStatus"><?=$tObj->getStatusString()?></td>
		<td id="ticketSubject"><?=$tObj->getSubject()?></td>
		<td id="ticketCreatedTime"><?=$tObj->getLastActivity()?></td>
		</tr>
        <?
        $i ++;
    }
    
    // no tickets, generate a dummy entry
    if ( $i == 0 )
    {
        ?>
   		<tr id="ticketEntryA">
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
	value="Neue Anfrage erstellen" /> <input type="hidden"
	name="newTicketForm" value="1" /></form>
<?php

}
/*
 * add new message to given ticket id
 */
else if ( isset($_REQUEST['text']) && isset($_REQUEST['ticketid']) && is_numeric( $_REQUEST['ticketid'] ) )
{
    if ( strlen( $_REQUEST['text'] ) > 3000 )
    {
        echo "Text ist zu lang!\n";
        return;
    }

    $tObj = new Ticket( $_REQUEST['ticketid'] );
    if ( ! $tObj->isValid() )
    {
        throw new Exception( __METHOD__ . " ERROR unable to get ticket from database\n" );
    }    
    
    $tObj = new Ticket($_REQUEST['ticketid']);
    
    if (!$tObj->isValid())
    {
        echo "Fehler beim Laden des Tickets\n";
    }
    $pText = $_REQUEST['text'];
    
    // extern $me
    //echo $me->getName()." -- ".$pText."\n";
    ?>
    <div id="addMsg">
    <?
    if ($tObj->addMessage($me->getName(), $pText))
    {
        ?>
        <div id="addMsgSuccess">
        Nachricht hinzugef&uuml;gt<br />
        </div>        
    	<?
    }
    else
    {
        echo "Fehler beim Hinzufügen der Nachricht\n";
    }    
    ?>
    <a href="ticketsystem.php?<?=htmlentities( session_name() . '=' . urlencode( session_id() ) . '&showMyTickets=1' )?>">Zurück zur Ticketübersicht</a>
    <a href="ticketsystem.php?<?=htmlentities( session_name() . '=' . urlencode( session_id() ) . '&ticketid=' . $tObj->getId() )?>">Zurück zu Ticket #<?=$tObj->getId()?></a>
    
    </div> <!-- /addMsg -->
    <?
}
// show all tickets with the given status // TODO, permissions
else
{
    print_r($_REQUEST);
    ?>
<div id="ticketoptions"></div>

<div id="ticketlist">

<?php
    $ticketManager = TicketManager::getInstance();
    $tickets = $ticketManager->getNumTicketsByStatus( TICKET_STATUS_NEW, 50 );
    ?>
<div id="ticketHeadline">Tickets mit Status <b>NEU</b></div>

<table id="ticketTable">
	<tr>
		<th>Benutzer</th>
		<th>Betreff</th>
		<th>Datum</th>
	</tr>

<?php
    $i = 0;
    foreach ( $tickets as $ticketId )
    {
        $tObj = new Ticket( $ticketId );
        // TODO, output through htmlentities()
        // alternate background colors
        $cssID = "ticketEntryA";
        if ( $i % 2 )
            $cssID = "ticketEntryB";
        
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
        
        echo "<td id=\"ticketReporter\">" . $tObj->getReporter() . "</td>";
        echo "<td id=\"ticketSubject\">" . $tObj->getSubject() . "</td>";
        echo "<td id=\"ticketCreatedTime\">" . $tObj->getTimeCreated() . "</td>";
        echo "</tr>";
        $i ++;
    }
    ?>

</table>

</div>
<!-- /ticketlist -->

<form
	action="<?php
    print $_SERVER['PHP_SELF'];
    ?>"
	method="post"><input id="newticketButton" type="submit"
	value="Neue Anfrage erstellen" /> <input type="hidden"
	name="newTicketForm" value="1" /></form>

<?php
}
?>
	
<?php
login_gui::html_foot();
?>
