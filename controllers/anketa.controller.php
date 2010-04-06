<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для анкетирования
 * @copyright http://easyfinance.ru/
 */

class Anketa_Controller extends _Core_Controller_UserCommon
{
    /**
     * Конструктор класса
     * @return void
     */
    function __init()
    {
        $this->tpl->assign('name_page', 'anketa');
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
     * Анкета АМТ
     */
    function amt()
    {
        $this->tpl->assign('anketa', $anketa);
    }
}