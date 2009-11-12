<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля категорий
 * @category category
 * @copyright http://easyfinance.ru/
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
     * Возвращает список пользовательских и системных категорий в формате JSON
     * @param array $args
     */
    function getCategory ($args) {
        $start = date("Y-m-d", mktime(0, 0, 0, date("m"), "01", date("Y")));
        $finish = date("Y-m-d", mktime(0, 0, 0, date("m")+1, "01", date("Y")));

        return die(json_encode($this->model->getCategory($start, $finish)));
    }

    /*
     * Возвращает html-строку для категорий. хак вместо show/hide
     */
    function cattypechange($args){
        $type=(int)$_POST['type'];
        die(json_encode($this->model->cattype($type))  );
    }
}