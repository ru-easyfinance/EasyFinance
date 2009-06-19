<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля logi
 * @copyright http://home-money.ru/
 * SVN $Id$
 */
class Category_Controller extends Template_Controller
{
    private $model = null;
    private $tpl = null;

    /**
     * Конструктор класса
     * @return void
     */
    function __construct()
    {
        $this->tpl = Core::getInstance()->tpl;
        $this->tpl->assign('name_page', 'categories/categories');
        $this->model = new Category_Model();
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
        $param = "and m.date > '".$date['start']."' and m.date < '".$date['finish']."'";

        $this->model->loadUserTree();
        $this->model->loadSumCategories($sys_currency, $param);

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

    function del ($args)
    {
        $id = html($_GET['id']);
        if (!$cc->deleteCategory($id, &$dbs))
        {
            $tpl->assign("error", "Категория не удалена");
        }
        $cc->loadUserTree();
        $cc->loadSumCategories($sys_currency);

        $tpl->assign("categories", $cc->tree);
        die ($tpl->fetch("categories/categories.list.html"));
    }

    function visible ($args)
    {
        $id = html($_GET['id']);
        $visible = html($_GET['visible']);

        if (!$cc->visibleCategory($id, $visible, &$dbs))
        {
            $tpl->assign("error", "Категория не скрыта");
        }
        $cc->loadUserTree();
        $cc->loadSumCategories($sys_currency);

        $tpl->assign("categories", $cc->tree);
        die($tpl->fetch("categories/categories.list.html"));
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