<?php
	require('../include.php');

        $url = 'http://'.$_SERVER['HTTP_HOST'].h_root.'/login/index.php?username=demo&password=demo&database='.$_REQUEST['database'];
	header('Location: '.$url, true, 303);
        die('HTTP redirect: <a href="'.htmlentities($url).'">'.htmlentities($url).'</a>');
?>
