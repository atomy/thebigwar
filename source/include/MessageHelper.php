<?php
/**
 *
 * $Id$
 *
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author firstname and lastname of author, <author@example.org>
 */

require_once(TBW_ROOT.'include/DBHelper.php');
require_once(TBW_ROOT.'include/UserHelper.php');
require_once(TBW_ROOT.'engine/newclasses/Message.php');


/**
 * Short description of class MessageHelper
 *
 * @access public
 * @author firstname and lastname of author, <author@example.org>
 */
class MessageHelper
{
    /**
     * Short description of method SendNewMessage
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  array iToUserIDs
     * @param  String szSubject
     * @param  String szMessageText
     * @param  pDatabase
     * @param  Integer iFromUserID
     * @return Boolean
     */
	
	/**
	 * convert given usernames to ids
	 * @param $szUserNames - array, input of usernames - UNSAFE
	 * @return array() userids
	 */
	public static function ConvertStringUsersToArrayIDs( &$szUserNames )
	{
		$dbHelper = DBHelper::getInstance();
		$toUserIDs = array();
		
		foreach($szUserNames as $uName)
		{
			$toUserIDs[] = UserHelper::getUserIdOfUsername($uName);
		}
		
		return $toUserIDs;
	}
	
	/**
	 * sanitize messages to prevent sql injections and the like
	 * @param $szMessage - UNSAFE text message
	 * @return unknown_type
	 */
	public static function SanitizeTextMessage( &$szMessage )
	{
	    $dbHelper = DBHelper::getInstance();
		
	    $pszMessage = mb_convert_encoding( $szMessage, 'UTF-8', 'UTF-8' );
	    $pszMessage = strip_tags( $pszMessage );	    	
            $pszMessage = nl2br( $pszMessage );
        
            return $dbHelper->EscapeString($pszMessage);
	}
	
	/*
	 *

	 */
	/**
	 * send a message with the given parameters, alsmost all (but not all) checks are done in that func
	 * security checks:
	 * - target userid exists? \o/
	 * - from userid is authorized to send from this userid? (session cookie) - do not check here, this needs to be done by the calling func \o/
	 * - subject doesnt exceed any char limits \o/
	 * - subject is sanitized against mysql injections etc. \o/
	 * - text is sanitized against mysql injections etc. \o/
	 * - text doesnt exceed any char limits \o/
	 * @param $szUser
	 * @param $szToUsers
	 * @param $szSubject
	 * @param $szMessageText
	 * @param $szFromUser
	 * @param $iType
	 * @return unknown_type
	 */
    public static function SendNewMessage( &$userObj, $szUser, $szToUser, $szSubject, $szMessageText, $szFromUser, $iType)
    {
    	$error = '';

    	if ( strlen($szToUser) <= 0 || strlen($szUser) <= 0 || strlen($szFromUser) <= 0 )
    		throw new Exception( "MessageHelper::SendNewMessage() failed, wrong parameters given" );

    	if ( strlen($szUser) > USERNAME_MAXLEN || strlen($szToUser) > USERNAME_MAXLEN || strlen($szFromUser) > USERNAME_MAXLEN )
    		throw new Exception( "MessageHelper::SendNewMessage() failed, malformed parameters given (1)" );
    		
    	if ( !is_numeric($iType) )
    		throw new Exception( "MessageHelper::SendNewMessage() failed, malformed parameters given (2)" );
    		    		
		if($userObj->userLocked())
			$error = 'Gesperrter Account kann keine Nachrichten verfassen.';  			
		else if( strtolower( strtolower( $userObj->getName() ) == GLOBAL_DEMOACCNAME ) )
			$error = 'Demo-Account kann keine Nachrichten verfassen.';                       
		else if( strtolower( $_POST['empfaenger'] ) == GLOBAL_DEMOACCNAME )
			$error = 'Demo-Account kann keine Nachrichten empfangen.';
		else if ( strlen($szSubject) > INGAMEMESSAGES_MAX_SUBJECT_LENGTH )
    		$error = 'Der Betreff ist zu lang.';
    	else if ( strlen($szMessageText) <= 0 )
    		$error = 'Sie müssen eine Nachricht eingeben.';
    	else if ( strlen($szMessageText) > INGAMEMESSAGES_MAX_TEXT_LENGTH )
    		$error = 'Nachricht zu lang.';
    	else if ( !User::userExists($szUser) )
    		$error = 'Der Empfänger, den Sie eingegeben haben, existiert nicht.';
    		
    	if ( $error )
    		return $error;
    		
    	$dbHelper = DBHelper::getInstance();    	
    	$userID = UserHelper::getUserIdOfUsername($szUser);
    	
    	// subject handling
		$dbSave_szSubject = &MessageHelper::sanitizeTextMessage($szSubject);
		unset($szSubject);
    	
        // username/userid
    	$pszFromUser = $dbHelper->EscapeString($szFromUser);
    	unset($szFromUser);
    	$dbSave_szFromUser = strip_tags( $pszFromUser );    	
    	$iFromUserID = UserHelper::getUserIdOfUsername($dbSave_szFromUser);
    	
    	// text handling
    	$dbSave_szMessageText = &MessageHelper::sanitizeTextMessage($szMessageText);
    	unset($szMessageText);
    	    	
    	$iToUserID = UserHelper::getUserIdOfUsername($szToUser);
    	unset($szToUser);

    	$pMessage = new Message(-1, $userID); // -1 as id for new message
   		$pMessage->SetToUserID($iToUserID);
   		$pMessage->SetSubject($dbSave_szSubject);
   		$pMessage->SetMessageText($dbSave_szMessageText);
   		$pMessage->SetFromUserID($iFromUserID);
   		$pMessage->SetType($iType);
   	
   		if ( !$pMessage->SaveToDatabase() )
   			$error = "Fehler beim Senden der Nachricht.";

        return $error;
    }

