<?php
if(!isset($_SERVER['DOCUMENT_ROOT']) || strlen($_SERVER['DOCUMENT_ROOT']) <= 0)
    $_SERVER['DOCUMENT_ROOT'] = getcwd()."/..";
    
require_once($_SERVER['DOCUMENT_ROOT'].'/include/config_inc.php');

class DBHelper
{
    // Singleton stuff begin    
    static private $instance = null;
 
    static public function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self;
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
    public function getLink()
    {
        return $this->db;
    }
}