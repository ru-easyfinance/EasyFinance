<?php

define ('INDEX', true);

// Подключаем почту
require_once dirname(dirname(__FILE__)) . "/core/external/Swift/swift_required.php";
require_once dirname(dirname(__FILE__)) . "/include/config.php";

$mailTransport = Swift_SendmailTransport::newInstance('/usr/sbin/sendmail -bs');

$mailer = Swift_Mailer::newInstance( $mailTransport );

$mailer->registerPlugin(new Swift_Plugins_LoggerPlugin(new Swift_Plugins_Loggers_EchoLogger()));


// Получаем список пользователей, которым нужно отправить регистрацию

// Дата от которой нужно получить список пользователей
$date = "2010-02-12";


$dsn      = 'mysql:dbname='.SYS_DB_BASE.';host=127.0.0.1';
$user     = SYS_DB_USER;
$password = SYS_DB_PASS;

$mysql = mysql_connect(SYS_DB_HOST, SYS_DB_USER, SYS_DB_PASS);
mysql_select_db(SYS_DB_BASE, $mysql);

$unregistredUsers = mysql_query("SELECT * FORM users WHERE user_active=0 AND user_created >= '{$date}';", $mysql);

die(print_r($unregistredUsers));

$ids = '';
foreach ( $unregistredUsers as $k => $unUser ) {
    $body = "Здравствуйте, {$unUser['user_name']}!\n
        Ваш e-mail был указан при регистрации в системе.\n\n

        Ваша учётная запись была активирована.\n
        Для входа в систему используйте логин: {$unUser['user_login']}\n\n

        C уважением,\n
        Администрация системы EasyFinance
        https://easyfin.ru/ \n\n";

    $subject = "Вы зарегистрированы в системе управления личными финансами EasyFinance";

    $message = Swift_Message::newInstance()
        // Заголовок
        ->setSubject( $subject )
        // Указываем "От кого"
        ->setFrom(array('support@easyfinance.ru' => 'EasyFinance.ru'))
        // Говорим "Кому"
        ->setTo( array($unUser['user_mail'] => $unUser['user_login']) )
        // Устанавливаем "Тело"
        ->setBody($body, 'text/plain');
    // Отсылаем письмо

    $result = Core::getInstance()->mailer->send($message);

    if ( !empty ($ids)) { $ids .= ','; }
    $ids .= $unUser['id'];

    $mysqli->query("UPDATE users SET user_active=1 , user_new=0 WHERE id IN ( {$ids} )");
}

echo $logger->dump();