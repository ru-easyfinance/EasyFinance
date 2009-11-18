<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для бюджета
 * @copyright http://easyfinance.ru/
 * @author Max Kamashev <max.kamashev@gmail.com>
 * SVN $Id:  $
 */

class Budget_Controller extends Template_Controller
{
    /**
     * Модель бюджета
     * @var Budget_Model
     */
    private $model = null;	

    /**
     * Конструктор класса
     * @return void
     */
    function __construct()
    {
        $this->model = new Budget_Model();
    }

    /**
     * Индексная страница
     * @param $args array mixed
     * @return void
     */
    function index($args)
    {
        Core::getInstance()->tpl->assign('name_page', 'budget/budget');

        // Операция
        Core::getInstance()->tpl->assign('accounts', Core::getInstance()->user->getUserAccounts());
        Core::getInstance()->tpl->assign('category', get_tree_select());
        $targets = new Targets_Model();
        Core::getInstance()->tpl->assign('targetList', $targets->getLastList(0, 100));
    }

    /**
     * Загружает бюджет
     * @return
     */
    function load()
    {
        $start = formatRussianDate2MysqlDate(@$_POST['start']);
        $end   = '';
        if (!is_null($start)) {
            die(
                json_encode(                   
                    $this->model->loadBudget($start, $end)
                )
            );
        } else {
            die('[]');
        }
    }

    /**
     * Добавляет данные из мастера
     * @return void
     */
    function add()
    {
        $start = formatRussianDate2MysqlDate(@$_POST['start']);
        $end   = null;
        $json = json_decode(stripslashes(@$_POST['data']));
        
	$budget = array();
        foreach ($json->d as $val) {
            foreach ($val as $k => $v) {
                $v = str_replace(' ', '', $v);
                if ((float)$v <> 0) {
                    $budget['d'][$k] = (float)$v;
                }
            }
        }
        foreach ($json->p as $val) {
            foreach ($val as $k => $v) {
                $v = str_replace(' ', '', $v);
                if ((float)$v <> 0) {
                    $budget['p'][$k] = (float)$v;
                }
            }
        }
        die(json_encode($this->model->add($budget, $start)));
    }

    /**
     * Редактирует категорию в бюджете
     * @return void
     */
    function edit()
    {
        $type  = trim(@$_POST['type']);
        $id    = abs(@$_POST['id']);
        $value = (float)@$_POST['value'];
        $date  = formatRussianDate2MysqlDate(@$_POST['date']);
        die(json_encode($this->model->edit($type, $id, $value, $date)));
    }

    /**
     * Удаляет категорию в бюджете
     */
    function del()
    {
        $category = (int)@$_POST['category'];
        $date     = formatRussianDate2MysqlDate($_POST['date']);
        $type     = (string)@$_POST['type'];
        die(json_encode($this->model->del($category, $date, $type)));
    }

}
