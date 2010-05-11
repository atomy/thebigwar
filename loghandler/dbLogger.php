<?php

if ( is_file( '../include/config_inc.php' ) )
{
   	require_once( '../include/config_inc.php' );
}
else if ( is_file( '../../include/config_inc.php' ) )
{
   	require_once( '../../include/config_inc.php' );
}
else
{
  	require_once( 'include/config_inc.php' );
}

class DBLogger
{
    private $db;
    
    // needed for testruns, we dont want to log them
    private $dummyRun;
    
    public function __construct()
    {
    	if ( is_file('NOSQLLOG') || is_file('../NOSQLLOG') || is_file('../../NOSQLLOG') || is_file('../../../NOSQLLOG') )
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
           echo "LOG()\n";
           
        $sql = "INSERT INTO 
        	userlog(username, logmsg) 
        	VALUES('$uname', '$logmsg')";
        
        $this->db->query($sql);         
        //print mysqli_error($this->db);
    }
}