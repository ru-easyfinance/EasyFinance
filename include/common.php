<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Файл с общими настройками проекта
 * @copyright https://easyfinance.ru/
 * SVN $Id$
 */
error_reporting( E_ERROR );

require_once dirname(__FILE__)."/config.php";
require_once SYS_DIR_INC.'/functions.php';

if (DEBUG) {
    // В режиме DEBUG выводим отладочные сообщения в консоль firebug < http://getfirebug.com/ > через плагин firephp < http://www.firephp.org/ >
    require_once SYS_DIR_LIBS . 'external/FirePHPCore/FirePHP.class.php';
}


// Подключение нового ядра
include_once('../classes/_Core/_Core.php');
new _Core();

spl_autoload_register('__autoload');

// Подгружаем внешние библиотеки
require_once SYS_DIR_LIBS . 'external/DBSimple/Mysql.php';

// Настраиваем смарти

//$tpl->assign('revision', REVISION);

// Добавляем ссылки на БД, Смарти, Пользователя и Валюты - в наше ядро
Core::getInstance()->currency = new Currency();
Core::getInstance()->user = new User();
Core::getInstance()->js = array(
    'welcome' => array('welcome'),
    'targets' => array('targets'),
    'report' => array(
        'report',
        'jquery/jquery.jqplot.min',
        'jquery/jqplot.categoryAxisRenderer.min',
        'jquery/jqplot.barRenderer.min',
        'jquery/jqplot.pieRenderer.min',
        'jquery/excanvas.min'),
    'registration' => array('registration'),
    'profile' => array('profile'),
    'periodic' => array('periodic'),
    'operation' => array('widgets/operations/operationsJournal.widget', 'operation'),
    'mail' => array('mail', 'models/mail.model', 'widgets/mail.widget'),
    'expert' => array('models/mail.model', 'widgets/mail.widget', 'jquery/jquery.form', 'jquery/jHtmlArea-0.7.0', 'jquery/jHtmlArea.ColorPickerMenu-0.7.0', 'models/expert.model', 'widgets/expert/expertEditInfo.widget', 'widgets/expert/expertEditPhoto.widget', 'widgets/expert/expertEditCertificates.widget', 'widgets/expert/expertEditServices.widget', 'screens/expert.screen', 'jquery/jquery.fancybox-1.0.0'),
    'expertslist' => array('widgets/services/expertsList.widget', 'screens/services.screen', 'jquery/jquery.fancybox-1.0.0'),
    'login' => array('welcome'),
    'info' => array('info'),
    'category' => array('models/category.model', 'category'),
    'calendar' => array('calendar'),
    'admin' => array( 'admin'),
    'accounts' => array('widgets/accounts/accountEdit.widget', 'widgets/accounts/accountsJournal.widget', 'accounts'),
    'review' => array('jquery/jquery.fancybox-1.0.0', 'review'),
    'budget' => array('budget','models/category.model', 'models/budget.model','widgets/budget/budget.widget','widgets/budget/budgetMaster.widget')
);

// Почта
include_once "../core/external/Swift/swift_required.php";

$mailTransport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, 'ssl')
	//->setUsername('info@easyfinance.ru')
	//->setPassword('j2df32nD3l7sFa2');
	->setUsername('support@easyfinance.ru')
	->setPassword('B6BestMyA6Yo');

Core::getInstance()->mailer = Swift_Mailer::newInstance( $mailTransport );
