<?php

$db_user = 'test_user';
$db_pass = 'test_user';
$db_dbase = 'test';
$db_tbl = 'epos_data';

$e_pos_login = "homemoney";
$e_pos_pass  = md5("140188140188");

$lnk = mysql_pconnect('localhost', $db_user, $db_pass);
@mysql_select_db($db_dbase, $lnk);



?>
