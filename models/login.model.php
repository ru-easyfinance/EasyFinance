<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс модели для логина
 * @category login
 * @copyright http://home-money.ru/
 * @version SVN $Id$
 */
class Login_Model
{
    /**
     * Активирует нового пользователя
     * @return void
     */
    function activate_user() {
        $user = Core::getInstance()->user;
        if (count($user->getUserCategory()) == 0) {
            if($user->getCategory($user->getId())) {
                $message = "<html><head><title>Успешная регистрация на сайте домашней бухгалтерии Home-Money.ru</title></head>
                <p>Здравствуйте, ".$user->user_props['user_name']."!</p>
                <p>Поздравляем вас с успешным завершением регистрации в системе. Теперь вы можете
                в любое время войти в систему, введя свой логин и пароль на сайте www.home-money.ru.</p>
                <p>Используйте наш сервис для контроля своей домашней бухгалтерии.</p>
                <p>Мы надеемся, что с помощью Home-money.ru Вам будет удобно планировать и контролировать
                ваш личный и семейный бюджет, принимать на основе объективной информации взвешенные решения,
                вносить коррективы в свой финансовый план.</p>
                <p>Отслеживайте динамику своих расходов и доходов, анализируйте рациональность личных и семейных
                трат с сайтом для ведения домашней бухгалтерии Home-money.ru.</p>
                <p>Контролируйте состояние своих финансов и семейный бюджет круглосуточно из любой точки мира –
                с рабочего места, из кафе, даже из машины. Все, что для этого нужно, – доступ в Интернет и
                компьютер или мобильный телефон - сервис Home-money.ru  доступен с мобильных телефонов и
                коммуникаторов (специальная PDA версия). Для доступа в PDA версию просто наберите Home-money.ru
                в браузере Вашего коммуникатора или телефона (сервис распознает PDA режим автоматически).</p>
                <p>Кроме личного бюджета с помощью Home-Money.ru Вы можете контролировать состояние финансов
                Вашего малого бизнеса. </p>
                <p>Пожалуйста, ознакомьтесь с несколькими рекомендациями:</p>
                <ul>
                <li>Запомните ваш пароль или сохраните его в надежном месте. В случае, если вы забудете ваш пароль,
                письмо о его восстановлении придет на этот адрес.</li>
                <li>Если вам понадобится дополнительная информация о работе с системой, воспользуйтесь разделом
                меню Инструкции.</li>
                <li>Если у вас возникнут какие-то сложности в работе с системой, обратитесь за помощью в службу
                поддержки support@home-money.ru.</li>
                </ul>
                <p>C уважением,<br/>Администрация системы Home-money.ru</p>
                <p>Email: <a href='mailto:info@home-money.ru'>info@home-money.ru</a><br/>
                <a href='http://www.home-money.ru'>www.home-money.ru</a>";

                $headers = "Content-type: text/html; charset=utf-8\n";
                $headers .= "From: info@home-money.ru\n";
                $subject = "Успешная регистрация на сайте домашней бухгалтерии Home-Money.ru";
                mail($_SESSION['user']['user_mail'], $subject, $message, $headers);
                header("Location: /accounts/"); exit;
            } else {
                trigger_error("Справочник не загружен!", E_USER_WARNING);
            }
        } else {

        }
    }

    /**
     * Пользователь авторизируется через диалог ввода логина и пароля
     */
    function auth_user() {
        $user = Core::getInstance()->user;
        if (!empty($_POST['login']) && !empty($_POST['pass'])) {
            $login = htmlspecialchars(@$_POST['login']);
            $pass = sha1(@$_POST['pass']);
            if ($user->initUser($login,$pass)) {
                // Шифруем и сохраняем куки
                if (isset($_POST['autoLogin'])) {
                    setcookie(COOKIE_NAME, encrypt(array($login,$pass)), time() + COOKIE_EXPIRE, COOKIE_PATH, COOKIE_DOMEN, COOKIE_HTTPS);
                // Шифруем, но куки теперь сохраняются лишь до конца сессии
                } else {
                    setcookie(COOKIE_NAME, encrypt(array($login,$pass)), time(), COOKIE_PATH, COOKIE_DOMEN, COOKIE_HTTPS);
                }
                // У пользователя нет категорий, т.е. надо помочь ему их создать
                if (count($user->getUserCategory()) == 0) {
                    $model = new Login_Model();
                    $model->activate_user();
                } else {
                    //@FIXME Перенести отсюда создание периодических транзакций и повесить их на крон
                    $periodic = new Periodic_Model();
                    $periodic->getInsertPeriodic();
                    $user->init($user->getId());
                    $user->save($user->getId());
                    // Если у нас есть запись в сессии, куда пользователь хотел попасть, то перенаправляем его туда
                    if (isset($_SESSION['REQUEST_URI'])) {
                        header("Location: ".$_SESSION['REQUEST_URI']);
                        unset($_SESSION['REQUEST_URI']);
                        exit;
                    } else {
                        header("Location: /accounts/");
                        exit;
                    }
                }
            }
        }
    }
}