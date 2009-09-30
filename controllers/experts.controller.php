<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля экспертов
 * @copyright http://home-money.ru/
 * @version SVN $Id: accounts.controller.php 232 2009-08-21 14:34:45Z rewle $
 */

class Experts_Controller extends Template_Controller
{

    /**
     * Ссылка на класс Smarty
     * @var <Smarty>
     */
    private $tpl = null;

    /**
     * Ссылка на класс модель
     * @var <Accounts_Model>
     */
    private $model = null;

    /**
     * Конструктор класса
     * @return void
     */
    function __construct()
    {
        $this->tpl   = Core::getInstance()->tpl;
        $this->model = new Experts_Model();
    }

    /**
     * Индексная страница
     * @return void
     */
    function index()
    {
        $this->model->index();

        $this->tpl->assign('name_page', 'experts/experts');

        $this->tpl->assign('desktop',($this->model->get_desktop()));//main div class
        $js_list = $this->model->js_list();//array js with indeficators;



    }
//////////////////////////////////experts///////////////////////////////////////

    function get_desktop_fields()//only experts//todo after dl
    {
        return false;
    }

    /** обновляет значения для панельки эксперта
     *
     */
    function get_desktop_field()
    {
        $field_id = $_POST['field_id'];
        if (!$field_id)
            die();
        die(json_encode($this->model->get_desktop_field($field_id)));
    }

    /** редактирование профиля эксперта
     *
     */
    function update_expert()
    {
        $param['mini_desc'] = $_POST['mini_desc'];//str
        $param['description'] = $_POST['description'];//str
        $param['services'] = $_POST['services'];////array
        $param['themes'] = $_POST['themes'];//array
        $param['sertificat'] = $_POST['sertificat'];//str
        die($this->model->update_expert());
    }

/////////////////////////////////users//////////////////////////////////////////

    /*
     * список всех экспертов с возможностью сортировки
     * @todo возможно стоит хранить этот список в куки?будующее
     */
    function get_experts_list()//expert light info//only user
    {
        $order = $_POST['order'];
        if (!$order)
            $order = 'id';
        die(json_encode($this->model->get_experts_list($order)));
    }

    /*
     * полная инфа о эксперте.возможна сортировка.
     */
    function get_expert()//expert foolinfo
    {
        $expert_id = $_POST['expert_id'];
        if (!$expert_id)
            die();
        die(json_encode($this->model->get_expert($expert_id)));
    }

    function add()//reit question etc//@todo after mail/
    {

    }
}