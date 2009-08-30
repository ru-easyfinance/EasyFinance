<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс модели для управления отчётами
 * @category report
 * @author Max Kamashev (ukko) <max.kamashev@gmail.com>
 * @copyright http://home-money.ru/
 * @version SVN $Id$
 */
class Report_Model {
    /**
     * Ссылка на экземпляр DBSimple
     * @var DbSimple_Mysql
     */
    private $db = NULL;

    /**
     * Конструктор
     * @return void
     */
    function  __construct() {
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
    function getPie($drain = 0, $start = '', $end = '', $account = 0) {
        if ($drain == 1) {
            $title = new OFC_Elements_Title('Расход за период с '.@$_GET['dateFrom'].' по '.@$_GET['dateTo']);
        } else {
            $title = new OFC_Elements_Title('Доход за период с '.@$_GET['dateFrom'].' по '.@$_GET['dateTo']);
        }
        if ($account > 0) {
            $sql = "SELECT o.money, IFNULL(c.cat_name, '') AS cat FROM operation o
                LEFT JOIN category c ON c.cat_id = o.cat_id
                WHERE o.user_id = ? AND o.account_id = ? AND o.drain = ?
                GROUP BY o.cat_id";
            $result = $this->db->select($sql, Core::getInstance()->user->getId(), $account, $drain);
        } else {
            $sql = "SELECT o.money, IFNULL(c.cat_name, '') AS cat FROM operation o
                LEFT JOIN category c ON c.cat_id = o.cat_id
                WHERE o.user_id = ? AND o.drain = ?
                GROUP BY o.cat_id";
            $result = $this->db->select($sql, Core::getInstance()->user->getId(), $drain);
        }
        
        $pie = new OFC_Charts_Pie();
        $array = array();
        foreach ($result as $v) {
             $array[]= new OFC_Charts_Pie_Value((float)$v['money'], $v['cat']);
        }
        $pie->values = $array;
//        $pie->type   = 'pie';
        $pie->tip = '#val# из #total#<br>#percent# из 100%';
        $pie->alpha   = 0.6;
        $pie->border  = 2;

        $ofc = new OFC_Chart();
        $ofc->set_title($title);
        $ofc->add_element($pie);
        //$ofc->set_x_axis(null);
        
        return $ofc->toPrettyString();
    }

}