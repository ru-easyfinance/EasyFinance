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
Core::getInstance()->db = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE."_test");

$ex = new myCurrencyExchange();
foreach(efCurrencyModel::loadAll() as $row) {
    $ex->setRate($row['cur_id'], $row['rate'], myMoney::RUR);
}
sfConfig::set('ex', $ex);

// И обработчик ошибок для бд
Core::getInstance()->db->setErrorHandler('databaseErrorHandlerTest');

require_once(dirname(__FILE__).'/UnitTestCase.php');
require_once(dirname(__FILE__).'/TestMailInvokerStub.php');
require_once(dirname(__FILE__).'/CreateObjectHelper.php');

// Заглушка для почты
$invoker = new TestMailInvokerStub;
Swift_DependencyContainer::getInstance()
    ->register('transport.mailinvoker')
    ->asValue($invoker);
$transport = Swift_MailTransport::newInstance();
Core::getInstance()->mailer = Swift_Mailer::newInstance($transport);

/**
 * Выводит форматированный вывод о SQL ошибках
 * @param string $message
 * @param array $info
 */
function databaseErrorHandlerTest($message, $info)
{
    // Выводим подробную информацию об ошибке.
    trigger_error(print_r($info));
}