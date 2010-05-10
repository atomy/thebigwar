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

#ticketHeadline {
	text-align: center;
	font-size: 1.3em;
	width: 100%;
}

#ticketTableEntryA {
	background-color: #778899;
}

#ticketTableEntryB {
	background-color: #7788AA;
}

#ticketTableSubject {
	width: 60%;
}

#ticketTableCreatedTime {
	width: 20%;
	text-align: center;
}

#ticketTableStatus {
	width: 15%;
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

#ticketDetail {
	border: 0.2em solid #AAAAAA;
	margin: 20px;
	height: 320px;
}

#ticketDetailSideBar {
	margin: 5px;
	width: 20%;
	min-height: 100px;
	border-right: 0.3em solid #778899;
}

#ticketDetailMain {
	margin: 5px;
	width: 75%;
	float: right;
	text-align: left;
}

#ticketDetailSubject {
	
}

#ticketDetailReporter {
	width: 100%;
	text-align: center;
	font-size: 1.2em;
  	font-weight: bold;	
}

#ticketDetailCreatedTime {
	
}

#ticketError {
	font-size: 1.5em;
	color: #FFB266;
}

</style>

<script type="text/javascript">
  function goTo(theUrl)
  {
  	document.location.href = theUrl;
  }
</script>

<?php
// TODO, maximale anzahl offener tickets begrenzen
// TODO, permissions and shit
// create a new ticket with the given parameters
if ( isset( $_REQUEST['newTicket'] ) && isset( $_REQUEST['subject'] ) && isset( $_REQUEST['text'] ) )
{
    if ( strlen( $_REQUEST['subject'] ) > MAX_SUBJECT_LEN )
    {
        ?>
        <div id="ticketError">Fehler: Betreff ist zu lang!</div> 
        <?
        return;
    }
    
    if ( strlen( $_REQUEST['text'] ) > MAX_MESSAGE_LEN )
    {
        ?>
        <div id="ticketError">Fehler: Text ist zu lang!</div> 
        <?
        return;
    }    
    
    $pText = mb_convert_encoding($_REQUEST['text'], 'UTF-8', 'UTF-8');
    unset($_REQUEST['text']);
    $pText = strip_tags($pText);
    $pText = nl2br($pText);
    $pText = wordwrap($pText, MAX_MESSAGE_TEXTTILLWRAP, "<br />", true);    
    $lineCount = substr_count($pText, "<br />"); 
    
    if ( $lineCount >= MAX_MESSAGE_LINES )
    {
        ?>
        <div id="ticketError">Fehler: Text ist zu lang!</div> 
        <?
        return;
    }    
    
    $pSubject = mb_convert_encoding($_REQUEST['subject'], 'UTF-8', 'UTF-8');
    unset($_REQUEST['subject']);
    $pSubject = strip_tags($pSubject);   
    
    $tObj = new Ticket();
    // extern $me
    $tId = $tObj->create( $me->getName(), $pText, $pSubject );
    ?>
    
    <div id="addMsg">
    <?
    if ( $tId >= 0 )
    {
        ?>
        <div id="addMsgSuccess">
        Ticket hinzugef&uuml;gt<br />
        </div>  
        <a href="ticketsystem.php?<?=htmlentities( session_name() . '=' . urlencode( session_id() ) . '&showMyTickets=1' )?>">Zurück zur Ticketübersicht</a>
    	<a href="ticketsystem.php?<?=htmlentities( session_name() . '=' . urlencode( session_id() ) . '&ticketid=' . $tId )?>">Zum erstellten Ticket #<?=$tId?></a> 
    	<?
    }
    else
    {
        ?>
        <div id="ticketError">Fehler beim Erstellen des Tickets!</div> 
        <a href="ticketsystem.php?<?=htmlentities( session_name() . '=' . urlencode( session_id() ) . '&showMyTickets=1' )?>">Zurück zur Ticketübersicht</a>      
        <?        
    }    
    ?>   
    </div> <!-- /addMsg -->    
    <?
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
    
    $msgIDs = $tObj->getMessages();
    $msgObj = new TicketMessage( $msgIDs[0] );
    ?>
    <h1>Ticket #<?=$tObj->getId()?></h1>
    <div id="ticketDetail">    
    	<div id="ticketDetailSubject"><h2><?=$tObj->getSubject()?></h2></div>    	
    	<div id="ticketDetailMain"><?=$msgObj->getText()?></div> 
    	<div id="ticketDetailSideBar">
    		<div id="ticketDetailReporter"><?=$tObj->getReporter()?></div>
    		<div id="ticketDetailCreatedTime"><?=$tObj->getTimeCreated()?></div>
    	</div> 
    </div> <!-- ticketDetail -->
    
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
    			<div id="ticketDetailReporter"><?=$msgObj->getUsername()?></div>
    			<div id="ticketDetailCreatedTime"><?=$msgObj->getTimeCreated()?></div>
    		</div> 
    	</div> <!-- ticketDetail -->
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
	<input id="ticketAddMessage" type="submit"
		value="Nachricht absenden" />	
	<input type="hidden" name="ticketid" value="<?=$_REQUEST['ticketid']?>" />
	</form>	
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
	value="Neue Anfrage erstellen"> 
	<input type="hidden" name="newTicketForm" value="1" />
</form>
<?php

}
/*
 * add new message to given ticket id
 */
else if ( isset($_REQUEST['text']) && isset($_REQUEST['ticketid']) && is_numeric( $_REQUEST['ticketid'] ) )
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

    $pText = mb_convert_encoding($_REQUEST['text'], 'UTF-8', 'UTF-8');   
    unset($_REQUEST['text']);
    $pText = strip_tags($pText);
    $pText = nl2br($pText);
    $pText = wordwrap($pText, MAX_MESSAGE_TEXTTILLWRAP, "<br />", true);
    $lineCount = substr_count($pText, "<br />");   

    echo "LC: ".$lineCount."\n";
    
    if ( $lineCount >= MAX_MESSAGE_LINES )
    {
        ?>
        <div id="ticketError">Fehler: Text ist zu lang!</div> 
        <?
        return;
    }       

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
        ?>
        <div id="ticketError">Fehler beim Hinzufügen der Nachricht!</div> 
        <?        
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
        <td id="ticketTableReporter"><?=$tObj->getReporter()?></td>
        <td id="ticketTableSubject"><?=$tObj->getSubject()?></td>
        <td id="ticketTableCreatedTime"><?=$tObj->getTimeCreated()?></td>
        </tr>
        <?
        $i ++;
    }
    ?>

</table>

</div>
<!-- /ticketlist -->

<form action="<?=$_SERVER['PHP_SELF']?>" method="post"><input id="newticketButton" type="submit" value="Neue Anfrage erstellen" /> 
	<input type="hidden" name="newTicketForm" value="1" />
</form>

<?php
}
?>
	
<?php
login_gui::html_foot();
?>