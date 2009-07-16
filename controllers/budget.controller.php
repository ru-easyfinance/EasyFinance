<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для бюджета
 * @copyright http://home-money.ru/
 * @author Roman Korostov
 * SVN $Id: budget.controller.php 90 2009-07-13 13:33:37Z korogen $
 */

class Budget_Controller extends Template_Controller
{
    private $money = null;
    private $user = null;
    private $tpl = null;
    private $model = null;	

    /**
     * Конструктор класса
     * @return void
     */
    function __construct()
    {
		
        $this->user = Core::getInstance()->user;
        $this->tpl = Core::getInstance()->tpl;
        //$this->model = new Accounts_Model();
        //$this->money = new Money();

        $this->tpl->assign('name_page', 'budget/budget');
    }

    /**
     * Индексная страница
     * @param $args array mixed
     * @return void
     */
    function index($args)
    {
        //TODO Переписать условие, когда сменим инкрементные поля
        if (!$this->user->getId()) {
            header("Location: /");
            exit;
        }

        $this->tpl->assign("page_title", "account all");
		//$this->tpl->assign('accounts', $this->user->initUserAccounts($this->user->getId()));
		//$this->tpl->assign('type_accounts', $this->model->getTypeAccounts());
		//pre($this->user->initUserAccounts($this->user->getId()));
		//$this->tpl->assign("template", "default");
    }
}