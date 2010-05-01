<?php

require_once '../include/config_inc.php';
require_once TBW_ROOT.'include/DBHelper.php';
require_once TBW_ROOT.'ticketsystem/TicketMessage.php';
require_once TBW_ROOT.'ticketsystem/TicketManager.php';

error_reporting(E_ALL);
?>

<html>
<head>
<style type="text/css">
	#submit_button { 
		background-image: url(../images/green_tick.png); 
		background-repeat: no-repeat;
		background-position: center right;
		background-color:#000000; 
		color:#FFFFFF; text-align: left; height: 50px; width:180px; border:3px solid #FFFFFF; 
		margin-left: 50px;
		}
	#reset_button { 
		background-image: url(../images/red_cross.png); 
		background-repeat: no-repeat;
		background-position: center right;
		background-color:#000000; 
		color:#FFFFFF; text-align: left; height: 50px; width:180px; border:3px solid #FFFFFF; 
		margin-left: 20px;
		}		
	#input_subject {
		width: 420px;
		margin-bottom: 20px;
	}
	body { background-color:#AABBCC; width: 800px; }
	div { border: 1px solid; }
	
	#ticketoptions { }
	#ticketlist { }
	#ticketHeadline { text-align: center; font-size: 1.2em; width: 100%; }
	#ticketEntryA { background-color: #FFFFCC; }
	#ticketEntryB { background-color: #AAFFCC; }
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
</style>

<script type="text/javascript">
  function goTo(theUrl)
  {
  	document.location.href = theUrl;
  }
</script>

</head>
<body>

<?php 
if ( isset($_REQUEST['newTicket']) && isset($_REQUEST['subject']) && isset($_REQUEST['text']) )
{
    echo "CASE 1\n";
    // TODO, limits für parameter prüfen
    $tObj = new Ticket();
    $pText = $_REQUEST['text'];
    $pSubject = $_REQUEST['subject'];
    
    $tId = $tObj->create(/*TODO*/ "Hans", $pText, $pSubject); 
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
    echo "CASE 2\n";
    ?>    
        <form action="<?php print $_SERVER['PHP_SELF']; ?>" method="post">
            <dl>
                <dt>Betreff</dt>
                <dd><input id="input_subject" type="text" name="subject" width="50"/></dd>
                <dt>Deine Anfrage:</dt>
                <dd><textarea name="text" rows="20" cols="50"></textarea></dd>
            </dl>
            <p>
            <input id="submit_button" type="submit" value="Ticket absenden" onClick="check_form();" />
            <input id="reset_button" type="reset" value="Abbrechen" />
            <input type="hidden" name="newTicket" value="1" />
            </p>
        </form>
    <?php     
}
else if ( isset($_REQUEST['ticketid']) && is_numeric($_REQUEST['ticketid']))
{
    echo "CASE 3\n";
    
    $tObj = new Ticket($_REQUEST['ticketid']);
    if (!$tObj->isValid())
    {
    	throw new Exception( __METHOD__." ERROR unable to get ticket from database\n");
    }
 
    echo "<div id=\"ticketHeader\">";
    echo "<div id=\"ticketReporter\">".$tObj->getReporter()."</div>";
    echo "<div id=\"ticketSubject\">".$tObj->getSubject()."</div>";  
    echo "<div id=\"ticketCreatedTime\">".$tObj->getTimeCreated()."</div>";
    
    $msgIDs = $tObj->getMessages();
    
    $i = 0;
    // loop through all linked messageIDs and create their objects for output
    for( $msgObj = new TicketMessage($msgIDs[$i]); isset($msgIDs[$i]) && $msgObj->isValid(); $msgObj = new TicketMessage($msgIDs[$i]) )
    {
        if ( $i == 0 )
        {
            echo "<div id=\"ticketMessage\">".$msgObj->getText()."</div>";
            echo "</div>"; // ticketHeader
            echo "<div id=\"ticketBody\">";
        }
        else
        {
            echo "<div id=\"ticketMessageEntry\">";
            echo "<div id=\"ticketMessageEntryText\">".$msgObj->getText()."</div>";
            echo "<div id=\"ticketMessageEntryUser\">".$msgObj->getUsername()."</div>";
            echo "<div id=\"ticketMessageEntryTime\">".$msgObj->getTimeCreated()."</div>";
            echo "</div>"; // ticketMessageEntry
        }
        $i++;
    }
    
    echo "</div>"; // ticketBody
}
else
{
    echo "CASE 4\n";
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
    
    // alternate background colors
    $cssID = "ticketEntryA";
    if ($i % 2)
       $cssID = "ticketEntryB";
       
    echo "<tr 
    	id=\"".$cssID."\" 
    	onclick=\"goTo('".$_SERVER['PHP_SELF']."?ticketid=".$ticketId."')\" 
    	onmouseover=\"style.backgroundColor='#AADD88'\" 
    	onmouseout=\"style.backgroundColor='#FAAABB'\"
    	>"; 
    
    echo "<td id=\"ticketReporter\">".$tObj->getReporter()."</td>";
    echo "<td id=\"ticketSubject\">".$tObj->getSubject()."</td>";  
    echo "<td id=\"ticketCreatedTime\">".$tObj->getTimeCreated()."</td>"; 
    echo "</tr>";    
    $i++; 
}
?>

</table>
</div> <!-- /ticketlist -->
<?php 
}
?>
</body>
</html>            