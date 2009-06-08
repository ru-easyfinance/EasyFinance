<?
/**
 * Класс контроллера для модуля welcome
 * @copyright http://home-money.ru/
 * SVN $Id$
 */
class Welcome_Controller extends Template_Controller {

    /**
     * Страница по умолчанию без параметров
     * @return void
     */
    function index() {
        $tpl = Core::getInstance()->tpl;

        $welcome = new Welcome_Model();
        $count = 800 + $welcome->getCountUsers();
        $transaction = $welcome->getAllTransaction();

        $tpl->assign('name_page', 'welcome');
        $tpl->assign("user_count", $count);
        $tpl->assign("money_count", $transaction);
        $tpl->assign('sys_currency', $sys_currency); // XXX

        $welcome->getAtricles();
        $tpl->assign('articles', $row);

        if ($_GET['wish'] == 'ok') {
            $tpl->assign('wish', 'Спасибо за отзыв!');
        }

        if(!empty($_POST['email']) && !empty($_POST['captcha'])) {
            $welcome->sendFeedBack();
        }

//        if (!empty($_COOKIE[COOKIE_NAME]) && !empty($_COOKIE[COOKIE_VAL]) && empty($_SESSION['user'])) {
//            if ($user->initUser(html($_COOKIE['autoLogin']), html($_COOKIE['autoPass']))) {
//                header("Location: https://www.home-money.ru/index.php?modules=account");
//            }
//        }

    }

}
