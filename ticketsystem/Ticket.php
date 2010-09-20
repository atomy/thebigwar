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
    public function addMessage( $username = false, $message = false, $gameoperator = false )
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
        $tMsg->create( $this->getId(), $username, $message, $gameoperator );
        $this->messages[] = $tMsg->getId();  
        
        if ( $gameoperator == true )
        {
            $this->setStatus(TICKET_STATUS_ANSWERED);
        }
        else if ( $this->getStatus() == TICKET_STATUS_ANSWERED || $this->getStatus() == TICKET_STATUS_RESOLVED )
        {
            $this->setStatus(TICKET_STATUS_WAITING);
        }

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
     * sets the $status
     */
    public function setStatus( $status )
    {        
        $dbhelper = DBHelper::getInstance();
        $dbLink = &$dbhelper->getLink();  

        // vom GO beantwortet, benachrichtige den User (reporter)
        if ( $status == TICKET_STATUS_ANSWERED )
        {
            $this->notifyReporter();
        }
        
        // status change
        $this->status = $status;        
        $qry = "UPDATE `tickets` SET status = '".$this->status."' WHERE id = '".$this->getId()."'";

        if ( $dbLink->query($qry) === false )
        {
            throw new Exception(__METHOD__." unable execute sql query");
        }
        
        return true;
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
        return $GLOBALS['TICKETSTATUS_DESC'][$this->status];
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
    
    /**
	 * sends a note to the reporter of the ticket that his ticket has been updated
     */
    public function notifyReporter()
    {
        $message = new Message();
        if (!$message->create())
        {
            return false;
        }        
            
        $message->from( "TicketSystem" );     
        $message->to( $this->getReporter() );             
        $message->subject( "Antwort auf Ticket #".$this->getId() );
                
        $subJ = $this->getSubject();
        $tID = $this->getId();
        
        $msgText = "Deinem Ticket mit dem Betreff \"".$subJ."\" wurde eine neue Nachricht hinzugefügt.<br/><br/>";
        $msgText .= "<a href=\"ticketsystem.php?ticketid=".$tID."\">Klicke hier um zum Ticket zu gelangen</a>";        
                
        $message->text( $msgText );        
        $message->html( true );
        
        $message->addUser( $this->getReporter(), 6 );                

        unset( $message );       
    }
    
    /**
     * renames the reporter of the ticket
     */
    public function renameReporter($newname)
    {        
        $dbhelper = DBHelper::getInstance();
        $dbLink = &$dbhelper->getLink();             
        $qry = "UPDATE `tickets` SET reporter = '".$newname."' WHERE id = '".$this->getId()."'";

        if ( $dbLink->query($qry) === false )        
            throw new Exception(__METHOD__." unable execute sql query");  
                       
        return true;
    }
}