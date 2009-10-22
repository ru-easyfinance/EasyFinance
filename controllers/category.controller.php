<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля категорий
 * @category category
 * @copyright http://home-money.ru/
 * @version SVN $Id$
 */
class Category_Controller extends Template_Controller
{
    /**
     * Ссылка на класс модели категории
     * @var Category_Model
     */
    private $model = null;

    /**
     * Ссылка на Шаблон Смарти
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
        $newID = $this->model->add();
        
        if ($newID){
            die ( json_encode(
                array(
                    'id' => $newID,
                )
            ));

        } else {
            die(false);
        }
    }

    /**
     * Редактирует категорию
     * @param $args array mixed
     * @return void
     */
    function edit($args)
    {
        if ($this->model->edit()) {
            die('[]');
        } else {
            die(false);
        }
    }

    /**
     * Удаляет указанную категорию
     * @param $args array mixed
     * @return void
     */
    function del ($args)
    {
        $id = (int)$_POST['id'];
        if ($this->model->del($id)) {
            die('[]');
        } else {
            die(false);
        }
    }

    /**
     * Сдела
     * @deprecated
     * @param $args
     * @return unknown_type
     */
    function visible ($args)
    {
        $id = (int)$_POST['id'];
        $visible = (int)$_POST['visible'];

        if (!$this->model->visibleCategory($id, $visible)) {
            $this->tpl->assign("error", "Категория не скрыта");
        }
        $this->model->loadUserTree();
        $this->model->loadSumCategories($sys_currency);

        $this->tpl->assign("categories", $this->model->tree);
        die($this->tpl->fetch("categories/categories.list.html"));
    }

    /**
     * Возвращает список пользовательских и системных категорий в формате JSON
     * @param array $args
     */
    function getCategory ($args) {
        $date['start'] = date("Y-m-d", mktime(0, 0, 0, date("m"), "01", date("Y")));
        $date['finish'] = date("Y-m-d", mktime(0, 0, 0, date("m")+1, "01", date("Y")));
        $sum = $this->model->loadSumCategories($sys_currency, $date['start'], $date['finish']);

        $users = array();
        foreach (Core::getInstance()->user->getUserCategory() as $val) {
            $users[$val['cat_id']] = array(
                'id'      => $val['cat_id'],
                'parent'  => $val['cat_parent'],
                'system'  => $val['system_category_id'],
                'name'    => $val['cat_name'],
                'type'    => $val['type'],
                'visible' => $val['visible'],
//                'often' => $val['often'],
//                'active' => $val['active']
            );
            if ($val['cat_id'] == $sum['cat_id']) {
                $users[$val['cat_id']]['summ'] = $sum['sum'];
                //$users[$val['cat_id']]['cur'] = Core::
            }
        }
        $systems = $this->model->system_categories;
        $systems[0] = array('id'=>'0','name'=>'Не установлена');
/*
        $systems = array(
            0  => array(
                'id'     => 0,
                'name'   => 'Не установлена',
                'group'  => 0,
                'parent' => 0
            )
        );

        foreach ($this->model->system_categories as $val) {
            $systems[$val['system_category_id']] = array(
                'id'     => $val['system_category_id'],
                'name'   => $val['system_category_name'],
                'group'  => $val['system_group_id'],
                'parent' => $val['parent_id']
            );
        }
  */
        die ( json_encode(
            array(
                'user'=>$users,
                'system'=>$systems)
        ));
    }
    /*
     * возвращает html-строку для категорий. хак вместо show/hide
     */
    function cattypechange($args){
        $type=(int)$_POST['type'];
        die(json_encode($this->model->cattype($type))  );
    }
}