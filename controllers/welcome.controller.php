<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля welcome
 * @category welcome
 * @copyright http://easyfinance.ru/
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
        
    }
}
