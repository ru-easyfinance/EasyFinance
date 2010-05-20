<?php if (!defined('INDEX')) trigger_error("Index required!", E_USER_WARNING);
/**
 * Класс контроллера для модуля отчётов
 * @category report
 * @copyright http://easyfinance.ru/
 * @version SVN $Id$
 */
class Report_Controller extends _Core_Controller_UserCommon
{
    /**
     * Модель класса календарь
     * @var Report_Model
     */
    private $_model = null;

    private $_user = NULL;

    private $_reports = array();
    
    /**
     * Конструктор класса
     * @return void
     */
    protected function __init()
    {
        $this->_user = Core::getInstance()->user;
        $this->tpl->assign('name_page', 'report/report');
        $this->_model = new Report_Model();

        // Виды и названия отчетов
        $this->_reports = array(
            'graph_profit'              => 'Доходы',
            'graph_loss'                => 'Расходы',
            'graph_profit_loss'         => 'Сравнение расходов и доходов',
            'txt_profit'                => 'Детальные доходы',
            'txt_loss'                  => 'Детальные расходы',
            'txt_loss_difference'       => 'Сравнение расходов за периоды',
            'txt_profit_difference'     => 'Сравнение доходов за периоды',
            'txt_profit_avg_difference' => 'Сравнение доходов со средним за периоды',
            'txt_loss_avg_difference'   => 'Сравнение расходов со средним за периоды',
        );
    }

    /**
     * Индексная страница
     * @param $args array mixed
     * @return void
     */
    function index($args)
    {
        $this->tpl->assign('reports', $this->_reports);
        $this->tpl->assign('accounts', Core::getInstance()->user->getUserAccounts());
        $this->tpl->assign('currency', Core::getInstance()->user->getUserCurrency());
        $this->tpl->assign('dateFrom', date('01.m.Y'));
        $this->tpl->assign('dateTo',   date(date('t').'.m.Y'));

        $lastmonth = date('m') - 1;
        if ($lastmonth == 0) {
            $lastmonth = 12;
        }

        $days = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
        $this->tpl->assign('dateFrom2', '01'.'.'.$lastmonth.'.'.( ($lastmonth!=12)?date('Y'):date('Y')-1 ) );
        $this->tpl->assign('dateTo2',   $days[$lastmonth-1].'.'.$lastmonth.'.'.( ($lastmonth!=12)?date('Y'):date('Y')-1 ));
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

        if (!empty ($account)) {
            $accounts = $account;
        } else {
            $accounts = '';
            $acc = explode(',', $_GET['acclist']);
            foreach($acc as $value) {
                if ((int)$value > 0) {
                    if (! empty($accounts)) {
                        $accounts .= ',';
                    }
                    $accounts .= (int)$value;
                }
            }
        }

        switch ($report) {
            case 'graph_profit': //Доходы
                die(json_encode($this->_model->getPie(0, $start, $end, $accounts, $currency)));
                break;
            case 'graph_loss':   // Расходы
                die(json_encode($this->_model->getPie(1, $start, $end, $accounts, $currency)));
                break;
            case 'graph_profit_loss': //Сравнение расходов и доходов
                die(json_encode($this->_model->getBars($start, $end, $accounts, $currency)));
                break;
            case 'txt_profit': //Детальные доходы
                die(json_encode($this->_model->SelectDetailed(0, $start, $end, $accounts, $currency)));
                break;
            case 'txt_loss': //Детальные расходы
                die(json_encode($this->_model->SelectDetailed(1, $start, $end, $accounts, $currency)));
                break;
            case 'txt_loss_difference': //Сравнение расходов за периоды
                die(json_encode($this->_model->CompareWaste($start, $end, $start2, $end2, $account, $currency, $acclist))  );
                break;
            case 'txt_profit_difference': //Сравнение доходов за периоды
                die(json_encode($this->_model->CompareIncome($start, $end, $start2, $end2, $account, $currency, $acclist))  );
                break;
            case 'txt_profit_avg_difference': //Сравнение доходов со средним за периоды
                die(json_encode($this->_model->AverageIncome($start, $end, $start2, $end2, $account, $currency, $acclist))  );
                break;
            case 'txt_loss_avg_difference': //Сравнение расходов со средним за периоды
                die(json_encode($this->_model->AverageWaste($start, $end, $start2, $end2, $account, $currency, $acclist))  );
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