    /**
     * Short description of method DeleteMessages
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  array iMessageIDs
     * @param  pDatabase
     * @param  Integer iUserID
     * @return Boolean
     */
    public static function DeleteMessages($iMessageIDs, $pDatabase,  Integer $iUserID)
    {
        $returnValue = null;

        return $returnValue;
    }

    /**
     * Short description of method GetUsersMessagesByType
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  Integer iUserID
     * @param  Integer iType
     * @param  pDatabase
     * @return Message
     */
    public static function GetUsersMessagesByType( Integer $iUserID,  Integer $iType, $pDatabase)
    {
        $returnValue = null;

        return $returnValue;
    }

    /**
     * Short description of method ArchiveMessage
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  Integer iUserID
     * @param  pDatabase
     * @param  array iMessageIDs
     * @return Boolean
     */
    public static function ArchiveMessage( Integer $iUserID, $pDatabase, $iMessageIDs)
    {
        $returnValue = null;

        return $returnValue;
    }

    /**
     * Short description of method ReadMessage
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  int iMessageID
     * @param  Integer iUserID
     * @param  pDatabase
     * @return Message
     */
    public static function ReadMessage($szUser, $iMessageID, $bMarkAsRead = true)
    {   		   		
    	if ( !is_numeric($iMessageID) || strlen($szUser) > USERNAME_MAXLEN )
    		throw new Exception( "MessageHelper::ReadMessage() failed, malformed parameters given" );

    	$dbHelper = DBHelper::getInstance();
    	$iUserID = UserHelper::GetUserIdOfUsername($szUser);
    	
    	$dbSave_iMessageID = intval($iMessageID); // is_num checked!
    	$dbSave_iUserID = intval($iUserID); // save generated
    	unset($iUserID);
    	unset($iMessageID);
    	
    	$sql = "SELECT * FROM `tbw_messages` WHERE id = '".$dbSave_iMessageID."' AND userid = '".$dbSave_iUserID."';";
    	$result = &$dbHelper->DoQuery($sql);
    	$output = $result->fetch_assoc();
    	$newMsgObj = &self::ConvertDBResultToMessageObject($output);
    	$result->free();
    	
    	if ( $bMarkAsRead && !$newMsgObj->GetIsRead() )    	
    		self::MarkMessageAsRead( $szUser, $dbSave_iMessageID );  	
    	
    	return $newMsgObj;
    }	
    
