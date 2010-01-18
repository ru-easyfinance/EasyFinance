<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Файл с общими настройками проекта
 * @copyright https://easyfinance.ru/
 * SVN $Id$
 */

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
require_once SYS_DIR_LIBS . 'external/smarty/Smarty.class.php';
require_once SYS_DIR_LIBS . 'external/smarty/Smarty_Compiler.class.php';
require_once SYS_DIR_LIBS . 'external/smarty/Config_File.class.php';

// Устанавливаем обработчик ошибок
set_error_handler("UserErrorHandler");

// Настраиваем смарти
$tpl = new Smarty();

$tpl->template_dir    =  SYS_DIR_ROOT.'/views';
$tpl->compile_dir     =  TMP_DIR_SMARTY.'/cache';

$tpl->plugins_dir     =  array(SYS_DIR_LIBS.'external/smarty/plugins');
$tpl->compile_check   =  true;
$tpl->force_compile   =  false;
$tpl->assign('revision', REVISION);

if ( CSS_MINIFY )
{
    $tpl->append('css', 'global-min.css');
}
else
{
    $tpl->append('css', 'main.css');

    $tpl->append('css', 'jquery/south-street/ui.all.css');
    //$tpl->append('css', 'jquery/south-street/ui.base.css');
    $tpl->append('css', 'jquery/south-street/ui.core.css');
    $tpl->append('css', 'jquery/south-street/ui.resizable.css');
    $tpl->append('css', 'jquery/south-street/ui.dialog.css');
    $tpl->append('css', 'jquery/south-street/ui.tabs.css');
    $tpl->append('css', 'jquery/south-street/ui.datepicker.css');

    $tpl->append('css', 'jquery/jquery.jgrowl.css');
    $tpl->append('css', 'jquery/fullcalendar.css');

}

$tpl->append('css', 'menuUser.css');

$tpl->append('css', 'jquery/jHtmlArea.css');
$tpl->append('css', 'jquery/jHtmlArea.ColorPickerMenu.css');

$tpl->append('css', 'jquery/fancy.css');

$tpl->append('css', 'jquery/sexyCombo.css');

$tpl->append('css', 'report.css');
$tpl->append('css', 'expert.css');
$tpl->append('css', 'expertsList.css');
$tpl->append('css', 'operationsJournal.css');
$tpl->append('css', 'budgetMaster.css');
$tpl->append('css', 'budget.css');

if( JS_MINIFY )
{
    $tpl->append('js',  'global-min.js');
}
else
{
	foreach ( file( SYS_DIR_INC . 'js/global.list') as $js )
	{
		$tpl->append('js', $js);
	}
}

$tpl->append('js', 'flowplayer-3.1.4.min.js');
$tpl->append('js', 'feedback.js');
$tpl->append('js', 'widgets/help.widget.js');
$tpl->append('js', 'models/accounts.model.js');
$tpl->append('js', 'models/category.model.js');
$tpl->append('js', 'widgets/accounts/accountsPanel.widget.js');
$tpl->append('js', 'widgets/operations/operationEdit.widget.js');

if(IS_DEMO){
    $tpl->append('js',  'demo_message.js');
}

// Добавляем ссылки на БД, Смарти, Пользователя и Валюты - в наше ядро
Core::getInstance()->tpl = $tpl;
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
