<?php

require_once '../include/config_inc.php';
require_once TBW_ROOT.'include/DBHelper.php';
require_once TBW_ROOT.'ticketsystem/DBOject.php';

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
     * message content
     * @var unknown_type
     */
    private $text;    
    
    /**
     * constructor, if id is given lookup object data on database
     * @param $id
     */
    public function __construct( $id = false )
    {
        $this->id = -1;
        $this->ticketid = -1;
        $this->username = "";
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
            $qry = "SELECT * FROM `ticketmessages` WHERE `id` = '".$id."'";
            $result = $dbLink->query($qry);
            if (!$result) 
            {
                echo "ERROR looking up TicketMessage!".$dbLink->error."\n";
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

            $this->loaded = true;
        }
    }   
    
    public function create( $ticketid = false, $username = false, $text = false )
    {
        if ( $ticketid == false || $username == false || $text == false || !is_numeric($ticketid) )
        {
            throw new Exception("__METHOD__ missing argument");
        }
        
        $this->ticketid = $ticketid;
        $this->username = $username;
        $this->text = $text;
        
        $dbhelper = DBHelper::getInstance();
        $dbLink = &$dbhelper->getLink();
        
        $username = mysqli_real_escape_string($dbLink, $username);
        $text = mysqli_real_escape_string($dbLink, $text);
        
        // add the new ticket to the database
        $query = "INSERT INTO `ticketmessages` ('ticketid', 'message', 'username') VALUES ('".$ticketid."','".$text."','".$username."')";
        if (!$dbLink->query($query))
        {
            throw new Exception("ERROR adding TicketMessage!");
        }      

        $this->loaded = true;
    }
}