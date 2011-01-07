<?php

require_once( $_SERVER['DOCUMENT_ROOT'].'/include/config_inc.php' );

class DBLogger
{
    private $db;
    
    // needed for testruns, we dont want to log them
    private $dummyRun;
    
    public function __construct()
    {
    	if ( is_file($_SERVER['DOCUMENT_ROOT'].'/NOSQLLOG') )
	{
	    $this->dummyRun = true;
	}
	else
	{
	    $this->db = new mysqli(MYSQL_LOGDB_HOST, MYSQL_LOGDB_USER, MYSQL_LOGDB_PASS, MYSQL_LOGDB_DB);
	}
    }
    

    public function logUserAction($username, $msg)
    {
    	if($this->dummyRun)
	    return;

        $uname = $this->db->real_escape_string($username);
        $logmsg = $this->db->real_escape_string($msg);
        $username = $msg = false;
           
        $sql = "INSERT INTO 
        	userlog(username, logmsg) 
        	VALUES('$uname', '$logmsg')";
        
        $this->db->query($sql);         
        //print mysqli_error($this->db);
    }
}
