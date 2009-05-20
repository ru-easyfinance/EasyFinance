<?
/**
 * file: login.php
 * author: Roman Korostov
 * date: 23/01/07
 **/

$tpl->assign('name_page', 'login');

//$user->deleteOldRegisterRecord();

if (!empty($_SESSION['user'])) {
    header("Location: index.php?modules=account");
    exit();
} else {
    if (!empty($p_login) && !empty($p_pass)) {
        $login = html($p_login);
        $pass = md5($p_pass);

        if ($_POST['autoLogin']) {
            setcookie("autoLogin", $login, time() + 1209600);
            setcookie("autoPass", $pass, time() + 1209600);
        }

        if ($user->initUser($login,$pass)) {
            if (empty($_SESSION['user_category'])) {
                if($user->getCategory($user->getId())) {
                    $message = "
					<html><head><title>Успешная регистрация на сайте домашней бухгалтерии Home-Money.ru</title></head>
					<p>
					Здравствуйте, ".$_SESSION['user']['user_name']."!
					</p>
					<p>
					Поздравляем вас с успешным завершением регистрации в системе. Теперь вы можете в любое время войти в систему, введя свой логин и пароль на сайте www.home-money.ru.
					</p>
					<p>Используйте наш сервис для контроля своей домашней бухгалтерии.</p>

					<p>Мы надеемся, что с помощью Home-money.ru Вам будет удобно планировать и контролировать ваш личный и семейный бюджет, принимать на основе объективной информации взвешенные решения, вносить коррективы в свой финансовый план.</p>

					<p>Отслеживайте динамику своих расходов и доходов, анализируйте рациональность личных и семейных трат с сайтом для ведения домашней бухгалтерии Home-money.ru.</p>

					<p>Контролируйте состояние своих финансов и семейный бюджет круглосуточно из любой точки мира – с рабочего места, из кафе, даже из машины. Все, что для этого нужно, – доступ в Интернет и компьютер или мобильный телефон - сервис Home-money.ru  доступен с мобильных телефонов и коммуникаторов (специальная PDA версия). Для доступа в PDA версию просто наберите Home-money.ru в браузере Вашего коммуникатора или телефона (сервис распознает PDA режим автоматически).</p>

					<p>Кроме личного бюджета с помощью Home-Money.ru Вы можете контролировать состояние финансов Вашего малого бизнеса. </p>

					<p>Пожалуйста, ознакомьтесь с несколькими рекомендациями:</p>

					<ul>
					<li>Запомните ваш пароль или сохраните его в надежном месте. В случае, если вы забудете ваш пароль, письмо о его восстановлении придет на этот адрес.</li>
					<li>Если вам понадобится дополнительная информация о работе с системой, воспользуйтесь разделом меню Инструкции.</li>
					<li>Если у вас возникнут какие-то сложности в работе с системой, обратитесь за помощью в службу поддержки support@home-money.ru.</li>
					</ul>

					<p>C уважением,<br>
					Администрация системы Home-money.ru
					</p>
					<p>Email: <a href='mailto:info@home-money.ru'>info@home-money.ru</a><br>
					<a href='http://www.home-money.ru'>www.home-money.ru</a>
					";

                    $headers = "Content-type: text/html; charset=utf-8\n";
                    $headers .= "From: info@home-money.ru\n";
                    $subject = "Успешная регистрация на сайте домашней бухгалтерии Home-Money.ru";
                    mail($_SESSION['user']['user_mail'], $subject, $message, $headers);

                    header("Location: index.php?modules=first_start");
                    exit();
                } else {
                    message_error(GENERAL_ERROR, "Справочник не загружен!");
                }
            } else {
                $prt->getInsertPeriodic($user->getId());
                $user->init($user->getId());
                $user->save($user->getId());
                if ($_SESSION['template_new'] == 'on') {
                    header("Location: index.php?modules=accounts");
                } else {
                    header("Location: index.php?modules=account");
                }
                exit();
            }
        }
    }
}