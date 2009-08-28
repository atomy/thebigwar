<?php
	require_once( '../../include/config_inc.php' );
	require( TBW_ROOT.'engine/include.php' );

	session_start();

	$_SESSION = array();
	if(isset($_COOKIE[session_name()]))
		setcookie(session_name(), '');
	session_destroy();

	$url = explode('/', $_SERVER['PHP_SELF']);
	array_pop($url); array_pop($url); array_pop($url);
	$url = 'http://'.$_SERVER['HTTP_HOST'].implode('/', $url).'/index.php';
	header('Location: '.$url);
	die('Logged out successfully. <a href="'.htmlentities($url).'">Back to home page</a>.');
?>
