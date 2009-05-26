<?
/**
* file: index.php
* author: Roman Korostov
* date: 24/01/07
**/

unset ($_SESSION['user']);
setcookie("autoLogin", "");
setcookie("autoPass", "");
setcookie("f1", "");
setcookie("f2", "");
session_destroy();
if (!isset($_GET['pda']))
{
  header ("Location: http://www.home-money.ru/index.php");
}else{
  header ("Location: /pda/index.php");
}
?>