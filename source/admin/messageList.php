<?php
if(!isset($_SERVER['DOCUMENT_ROOT']) || strlen($_SERVER['DOCUMENT_ROOT']) <= 0)
    $_SERVER['DOCUMENT_ROOT'] = getcwd()."/..";
    
require_once($_SERVER['DOCUMENT_ROOT'].'/include/config_inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/include/util.php' );
require($_SERVER['DOCUMENT_ROOT'].'/admin/include.php');

// TODO, this needs to support multiple pages to view messages, 
// however, how the current messages are stored its kinda impossible to have those listed on fallowing pages

// TODO, add new permissions flag, using the "log" permission for now
// check access for "Listing Messages"
// if (! isset ( $admin_array ['permissions'] [10] ) || ! $admin_array ['permissions'] [10])
	die ( 'No access.' ); // disabling for all, not working atm

admin_gui::html_head ();

$dh = opendir ( global_setting ( "DB_PLAYERS" ) );

?>
<style type="text/css">
	#user { min-width:150px; }
	#msgID { min-width:150px; }
	#age { min-width:120px; }
	#cat { min-width:140px; text-align:center; }
</style>
<?php 

echo "<table>\n";
echo "<tr>\n";
echo "<td id=\"user\">user</td>\n";
echo "<td id=\"msgID\">msgID</td>\n";
echo "<td id=\"age\">age</td>\n";
echo "<td id=\"cat\">category</td>\n";
echo "<td id=\"status\">status</td>\n";
echo "</tr>\n";

while ( ($filename = readdir ( $dh )) !== false ) {
	if (! is_file ( global_setting ( "DB_PLAYERS" ) . '/' . $filename )) {
		continue;
	}
	
	$user = Classes::User ( urldecode ( $filename ) );
	$message_categories = $user->getMessageCategoriesList ();
	
	foreach ( $message_categories as $category ) {
		$messages_list = $user->getMessagesList ( $category );
		
		foreach ( $messages_list as $message_id ) {
			$message_obj = Classes::Message ( $message_id );
			$difftimestamp = time () - $message_obj->getTime (); // ( time() - $message_obj->getTime() ) > $max_diff
			$msgStatus = $message_obj->getStatus ();
			// $user->checkMessageStatus( $message_id, $category ) - WTF?	
			//date("\m: n \d: j \h: G \m: i \s: s",		

			echo "<tr>\n";
			echo "<td id=\"user\">".$user->getName()."</td>\n"; // user
			echo "<td id=\"msgID\">".$message_id."</td>\n"; // msgID
			echo "<td id=\"age\">".timeAgo($message_obj->getTime())."</td>\n"; // age
			echo "<td id=\"cat\">".msgCategoryToText($category)."</td>\n";
			echo "<td id=\"status\">".$msgStatus."</td>\n"; // status
			echo "</tr>\n";
			Classes::resetInstances ( 'Message' );
		}
	}
}
echo "</table>\n";

admin_gui::html_foot ();
?>