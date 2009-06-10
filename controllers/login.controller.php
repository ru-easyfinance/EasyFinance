<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля logi
 * @copyright http://home-money.ru/
 * SVN $Id$
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
     * @param $args array mixed
     * @return void
     */
    function index($args)
    {
    // FIXME
        $user = Core::getInstance()->user;
        if ($user->getId()) {
        //if (!empty($_SESSION['user'])) {
            header("Location: /account/"); exit;
        } else {
            if (!empty($_POST['login']) && !empty($_POST['pass'])) {

                $login = htmlspecialchars($_POST['login']);
                $pass = md5($_POST['pass']);

                if ($_POST['autoLogin']) {
                    setcookie("autoLogin", $login, time() + 1209600, COOKIE_PATH, COOKIE_DOMEN, COOKIE_HTTPS);
                    setcookie("autoPass", $pass, time() + 1209600, COOKIE_PATH, COOKIE_DOMEN, COOKIE_HTTPS);
                }

                if ($user->initUser($login,$pass)) {

                } else {
//                        $prt->getInsertPeriodic($user->getId());
                    $user->init($user->getId());
                    $user->save($user->getId());
                    if ($_SESSION['template_new'] == 'on') {
                        header("Location: /accounts/"); exit;
                    }else{
                        header("Location: /account/"); exit;
                    }
                }
            }
        }
    }
}