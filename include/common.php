<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Файл с общими настройками проекта
 */

require_once SYS_DIR_INC.'/functions.php';

if (DEBUG) {
    // В режиме DEBUG выводим отладочные сообщения в консоль firebug < http://getfirebug.com/ > через плагин firephp < http://www.firephp.org/ >
    require_once SYS_DIR_LIBS . 'external/FirePHPCore/FirePHP.class.php';
}


// Подключение нового ядра
require_once( dirname ( dirname ( __FILE__ ) ) . '/classes/_Core/_Core.php');
new _Core();

spl_autoload_register('__autoload');

// Подгружаем внешние библиотеки
require_once SYS_DIR_LIBS . 'external/DBSimple/Mysql.php';

$core = Core::getInstance();

// Загрузить курсы валют
// Старые
require_once(dirname(__FILE__) . '/../core/currency.class.php');
$core->currency = new oldCurrency();
// Новые
require_once SYS_DIR_ROOT . '/sf/lib/util/myMoney.php';
require_once SYS_DIR_ROOT . '/sf/lib/util/myCurrencyExchange.php';
require_once SYS_DIR_ROOT . '/classes/Currency/efCurrencyModel.php';
require_once SYS_DIR_ROOT . '/sf/lib/vendor/symfony/lib/config/sfConfig.class.php';

$ex = new myCurrencyExchange();
foreach(efCurrencyModel::loadAll() as $row) {
    $ex->setRate($row['cur_id'], $row['rate'], myMoney::RUR);
}
sfConfig::set('ex', $ex);

require_once(dirname(__FILE__) . '/../core/user.class.php');
$core->user = new oldUser();

//редиректы по условиям

//проверим, что определены нужные переменные, чтобы не падать на тестах
//TODO: тесты отвязать от общего окружения - иначе какие это модульные тесты
if (isset($_SERVER) && isset($_SERVER['REQUEST_URI'])) {

    if (isset($_SERVER['HTTP_HOST'])) {
        //для тестов сымитируем старое поведение: перекидываем по умолчанию на https,
        //пока пользователь с https явно не перейдет на http
        //на демо такого делать не нужно
        $needRedirectToHttps = !USING_HTTPS && !IS_DEMO;


        if ($needRedirectToHttps) {
            //проверим, что еще не перекидывали
            $redirectToHttpsFlagName = "REDIRECTED_TO_HTTPS_ALREADY";

            if (!isset($_COOKIE[$redirectToHttpsFlagName])) {
                setcookie($redirectToHttpsFlagName, 1, 0, COOKIE_PATH, COOKIE_DOMEN, COOKIE_HTTPS);
                header("Location: " . "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
                exit;
            }
        }
    }

    //если зашли на главную авторизованным пользователем,
    //по умолчанию кинем его на его стартовую страницу
    $currentUri = $_SERVER['REQUEST_URI'];
    $currentUriIsRoot = $currentUri == "/" || $currentUri == "/index.php";

    if ($currentUriIsRoot && $core->CurrentUserIsAuthorized()) {

        //проверим, что еще не перекидывали,
        //чтобы дать залогиненному возможность заходить на главную
        $redirectToStartFlagName = "REDIRECTED_TO_START_PAGE_ALREADY";

        if (!isset($_COOKIE[$redirectToStartFlagName])) {
            setcookie($redirectToStartFlagName, 1, 0, COOKIE_PATH, COOKIE_DOMEN, COOKIE_HTTPS);
            $core->redirectToStartPage();
        }
    }
}

$core->js = array(
    '' => array('welcome'), // слайды на главной
    'targets' => array('targets'),
    'report' => array('widgets/report.widget', 'models/report.model'),
    'registration' => array('registration'),
    'profile' => array(
		'profile',
        'models/user.model',
        'widgets/profile/profile.widget',
        'widgets/profile/userCurrency.widget',
        'widgets/profile/userIntegrations.widget',
        'widgets/operations/operationReminders.widget'
        ),
    'operation' => array(
		'widgets/operations/operationsJournal.widget',
		'widgets/operations/operationReminders.widget',
		'widgets/operations/operationEdit.widget',
		'models/accounts.model',
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
    'login' => array('welcome', 'login'),
    'info' => array('info'),
    'promo' => array('screens/promoTKS.screen'),
    'integration' => array(
        'registration',
        'login',
        'widgets/profile/userIntegrations.widget',
        'integration/omni',
        'integration/tabs',
        'integration/validator',
        'integration/countries',
        'screens/integration.screen'),
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
require_once dirname(dirname(__FILE__)) . "/sf/lib/vendor/symfony/lib/vendor/swiftmailer/swift_required.php";

// Если это продуктив, то используем для отправки писем - sendmail
if (defined('ENVIRONMENT') && ENVIRONMENT == 'prod') {
    // sendmail
    $mailTransport = Swift_SendmailTransport::newInstance('/usr/sbin/sendmail -bs');

//    // mail
//    $transport = Swift_MailTransport::newInstance();

//    // smtp
//    $mailTransport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, 'ssl')
//        //->setUsername('info@easyfinance.ru')
//        //->setPassword('j2df32nD3l7sFa2');
//        ->setUsername('support@easyfinance.ru')
//        ->setPassword('7uN3BN6t');
} else {

    if (MAIL_ENABLED) {
        // mail
        $mailTransport = Swift_MailTransport::newInstance();

    } else {
        // Заглушка для почты
        require_once(dirname(__FILE__).'/../tests/unit/TestMailInvokerStub.php');

        $invoker = new TestMailInvokerStub;
        Swift_DependencyContainer::getInstance()
            ->register('transport.mailinvoker')
            ->asValue($invoker);
        $mailTransport = Swift_MailTransport::newInstance();

    }

}

if (!empty($_SERVER['HTTP_HOST']) && ($_SERVER['HTTP_HOST'] == 'easyfinance.ru' || $_SERVER['HTTP_HOST'] == 'rc.easyfinance.ru')) {
    sfConfig::set('mailCardAMT', 'card.statement@amtbank.com');
} else {
    sfConfig::set('mailCardAMT', 'devel_test@easyfinance.ru');
}

$core->mailer = Swift_Mailer::newInstance($mailTransport);