    public static function ConvertDBResultToMessageObject( $result )
    {
    	$iMsgID = $result['id'];
    	$iOwnerID = $result['userid'];
    	
		$newMsg = new Message($iMsgID, $iOwnerID);
		$newMsg->SetUserID($result['userid']);
		$newMsg->SetFromUserID($result['fromUser']);
		$newMsg->SetSubject($result['subject']);			
		$newMsg->SetMessageText($result['text']);
		$newMsg->SetTime($result['time']);
		$newMsg->SetToUserID($result['toUser']);
		$newMsg->SetFromUserID($result['fromUser']);
		$newMsg->SetType($result['msgType']);
		$newMsg->SetRead($result['msgRead']);
		$newMsg->SetArchieved($result['isArchieved']);
		
    	return $newMsg;
    }

   
    /**
     * a user was deleted, kill his messages!
     * @param $uId - SAVE
     * @return unknown_type
     */
    public static function DeleteUser( $uId )
    {
    	if ( !is_numeric($uId) )
    		throw new Exception("MessageHelper::DeleteUser() malformed userID given");
    		    	
    	$dbSave_uId = intval($uId); // is_num checked!
    	$dbLink = DBHelper::getInstance();
    	$sql = "DELETE FROM `tbw_messages` WHERE id = '".$dbSave_uId.";";
    	$dbLink->DoQuery($sql);
    }
    
    // no prev messages, doesnt make any sense
    public static function GetPrevUnreadMessageID($szUserName, $msgType)
    {    	
		return false;
    }
    
    public static function GetNextUnreadMessageID($szUserName, $msgType)
    {
    	$nextMsgObj = self::GetNewMessageForType($szUserName, $msgType);
    	
    	if ($nextMsgObj)
    		return $nextMsgObj->GetID();    		
    	else
    		return false;
    }   
    
    public static function MarkMessageAsRead($szUserName, $msgID)
    {
    	if ( !is_numeric($msgID) )
    		throw new Exception("MessageHelper::MarkMessageAsRead() malformed msgID given");
    		    	
    	$iUserID = UserHelper::GetUserIdOfUsername($szUserName);
    	$msgObj = new Message($msgID, $iUserID);
    	$msgObj->LoadFromDatabase();
    	
    	if ($msgObj->GetIsRead())
    		return;
    		
    	$msgObj->SetRead(true);
    	$msgObj->SaveToDatabase();
    }
    
    public static function DeleteMessage($szUserName, $msgID)
    {
    	if ( !is_numeric($msgID) )
    		throw new Exception("MessageHelper::DeleteMessage() malformed msgID given");
    		
    	$iUserID = UserHelper::GetUserIdOfUsername($szUserName);
    	
		$dbSave_iUserID = intval($iUserID);
    	$dbSave_msgID = intval($msgID);
    	
    	$dbHelper = DBHelper::getInstance();    	
   		$sql = "DELETE FROM `tbw_messages` WHERE userid = '".$dbSave_iUserID."' AND id = '".$dbSave_msgID."' LIMIT 1;";
   		$dbHelper->DoQuery($sql);    	
    }
    
    public static function ArchieveMessage($szUserName, $msgID)
    {
    	if ( !is_numeric($msgID) )
    		throw new Exception("MessageHelper::ArchieveMessage() malformed msgID given");
    		
    	$iUserID = UserHelper::GetUserIdOfUsername($szUserName);
    	$msgObj = new Message($msgID, $iUserID);
    	$msgObj->LoadFromDatabase();
    	
    	if ($msgObj->GetIsArchieved())
    		return;
    		
    	$msgObj->SetArchieved(true);
    	$msgObj->SaveToDatabase();
    }
    
    /**
     * 
     * @param $szUserName
     * @param $iMsgType -- -1 = all
     * @param $bRead
     * @return unknown_type
     */
    public static function GetMessageCountForType($szUserName, $iMsgType, $bUnreadOnly)
    {    
    	if(!is_numeric($iMsgType))
    		throw new Exception("MessageHelper::GetMessageCountForType() malformed type given");
    		    	
    	$userID = UserHelper::GetUserIdOfUsername($szUserName);
    	$dbHelper = DBHelper::getInstance();
    	
    	$dbSave_userID = intval($userID);
    	$dbSave_iMsgType = intval($iMsgType);
    	
    	if ( $iMsgType >= 0 )
    	{
    		if ( $bUnreadOnly )
   				$sql = "SELECT COUNT(*) FROM `tbw_messages` WHERE userid = '".$dbSave_userID."' AND msgType = '".$dbSave_iMsgType."' AND msgRead = '0';";
   			else
   				$sql = "SELECT COUNT(*) FROM `tbw_messages` WHERE userid = '".$dbSave_userID."' AND msgType = '".$dbSave_iMsgType."';";
    	}
    	else
    	{
   			if ( $bUnreadOnly )
   				$sql = "SELECT COUNT(*) FROM `tbw_messages` WHERE userid = '".$dbSave_userID."' AND msgRead = '0';";
   			else
    			$sql = "SELECT COUNT(*) FROM `tbw_messages` WHERE userid = '".$dbSave_userID."';";
    	}
    	    		
		$result = &$dbHelper->DoQuery($sql);
		$output = $result->fetch_assoc();
		
		return array_pop($output);
    }
    
