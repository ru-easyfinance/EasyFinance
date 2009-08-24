<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля welcome
 * @category welcome
 * @copyright http://home-money.ru/
 * @version SVN $Id$
 */
class Welcome_Controller extends Template_Controller {

    /**
     * Страница по умолчанию без параметров
     * @return void
     */

    function index() {
        $tpl = Core::getInstance()->tpl;
        $welcome = new Welcome_Model();

        $tpl->assign('name_page', 'welcome');
        $tpl->assign("user_count", $welcome->getCountUsers());
        $tpl->assign("money_count", $welcome->getAllTransaction());
        $tpl->assign('sys_currency', $sys_currency); // @FIXME Узнать что это за хрень и что туда надо возвращать
        $tpl->assign('articles', $welcome->getAtricles());
        $tpl->append('js','welcome.js');
//        if(!empty($_POST['email']) && !empty($_POST['captcha'])) {
//            $welcome->sendFeedBack();
//        }
    }
}
