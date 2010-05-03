<?php

require_once '../include/config_inc.php';
require_once TBW_ROOT.'include/DBHelper.php';
require_once TBW_ROOT.'ticketsystem/TicketMessage.php';
require_once TBW_ROOT.'ticketsystem/DBObject.php';
require_once TBW_ROOT.'ticketsystem/TicketConstants.php';


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
     * subject of the ticket
     * @var unknown_type
     */
    private $subject;
    
    /**
     * time created of the ticket
     * @var unknown_type
     */
    private $time_created;
    
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
        $this->subject = "";
        $this->status = -1;
        $this->setLoaded(false);    

        if ( $id >= 0 && $id !== false )
        {
            if (!is_numeric($id))
            {
                throw new Exception(__METHOD__." given $id is not a number");
            }
            $this->id = $id;
            
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
                $this->reporter = $row['reporter'];
            else
                throw new Exception("ERROR reporter not set");
                
            if (isset($row['status']))
                $this->status = $row['status'];
            else
                throw new Exception("ERROR status not set");   
                
            if (isset($row['subject']))
                $this->subject = base64_decode($row['subject']);
            else
                throw new Exception("ERROR subject not set");

            if (isset($row['time_created']))
                $this->time_created = $row['time_created'];
            else
                throw new Exception("ERROR time_created not set");                
                

            // load messages from db
            $qry = "SELECT id FROM `ticketmessages` WHERE `ticketid` = '".$id."'";
            $result = $dbLink->query($qry);
            if (!$result) 
            {
                echo "ERROR looking up TicketMessages!".$dbLink->error."\n";
            }
            
            for($row = $result->fetch_array(MYSQLI_ASSOC); $row; $row = $result->fetch_array(MYSQLI_ASSOC))
            {
                $this->messages[] = $row['id'];
            }
            $result->close();                

            $this->setLoaded(true);
            echo "dbg: finished loading ticket: ".$this->id."\n";
        }
    }
    
    public function create( $reporter = false, $text = false, $subject = false )
    {
        if ( $reporter == false || $text == false || $subject == false )
        {
            die(__METHOD__." missing argument");
        }    
            
        // we dont have the id yet, its auto-generated, catch it when execing the db query
        $this->id = -1;
        $this->reporter = $reporter;
        $this->text = $text;
        $this->subject = $subject;
        
        $dbhelper = DBHelper::getInstance();
        $dbLink = &$dbhelper->getLink();
        
        $reporter = mysqli_real_escape_string($dbLink, $reporter);
        $text = base64_encode($text);
        $subject = base64_encode($subject);
                
        $qry = "INSERT INTO `tickets` (reporter, status, subject) VALUES ('".$reporter."', ".TICKET_STATUS_NEW.", '".$subject."')";
        echo "execing: ".$qry."\n";
        $result = $dbLink->query($qry);
        
        // add the new ticket to the database
        if ($result === false) 
        {
            throw new Exception( __METHOD__." ERROR adding Ticket!");
        }       
        
        $this->id = $dbLink->insert_id; 
        $this->time_created = time(); 
        $this->loaded = true;        
        $this->addMessage( $reporter, $text );   

        return $this->id;
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
            die(__METHOD__." missing argument");
        }   
        
        $dbhelper = DBHelper::getInstance();
        $dbLink = &$dbhelper->getLink();
        
        $dbMessage = mysqli_real_escape_string($dbLink, $message);
        $message = false;

        $tMsg = new TicketMessage();
        $tMsg->create( $this->id, $username, $dbMessage );
        $this->messages[] = $tMsg->getId();        
    }
    
	/**
     * @return the $reporter
     */
    public function getReporter( )
    {
        return $this->reporter;
    }

	/**
     * @return the $messages
     */
    public function getMessages( )
    {
        return $this->messages;
    }

	/**
     * @return the $status
     */
    public function getStatus( )
    {
        return $this->status;
    }

	/**
     * @return the $subject
     */
    public function getSubject( )
    {
        return $this->subject;
    }
    
    public function getTimeCreated()
    {
        return $this->time_created;
    }
    
    /**
     * get the first message of a ticket
     */
    public function getFirstMessageObj()
    {
        $tMsg = new TicketMessage($this->messages[0]);
        
        if($tMsg->isValid())
        {
            throw new Exception(__METHOD__." unable to get ticket");
        }
        
        return $tMsg;
    }    
}