<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля login
 * @category login
 * @copyright http://home-money.ru/
 * @version SVN $Id$
 */
class Login_Controller extends Template_Controller
{

    /**
     * Конструктор класса
     * @return void
     */
    function __construct()
    {
        Core::getInstance()->tpl->assign('name_page', 'login');
    }

    /**
     * Индексная страница
     * @param <array> $args  mixed
     * @return void
     */
    function index($args)
    {
        $user = Core::getInstance()->user;

        if ($user->getId()) {
            //FIXME Если ничего другого в адресной строке не задано, то перенаправлять на кошельки,
            //иначе - на туда, куда просился пользователь
            header("Location: /accounts/");
            exit;
        } else {
            // Пользователь авторизируется через диалог ввода логина и пароля
            if (!empty($_POST['login']) && !empty($_POST['pass'])) {

                $login = htmlspecialchars($_POST['login']);
                $pass = md5($_POST['pass']);

                if ($_POST['autoLogin']) {
                    setcookie("autoLogin", $login, time() + 1209600, COOKIE_PATH, COOKIE_DOMEN, COOKIE_HTTPS);
                    setcookie("autoPass", $pass, time() + 1209600, COOKIE_PATH, COOKIE_DOMEN, COOKIE_HTTPS);
                }

                if ($user->initUser($login,$pass)) {
                    if (count($user->getUserCategory()) == 0) {
                        $model = new Login_Model();
                        $model->activate_user();

                    } else {
                        $periodic = new Periodic_Model();
                        $periodic->getInsertPeriodic();
                        $user->init($user->getId());
                        $user->save($user->getId());
                        header("Location: /accounts/");
                        exit;
                    }
                }
            }
        }
    }
}