<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для бюджета
 * @copyright http://easyfinance.ru/
 * @author Max Kamashev <max.kamashev@gmail.com>
 * SVN $Id:  $
 */

class Budget_Controller extends _Core_Controller_UserCommon
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
    protected function __init()
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
        $this->tpl->assign('name_page', 'budget/budget');

        // Операция
        $this->tpl->assign('accounts', Core::getInstance()->user->getUserAccounts());
        $this->tpl->assign('category', get_tree_select());
        $targets = new Targets_Model();
        $this->tpl->assign('targetList', $targets->getLastList(0, 100));
    }

    /**
     * Загружает бюджет
     * @return
     */
    function load()
    {
        // Получаем дату начала бюджета
        $start = formatRussianDate2MysqlDate(@$_POST['start']);

        // Вычисляем дату конца бюджета (сейчас ровно до конца месяца начала)
        $end   = date( 'Y-m-d',
            mktime(0, 0, 0, date('m', strtotime($start . ' 00:00:00')) +1, 0)
        );

        if ( ! is_null($start) ) {
            die(
                json_encode(
                    $this->model->loadBudget($start, $end, null, null, Core::getInstance()->user->getUserProps('user_currency_default'))
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
                $budget['d'][$k] = (float)$v;
            }
        }
        foreach ($json->p as $val) {
            foreach ($val as $k => $v) {
                $v = str_replace(' ', '', $v);
                $budget['p'][$k] = (float)$v;
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
        $date  = formatRussianDate2MysqlDate(@$_POST['start']);
        die(json_encode($this->model->edit($type, $id, $value, $date)));
    }

    /**
     * Удаляет категорию в бюджете
     */
    function del()
    {
        $category = (int)@$_POST['id'];
        $date     = formatRussianDate2MysqlDate($_POST['start']);
        $type     = (string)@$_POST['type'];
        die(json_encode($this->model->del($category, $date, $type)));
    }

}
