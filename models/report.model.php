<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс модели для управления отчётами
 * @category report
 * @author Max Kamashev (ukko) <max.kamashev@gmail.com>
 * @copyright http://home-money.ru/
 * @version SVN $Id$
 */
class Report_Model
{
    /**
     * Ссылка на экземпляр DBSimple
     * @var DbSimple_Mysql
     */
    private $db = NULL;

    /**
     * Конструктор
     * @return void
     */
    function  __construct()
    {
        $this->db   = Core::getInstance()->db;
        require_once 'OFC/OFC_Chart.php';
    }

    /**
     * Возвращает сформированный JSON для круговой диаграммы
     * @see http://teethgrinder.co.uk/open-flash-chart-2/pie-chart.php
     * @param int $drain 0 - доход, 1 - расход
     * @param string timestamp $start
     * @param string timestamp $end
     * @return json
     */
    function getPie($drain = 0, $start = '', $end = '', $account = 0)
    {
        if ($drain == 1) {
            $title = new OFC_Elements_Title('Расходы за период с '.@$_GET['dateFrom'].' по '.@$_GET['dateTo']);
        } else {
            $title = new OFC_Elements_Title('Доходы за период с '.@$_GET['dateFrom'].' по '.@$_GET['dateTo']);
        }
        if ($account > 0) {
            $sql = "SELECT o.money, IFNULL(c.cat_name, '') AS cat FROM operation o
                LEFT JOIN category c ON c.cat_id = o.cat_id
                WHERE o.user_id = ? AND o.account_id = ? AND o.drain = ?
                    AND `date` BETWEEN ? AND ?
                GROUP BY o.cat_id";
            $result = $this->db->select($sql, Core::getInstance()->user->getId(), 
                $account, $drain, $start, $end);
        } else {
            $sql = "SELECT o.money, IFNULL(c.cat_name, '') AS cat FROM operation o
                LEFT JOIN category c ON c.cat_id = o.cat_id
                WHERE o.user_id = ? AND o.drain = ?
                    AND `date` BETWEEN ? AND ?
                GROUP BY o.cat_id";
            $result = $this->db->select($sql, Core::getInstance()->user->getId(), 
                $drain, $start, $end);
        }
        
        $pie = new OFC_Charts_Pie();
        $array = array();
        foreach ($result as $v) {
             $array[]= new OFC_Charts_Pie_Value((float)$v['money'], $v['cat']);
        }
        $pie->values = $array;
        $pie->tip = '#label# #val# из #total#<br>#percent# из 100%';
        $pie->alpha   = 0.6;
        $pie->border  = 2;

        $ofc = new OFC_Chart();
        $ofc->set_title($title);
        $ofc->add_element($pie);
        //$ofc->set_x_axis(null);
        
        return $ofc->toPrettyString();
    }

    /**
     * Возвращает сформированный JSON для двойной диаграммы
     * @see http://teethgrinder.co.uk/open-flash-chart-2/bar-2-bars.php
     */
    function getBars($start = '', $end = '', $account=0)
    {
//$data = array(9,8,7,6,5,4,3,2,1);
//$bar = new bar_glass();
//$bar->colour( '#BF3B69');
//$bar->key('Last year', 12);
//$bar->set_values( $data );
//
//$data2 = array(10,9,8,7,6,5,4,3,2);
//$bar2 = new bar_glass();
//$bar2->colour( '#5E0722' );
//$bar2->key('This year', 12);
//$bar2->set_values( $data2 );
//
//$chart = new open_flash_chart();
//$chart->set_title( $title );
//$chart->add_element( $bar );
//$chart->add_element( $bar2 );

        if ($account > 0) {
            $sql = "SELECT money, DATE_FORMAT(`date`,'%Y-%m') as `datef`, drain
                FROM operation o GROUP BY drain, `datef`
                WHERE user_id = ? AND `date` BETWEEN ? AND ? AND account_id = ?";
            $result = $this->db->select($sql, Core::getInstance()->user->getId(), $start, $end, $account);
        } else {
            $sql = "SELECT money, DATE_FORMAT(`date`,'%Y-%m') as `datef`, drain
                FROM operation o GROUP BY drain, `datef`
                WHERE user_id = ? AND `date` BETWEEN ? AND ?";
            $result = $this->db->select($sql, Core::getInstance()->user->getId(), $start, $end);
        }
        $title = new OFC_Elements_Title('Сравнение расходов и доходов за период с '.
            @$_GET['dateFrom'].' по '.@$_GET['dateTo']);
        $drain = $profit = array();
        foreach ($result as $v) {
            if ($v['drain'] == 0) { //Доход
                //$v['money']
            } else {

            }
        }
        $bar1 = new OFC_Charts_Bar();
        $bar1->set_colour('#BF3B69');
        $bar1->set_key('Расходы', 12);
        $bar1->set_values($v1);
        $bar2 = new OFC_Charts_Bar();
        $bar2->set_colour('#5E0722');
        $bar2->set_key('Доходы', 12);
        $bar1->set_values($v2);

        $ofc = new OFC_Chart();
        $ofc->set_title($title);
        $ofc->add_element($bar1);
        $ofc->add_element($bar2);
        return $ofc->toPrettyString();
    }
}