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

        $this->model->loadUserTree();
        $this->model->loadSumCategories($sys_currency, $date['start'], $date['finish']);

        $this->tpl->assign("categories", $this->model->tree);
        $this->tpl->assign("sys_categories", $this->model->system_categories);
        $this->tpl->assign("template", "default");
    }

    /**
     * Создаёт новую категорию
     * @param $args array mixed
     * @return void
     */
    function add($args)
    {
        $category['user_id']            = Core::getInstance()->user->getId();
        $category['cat_name']           = htmlspecialchars(@$_POST['name']);
        $category['type']               = htmlspecialchars(@$_POST['type']);
        $category['cat_parent']         = htmlspecialchars(@$_POST['parent']);
        $category['system_category_id'] = (int)@$_POST['system'];
        $category['cat_id']             = (int)@$_POST['category_id'];
        $category['visible']            = 1;
        $category['cat_active']         = 1;
        $category['often']              = htmlspecialchars(@$_POST['often']);

        if (!empty($category['cat_id'])) {
            $this->model->updateCategory($category);
        } elseif (!$this->model->createNewCategory($category)) {
            $this->tpl->assign("error", "Категория не добавлена");
        }
        $this->model->loadUserTree();
        $this->model->loadSumCategories($sys_currency); //FIXME Откуда она взялась? Из конфига?

        $this->tpl->assign("categories", $this->model->tree);
        $this->tpl->assign("sys_categories", $this->model->system_categories);
        die ($this->tpl->fetch("categories/categories.list.html"));
    }

    /**
     * Редактирует категорию
     * @param $args array mixed
     * @return void
     */
    function edit($args)
    {
        $id = (int)$_POST['id'];

        $edit = $this->model->selectCategoryId($id);
        $this->tpl->assign("edit", $edit);

        if (count($edit) == 0) {
            die('<div class="error">Похоже что у вас нет прав для редактирования этой категории</div>');
        } else {
            $this->tpl->assign("categories", $this->model->tree);
            $this->tpl->assign("sys_categories", $this->model->system_categories);
            die ($this->tpl->fetch("categories/categories.block_create.html"));
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
        if (!$this->model->deleteCategory($id)) {
            $this->tpl->assign("error", "Категория не удалена");
        }
        $this->model->loadUserTree();
        //FIXME не ясно что делать с $sys_currency
        $this->model->loadSumCategories($sys_currency);

        $this->tpl->assign("categories", $cc->tree);
        die ($this->tpl->fetch("categories/categories.list.html"));
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