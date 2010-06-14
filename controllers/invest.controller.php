<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля инвестиционных активов пользователя
 * @copyright http://easyfinance.ru/
 * @version SVN $Id: $
 */

class Invest_Controller extends _Core_Controller_UserCommon
{

    /**
     * Ссылка на класс User
     * @var User
     */
    private $user = null;

    /**
     * Ссылка на класс модель
     * @var Invest_Model
     */
    private $model = null;

    /**
     * Конструктор класса
     * @return void
     */
    function __init()
    {
        $this->user  = Core::getInstance()->user;
        $this->model = new Invest_Model();
        $this->tpl->assign('name_page', 'invest/portfolio');
    }

    /**
     * Индексная страница
     * @param $args array mixed
     * @return void
     */
    function index()
    {
        /*$cur=Core::getInstance()->user->getUserCurrency();
        $cur_k=array_shift($cur);
        $this->tpl->assign("page_title", "account all");
        $this->tpl->assign('accounts', Core::getInstance()->user->getUserAccounts());
        $this->tpl->assign('type_accounts', $this->model->getTypeAccounts());*/
        $this->tpl->assign("template", "default");
        //$this->tpl->assign("cur", json_encode($cur_k['abbr']));

        // Операция
        $this->tpl->assign('category', get_tree_select());
        $targets = new Targets_Model();
        //$this->tpl->assign('targetList', $targets->getLastList(0, 100));
    }
}