<?php

require_once '../include/config_inc.php';
require_once TBW_ROOT.'include/DBHelper.php';
require_once TBW_ROOT.'ticketsystem/TicketMessage.php';
require_once TBW_ROOT.'ticketsystem/DBOject.php';


// valid ticket status, keep in sync with TicketManager::isValidStatus()
define('TICKET_STATUS_NEW', 1);
define('TICKET_STATUS_RESOLVED', 2);
define('TICKET_STATUS_CLOSED', 3);

class Ticket extends DBObject
{   
    /**
     * username of the reporter
     * @var string
     */
    private $reporter;
    
    /**
     * holds all messages belonging to the ticket
     * @var array(TicketMessage)
     */
    private $messages;
    
    /**
     * status of the ticket
     * @var int
     */
    private $status;  
    
    /**
     * constructor, creates ticket obj with the given parameters
     * @param $reporter
     * @param $text
     */
    public function __construct( $id = false )    
    {
        $this->id = -1;
        $this->reporter = "";
        $this->messages = "";
        $this->status = -1;
        $this->loaded = false;     

        if ( $id >= 0 )
        {
            if (!is_numeric($id))
            {
                throw new Exception("__METHOD__ given $id is not a number");
            }
            
            $dbhelper = DBHelper::getInstance();
            $dbLink = &$dbhelper->getLink();
        
            // load ticket from db
            $qry = "SELECT * FROM `tickets` WHERE `id` = '".$id."'";
            $result = $dbLink->query($qry);
            if (!$result) 
            {
                echo "ERROR looking up Ticket!".$dbLink->error."\n";
            }
            
            $row = $result->fetch_array(MYSQLI_ASSOC);
            $result->close();
            
            if (isset($row['reporter']))
                $this->ticketid = $row['reporter'];
            else
                throw new Exception("ERROR reporter not set");
                
            if (isset($row['status']))
                $this->username = $row['status'];
            else
                throw new Exception("ERROR status not set");   

            $this->loaded = true;
        }
    }
    
    public function create( $reporter = false, $text = false )
    {
        if ( $reporter == false || $text == false )
        {
            die("__METHOD__ missing argument");
        }    
            
        // we dont have the id yet, its auto-generated, catch it when execing the db query
        $this->id = -1;
        $this->reporter = $reporter;
        $this->text = $text;
        
        $dbhelper = DBHelper::getInstance();
        $dbLink = &$dbhelper->getLink();
        
        $reporter = mysqli_real_escape_string($dbLink, $reporter);
        $text = mysqli_real_escape_string($dbLink, $text);
        
        $result = $dbLink->query("INSERT INTO `tickets` ('reporter', 'status') VALUES ('".$reporter."', TICKET_STATUS_NEW)");
        
        // add the new ticket to the database
        if ($result === false) 
        {
            echo "ERROR adding Ticket!\n";
        }       
        
        $this->id = $dbLink->insert_id;  
        $this->loaded = true;        
        $this->addMessage( $reporter, $text );    
    }
    
    /**
     * links a new message object to the ticket
     * @param unknown_type $username
     * @param unknown_type $message
     */
    public function addMessage( $username = false, $message = false )
    {
        if ( $username == false || $message == false || $this->id < 0 )
        {
            die("__METHOD__ missing argument");
        }   
        
        $dbhelper = DBHelper::getInstance();
        $dbLink = &$dbhelper->getLink();
        
        $reporter = mysqli_real_escape_string($dbLink, $reporter);

        new TicketMessage( $this->id, $username, $message );        
    }
}