<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля финансовых целей
 * @category targets
 * @copyright http://home-money.ru/
 * @version SVN $Id$
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
        $this->tpl->assign('pop_list_targets', $this->model->getPopList());
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
        // Если мы успешно отредактировали цель, то перекидываем на список целей
        if ($this->model->edit()) {
            header("Location: /targets/");
            exit();
        } else {
            $this->tpl->assign('action','edit');
            $this->tpl->assign('template','form');
        }
    }
    
    /**
     * Удаляет выбранное событие
     * @param $args array mixed Какие-нибудь аргументы
     * @return <void>
     */
    function del($args)
    {
        // @FIXME Переписать логику
        $this->model->del();
        header("Location: /targets/");
        exit();
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