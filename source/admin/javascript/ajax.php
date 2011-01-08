<?php
	$LOGIN = true;
    
	define('ignore_action', true);
	define('ajax', true);

if(!isset($_SERVER['DOCUMENT_ROOT']) || strlen($_SERVER['DOCUMENT_ROOT']) <= 0)
    $_SERVER['DOCUMENT_ROOT'] = getcwd()."/..";
    
require_once($_SERVER['DOCUMENT_ROOT'].'/include/config_inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/admin/include.php');

	__autoload('User');
	
	if( !$adminObj->can( ADMIN_EDITITEMS ) )
		die("No Access");
	
	header('Content-type: text/xml;charset=UTF-8');
	echo '<xmlresponse>';
	switch($_GET['action']) {
		case 'level':
			$user = trim($_GET['user']);
			$planet = trim($_GET['planet']);
			if(User::userExists($user)) {
				$that_user = Classes::User($user);
				if($that_user->isOwnPlanet($planet)) {
					$items = $that_user->getItemsList();
					echo '<info>Daten geladen</info>';
					echo '<items>';
					foreach($items as $item) {
						echo '<item id="'.$item.'" wert="'.$that_user->getItemLevel($item).'" />';
					}
					echo '</items>';
				} else {
					echo '<info>Der Planet '.$planet.' gehört nicht zu '.$user.'</info>';
				}
			} else {
				echo '<info>Es gibt keinen Spieler mit dem Namen '.$user.'</info>';
			}
			break;
		case 'add':
			$user = trim($_GET['user']);
			$planet = trim($_GET['planet']);
			$value = trim($_GET['value']);
			$id = trim($_GET['id']);
			if(User::userExists($user)) {
				$that_user = Classes::User($user);
				if($that_user->isOwnPlanet($planet)) {
					if($that_user->changeItemLevel($id,$value)) {
						echo '<info>Wert geändert</info>';
					} else {
						echo '<info>Fehler</info>';
					}
				} else {
					echo '<info>Der Planet '.$planet.' gehört nicht zu '.$user.'</info>';
				}
			} else {
				echo '<info>Es gibt keinen Spieler mit dem Namen '.$user.'</info>';
			}
			break;
	}
	echo '</xmlresponse>';
?>