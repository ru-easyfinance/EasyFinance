<?php
require_once 'library/People/People.Class.PasswordHash.php';
$p = new PasswordHash(8,1);
$pass = $p->HashPassword('gfh0km');


