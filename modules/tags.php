<?
if (!isset($_GET['ajax']) || !$_GET['ajax']) {
	include("../core/modules/tags.module.php");
} else {
	include("../core/modules/ajax/tags.ajax.php");
}
?>