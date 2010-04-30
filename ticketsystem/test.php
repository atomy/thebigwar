<?php

require_once '../include/config_inc.php';
require_once TBW_ROOT.'include/DBHelper.php';
require_once TBW_ROOT.'ticketsystem/TicketMessage.php';

?>

<html>
<head>
</head>
<body>

<?php 
if ( isset($_REQUEST['newTicket']) && isset($_REQUEST['subject']) && isset($_REQUEST['text']) )
{
    // TODO, limits für parameter prüfen
    $tObj = new Ticket();
    $pText = $_REQUEST['text'];
    $pSubject = $_REQUEST['subject'];
    
    $tId = $tObj->create(/*TODO*/ "Hans", $pText, $pSubject);
    if ( $tId >= 0)
    {
        echo "Ticket erfolgreich erstellt! (".$tId.")"; // TODO, more sexier success message
    }
}
else if ( isset($_REQUEST['newTicketForm']) )
{
    // TODO, formular für ticketeintrag
    ?>
    <form>    
    </form>
    <?php     
}
else if ( isset($_REQUEST['ticketid']) && is_numeric($_REQUEST['ticketid']))
{

    $tObj = new Ticket($_REQUEST['ticketid']);
    if (!$tObj->isValid())
    {
    	echo "ERROR unable to get ticket from database\n";
    }
 
    echo "<div id=\"ticketHeader\">";
    echo "<div id=\"ticketReporter\">".$tObj->getReporter()."</div>";
    echo "<div id=\"ticketSubject\">".$tObj->getSubject()."</div>";  
    echo "<div id=\"ticketCreatedTime\">".$tObj->getTimeCreated()."</div>";
    
    $msgIDs = $tObj->Messages();
    
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
?>
<div id="ticketoptions">
</div>

<div id="ticketlist">

<?php
$ticketManager = TicketManager::getInstance();
$tickets = $ticketManager->getNumTicketsByStatus(TICKET_STATUS_NEW, 50);

foreach( $tickets as $ticketId )
{
    $tObj = new Ticket($ticketId);
    echo "<div id=\"ticketEntry\">";
    echo "<div id=\"ticketReporter\">".$tObj->getReporter()."</div>";
    echo "<div id=\"ticketSubject\">".$tObj->getSubject()."</div>";  
    echo "<div id=\"ticketCreatedTime\">".$tObj->getTimeCreated()."</div>";      
    echo "</div>";
}
?>

</div>
<?php 
}
?>
</body>
</html>            