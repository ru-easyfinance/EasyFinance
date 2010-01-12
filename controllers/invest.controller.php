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
}