<?php

if(!defined('INDEX'))
    trigger_error("Index required!", E_USER_WARNING);

/**
 * Класс контроллера для страницы интеграции
 * @copyright http://easyfinance.ru/
 */
class Integration_Controller extends _Core_Controller
{

    /**
     * Ссылка на класс User
     * @var User
     */
    private $_user = null;

    /**
     * Конструктор класса
     * @return void
     */
    function __init()
    {
        $this->_user = Core::getInstance()->user;
        $this->tpl->assign('name_page', 'integration/amt');

    }

    /**
     * Индексная страница
     * @param $args array mixed
     * @return void
     */
    function index()
    {

    }

    /**
     * Привязываем счёт к счёту в банке
     *
     * @return void
     */
    function binding()
    {
        $account_id = 592;

        $debetCard = new Account_DebetCard();

        if ($debetCard->binding($account_id)) {
            die(json_encode(array('result'=>array('text'=>'Счёт успешно привязан'))));
        } else {
            die(json_encode(array('error'=>array('text'=>'Счёт успешно привязан'))));
        }
    }

}
