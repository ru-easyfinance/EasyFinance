<?php
/**
 * @author ukko
 */

define('INDEX', true);

error_reporting( E_ALL );

// Запускаем сессию, ибо могут возникнуть проблемы с хидерами
session_start();

// Подключаем файл с общей конфигурацией проекта
require_once dirname(dirname(dirname(__FILE__))) . '/include/config.php';

// Загружаем общие данные
// @todo оторвать!
require_once SYS_DIR_INC . 'common.php';