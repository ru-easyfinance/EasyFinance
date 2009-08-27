<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля финансовых целей
 * @category targets
 * @copyright http://home-money.ru/
 * @version SVN $Id: targets.controller.php 157 2009-08-12 08:28:16Z rewle $
 */
class Targets_Controller extends Template_Controller
{
    /**
     * Модель класса календарь
     * @var Targets_Model
     */
    private $model = null;

    /**
     * Ссылка на класс Смарти
     * @var Smarty
     */
    private $tpl = null;

    /**
     * Конструктор класса
     * @return void
     */
    function __construct()
    {
        $this->tpl = Core::getInstance()->tpl;
        $this->tpl->assign('name_page', 'targets/targets');
        $this->model = new Targets_Model();
        $this->model->_setFormSelectBoxs();

        $this->tpl->append('css','jquery/jquery.calculator.css');
        $this->tpl->append('css','jquery/south-street/ui.datepicker.css');
        $this->tpl->append('css','jquery/south-street/ui.dialog.css');
        $this->tpl->append('css','jquery/south-street/ui.all.css');

        $this->tpl->append('js','jquery/ui.core.js');
        $this->tpl->append('js','jquery/ui.resizable.js');
        $this->tpl->append('js','jquery/ui.draggable.js');
        $this->tpl->append('js','jquery/ui.dialog.js');
        $this->tpl->append('js','jquery/ui.datepicker.js');
        $this->tpl->append('js','jquery/i18n/ui.datepicker-ru.js');
        $this->tpl->append('js','jquery/jquery.calculator.min.js');
        $this->tpl->append('js','jquery/jquery.calculator-ru.js');
        $this->tpl->append('js','targets.js');
    }

    /**
     * Индексная страница
     * @param $args array mixed
     * @return void
     */
    function index($args)
    {
        // Список ближайших целей пользователя
        $this->tpl->assign('user_list_targets', $this->model->getLastList());

        //Список популярных целей у остальных
        $pop = $this->model->getPopList();
        $this->tpl->assign('pop_list_targets', $pop);
        $this->tpl->assign('template','default');
    }

    /**
     * Возвращает данные о выбранной финансовой цели в формате JSON
     * @param int $id Ид финцели
     */
    function get($id = 0)
    {
        $id = (int)@$id[0];
        die(json_encode($this->model->getTarget($id)));
    }

    /**
     * Добавляет новое событие
     * @param $args array mixed Какие-нибудь аргументы
     * @return void
     */
    function add($args)
    {
        die($this->model->add());
    }

    /**
     * Редактирует событие
     * @param $args array mixed Какие-нибудь аргументы
     * @return <void>
     */
    function edit($args)
    {
        die($this->model->edit());
    }
    
    /**
     * Удаляет выбранное событие
     * @param $args array mixed Какие-нибудь аргументы
     * @return <void>
     */
    function del($args)
    {
        if ($this->model->del()) {
            die('[]');
        } else {
            trigger_error('Не удалось удалить фин.цель', E_USER_NOTICE); exit;
        }
    }

    function user_list()
    {
        $this->tpl->assign('user_list_targets',$this->model->getLastList($index));
        $this->tpl->assign('template','pages.list');
        $this->tpl->assign('view_list','user_list');
    }

    function pop_list()
    {
        $tpl->assign('pop_list_targets',$targets->getPopList($index));
        $tpl->assign('template','pages.list');
        $tpl->assign('view_list','pop_list');
    }
}