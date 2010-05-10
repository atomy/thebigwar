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
        $this->setId(-1);
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
            $this->setId($id);
            
            $dbhelper = DBHelper::getInstance();
            $dbLink = &$dbhelper->getLink();
        
            // load ticket from db
            $qry = "SELECT * FROM `tickets` WHERE `id` = '".$id."'";
            $result = $dbLink->query($qry);
            if (!$result) 
            {
                throw new Exception(__METHOD__." ERROR looking up Ticket!".$dbLink->error);
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
                $this->subject = $row['subject'];
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
                throw new Exception(__METHOD__." ERROR looking up TicketMessages!");
            }
            
            for($row = $result->fetch_array(MYSQLI_ASSOC); $row; $row = $result->fetch_array(MYSQLI_ASSOC))
            {
                $this->messages[] = $row['id'];
            }
            $result->close();                

            $this->setLoaded(true);
            //echo "dbg: finished loading ticket: ".$this->getId()."\n";
        }
    }
    
    public function create( $reporter = false, $text = false, $subject = false )
    {
        if ( $reporter == false || $text == false || $subject == false )
        {
            throw new Exception(__METHOD__." missing argument");
        }    
            
        // we dont have the id yet, its auto-generated, catch it when execing the db query
        $this->setId(-1);
        $this->reporter = $reporter;
        $this->text = $text;
        $this->subject = $subject;
        
        $dbhelper = DBHelper::getInstance();
        $dbLink = &$dbhelper->getLink();
        
        $dbReporter = mysqli_real_escape_string($dbLink, $reporter);
        $dbSubject = mysqli_real_escape_string($dbLink, $subject);
        unset($subject);
                
        $qry = "INSERT INTO `tickets` (reporter, status, subject) VALUES ('".$dbReporter."', ".TICKET_STATUS_NEW.", '".$dbSubject."')";
        $result = $dbLink->query($qry);
        
        // add the new ticket to the database
        if ($result === false) 
        {
            throw new Exception( __METHOD__." ERROR adding Ticket!");
        }       
        
        $this->setId($dbLink->insert_id); 
        $this->time_created = time(); 
        $this->loaded = true;        
        $this->addMessage($reporter, $text);   

        return $this->getId();
    }
    
    /**
     * links a new message object to the ticket
     * @param unknown_type $username
     * @param unknown_type $message
     */
    public function addMessage( $username = false, $message = false )
    {
        if ($username == false || $message == false)
        {
            throw new Exception( __METHOD__. " missing argument");
        }
        else if ($this->getId() < 0)
        {
            throw new Exception( __METHOD__. " invalid ticketid: ".$this->getId());
        }   
        
        $dbhelper = DBHelper::getInstance();
        $dbLink = &$dbhelper->getLink();      

        $tMsg = new TicketMessage();
        $tMsg->create( $this->getId(), $username, $message );
        $this->messages[] = $tMsg->getId();  

        return true;
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
    
    /**
     * returns the current tickets status in a readable string
     */
    public function getStatusString()
    {
        switch ($this->status)
        {
            case TICKET_STATUS_NEW:
                return "Neu";
            break;
            
            case TICKET_STATUS_RESOLVED:
                return "Erledigt";
            break;

            case TICKET_STATUS_CLOSED:
                return "Geschlossen";
            break;

            case TICKET_STATUS_ANSWERED:
                return "Beantwortet";
            break;
            
            case TICKET_STATUS_WAITING:
                return "Wartend";
            break;                        

            default:
                return "";
        }
    }
    
    /**
     * returns last activity of the given ticket
     */
    public function getLastActivity()
    {
        $lastId = end($this->messages);

        $lastMsg = new TicketMessage($lastId);
        $lastActive = $lastMsg->getTimeCreated();
                        
        return $lastActive; 
    }
}