<?php
/**
 * Модуль для работы с категориями и тегами
 */

// Если запрос пришел не от аякс
if (!isset($_GET['ajax']) || !$_GET['ajax']) {
	include(SYS_DIR_MOD."categories/categories.module.php");
} else {
	//include(SYS_DIR_MOD."categories/ajax/categories.ajax.php");
	include(SYS_DIR_MOD."categories/categories.module.php");
}
?>