<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля инфопанелей
 * @copyright http://easyfinance.ru/
 * @version SVN $Id:  $
 */
class Info_Controller extends _Core_Controller_UserCommon
{
    /**
     * Модель класса инфо панелей
     * @var Info_Model
     */
    private $info_model = null;

    /**
     * Конструктор класса
     * @return void
     */
    function __init()
    {
        $this->info_model = new Info_Model();
    }


    /**
     * Индексная страница
     * @return void
     */
    function index()
    {
        $this->tpl->assign('name_page', 'info_panel/info_panel');

        //@FIXME Удалить позже
        $this->tpl->assign('accounts', Core::getInstance()->user->getUserAccounts());
    }

    /**
     * Получает данные для тахометров
     * @result void
     */
    function get_data()
    {
        //$date_start = formatRussianDate2MysqlDate($_POST['start']);
        //$date_end = formatRussianDate2MysqlDate($_POST['end']);
        //die(json_encode($this->info_model->tohometrs()));
        die(json_encode($this->info_model->get_data()));
    }
}