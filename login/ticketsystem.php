<?php
require_once '../include/config_inc.php';
require_once TBW_ROOT.'include/DBHelper.php';
require_once TBW_ROOT.'ticketsystem/TicketMessage.php';
require_once TBW_ROOT.'ticketsystem/TicketManager.php';	
	require( TBW_ROOT.'login/scripts/include.php' );

	login_gui::html_head(false);
?>

<style type="text/css">
	#submit_button { 
		background-image: url(../images/green_tick.png); 
		background-repeat: no-repeat;
		background-position: center right;
		color:#FFFFFF; text-align: left; height: 50px; width:180px; 
		border: 0.2em solid #AAAAAA;
		margin-left: 50px;
		}
	#reset_button { 
		background-image: url(../images/red_cross.png); 
		background-repeat: no-repeat;
		background-position: center right;
		color:#FFFFFF; text-align: left; height: 50px; width:180px; 
		border: 0.2em solid #AAAAAA;
		margin-left: 20px;
		}		
	#input_subject {
		width: 420px;
		margin-bottom: 20px;
	}	
	
	#ticketoptions { }
	#ticketlist { }
	#ticketHeadline { text-align: center; font-size: 1.3em; width: 100%; }
	#ticketEntryA { background-color: #778899; }
	#ticketEntryB { background-color: #7788AA; }
	#ticketReporter { width: 20%; }
	#ticketSubject { width: 60%; }
    #ticketCreatedTime { width: 20%; text-align: center; }
	#ticketMessage {}
	#ticketMessageEntry {  }
	#ticketMessageEntryText {}
	#ticketMessageEntryUser {}
	#ticketMessageEntryTime {}
	#ticketBody {}
	#ticketTable { width: 100% }	
	#ticketTable th { font-size: 1.2em; }	
	#newticketButton { 
		background-image: url(../images/new_ticket.png); 
		background-repeat: no-repeat;
		background-position: center right; 
		color:#FFFFFF; 
		text-align: left; 
		height: 55px; 
		width: 250px; 
		border: 0.2em solid #AAAAAA;
		font-size: 1.3em; 
		margin-top: 20px;
		margin-left: 250px;	
		line-height: 1.7;
	}
</style>	
	
<script type="text/javascript">
  function goTo(theUrl)
  {
  	document.location.href = theUrl;
  }
</script>

