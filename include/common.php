<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Файл с общими настройками проекта
 * @copyright https://easyfinance.ru/
 * SVN $Id$
 */

require_once SYS_DIR_INC.'/functions.php';

if (DEBUG) {
    // В режиме DEBUG выводим отладочные сообщения в консоль firebug < http://getfirebug.com/ > через плагин firephp < http://www.firephp.org/ >
    require_once SYS_DIR_LIBS . 'external/FirePHPCore/FirePHP.class.php';
}


// Подключение нового ядра
require_once( dirname ( dirname ( __FILE__ ) ) . '/classes/_Core/_Core.php' );
new _Core();

spl_autoload_register('__autoload');

// Подгружаем внешние библиотеки
require_once SYS_DIR_LIBS . 'external/DBSimple/Mysql.php';

// Добавлем очерёдность загрузки для JS файлов
Core::getInstance()->currency = new Currency();
Core::getInstance()->user = new User();
Core::getInstance()->js = array(
    '' => array('welcome'), // слайды на главной
    'targets' => array('targets'),
    'report' => array('widgets/report.widget', 'models/report.model'),
    'registration' => array('registration'),
    'profile' => array(
		'profile',
        'models/user.model',
        'widgets/profile/profile.widget',
        'widgets/profile/userCurrency.widget',
        'widgets/profile/userIntegrations.widget'
        ),
    'operation' => array(
		'widgets/operations/operationsJournal.widget', 
		'operation'),
    'mail' => array('mail',
		'models/mail.model',
		'widgets/mail.widget'),
    'expert' => array(
		'models/mail.model', 
		'widgets/mail.widget', 
		'jquery/jquery.form', 
		'jquery/jHtmlArea-0.7.0', 
		'jquery/jHtmlArea.ColorPickerMenu-0.7.0', 
		'models/expert.model', 
		'widgets/expert/expertEditInfo.widget', 
		'widgets/expert/expertEditPhoto.widget', 
		'widgets/expert/expertEditCertificates.widget', 
		'widgets/expert/expertEditServices.widget', 
		'screens/expert.screen', 
		'jquery/jquery.fancybox-1.0.0'),
    'expertslist' => array(
		'widgets/services/expertsList.widget', 
		'screens/services.screen', 
		'jquery/jquery.fancybox-1.0.0'),
    'login' => array('welcome'),
    'info' => array('info'),
    'integration' => array('registration', 'screens/integration.screen'),
    'category' => array(
		'models/category.model', 
		'category'),
    'calendar' => array(
		'jquery/fullcalendar',
        'calendar',
        'widgets/calendar/calendar.widget',
        'widgets/calendar/calendarList.widget'),
    'admin' => array( 'admin'),
    'accounts' => array(
		'widgets/accounts/accountsJournal.widget', 
		'accounts'),
    'review' => array(	
		'jquery/jquery.fancybox-1.0.0', 
		'review'),
    'budget' => array(
		'budget',
		'models/category.model', 
		'models/budget.model',
		'widgets/budget/budget.widget',
		'widgets/budget/budgetMaster.widget')
);

// Почта
require_once dirname( dirname ( __FILE__ ) ) . "/core/external/Swift/swift_required.php";

// sendmail
$mailTransport = Swift_SendmailTransport::newInstance('/usr/sbin/sendmail -bs');

/*
// mail
$transport = Swift_MailTransport::newInstance();
*/

/*
// smtp
$mailTransport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, 'ssl')
	//->setUsername('info@easyfinance.ru')
	//->setPassword('j2df32nD3l7sFa2');
	->setUsername('support@easyfinance.ru')
	->setPassword('7uN3BN6t');
*/

Core::getInstance()->mailer = Swift_Mailer::newInstance( $mailTransport );
