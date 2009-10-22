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

$tpl->append('js', 'jquery/jquery.min.js');

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
    $tpl->append('css', 'jquery/jquery.calculator.css');
    $tpl->append('css', 'jquery/fullcalendar.css');
}

$tpl->append('css', 'jquery/jHtmlArea.css');
$tpl->append('css', 'jquery/jHtmlArea.ColorPickerMenu.css');

$tpl->append('css', 'jquery/fancy.css');

$tpl->append('css', 'expertsList.css');

if( JS_MINIFY )
{
    $tpl->append('js',  'global-min.js');
}
else
{
    // jQuery & plugins
    $tpl->append('js',  'jquery/ui.core.js');
    $tpl->append('js',  'jquery/i18n/jquery-ui-i18n.js');
    $tpl->append('js',  'jquery/ui.resizable.js');
    $tpl->append('js',  'jquery/ui.draggable.js');
    $tpl->append('js',  'jquery/ui.dialog.js');
    $tpl->append('js',  'jquery/ui.datepicker.js');
    $tpl->append('js',  'jquery/jquery.qtip.js');
    $tpl->append('js',  'jquery/jquery.jgrowl.min.js');

    $tpl->append('js',  'jquery/jquery.calculator.js');
    $tpl->append('js',  'jquery/jquery.calculator-ru.js');
    $tpl->append('js',  'jquery/jquery.mousewheel.js');             // хз где юзается
    $tpl->append('js',  'jquery/jquery.em.js');                     // Пока Используется в jQuery.plots и jScrollPane
//    $tpl->append('js',  'jquery/jScrollPane.js');                   /** @deprecated */
    $tpl->append('js',  'jquery/jquery.maskedinput-1.2.2.min.js');  /** @deprecated */
    $tpl->append('js',  'jquery/jquery.timepicker-table.min.js');   // Используется в календаре
    $tpl->append('js',  'jquery/ui.tabs.js');
    //$tpl->append('js',  'jquery/i18n/ui.datepicker-ru.js');       /** @deprecated */ // Дублирует jquery/i18n/jquery-ui-i18n.js
    $tpl->append('js',  'jquery/jquery.validate.js');               // Используется только для валидации при регистрации, возможно стоит от него избавится!
    $tpl->append('js',  'jquery/tinysort.js');                      // Используется для тегов
    $tpl->append('js',  'jquery/fullcalendar.js');                  // Большой календарь
    $tpl->append('js',  'jquery/jquery.form.js');                   /** @deprecated */
    $tpl->append('js',  'jquery/jquery.cookie.js');                 /** @deprecated */ //Проверить где он сейчас используется и изменить все связи

    // external libs
    $tpl->append('js',  'anychart/AnyChart.js');
    $tpl->append('js',  'calculator/calculator.js');                // Калькулятор у Саши

    // internal
    $tpl->append('js',  'main.js');
    $tpl->append('js',  'helpers.js');                              //WTF???
}

$tpl->append('js',  'jquery/jquery.fancybox-1.0.0.js');

$tpl->append('js',  'easyfinance.js');
$tpl->append('js',  'models/category.model.js');
$tpl->append('js',  'models/budget.model.js');
$tpl->append('js',  'widgets/budget.widget.js');


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
    'operation' => array('models/operation.model', 'widgets/operations/operationsJournal.widget', 'operation'),
    'mail' => array('mail', 'models/mail.model', 'widgets/mail.widget'),
    'expert' => array('jquery/form', 'jquery/jHtmlArea-0.6.0', 'jquery/jHtmlArea.ColorPickerMenu-0.6.0', 'models/expert.model', 'widgets/expert/expertEditInfo.widget', 'widgets/expert/expertEditPhoto.widget', 'widgets/expert/expertEditCertificates.widget', 'widgets/expert/expertEditServices.widget', 'screens/expert.screen'),
    'expertslist' => array('widgets/services/expertsList.widget', 'screens/services.screen'),
    'login' => array('welcome'),
    'info' => array('info'),
    'category' => array('category'),
    'calendar' => array('calendar'),
    'admin' => array( 'admin'),
    'accounts' => array('accounts'),
    'review' => array('review'),
    'budget' => array('budget')
);
