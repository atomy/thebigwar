<?php

require_once '../include/config_inc.php';
require_once TBW_ROOT.'include/DBHelper.php';
require_once TBW_ROOT.'ticketsystem/DBObject.php';
require_once TBW_ROOT.'ticketsystem/TicketConstants.php';

/**
 * creates a object to seperate each message on a ticket
 * @author atomy 
 */
class TicketMessage extends DBObject
{   
    /**
     * linked to the given ticketid
     * @var unknown_type
     */
    private $ticketid;
    
    /**
     * the user which has written it
     * @var unknown_type
     */
    private $username;
    
    /**
     * time that message was created
     * @var unknown_type
     */
    private $time_created;
    
    /**
     * message content
     * @var unknown_type
     */
    private $text;

    /**
     * is this written by a gameoperator?
     * @var unknown_type
     */
    private $gameoperator;
    
    /**
     * constructor, if id is given lookup object data on database
     * @param $id
     */
    public function __construct( $id = false )
    {
        $this->setId(-1);
        $this->ticketid = -1;
        $this->username = "";
        $this->setLoaded(false);
        $this->gameoperator;
        
        if ( $id >= 0 && $id !== false )
        {
            if (!is_numeric($id))
            {
                throw new Exception("__METHOD__ given $id is not a number");
            }
            $this->setId($id);
            
            $dbhelper = DBHelper::getInstance();
            $dbLink = &$dbhelper->getLink();
        
            // load ticket from db
            $qry = "SELECT * FROM `ticketmessages` WHERE `id` = '".$id."'";
            //echo "execing qry: ".$qry."\n";
            $result = $dbLink->query($qry);
            if (!$result) 
            {
                throw new Exception(__METHOD__." ERROR looking up TicketMessage!".$dbLink->error);
            }
            
            $row = $result->fetch_array(MYSQLI_ASSOC);
            $result->close();
            
            if (isset($row['ticketid']))
                $this->ticketid = $row['ticketid'];
            else
                throw new Exception("ERROR ticketid not set");
                
            if (isset($row['username']))
                $this->username = $row['username'];
            else
                throw new Exception("ERROR username not set");   

            if (isset($row['message']))
                $this->text = $row['message'];
            else
                throw new Exception("ERROR message not set");  
                                
            if (isset($row['gameoperator']))
                $this->gameoperator = $row['gameoperator'];
            else
                throw new Exception("ERROR gameoperator not set");                    
 
            if (isset($row['time_created']))
                $this->time_created = $row['time_created'];
            else
                throw new Exception("ERROR time_created not set");                
                
            $this->setLoaded(true);
        }
    }   
    
    /**
     * creates a new ticket with the given parameters and saves it to database
     * @param $ticketid
     * @param $username
     * @param $text
     */
    public function create( $ticketid = false, $username = false, $text = false, $gameoperator = false )
    {
        if ( $ticketid == false || $username == false || $text == false || !is_numeric($ticketid) )
        {
            throw new Exception(__METHOD__." missing argument");
        }
        
        $this->ticketid = $ticketid;
        $this->username = $username;
        $this->text = $text;
        
        $dbhelper = DBHelper::getInstance();
        $dbLink = &$dbhelper->getLink();
        
        $dbUsername = mysqli_real_escape_string($dbLink, $username);
        $dbText = mysqli_real_escape_string($dbLink, $text);
        unset($text);
        unset($username);
               
        // add the new ticket to the database
        $query = "INSERT INTO `ticketmessages` (ticketid, message, username, gameoperator) VALUES ('".$ticketid."','".$dbText."','".$dbUsername."','".$gameoperator."')";
        if (!$dbLink->query($query))
        {
            throw new Exception("ERROR adding TicketMessage!");
        }      

        $this->time_created = time();
        $this->setId($dbLink->insert_id); 
        $this->loaded = true;
    }
        
	/**
     * @return the $username
     */
    public function getUsername( )
    {
        return $this->username;
    }

	/**
     * @return the $time_created
     */
    public function getTimeCreated( )
    {
        return $this->time_created;
    }

	/**
     * @return the $text
     */
    public function getText( )
    {
        return $this->text;
    }
    
    public function isGameoperator()
    {
        return $this->gameoperator;
    }
    
    /**
     * renames the reporter of the ticket
     */
    public function renameUser($newname)
    {        
        $dbhelper = DBHelper::getInstance();
        $dbLink = &$dbhelper->getLink();             
        $qry = "UPDATE `ticketmessages` SET username = '".$newname."' WHERE id = '".$this->getId()."'";

        if ( $dbLink->query($qry) === false )        
            throw new Exception(__METHOD__." unable execute sql query");  
                       
        return true;
    }    
}