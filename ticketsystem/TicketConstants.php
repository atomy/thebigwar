<?php

define('MAX_MESSAGE_LEN', 1600); 
define('MAX_SUBJECT_LEN', 64); 
define('MAX_MESSAGE_LINES', 20); 
define('MAX_MESSAGE_TEXTTILLWRAP', 70);

// valid ticket status, keep in sync with TicketManager::isValidStatus()
$TICKETSTATUS = array(
    0 => 'TICKET_STATUS_NEW',
    1 => 'TICKET_STATUS_WAITING',
    2 => 'TICKET_STATUS_ANSWERED',
    3 => 'TICKET_STATUS_RESOLVED',    
);

$TICKETSTATUS_DESC = array(
    0 => 'Neu',
    1 => 'Wartend',
    2 => 'Beantwortet',    
    3 => 'Erledigt',
);

foreach( $GLOBALS['TICKETSTATUS'] as $id => $name )
{
    define($name, $id);
}