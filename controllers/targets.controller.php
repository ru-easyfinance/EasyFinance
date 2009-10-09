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
        $this->tpl->assign('user_list_targets', $this->model->getLastList(0,6));
        $this->tpl->assign('pop_list_targets', $this->model->getPopList());     
        $this->tpl->assign('category',get_tree_select());
        $this->tpl->assign('accounts',Core::getInstance()->user->getUserAccounts());
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
        die(json_encode($this->model->add()));
    }

    /**
     * Редактирует событие
     * @param $args array mixed Какие-нибудь аргументы
     * @return void
     */
    function edit($args)
    {
        die(json_encode($this->model->edit()));
    }
    
    /**
     * Удаляет выбранное событие
     * @param $args array mixed Какие-нибудь аргументы
     * @return void
     */
    function del($args)
    {
        $id = abs((int)$_POST['id']);
        //die(json_encode($this->model->del($id)));
        die(json_encode($this->model->delTarget($id)));
    }

    /**
     * Возвращает список пользовательских целей
     */
    function user_list()
    {
        die(json_encode($this->model->getLastList(0, 100)));
    }

    /**
     * Список популярных целей
     */
    function pop_list($args)
    {
        $index = (int)$args[0];
        die(json_encode($this->model->getPopList($index)));
    }
}