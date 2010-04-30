<?php

require_once '../include/config_inc.php';
require_once TBW_ROOT.'include/DBHelper.php';
require_once TBW_ROOT.'ticketsystem/Ticket.php';

class TicketManager
{
    // Singleton stuff begin    
    static private $instance = null;
 
    static public function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }
 
    private function __construct(){}
    private function __clone(){}    
    // Singleton stuff end

    /**
     * adds a new ticket to the database
     * @param $reporter
     * @param $text
     */
    public function newTicket( $reporter = false, $text = false )
    {
        if ( $reporter == false || $text == false )
        {
            throw new Exception("__METHOD__ missing argument");
        }

        new Ticket( $reporter, $text );
    }
    
    /**
     * sets the given status to a ticket
     * @param $ticketID
     */
    public function setStatus( $ticketID = false, $status = false )
    {
        if ( $ticketID == false || $status == false || !$this->isValidStatus( $status ) )
        {
            throw new Exception("__METHOD__ missing argument");
        }        
        $ticketObj = &$this->getTicketByID( $ticketID );
        $ticketObj->setStatus( $status );
    }
    
    /**
     * adds the given message to a ticket
     * @param $userName
     * @param $text
     */
    public function addMessage( $ticketID = false, $userName = false, $text = false )
    {
        if ( $ticketID == false || $userName == false || $text == false )
        {
            throw new Exception("__METHOD__ missing argument");
        }  
        
        new TicketMessage( $ticketID, $userName, $text );
    }
    
    /**
     * get the given number of tickets from database by status
     * @param $status
     * @param $num
     * @return array() ticket-ids
     */
    public function getNumTicketsByStatus( $status = false, $num = false )
    {
        if ( $status == false || $num == false )
        {
            throw new Exception("__METHOD__ missing argument");
        }  
                
        if (!$this->isValidStatus($status))
        {
            throw new Exception("__METHOD__ invalid status given");   
        }
        
        if ($num > 100)
        {
            throw new Exception("__METHOD__ too much tickets requested");   
        }
        
        $dbhelper = DBHelper::getInstance();
        $dbLink = &$dbhelper->getLink();
        
        // load ticketids from db
        $qry = "SELECT * FROM `tickets` WHERE `status` = '".$status."'";
        $result = $dbLink->query($qry);

        if (!$result) 
        {
            echo "ERROR looking up tickets!".$dbLink->error."\n";
        }
            
        $ticketIDs = array();
        for( $row = $result->fetch_array(MYSQLI_ASSOC); $row; $row = $result->fetch_array(MYSQLI_ASSOC))
        {
            if (!isset($row['id']))
            {
                throw new Exception("__METHOD__ ticket w/o any id");   
            } 
            $ticketIDs[] = $row['id'];
        }
        $result->close();
        
        return $ticketIDs;
    }
    
    /**
     * returns ticket object for the given ticket-id
     * @param unknown_type $id
     */
    public function getTicketByID( $id )
    {
        return new Ticket( $id );
    }
    
    /**
     * checks if the given status is a valid one
     * @param $status
     */
    public function isValidStatus( $status )
    {
        switch($status)
        {
            case TICKET_STATUS_NEW:
                return true;              
                
            case TICKET_STATUS_RESOLVED:
                return true;
             
            case TICKET_STATUS_CLOSED:
                return true;
                
            default:
                return false;           
        }
    }
}

// Singleton instanziieren
//$ticketManager = TicketManager::getInstance();

 ?>