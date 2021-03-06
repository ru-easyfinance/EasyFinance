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
            'graph_loss'                => 'Расходы - итоги',
            'txt_loss'                  => 'Расходы - детальные',
            'txt_loss_difference'       => 'Расходы - сравнение за периоды',
            'matrix_loss'               => 'Расходы - по категориям и меткам',
            'graph_profit'              => 'Доходы &nbsp;&nbsp;- итоги',
            'txt_profit'                => 'Доходы &nbsp;&nbsp;- детальные',
            'txt_profit_difference'     => 'Доходы &nbsp;&nbsp;- сравнение за периоды',
            'matrix_profit'             => 'Доходы &nbsp;&nbsp;- по категориям и меткам',
            'graph_profit_loss'         => 'Сравнение расходов и доходов',
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

        //@FIXME Отвязать от смарти. Пусть данные берутся из res
        $this->tpl->assign('accounts', Core::getInstance()->user->getUserAccounts());
        $this->tpl->assign('currency', Core::getInstance()->user->getUserCurrency());

        $this->tpl->assign('dateFrom', date('01.m.Y'));
        $this->tpl->assign('dateTo',   date(date('t').'.m.Y'));

        $lastMonth = date('m') - 1;
        if ($lastMonth == 0) {
            $lastMonth = 12;
        }

        $lastYear = ($lastMonth != 12) ? date('Y') : date('Y') - 1;

        $monthYear = $lastMonth . '.' . $lastYear;

        $this->tpl->assign('dateFrom2', '01' . '.' . $monthYear);
        $this->tpl->assign('dateTo2', cal_days_in_month(CAL_GREGORIAN, $lastMonth, $lastYear) . '.' . $monthYear);
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
        $acclist = @$_GET['acclist'];

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
                $this->_renderJson(
                    $this->_model->getPie(1, $start, $end, $accounts, $currency)
                );
            case 'graph_loss':   // Расходы
                $this->_renderJson(
                    $this->_model->getPie(0, $start, $end, $accounts, $currency)
                );
            case 'graph_profit_loss': //Сравнение расходов и доходов
                $this->_renderJson(
                    $this->_model->getBars($start, $end, $accounts, $currency)
                );
            case 'txt_profit': //Детальные доходы
                $this->_renderJson(
                    $this->_model->SelectDetailed(1, $start, $end, $accounts, $currency)
                );
            case 'txt_loss': //Детальные расходы
                $this->_renderJson(
                    $this->_model->SelectDetailed(0, $start, $end, $accounts, $currency)
                );
            case 'txt_loss_difference': //Сравнение расходов за периоды
                $this->_renderJson(
                    $this->_model->CompareForPeriods(0, $start, $end, $start2, $end2, $accounts, $currency)
                );
            case 'txt_profit_difference': //Сравнение доходов за периоды
                $this->_renderJson(
                    $this->_model->CompareForPeriods(1, $start, $end, $start2, $end2, $accounts, $currency)
                );
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


    /**
     * Выводит в браузер json строку
     *
     * @param $data Переменная произвольного типа, которая будет преобразована в JSON
     * @return void
     */
    private function _renderJson($data) {
        die(json_encode($data));
    }
}
