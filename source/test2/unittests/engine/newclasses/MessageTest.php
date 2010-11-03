<?
require_once '../../../../include/config_inc.php';
require_once TBW_ROOT.'engine/newclasses/Message.php';
require_once TBW_ROOT.'include/DBHelper.php';

class MessageTest extends PHPUnit_Framework_TestCase
{        
    protected function setUp()
    {
        $dbHelper = DBHelper::getInstance();
        $file = TBW_ROOT.'test2/sql/tbw_messages.sql';        
        exec('mysql -u'.MYSQL_LOGDB_USER.' -p'.MYSQL_LOGDB_PASS.' -h'.MYSQL_LOGDB_HOST.' '.MYSQL_LOGDB_DB.' < '.$file);
    }
    
    protected function tearDown()
    {
        //$dbHelper = DBHelper::getInstance();
        //$dbHelper->DoQuery("DROP TABLE IF EXISTS `tbw_messages`;");
    }
    
    public function testConstruct()
    {
        $msgID = 1337;
        $userID = 12;
        $fromUserID = 2;
        $toUserID = 3;
        $subject = "test1234";
        $message = "test ing";
        $type = 0;
        $achieved = false;
        $time = 1337;
        $bRead = true;
    
        $msgObj = new Message($msgID, $userID, $fromUserID, $toUserID, $subject, $message, $type, $achieved, $time, $bRead);
        $this->assertEquals($msgObj->GetTime(),$time);
        
        $msgObj = new Message(-1, $userID);
        $this->assertEquals($msgObj->GetTime(),time());
    }
    
    public function testSaveToDatabase()
    {
        $userID = 12;
        $fromUserID = 2;
        $toUserID = 3;
        $subject = "test1234";
        $message = "test ing";
        $type = 1;
        $achieved = 1;
        $bRead = 1;
    
        $msgObj = new Message(-1, $userID, $fromUserID, $toUserID, $subject, $message, $type, $achieved, 0 /*$time*/, $bRead);
        
        $msgObj->SaveToDatabase();
        $time = time();
        
        $msgId = $msgObj->GetID();
        $tMsg = new Message($msgId);
        $tMsg->LoadFromDatabase();
        
        $this->assertEquals($tMsg->GetSubject(), $subject);
        $this->assertEquals($tMsg->GetText(), $message);
        $this->assertEquals($tMsg->GetTime(), $time);
        $this->assertEquals($tMsg->GetToUserID(), $toUserID);
        $this->assertEquals($tMsg->GetFromUserID(), $fromUserID);
        $this->assertEquals($tMsg->GetType(), $type);
        $this->assertEquals($tMsg->GetUserID(), $userID);
        $this->assertEquals($tMsg->GetIsRead(), $bRead);
        $this->assertEquals($tMsg->GetIsArchieved(), $achieved);
        $this->assertEquals($tMsg->GetIsRead(), $bRead);
    }
    
    public function testLoadFromDatabase()
    {
        $msg = new Message(23);
        $msg->LoadFromDatabase();
        
        $this->assertEquals($msg->GetSubject(), 'Re: testbetreff');
        $this->assertEquals($msg->GetText(), 'aaiik');
        $this->assertEquals($msg->GetTime(), 1288455326);
        $this->assertEquals($msg->GetToUserID(), 3);
        $this->assertEquals($msg->GetFromUserID(), 2);
        $this->assertEquals($msg->GetType(), 5);
        $this->assertEquals($msg->GetUserID(), 3);
        $this->assertEquals($msg->GetIsRead(), 1);
        $this->assertEquals($msg->GetIsArchieved(), 0);
    }
}