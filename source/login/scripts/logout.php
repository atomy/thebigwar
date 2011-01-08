<?php
if(!isset($_SERVER['DOCUMENT_ROOT']) || strlen($_SERVER['DOCUMENT_ROOT']) <= 0)
    $_SERVER['DOCUMENT_ROOT'] = getcwd()."/..";
    
require_once($_SERVER['DOCUMENT_ROOT'].'/include/config_inc.php');
require($_SERVER['DOCUMENT_ROOT'].'/engine/include.php');

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
