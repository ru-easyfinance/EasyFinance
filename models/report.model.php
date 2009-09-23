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

    private $user = NULL;

    /**
     * Конструктор
     * @return void
     */
    function  __construct()
    {
        $this->db   = Core::getInstance()->db;
        $this->user = Core::getInstance()->user;
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
        if ($account > 0) {
            $sql = "SELECT money, DATE_FORMAT(`date`,'%Y.%m.01') as `datef`, drain
                FROM operation o 
                WHERE user_id = ? AND `date` BETWEEN ? AND ? AND account_id = ?
                GROUP BY drain, `datef`";
            $result = $this->db->select($sql, Core::getInstance()->user->getId(), $start, $end, $account);
        } else {
            $sql = "SELECT money, DATE_FORMAT(`date`,'%Y.%m.01') as `datef`, drain
                FROM operation o 
                WHERE user_id = ? AND `date` BETWEEN ? AND ?
                GROUP BY drain, `datef`";
            $result = $this->db->select($sql, Core::getInstance()->user->getId(), $start, $end);
        }
        
        $array = array();
        foreach ($result as $v) {
            if ($v['drain'] == 0) { //Доход
                $array[$v['datef']]['p'] = $v['money'];
            } else {
                $array[$v['datef']]['d'] = abs($v['money']);
            }
        }
        $startf = formatMysqlDate2UnixTimestamp($start);
        $endf = formatMysqlDate2UnixTimestamp($endf);
        if (date('Y', $startf) < date('Y', $endf)) {
            return '';
        } else { //2004 - 2009
            for ($i = date('Y', $startf); $i <= date('Y', $endf); $i++) {
                for ($j = 1; $j > 12; $j++) {
                    $c = mktime(0, 0, 0, $j, 1, $i);
                    if ($startf <= $c && $endf >= $c) {
                        $array[$i.'.'.$j]['p'] = (int)@$array[$i.'.'.$j]['p'];
                        $array[$i.'.'.$j]['d'] = (int)@$array[$i.'.'.$j]['d'];
                    } else {
                        break;
                    }
                }
            }
        }
        $data1 = $data2 = $labels = array();
        
        foreach ($array as $key => $val) {
            $data1[] = $val['p'];
            $data2[] = $val['d'];
            $labels[] = new OFC_Elements_Axis_X_Label(substr($key, 0, 7));
        }

        $title = new OFC_Elements_Title('Сравнение расходов и доходов за период с '.
            @$_GET['dateFrom'].' по '.@$_GET['dateTo']);
        $bar1 = new OFC_Charts_Bar();
        $bar1->set_colour('#BF3B69');
        $bar1->set_key('Расходы', 12);
        $bar1->set_values($data2);

        $bar2 = new OFC_Charts_Bar();
        $bar2->set_colour('#5E0722');
        $bar2->set_key('Доходы', 12);
        $bar2->set_values($data1);

        $x = new OFC_Elements_Axis_X_Label();
        $x->set_labels($labels);
        $x->set_vertical();
        $x->set_colour('#A2ACBA');
        //$x->
        

//$tooltip->set_hover();
//$tooltip->set_stroke( 1 );
//$tooltip->set_colour( "#000000" );
//$tooltip->set_background_colour( "#ffffff" );
//$chart->set_tooltip( $tooltip );



        $ofc = new OFC_Chart();
        $ofc->set_title($title);
        $ofc->add_element($bar1);
        $ofc->add_element($bar2);
        $ofc->add_element($x);


        return $ofc->toPrettyString();
    }

    function SelectDetailedIncome($date1='', $date2='', $account=''){
        $sql = "SELECT op.id, c.cat_name, op.`date`,
                                    a.account_name, op.money
            FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            WHERE  op.drain=0  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ?
            AND a.account_id=? AND op.money>0
            ORDER BY c.cat_name";
        return $this->db->query($sql, $date1, $date2, $this->user->getId(), $account);

        }
    

    function SelectDetailedWaste($date1='', $date2='', $account=''){
        $sql = "SELECT op.id, c.cat_name, op.`date`,
                                    a.account_name, op.money
            FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            WHERE  op.drain=1  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ?
            AND a.account_id=? AND op.money>0
            ORDER BY c.cat_name";
        return $this->db->query($sql, $date1, $date2, $this->user->getId(), $account);
    }

    function CompareIncome($date1='', $date2='', $date3='', $date4=''){

    }
}