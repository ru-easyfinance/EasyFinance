<?php
/**
 * Модуль планирования бюджета
 */

// Если запрос пришел не от аякс
if (!isset($_GET['ajax']) || !$_GET['ajax']) {
	include(SYS_DIR_MOD."Plan/plan.module.php");
} else {
	include(SYS_DIR_MOD."Plan/ajax/plan.ajax.php");
}
?>