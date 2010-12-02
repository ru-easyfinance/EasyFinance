<?php

define ('INDEX', true);

// Подключаем почту
require_once dirname(dirname(__FILE__)) . "/core/external/Swift/swift_required.php";
require_once dirname(dirname(__FILE__)) . "/include/config.php";

//$mailTransport = Swift_SendmailTransport::newInstance('/usr/sbin/sendmail -bs');
//$mailTransport = Swift_MailTransport::newInstance();
// smtp
$mailTransport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, 'ssl')
	->setUsername('info@easyfinance.ru')
	->setPassword('j2df32nD3l7sFa2');
	//->setUsername('support@easyfinance.ru')
	//->setPassword('7uN3BN6t');

$mailer = Swift_Mailer::newInstance( $mailTransport );
$logger = new Swift_Plugins_Loggers_EchoLogger();
$mailer->registerPlugin(new Swift_Plugins_LoggerPlugin( $logger ));


// Получаем список пользователей, которым нужно отправить регистрацию

// Дата от которой нужно получить список пользователей
$date = "2009-02-12";


$dsn      = 'mysql:dbname='.SYS_DB_BASE.';host=127.0.0.1';
$user     = SYS_DB_USER;
$password = SYS_DB_PASS;

$mysql = mysql_connect(SYS_DB_HOST, SYS_DB_USER, SYS_DB_PASS);
mysql_select_db(SYS_DB_BASE, $mysql);

$ids = '';
$result = mysql_query("SELECT * FROM users WHERE user_active=0 AND user_created >= '{$date}';", $mysql);
while ($unUser = mysql_fetch_array($result, MYSQL_ASSOC)) {

    $body = "\nЗдравствуйте, {$unUser['user_name']}!\n
        Ваш e-mail был указан при регистрации в системе.\n\n

        Ваша учётная запись была активирована.\n
        Для входа в систему используйте логин: {$unUser['user_login']}\n\n

        C уважением,\n
        Администрация системы EasyFinance
        http://easyfin.ru/ \n\n";

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

    $mailer->send($message);

    if ( !empty ($ids)) { $ids .= ','; }
    $ids .= $unUser['id'];

    mysql_query("UPDATE users SET user_active=1 , user_new=0 WHERE id IN ( {$ids} )", $mysql);
}

echo $logger->dump();
mysql_close($mysql);