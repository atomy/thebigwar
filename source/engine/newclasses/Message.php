<?php

if ( ! defined( TBW_ROOT ) && file_exists( '../include/config_inc.php' ) )
{
    require_once ( '../include/config_inc.php' );
}
else if ( ! defined( TBW_ROOT ) && file_exists( '../../include/config_inc.php' ) )
{
    require_once ( '../../include/config_inc.php' );
}

require_once( TBW_ROOT.'loghandler/logger.php' );
require_once( TBW_ROOT.'engine/newclasses/DatabaseItem.php');

/**
 * Short description of class Message
 *
 * @access public
 * @author firstname and lastname of author, <author@example.org>
 */
class Message extends IDatabaseItem
{
    /**
     * Short description of attribute m_szText
     *
     * @access private
     * @var String
     */
    private $m_szText = null;

    /**
     * Short description of attribute m_iID
     *
     * @access private
     * @var Integer
     */
    private $m_iID = null;

    /**
     * Short description of attribute m_iFromUserID
     *
     * @access private
     * @var Integer
     */
    private $m_iFromUserID = null;

    /**
     * Short description of attribute m_iToUserID
     *
     * @access private
     * @var Integer
     */
    private $m_iToUserID = null;

    /**
     * Short description of attribute m_iTime
     *
     * @access private
     * @var Integer
     */
    private $m_iTime = null;

    /**
     * Short description of attribute m_szSubject
     *
     * @access private
     * @var Integer
     */
    private $m_szSubject = null;

    /**
     * Short description of attribute m_iType
     *
     * @access private
     * @var Integer
     */
    private $m_iType = null;

    /**
     * is this a new message?
     * @var unknown_type
     */
    private $m_bNewMessage = false;
    
    /**
     * was this message read by the user?
     * @var unknown_type
     */
    private $m_bRead = false;
    
    /**
     * who owns this mesage?
     * @var unknown_type
     */
    private $m_iUserID = null;

    private $m_bArchieved = false;
    
    /**
     * Short description of method Message
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  iID = 0
     * @param  iToUserID
     * @param  szSubject
     * @param  szMessageText
     * @return mixed
     */
    public function __construct($iID, $userID = -1, $iFromUserID = -1, $iToUserID = -1, $szSubject = "", $szMessageText = "", $iType = -1, $bAchieved = false, $time = -1)
    {
    	// id of <0 means this will be a new message saved to db later
    	if ( $iID < 0 )
    	{
    		$this->m_bNewMessage = true;
    		$this->m_iTime = time();
    	}    
    	else
    	{
    		$this->m_bNewMessage = false;
    		$this->m_iTime = $time;
    	}
 
    	$this->m_szText = $szMessageText;
    	$this->m_iID = $iID;
    	$this->m_iFromUserID = $iFromUserID;
    	$this->m_iToUserID = $iToUserID;    	
    	$this->m_szSubject = $szSubject;
    	$this->m_iType = $iType;    
    	$this->m_iUserID = $userID;	
    	$this->m_bArchieved = $bAchieved;
    	//echo "construct: ".$userID."<br>";
    }
    
    /**
     * either create a new message or save a changed msg to database
     * NOTE: not all fields are allowed to be changed!
     * (non-PHPdoc)
     * @see engine/newclasses/IDatabaseItem#SaveToDatabase()
     * - unittest added -
     */
    public function SaveToDatabase()
    {
    	if ( $this->m_bNewMessage ) 
    	{
    		$dbHelper = DBHelper::getInstance();
    		$sql = "INSERT INTO `tbw_messages` ( userid, subject, text, time, toUser, fromUser, msgType, msgRead, isArchieved ) VALUES ( '".$this->m_iUserID."', '".$this->m_szSubject."', '".$this->m_szText."', '".$this->m_iTime."', '".$this->m_iToUserID."', '".$this->m_iFromUserID."', '".$this->m_iType."', '".$this->m_bRead."', '".$this->m_bArchieved."' );";
    		//echo $sql."<br>";
    		return $dbHelper->doQuery($sql);
    	}
    	else
    	{
    		$dbHelper = DBHelper::getInstance();
    		$sql = "UPDATE `tbw_messages` SET msgRead = '".$this->m_bRead."', msgType = '".$this->m_iType."', isArchieved = '".$this->m_bArchieved."' WHERE id = '".$this->m_iID."';";
    		return $dbHelper->doQuery($sql);
    	}
    }
    
    /**
    */
    public function LoadFromDatabase()
    {
   	if ( $this->m_iID > 0 && is_numeric($this->m_iID) )
   	{
            $dbHelper = DBHelper::getInstance();
            $sql = "SELECT * FROM `tbw_messages` WHERE id = '".$this->m_iID."';";   			
            $result = &$dbHelper->doQuery($sql);
            $row = $result->fetch_array(MYSQLI_ASSOC);
   		
   	    $this->m_szSubject = $row['subject'];
            //echo "SUBJECT: ".$this->m_szSubject."\n";
            $this->m_szText = $row['text'];
            $this->m_iTime = $row['time'];
            $this->m_iToUserID = $row['toUser'];
            $this->m_iFromUserID = $row['fromUser'];
            $this->m_iType = $row['msgType'];
            $this->m_bRead = $row['msgRead'];
            $this->m_iUserID = $row['userid'];
            $this->m_bArchieved = $row['isArchieved'];
        }
   	else
            throw new Exception("Message::LoadFromDatabase() cant load from db with an invalid id!");   		
    }    
    
    public function SetToUserID( $toUserID )
    {
    	$this->m_iToUserID = $toUserID;    
    }
    
    public function SetUserID( $iUserID )
    {
    	$this->m_iUserID = $iUserID;    
    }
    
    public function GetUserID()
    {
    	return $this->m_iUserID;
    }     
    
    public function SetSubject( $szSubject )
    {
    	$this->m_szSubject = $szSubject;
    }

    public function SetMessageText( $szText )
    {
    	$this->m_szText = $szText;
    }
    
    public function SetFromUserID( $fromUserId )
    {
    	$this->m_iFromUserID = $fromUserId;
    }

    public function GetType()
    {
    	return $this->m_iType;
    }
        
    public function SetType( $iType )
    {
    	$this->m_iType = $iType;
    }
    
    public function GetID()
    {
    	return $this->m_iID;
    }
    
    public function SetRead( $iRead = true )
    {
    	$this->m_bRead = $iRead;
    }
    
    public function GetFromUserID()
    {
    	return $this->m_iFromUserID;
    }
    
    public function GetToUserID()
    {
    	return $this->m_iToUserID;
    }    
    
    public function GetSubject()
    {
    	return $this->m_szSubject;
    }
    
    public function GetTime()
    {
    	return $this->m_iTime;
    }
    
    public function SetTime( $iTime )
    {
    	$this->m_iTime = $iTime;
    }
    
    public function GetText()
    {
    	return $this->m_szText;
    }
    
    public function GetIsArchieved()
    {
    	return $this->m_bArchieved;
    }
    
    public function GetIsRead()
    {
    	return $this->m_bRead;
    }
    
    public function SetArchieved( $bInput = true )
    {
    	$this->m_bArchieved = $bInput;
    }
}