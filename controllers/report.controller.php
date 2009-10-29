<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля отчётов
 * @category report
 * @copyright http://easyfinance.ru/
 * @version SVN $Id$
 */
class Report_Controller extends Template_Controller
{
    /**
     * Модель класса календарь
     * @var Report_Model
     */
    private $model = null;

    /**
     * Ссылка на класс Смарти
     * @var Smarty
     */
    private $tpl = null;

    private $user = NULL;

    private $reports = array();
    
    /**
     * Конструктор класса
     * @return void
     */
    function __construct()
    {
        $this->tpl = Core::getInstance()->tpl;
        $this->user = Core::getInstance()->user;
        $this->tpl->assign('name_page', 'report/report');
        $this->model = new Report_Model();

        // Операция
        $this->tpl->assign('category', get_tree_select());
        $targets = new Targets_Model();
        $this->tpl->assign('targetList', $targets->getLastList(0, 100));

        // Виды и названия отчетов
        $this->reports = array(
            'graph_profit' => 'Доходы',
            'graph_loss' => 'Расходы',
            'graph_profit_loss' => 'Сравнение расходов и доходов',//*/
            'txt_profit' => 'Детальные доходы',
            'txt_loss' => 'Детальные расходы',
            'txt_loss_difference' => 'Сравнение расходов за периоды',
            'txt_profit_difference' => 'Сравнение доходов за периоды',
            'txt_profit_avg_difference' => 'Сравнение доходов со средним за периоды',
            'txt_loss_avg_difference' => 'Сравнение расходов со средним за периоды',
        );
    }

    /**
     * Индексная страница
     * @param $args array mixed
     * @return void
     */
    function index($args)
    {
        // JS & CSS


        

        $this->tpl->assign('reports',   $this->reports);
        $this->tpl->assign('accounts',  Core::getInstance()->user->getUserAccounts());
        $this->tpl->assign('currency',  Core::getInstance()->user->getUserCurrency());
        $this->tpl->assign('dateFrom',  date('01.m.Y'));
        $this->tpl->assign('dateTo',    date(date('t').'.m.Y'));
        $this->tpl->assign('dateFrom2', date('01.m.Y'));
        $this->tpl->assign('dateTo2',   date(date('t').'.m.Y'));
    }

    /**
     * 
     */

    function getData()
    {
        $report  = trim(@$_GET['report']);
        $start   = formatRussianDate2MysqlDate(@$_GET['dateFrom']);
        $end     = formatRussianDate2MysqlDate(@$_GET['dateTo']);
        $start2  = formatRussianDate2MysqlDate(@$_GET['dateFrom2']);
        $end2    = formatRussianDate2MysqlDate(@$_GET['dateTo2']);
        $account = (int)@$_GET['account'];
        $currency= (int)@$_GET['currency'];
        $acclist = $_GET['acclist'];
        //$acclist = explode(',',$accstr);
        //if ($acclist[0] == 32)break;
        switch ($report) {
            case 'graph_profit': //Доходы
                die(json_encode($this->model->getPie(0, $start, $end, $account, $currency, $acclist)));
                break;
            case 'graph_loss':   // Расходы
                die(json_encode($this->model->getPie(1, $start, $end, $account, $currency, $acclist)));
                break;
            case 'graph_profit_loss': //Сравнение расходов и доходов
                die(json_encode($this->model->getBars($start, $end, $account, $currency, $acclist)));
                break;
            case 'txt_profit': //Детальные доходы
                die(json_encode($this->model->SelectDetailedIncome($start, $end, $account, $currency, $acclist))  );
                break;
            case 'txt_loss': //Детальные расходы
                die(json_encode($this->model->SelectDetailedWaste($start, $end, $account, $currency, $acclist))  );
                break;
            case 'txt_loss_difference': //Сравнение расходов за периоды
                die(json_encode($this->model->CompareWaste($start, $end, $start2, $end2, $account, $currency, $acclist))  );
                break;
            case 'txt_profit_difference': //Сравнение доходов за периоды
                die(json_encode($this->model->CompareIncome($start, $end, $start2, $end2, $account, $currency, $acclist))  );
                break;
            case 'txt_profit_avg_difference': //Сравнение доходов со средним за периоды
                die(json_encode($this->model->AverageIncome($start, $end, $start2, $end2, $account, $currency, $acclist))  );
                break;
            case 'txt_loss_avg_difference': //Сравнение расходов со средним за периоды
                die(json_encode($this->model->AverageWaste($start, $end, $start2, $end2, $account, $currency, $acclist))  );
                break;
            default:
                die('
                    "elements": [{
                        "type": "pie",
                        "alpha": 0.6,
                        "start-angle": 35,
                        "animate": [ { "type": "fade" } ],
                        "colours": [ "#1C9E05", "#FF368D" ],
                        "values": [1,2,3,4,5,6,7]
                    }]');
        }


        
    }
}