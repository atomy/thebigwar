<?php

require_once '../include/config_inc.php';
require_once TBW_ROOT.'include/DBHelper.php';
require_once TBW_ROOT.'ticketsystem/Ticket.php';
require_once TBW_ROOT.'ticketsystem/TicketConstants.php';


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
            throw new Exception(__METHOD__." missing argument");
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
            throw new Exception(__METHOD__." missing argument");
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
            throw new Exception(__METHOD__." missing argument");
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
        if ( $status === false || $num == false )
        {
            throw new Exception(__METHOD__." missing argument");
        }  
                
        if (!$this->isValidStatus($status))
        {
            throw new Exception(__METHOD__." invalid status given");   
        }
        
        if ($num > 100)
        {
            throw new Exception(__METHOD__." too much tickets requested");   
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
                throw new Exception(__METHOD__." ticket w/o any id");   
            } 
            $ticketIDs[] = $row['id'];
        }
        $result->close();
        
        return $ticketIDs;
    }
    
    /**
     * get the given number of tickets from database where reporter is username
     * @param $status
     * @param $num
     * @return array() ticket-ids
     */
    function getNumMyTickets( $username = false, $num = false)
    {
        if ( $username == false || $num == false )
        {
            throw new Exception(__METHOD__." missing argument");
        }  
        
        if ($num > 100)
        {
            throw new Exception(__METHOD__." too much tickets requested");   
        }
        
        $dbhelper = DBHelper::getInstance();
        $dbLink = &$dbhelper->getLink();
        $username = mysqli_real_escape_string($dbLink, $username);
        
        // load ticketids from db
        $qry = "SELECT tickets.id, MAX( ticketmessages.time_created ) AS last_active FROM `tickets` LEFT JOIN `ticketmessages` ON tickets.id = ticketmessages.ticketid WHERE tickets.reporter = '".$username."' GROUP BY tickets.id ORDER BY last_active DESC";

	//echo "qry: ".$qry."\n";
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
                throw new Exception(__METHOD__." ticket w/o any id");   
            } 
            $ticketIDs[] = $row['id'];
        }
        $result->close();
        
        return $ticketIDs;
    }
    
    /**
     * get the given number of tickets from database where reporter is username and status is status
     * @param $status
     * @param $num
     * @return array() ticket-ids
     */
    function getNumMyTicketsByStatus( $username = false, $num = false, $status = false)
    {
        if ( $username == false || $num == false || $status == false )
        {
            throw new Exception(__METHOD__." missing argument");
        }  
        
        if ($num > 100)
        {
            throw new Exception(__METHOD__." too much tickets requested");   
        }
        
        $dbhelper = DBHelper::getInstance();
        $dbLink = &$dbhelper->getLink();
        $username = mysqli_real_escape_string($dbLink, $username);
        
        // load ticketids from db
        $qry = "SELECT * FROM `tickets` WHERE `reporter` = '".$username."' AND `status` = '".$status."'";
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
                throw new Exception(__METHOD__." ticket w/o any id");   
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
        if ( isset($GLOBALS['TICKETSTATUS'][$status]) )
        {
            return true;
        }
        return false;
    }
    
    public function getTicketNumByStatus( $status = false )
    {
        if ( $status === false )
        {
            throw new Exception(__METHOD__." missing argument");
        }
        
        $dbhelper = DBHelper::getInstance();
        $dbLink = &$dbhelper->getLink();
        
        // load ticketids from db
        $qry = "SELECT * FROM `tickets` WHERE `status` = '".$status."'";        
        $result = $dbLink->query($qry);
        
        return mysqli_num_rows($result);    
    }
    
    public function getTicketNumByStatusForUser( $status = false, $username = false )
    {
        if ( $status === false || $username === false )
        {
            throw new Exception(__METHOD__." missing argument");
        }
        
        $dbhelper = DBHelper::getInstance();
        $dbLink = &$dbhelper->getLink();
        
        // load ticketids from db
        $qry = "SELECT * FROM `tickets` WHERE `status` = '".$status."' AND `reporter` = '".$username."'";        
        $result = $dbLink->query($qry);
        
        return mysqli_num_rows($result);    
    }    
}
