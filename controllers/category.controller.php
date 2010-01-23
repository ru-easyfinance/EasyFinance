<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля категорий
 * @category category
 * @copyright http://easyfinance.ru/
 * @version SVN $Id$
 */
class Category_Controller extends _Core_Controller_UserCommon
{
    /**
     * Ссылка на класс модели категории
     * @var Category_Model
     */
    private $model = null;

    /**
     * Конструктор класса
     * @return void
     */
    protected function __init()
    {
        $this->tpl->assign('name_page', 'category/category');
        $this->model = new Category_Model();
        
    }

    /**
     * Индексная страница
     * @param $args array mixed
     * @return void
     */
    function index($args)
    {
        //$this->tpl->assign("category", Core::getInstance()->user->getUserCategory());
        $this->tpl->assign("sys_categories", $this->model->system_categories);

        // Операция
        $this->tpl->assign('accounts', Core::getInstance()->user->getUserAccounts());
        $this->tpl->assign('category', get_tree_select());
        $targets = new Targets_Model();
        $this->tpl->assign('targetList', $targets->getLastList(0, 100));
    }

    /**
     * Создаёт новую категорию
     * @param $args array mixed
     * @return void
     */
    function add($args)
    {
        $name   = htmlspecialchars(@$_POST['name']);
        $parent = (int)@$_POST['parent'];
        $system = (int)@$_POST['system'];
        $type   = (int)@$_POST['type'];

        die(json_encode($this->model->add($name, $parent, $system, $type)));
    }

    /**
     * Редактирует категорию
     * @param $args array mixed
     * @return void
     */
    function edit($args)
    {
        $id     = (int)@$_POST['id'];
        $name   = htmlspecialchars(@$_POST['name']);
        $parent = (int)@$_POST['parent'];
        $system = (int)@$_POST['system'];
        $type   = (int)@$_POST['type'];

        die(json_encode($this->model->edit($id, $name, $parent, $system, $type)));
    }

    /**
     * Удаляет указанную категорию
     * @param $args array mixed
     * @return void
     */
    function del ($args)
    {
        $id = (int)$_POST['id'];
        die(json_encode($this->model->del($id)));
    }

     /**
     * Возвращает список пользовательских и системных категорий в формате JSON
     * @param array $args
     */
    function getCategory ($args) {
        $start = date("Y-m-d", mktime(0, 0, 0, date("m"), "01", date("Y")));
        $finish = date("Y-m-d", mktime(0, 0, 0, date("m")+1, "01", date("Y")));

        return die(json_encode($this->model->getCategory($start, $finish)));
    }

    /**
     * Возвращает html-строку для категорий. хак вместо show/hide
     * @deprecated
     */
    function cattypechange($args){
        $type=(int)$_POST['type'];
        die(json_encode($this->model->cattype($type))  );
    }
}