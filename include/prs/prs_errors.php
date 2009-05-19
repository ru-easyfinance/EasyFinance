<?php
$dn = dirname(__FILE__);
require_once $dn.'/PEAR.php';

// Abstract Error class
class prs_Error extends PEAR_Error {}

// No file exists
class prs_NoFileExists_Error extends prs_Error {}
?>
