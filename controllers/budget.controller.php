<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для бюджета
 * @copyright http://home-money.ru/
 * @author Max Kamashev <max.kamashev@gmail.com>
 * SVN $Id:  $
 */

class Budget_Controller extends Template_Controller
{
    private $user = null;
    private $tpl = null;
    private $model = null;	

    /**
     * Конструктор класса
     * @return void
     */
    function __construct()
    {
        //header('Location: /targets/'); exit;
        $this->user  = Core::getInstance()->user;
        $this->tpl   = Core::getInstance()->tpl;
        $this->model = new Budget_Model();
        $this->tpl->assign('name_page', 'budget/budget');

        // Операция
        $this->tpl->assign('accounts', Core::getInstance()->user->getUserAccounts());
        $this->tpl->assign('category', get_tree_select());
        $targets = new Targets_Model();
        $this->tpl->assign('targetList', $targets->getLastList(0, 100));

    }

    /**
     * Индексная страница
     * @param $args array mixed
     * @return void
     */
    function index($args)
    {
        
    }

    /**
     * Загрузка бюджета
     * @return void
     */
    function loadBudget()
    {
        list($year,$month,$day) = explode("-", $_GET['current_date']);

        switch ($_GET['month'])
        {
            case "current":
                $current_date = date("Y-m-d", mktime(0, 0, 0, date("m"), "01", date("Y")));
                break;

            case "prev":
                $current_date = date("Y-m-d", mktime(0, 0, 0, $month-1, "01", $year));
                break;
            case "next":
                $current_date = date("Y-m-d", mktime(0, 0, 0, $month+1, "01", $year));
                break;
        }
        $this->tpl->assign("current_date", $current_date);

        $plan = $this->model->getUserPlan($current_date);

        if (count($plan))
        {
            die ($this->tpl->fetch("budget/budget.list.html"));
        }else{
            die ($this->tpl->fetch("budget/budget.empty.html"));
        }
    }

    /**
     * Загрузка бюджета
     * @return void
     */
    function create()
    {
        $categories_income = $this->model->getCategories(1);
        die ($this->tpl->fetch("budget/budget.create_form.html"));
    }
}