<?php 
if ( isset($_REQUEST['newTicket']) && isset($_REQUEST['subject']) && isset($_REQUEST['text']) )
{
    if ( strlen($_REQUEST['subject']) > 64 )
    {
        echo "Betreff ist zu lang!\n";
        return;
    }
    
    if ( strlen($_REQUEST['text']) > 3000 )
    {
        echo "Text ist zu lang!\n";
        return;
    }    
    
    $tObj = new Ticket();
    $pText = $_REQUEST['text'];
    $pSubject = $_REQUEST['subject'];
    
    $tId = $tObj->create($me->getName(), $pText, $pSubject); 
    if ( $tId >= 0)
    {
        echo "Ticket erfolgreich erstellt! (".$tId.")"; // TODO, more sexier success message
    }
    else
    {
        echo "Fehler beim Erstellen des Tickets!\n";
    }
}
else if ( isset($_REQUEST['newTicketForm']) )
{
    ?>    
    	<form action="ticketsystem.php?<?=htmlentities(session_name().'='.urlencode(session_id()))?>" method="post">
            <dl>
                <dt>Betreff</dt>
                <dd><input id="input_subject" type="text" name="subject" width="50"/></dd>
                <dt>Deine Anfrage:</dt>
                <dd><textarea name="text" rows="20" cols="50"></textarea></dd>
            </dl>
            <p>
            <input id="submit_button" type="submit" value="Ticket absenden"/>
            <input id="reset_button" type="reset" value="Abbrechen" />
            <input type="hidden" name="newTicket" value="1" />
            </p>
        </form>
    <?php     
}
else if ( isset($_REQUEST['ticketid']) && is_numeric($_REQUEST['ticketid']))
{
    $tObj = new Ticket($_REQUEST['ticketid']);
    if (!$tObj->isValid())
    {
    	throw new Exception( __METHOD__." ERROR unable to get ticket from database\n");
    }
 
    echo "<div id=\"ticketHeader\">";
    echo "<div id=\"ticketReporter\">".htmlspecialchars($tObj->getReporter())."</div>";
    echo "<div id=\"ticketSubject\">".htmlspecialchars($tObj->getSubject())."</div>";  
    echo "<div id=\"ticketCreatedTime\">".$tObj->getTimeCreated()."</div>";
    
    $msgIDs = $tObj->getMessages();
    
    $i = 0;
    // loop through all linked messageIDs and create their objects for output
    while ( isset($msgIDs[$i]) )
    {
        $msgObj = new TicketMessage($msgIDs[$i]);
        if ( !$msgObj->isValid() )
        {
            break;
        }

        if ( $i == 0 )
        {
            echo "<div id=\"ticketMessage\">".htmlspecialchars($msgObj->getText())."</div>";
            echo "</div>"; // ticketHeader
            echo "<div id=\"ticketBody\">";
        }
        else
        {
            echo "<div id=\"ticketMessageEntry\">";
            echo "<div id=\"ticketMessageEntryText\">".htmlspecialchars($msgObj->getText())."</div>";
            echo "<div id=\"ticketMessageEntryUser\">".htmlspecialchars($msgObj->getUsername())."</div>";
            echo "<div id=\"ticketMessageEntryTime\">".$msgObj->getTimeCreated()."</div>";
            echo "</div>"; // ticketMessageEntry
        }
        $i++;
    }
    
    echo "</div>"; // ticketBody
}
else if ( isset($_REQUEST['showMyTickets']) )
{
?>

<div id="ticketoptions">
</div>

<div id="ticketlist">

<?php
$ticketManager = TicketManager::getInstance();
$tickets = $ticketManager->getNumMyTickets($me->getName(), 50);
?>
<div id="ticketHeadline"><h1>Meine Tickets</h1></div>

<table id="ticketTable">
<tr>
<th>Betreff</th><th>Datum</th>
</tr>

<?php 
$i = 0;
foreach( $tickets as $ticketId )
{   
    $tObj = new Ticket($ticketId);
    // TODO, output through htmlentities()
    // alternate background colors
    $cssID = "ticketEntryA";
    if ($i % 2)
       $cssID = "ticketEntryB";
       
    if ($i % 2)
    {
    ?>       
        <tr id="<?=$cssID?>"
    		onclick="goTo('ticketsystem.php?<?=htmlentities(session_name().'='.urlencode(session_id()).'&ticketid='.$ticketId)?>')" 
    		onmouseover="style.backgroundColor='#667788'" 
    		onmouseout="style.backgroundColor='#778899'"
    		>
    <?php
    }
    else
    {
    ?>
        <tr id="<?=$cssID?>"
    		onclick="goTo('ticketsystem.php?<?=htmlentities(session_name().'='.urlencode(session_id()).'&ticketid='.$ticketId)?>')" 
    		onmouseover="style.backgroundColor='#667788'" 
    		onmouseout="style.backgroundColor='#7788AA'"
    		>
    <?php 
    }
    echo "<td id=\"ticketSubject\">".$tObj->getSubject()."</td>";  
    echo "<td id=\"ticketCreatedTime\">".$tObj->getTimeCreated()."</td>"; 
    echo "</tr>";    
    $i++;    
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

</div> <!-- /ticketlist -->

<form action="ticketsystem.php?<?=htmlentities(session_name().'='.urlencode(session_id()))?>" method="post">
	<input id="newticketButton" type="submit" value="Neue Anfrage erstellen"/>
    <input type="hidden" name="newTicketForm" value="1" />
</form>
<?php
    
}
else
{
?>
<div id="ticketoptions">
</div>

<div id="ticketlist">

<?php
$ticketManager = TicketManager::getInstance();
$tickets = $ticketManager->getNumTicketsByStatus(TICKET_STATUS_NEW, 50);
?>
<div id="ticketHeadline">Tickets mit Status <b>NEU</b></div>

<table id="ticketTable">
<tr>
<th>Benutzer</th><th>Betreff</th><th>Datum</th>
</tr>

<?php 
$i = 0;
foreach( $tickets as $ticketId )
{   
    $tObj = new Ticket($ticketId);
    // TODO, output through htmlentities()
    // alternate background colors
    $cssID = "ticketEntryA";
    if ($i % 2)
       $cssID = "ticketEntryB";
       
    if ($i % 2)
    {
    ?>       
        <tr id="<?=$cssID?>"
    		onclick="goTo('ticketsystem.php?<?=htmlentities(session_name().'='.urlencode(session_id()).'&ticketid='.$ticketId)?>')" 
    		onmouseover="style.backgroundColor='#667788'" 
    		onmouseout="style.backgroundColor='#778899'"
    		>
    <?php
    }
    else
    {
    ?>
        <tr id="<?=$cssID?>"
    		onclick="goTo('ticketsystem.php?<?=htmlentities(session_name().'='.urlencode(session_id()).'&ticketid='.$ticketId)?>')" 
    		onmouseover="style.backgroundColor='#667788'" 
    		onmouseout="style.backgroundColor='#7788AA'"
    		>
    <?php 
    }
    
    echo "<td id=\"ticketReporter\">".$tObj->getReporter()."</td>";
    echo "<td id=\"ticketSubject\">".$tObj->getSubject()."</td>";  
    echo "<td id=\"ticketCreatedTime\">".$tObj->getTimeCreated()."</td>"; 
    echo "</tr>";    
    $i++; 
}
?>

</table>

</div> <!-- /ticketlist -->

<form action="<?php print $_SERVER['PHP_SELF']; ?>" method="post">
	<input id="newticketButton" type="submit" value="Neue Anfrage erstellen"/>
    <input type="hidden" name="newTicketForm" value="1" />
</form>

<?php 
}
?>
	
<?php 
	login_gui::html_foot();
?>
