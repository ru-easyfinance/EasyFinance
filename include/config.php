<?
/**
* file: config.php
* author: Roman Korostov
* date: 23/01/07        
**/

// Path settings
define('SYS_DIR_ROOT',   "/home/rkorostov/data/www");
define('SYS_DIR_LIBS',   SYS_DIR_ROOT."/core/");
define('SYS_DIR_INC',    SYS_DIR_ROOT."/include/");
define('SYS_DIR_MOD',    SYS_DIR_ROOT."/modules/");
define('SYS_DIR_HTML',   SYS_DIR_ROOT."/templates/");
define('SYS_DIR_CACHE',  SYS_DIR_ROOT."/cache/");
define('URL_ROOT', 		 "http://".$_SERVER['HTTP_HOST']);
define('UPLOAD_DIR',	SYS_DIR_ROOT."/easyfinance.ru/upload/photo_experts/");

// DB settings
define('SYS_DB_HOST', 	'localhost');
define('SYS_DB_USER', 	'homemone');
define('SYS_DB_PASS', 	'lw0Hraec');
define('SYS_DB_BASE', 	'wwwhomemoneyru');

// Error codes
define('GENERAL_MESSAGE',  200);
define('GENERAL_ERROR',    202);
define('CRITICAL_MESSAGE', 203);
define('CRITICAL_ERROR',   204);

// Month setting
$sys_month =
array( 
       '01'   => 'Январь',
       '02'   => 'Февраль',
       '03'   => 'Март',
       '04'   => 'Апрель',
       '05'   => 'Май',
       '06'   => 'Июнь',
       '07'   => 'Июль',
       '08'   => 'Август',
       '09'   => 'Сентябрь',
       '10'  => 'Октябрь',
       '11'  => 'Ноябрь',
       '12'  => 'Декабрь');

//include(SYS_DIR_LIBS.'xml_parse.class.php');
$res[0]['USD'] = 25.2626;
$res[1]['EUR'] = 36.2670;

$sys_currency =
array(
                '1' => 1,
                '2' => $res[0]['USD'],
                '3' => $res[1]['EUR'],
				'4' => 5.17);
// Названия валют по кодам
$sys_currency_name =
array(
                '1' => 'руб.',
                '2' => '$',
                '3' => '&euro;',
				'4' => 'грв.');

// Виды и названия отчетов
$sys_reports = array(
	'graph_profit' => 'Доходы',
	'graph_loss' => 'Расходы',
	'graph_profit_loss' => 'Сравнение расходов и доходов',
	'txt_profit' => 'Детальные доходы',
	'txt_loss' => 'Детальные расходы',
	'txt_loss_difference' => 'Сравнение расходов за периоды',
	'txt_profit_difference' => 'Сравнение доходов за периоды',
	'txt_profit_avg_difference' => 'Сравнение доходов со средним за периоды',
	'txt_loss_avg_difference' => 'Сравнение расходов со средним за периоды',
);


define('SYS_MAX_PERPAGE',   3);               
define('DEFAULT_MODULE',   'welcam');

?>