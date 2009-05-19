<?
if (!isset($_GET['ajax']) || !$_GET['ajax']) {
	include("../core/modules/bills.module.php");
} else {
	include("../core/modules/ajax/bills.ajax.php");
}
?>