    public static function GetNewMessageForType($szUserName, $iMsgType)
    {    
    	if(!is_numeric($iMsgType))
    		throw new Exception("MessageHelper::GetNewMessageForType() malformed type given");
    		
    	$userID = UserHelper::GetUserIdOfUsername($szUserName);
    	$dbHelper = DBHelper::getInstance();
    	
    	$dbSave_userID = intval($userID);
    	$dbSave_iMsgType = intval($iMsgType);
    	
    	if ( $iMsgType >= 0 )
    	{
			$sql = "SELECT * FROM `tbw_messages` WHERE userid = '".$dbSave_userID."' AND msgType = '".$dbSave_iMsgType."' AND msgRead = '0' LIMIT 1;";
    	}
    	else
    	{
			$sql = "SELECT * FROM `tbw_messages` WHERE userid = '".$dbSave_userID."' AND msgRead = '0' LIMIT 1;";
    	}
    	    		
		$result = &$dbHelper->DoQuery($sql);
		$output = &$result->fetch_assoc();
		
		if ( $result->num_rows <= 0 )
			return false;
			
		$msgObj = &self::ConvertDBResultToMessageObject(&$output);
		$result->free();
		
		return $msgObj;
    }    
    
    public static function GetMessagesByType($szUserName, $iMsgType)
    {
    	if(!is_numeric($iMsgType))
    		throw new Exception("MessageHelper::GetMessagesByType() malformed type given");
    		    	
    	$userID = UserHelper::GetUserIdOfUsername($szUserName);
    	$dbHelper = DBHelper::getInstance();
    	
    	$dbSave_userID = intval($userID);
    	$dbSave_iMsgType = intval($iMsgType);
    	
   		$sql = "SELECT * FROM `tbw_messages` WHERE userid = '".$dbSave_userID."' AND msgType = '".$dbSave_iMsgType."';";

		$result = &$dbHelper->DoQuery($sql);
		$msgObjects = array();		
		
		for ($output = $result->fetch_assoc(); $output; $output = $result->fetch_assoc())
		{		
			$msgObjects[] = &self::ConvertDBResultToMessageObject($output);
		}
		
		return $msgObjects;
    }
    
    //o($me->GetName(), $_POST['weiterleitung-to'], $msgObj);
    public static function ForwardMessageTo(&$msgObj, &$userObj, $szForwardTo)
    {
    	$error = "";
    	$szFowardTo = trim($szForwardTo);
    	
    	if (!User::userExists($szForwardTo))
    		$error = "Der Empfänger, den Sie eingegeben haben, existiert nicht.";
    	else if($szForwardTo == $userObj->getName())
    		$error = "Sie können sich nicht selbst eine Nachricht schicken.";
    	else
    	{
    		$iMyUserID = UserHelper::GetUserIdOfUsername($userObj->getName());
    		$iToUserID = UserHelper::GetUserIdOfUsername($szFowardTo);
    		$szFromUser = UserHelper::GetUserNameOfUserID($msgObj->GetFromUserID());
    		$newMsgObj = new Message(-1, $iToUserID);
    		
			$weiterleitung_text = '';
            $weiterleitung_text .= "--- Weitergeleitete Nachricht";
            $weiterleitung_text .= ", Absender: ";
            $weiterleitung_text .= $szFromUser;
			$weiterleitung_text .= ", Sendezeit: ".date('H:i:s, Y-m-d', $msgObj->GetTime());
            $weiterleitung_text .= " ---<br>\n";
            $weiterleitung_text .= "\n ";
            
            $newMsgObj->SetMessageText($weiterleitung_text.$msgObj->GetText());
            $newMsgObj->SetSubject('Fwd: '.$msgObj->GetSubject());
            $newMsgObj->SetFromUserID($iMyUserID);
            $newMsgObj->SetType(MSGTYPE_USER);
            $newMsgObj->SetToUserID($iToUserID);
            $newMsgObj->SaveToDatabase();
    	}
    	return $error;
    }
}