<?php

// valid ticket status, keep in sync with TicketManager::isValidStatus()
define('TICKET_STATUS_NEW', 1);
define('TICKET_STATUS_RESOLVED', 2);
define('TICKET_STATUS_CLOSED', 3);
define('TICKET_STATUS_ANSWERED', 4);
define('TICKET_STATUS_WAITING', 5);
define('MAX_MESSAGE_LEN', 1600); 
define('MAX_SUBJECT_LEN', 64); 
define('MAX_MESSAGE_LINES', 20); 
define('MAX_MESSAGE_TEXTTILLWRAP', 70);
