<?php
/**
 * Модуль системы для экспертов
 */

// Если запрос пришел не от аякс
if (!isset($_GET['ajax']) || !$_GET['ajax']) {
	include(SYS_DIR_LIBS."modules/experts.module.php");
} else {
	include(SYS_DIR_LIBS."modules/ajax/experts.ajax.php");
}
?>