<?php

// TODO: check include path
//ini_set('include_path', ini_get('include_path'));
//session_start();
define('INDEX',true);
require_once 'C:/WebServers/home/hm/include/common.php';
$_POST['login'] = 'chel';
$_POST['pass'] = '123';
Core::getInstance()->authUser();
Core::getInstance()->parseUrl();
?>