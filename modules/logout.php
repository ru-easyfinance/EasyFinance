<?
/**
* file: index.php
* author: Roman Korostov
* date: 24/01/07
**/

unset ($_SESSION['user']);
setcookie("autoLogin", "");
setcookie("autoPass", "");
session_destroy();
if (!isset($_GET['pda']))
{
  header ("Location: /");
}else{
  header ("Location: /pda/");
}
?>