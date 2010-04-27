<?php

require_once '../include/config_inc.php';

class DBLogger
{
    private $db;
    
    public function __construct()
    {
        $this->db = new mysqli(MYSQL_LOGDB_HOST, MYSQL_LOGDB_USER, MYSQL_LOGDB_PASS, MYSQL_LOGDB_DB);
    }
    

    public function logUserAction($username, $msg)
    {
        $uname = $this->db->real_escape_string($username);
        $logmsg = $this->db->real_escape_string($msg);
        $username = $msg = false;
           echo "LOG()\n";
           
        $sql = "INSERT INTO 
        	userlog(username, logmsg) 
        	VALUES('$uname', '$logmsg')";
        
        $this->db->query($sql);         
        //print mysqli_error($this->db);
    }
}