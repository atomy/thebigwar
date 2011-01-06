<?php

class DBHelper
{
    // Singleton stuff begin    
    static private $instance = null;
 
    static public function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
 
    private function __clone(){}    
    // Singleton stuff end    
    
    /**
     * database link
     * @var unknown_type
     */
    private $db;
    
    private function __construct()
    {
        $this->db = new mysqli(MYSQL_LOGDB_HOST, MYSQL_LOGDB_USER, MYSQL_LOGDB_PASS, MYSQL_LOGDB_DB);
        
        if ( mysqli_connect_errno() )
        {
            die("Database connection failed: ".mysqli_connect_errno());
        }
    }    
    
    public function __destruct()
    {
        $this->db->close();
    }

    /**
     * returns db link
     */
    public function GetLink()
    {
        return $this->db;
    }
    
    public function DoQuery( $szSQL )
    {
    	$dbLink = $this->db;
    	
    	if (!$dbLink)
	    throw new Exception("DBHelper::doQuery database connection not available!");

    	$result = $dbLink->query($szSQL);
	//echo "exec query: ".$szSQL."\n";
    	
    	if ( !$result )
    	    throw new Exception("DBHelper::doQuery query failed!: ".$this->db->error);
    		
    	return $result;
    }
    
    public function GetAffectedRows()
    {
    	return $this->db->affected_rows;
    }
    
    public function EscapeString( $string )
    {
    	return $this->db->escape_string( $string );    	
    }
    
    public function GetInsertID()
    {
    	return $this->db->insert_id;
    }
}