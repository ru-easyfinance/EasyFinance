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
        $this->tpl->append('js','category.js');
    }

    /**
     * Индексная страница
     * @param $args array mixed
     * @return void
     */
    function index($args)
    {
        $date['start'] = date("Y-m-d", mktime(0, 0, 0, date("m"), "01", date("Y")));
        $date['finish'] = date("Y-m-d", mktime(0, 0, 0, date("m")+1, "01", date("Y")));

        //@FIXME ТА ЕЩЁ ДЫРА!
        $this->model->loadSumCategories($sys_currency, $date['start'], $date['finish']);

        //$this->tpl->assign("category", Core::getInstance()->user->getUserCategory());
        $this->tpl->assign("sys_categories", $this->model->system_categories);
    }

    /**
     * Создаёт новую категорию
     * @param $args array mixed
     * @return void
     */
    function add($args)
    {
        die($this->model->add());
    }

    /**
     * Редактирует категорию
     * @param $args array mixed
     * @return void
     */
    function edit($args)
    {
        die($this->model->edit());
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
     * Сдела
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
        }
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
        
        die ( json_encode(
            array(
                'user'=>$users,
                'system'=>$systems)
        ));
    }

    /**
     * Возвращает форму для создания новой категории
     * @return html
     */
    function reload_block_create()
    {
        $this->tpl->assign("categories", $this->model->tree);
        $this->tpl->assign("sys_categories", $this->model->system_categories);
        die($this->tpl->fetch("categories/categories.block_create.html"));
    }
}