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
        $this->model = new Category();
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

        $tpl->assign("categories", $this->model->tree);
        $tpl->assign("sys_categories", $this->model->system_categories);
        $tpl->assign("template", "default");
    }

    /**
     * Создаёт новую категорию
     * @param $args array mixed
     * @return void
     */
    function add($args)
    {
        $category['user_id'] = $_SESSION['user']['user_id'];
        $category['cat_name'] = @html($_GET['name']);
        $category['type'] = @html($_GET['type']);
        $category['cat_parent'] = @html($_GET['parent']);
        $category['system_category_id'] = @html($_GET['system']);
        $category['cat_id'] = @html($_GET['category_id']);
        $category['visible'] = 1;
        $category['cat_active'] = 1;
        $category['often'] = @html($_GET['often']);

        if (!empty($category['cat_id']))
        {
            $cc->updateCategory($category, &$dbs);
        }else{
            if (!$cc->createNewCategory($category, &$dbs))
            {
                $tpl->assign("error", "Категория не добавлена");
            }
        }
        $cc->loadUserTree();
        $cc->loadSumCategories($sys_currency);

        $tpl->assign("categories", $cc->tree);
        $tpl->assign("sys_categories", $cc->system_categories);
        echo $tpl->fetch("categories/categories.list.html");
    }

    /**
     * Редактирует категорию
     * @param $args array mixed
     * @return void
     */
    function edit($args)
    {
        $id = html($_GET['id']);

        $edit = $cc->selectCategoryId($id, &$dbs);

        $tpl->assign("edit", $edit[0]);
        $tpl->assign("categories", $cc->tree);
        $tpl->assign("sys_categories", $cc->system_categories);
        echo $tpl->fetch("categories/categories.block_create.html");
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
        echo $tpl->fetch("categories/categories.list.html");
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
        echo $tpl->fetch("categories/categories.list.html");
        exit;
    }
//// Если запрос пришел не от аякс
//if (!isset($_GET['ajax']) || !$_GET['ajax']) {
//	include(SYS_DIR_MOD."categories/categories.module.php");
//} else {
//	//include(SYS_DIR_MOD."categories/ajax/categories.ajax.php");
//	include(SYS_DIR_MOD."categories/categories.module.php");
//}
}