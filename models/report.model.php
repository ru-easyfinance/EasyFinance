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
    function getPie($drain = 0, $start = '', $end = '', $account = 0, $currency=0)
    {

        if ($account > 0) {
            $sql = "SELECT sum(o.money) AS money, cur.cur_char_code, IFNULL(c.cat_name, '') AS cat FROM operation o
                LEFT JOIN accounts a ON a.account_id=o.account_id
                LEFT JOIN category c ON c.cat_id = o.cat_id
                LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
                WHERE o.user_id = ? AND o.account_id = ? AND o.drain = ?
                    AND `date` BETWEEN ? AND ? 
                GROUP BY o.cat_id";//AND a.account_currency_id = ? 
            $result = $this->db->select($sql, Core::getInstance()->user->getId(), 
                $account, $drain, $start, $end/*, $currency*/);

        } else {
            $sql = "SELECT sum(o.money) AS money, cur.cur_char_code, IFNULL(c.cat_name, '') AS cat FROM operation o
                LEFT JOIN accounts a ON a.account_id=o.account_id
                LEFT JOIN category c ON c.cat_id = o.cat_id
                LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
                WHERE o.user_id = ? AND o.drain = ?
                    AND `date` BETWEEN ? AND ? 
                GROUP BY o.cat_id";
            $result = $this->db->select($sql, Core::getInstance()->user->getId(), 
                $drain, $start, $end/*, $currency*/);
        }
        /*foreach ($result as $v){
            $result['cat'] = addslashes($v['cat']);
        }*/
        $arr = array();
        $arr[0] = $result;
        $sql = "SELECT cur_char_code FROM currency
            WHERE cur_id = ?";
        $arr[1] = $this->db->query($sql, $currency);


        return $arr;
        
        //return $result;
        /*$array = array();
        foreach ($result as $v) {
            if ($v['cat'] != '')
             $array[] = array($v['cat'].' &nbsp;&nbsp;&nbsp; '.(float)$v['money'].'&nbsp;'.$v['cur_char_code'], (float)$v['money']);
        }
        return $array;//*/
    }

    /**
     * Возвращает сформированный JSON для двойной диаграммы
     * @see http://teethgrinder.co.uk/open-flash-chart-2/bar-2-bars.php
     */
    function getBars($start = '', $end = '', $account=0, $currency=0)
    {
        /*if ($account > 0) {
            $sql = "SELECT money, DATE_FORMAT(`date`,'%Y.%m.01') as `datef`, drain
                FROM operation o
                LEFT JOIN accounts a ON a.account_id=o.account_id
                WHERE o.user_id = ? AND `date` BETWEEN ? AND ? AND account_id = ? AND a.account_currency_id = ?
                GROUP BY drain, `datef`";
            $result = $this->db->select($sql, Core::getInstance()->user->getId(), $start, $end, $account, $currency);
        } else {
            $sql = "SELECT money, DATE_FORMAT(`date`,'%Y.%m.01') as `datef`, drain
                FROM operation o
                LEFT JOIN accounts a ON a.account_id=o.account_id
                WHERE o.user_id = ? AND `date` BETWEEN ? AND ? AND a.account_currency_id = ?
                GROUP BY drain, `datef`";
            $result = $this->db->select($sql, Core::getInstance()->user->getId(), $start, $end, $currency);
        }*/
        if ($account > 0) {
            $sql = "SELECT sum(money) AS su, cur.cur_char_code AS cu, DATE_FORMAT(`date`,'%Y.%m.01') as `datef`
                FROM operation o
                LEFT JOIN accounts a ON a.account_id=o.account_id
                LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
                WHERE o.user_id = ? AND `date` BETWEEN ? AND ? AND account_id = ? AND drain='1'
                GROUP BY drain, `datef`";
            $result = $this->db->select($sql, Core::getInstance()->user->getId(), $start, $end, $account/*, $currency*/);
        } else {
            $sql = "SELECT sum(money) AS su, cur.cur_char_code AS cu, DATE_FORMAT(`date`,'%Y.%m.01') as `datef`
                FROM operation o
                LEFT JOIN accounts a ON a.account_id=o.account_id
                LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
                WHERE o.user_id = ? AND `date` BETWEEN ? AND ? AND drain='1'
                GROUP BY drain, `datef`";
            $result = $this->db->select($sql, Core::getInstance()->user->getId(), $start, $end/*, $currency*/);
        }
         $i = -1;
        $array=array();

        /*foreach ($result as $v) {
            switch (substr($v['datef'],5,2)){
                case '01' : $array[]=array('Расходы за январь'.' &nbsp;&nbsp;&nbsp;&nbsp; '.abs($v['su']),abs($v['su']));break;
                case '02' : $array[]=array('Расходы за февраль'.' &nbsp;&nbsp;&nbsp; '.abs($v['su']),abs($v['su']));break;
                case '03' : $array[]=array('Расходы за март'.' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; '.abs($v['su']),abs($v['su']));break;
                case '04' : $array[]=array('Расходы за апрель'.' &nbsp;&nbsp;&nbsp;&nbsp; '.abs($v['su']),abs($v['su']));break;
                case '05' : $array[]=array('Расходы за май'.' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; '.abs($v['su']),abs($v['su']));break;
                case '06' : $array[]=array('Расходы за июнь'.' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; '.abs($v['su']),abs($v['su']));break;
                case '07' : $array[]=array('Расходы за июль'.' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; '.abs($v['su']),abs($v['su']));break;
                case '08' : $array[]=array('Расходы за август'.' &nbsp;&nbsp;&nbsp;&nbsp; '.abs($v['su']),abs($v['su']));break;
                case '09' : $array[]=array('Расходы за сентябрь'.' &nbsp; '.abs($v['su']),abs($v['su']));break;
                case '10' : $array[]=array('Расходы за октябрь'.' &nbsp;&nbsp;&nbsp; '.abs($v['su']),abs($v['su']));break;
                case '11' : $array[]=array('Расходы за ноябрь'.' &nbsp;&nbsp;&nbsp;&nbsp; '.abs($v['su']),abs($v['su']));break;
                case '12' : $array[]=array('Расходы за декабрь'.' &nbsp;&nbsp;&nbsp; '.abs($v['su']),abs($v['su']));break;
                default : $array[]=array('Расходы'.' &nbsp;&nbsp;&nbsp; '.abs($v['su']),abs($v['su']));
            }
            //$array[]=array('Расходы'.' &nbsp;&nbsp;&nbsp; '.abs($v['su']),abs($v['su']));
        }*/
        foreach ($result as $v) {
            switch (substr($v['datef'],5,2)){
                case '01' : {
                        $array[++$i]['lab']='Расходы за январь';
                        $array[$i]['sum']=abs($v['su']);
                        $array[$i]['curs']=$v['cu'];
                        break;
                }
                case '02' : {
                        $array[++$i]['lab']='Расходы за февраль';
                        $array[$i]['sum']=abs($v['su']);
                        $array[$i]['curs']=$v['cu'];
                        break;
                }
                case '03' : {
                        $array[++$i]['lab']='Расходы за март';
                        $array[$i]['sum']=abs($v['su']);
                        $array[$i]['curs']=$v['cu'];
                        break;
                }
                case '04' : {
                        $array[++$i]['lab']='Расходы за апрель';
                        $array[$i]['sum']=abs($v['su']);
                        $array[$i]['curs']=$v['cu'];
                        break;
                }
                case '05' : {
                        $array[++$i]['lab']='Расходы за май';
                        $array[$i]['sum']=abs($v['su']);
                        $array[$i]['curs']=$v['cu'];
                        break;
                }
                case '06' : {
                        $array[++$i]['lab']='Расходы за июнь';
                        $array[$i]['sum']=abs($v['su']);
                        $array[$i]['curs']=$v['cu'];
                        break;
                }
                case '07' : {
                        $array[++$i]['lab']='Расходы за июль';
                        $array[$i]['sum']=abs($v['su']);
                        $array[$i]['curs']=$v['cu'];
                        break;
                }
                case '08' : {
                        $array[++$i]['lab']='Расходы за август';
                        $array[$i]['sum']=abs($v['su']);
                        $array[$i]['curs']=$v['cu'];
                        break;
                }
                case '09' : {
                        $array[++$i]['lab']='Расходы за сентябрь';
                        $array[$i]['sum']=abs($v['su']);
                        $array[$i]['curs']=$v['cu'];
                        break;
                }
                case '10' : {
                        $array[++$i]['lab']='Расходы за октябрь';
                        $array[$i]['sum']=abs($v['su']);
                        $array[$i]['curs']=$v['cu'];
                        break;
                }
                case '11' : {
                        $array[++$i]['lab']='Расходы за ноябрь';
                        $array[$i]['sum']=abs($v['su']);
                        $array[$i]['curs']=$v['cu'];
                        break;
                }
                case '12' : {
                        $array[++$i]['lab']='Расходы за декабрь';
                        $array[$i]['sum']=abs($v['su']);
                        $array[$i]['curs']=$v['cu'];
                        break;
                }
                default : {
                        $array[++$i]['lab']='Расходы';
                        $array[$i]['sum']=abs($v['su']);
                        $array[$i]['curs']=$v['cu'];
                }
            }
            //$array[]=array('Расходы'.' &nbsp;&nbsp;&nbsp; '.abs($v['su']),abs($v['su']));
        }
        if ($account > 0) {
            $sql = "SELECT sum(money) AS su, cur.cur_char_code AS cu, DATE_FORMAT(`date`,'%Y.%m.01') as `datef`
                FROM operation o
                LEFT JOIN accounts a ON a.account_id=o.account_id
                LEFT JOIN category c ON c.cat_id = o.cat_id
                LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
                WHERE o.user_id = ? AND `date` BETWEEN ? AND ? AND account_id = ? AND drain='0' AND c.cat_name <> ''
                GROUP BY drain, `datef`";
            $result = $this->db->select($sql, Core::getInstance()->user->getId(), $start, $end, $account/*, $currency*/);
        } else {
            $sql = "SELECT sum(money) AS su, cur.cur_char_code AS cu, DATE_FORMAT(`date`,'%Y.%m.01') as `datef`
                FROM operation o
                LEFT JOIN accounts a ON a.account_id=o.account_id
                LEFT JOIN category c ON c.cat_id = o.cat_id
                LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
                WHERE o.user_id = ? AND `date` BETWEEN ? AND ? AND drain='0' AND c.cat_name <> ''
                GROUP BY drain, `datef`";//AND a.account_currency_id = ?
            $result = $this->db->select($sql, Core::getInstance()->user->getId(), $start, $end/*, $currency*/);
        }
        foreach ($result as $v) {
            switch (substr($v['datef'],5,2)){
                case '01' : {
                        $array[++$i]['lab']='Доходы за январь';
                        $array[$i]['sum']=abs($v['su']);
                        $array[$i]['curs']=$v['cu'];
                        break;
                }
                case '02' : {
                        $array[++$i]['lab']='Доходы за февраль';
                        $array[$i]['sum']=abs($v['su']);
                        $array[$i]['curs']=$v['cu'];
                        break;
                }
                case '03' : {
                        $array[++$i]['lab']='Доходы за март';
                        $array[$i]['sum']=abs($v['su']);
                        $array[$i]['curs']=$v['cu'];
                        break;
                }
                case '04' : {
                        $array[++$i]['lab']='Доходы за апрель';
                        $array[$i]['sum']=abs($v['su']);
                        $array[$i]['curs']=$v['cu'];
                        break;
                }
                case '05' : {
                        $array[++$i]['lab']='Доходы за май';
                        $array[$i]['sum']=abs($v['su']);
                        $array[$i]['curs']=$v['cu'];
                        break;
                }
                case '06' : {
                        $array[++$i]['lab']='Доходы за июнь';
                        $array[$i]['sum']=abs($v['su']);
                        $array[$i]['curs']=$v['cu'];
                        break;
                }
                case '07' : {
                        $array[++$i]['lab']='Доходы за июль';
                        $array[$i]['sum']=abs($v['su']);
                        $array[$i]['curs']=$v['cu'];
                        break;
                }
                case '08' : {
                        $array[++$i]['lab']='Доходы за август';
                        $array[$i]['sum']=abs($v['su']);
                        $array[$i]['curs']=$v['cu'];
                        break;
                }
                case '09' : {
                        $array[++$i]['lab']='Доходы за сентябрь';
                        $array[$i]['sum']=abs($v['su']);
                        $array[$i]['curs']=$v['cu'];
                        break;
                }
                case '10' : {
                        $array[++$i]['lab']='Доходы за октябрь';
                        $array[$i]['sum']=abs($v['su']);
                        $array[$i]['curs']=$v['cu'];
                        break;
                }
                case '11' : {
                        $array[++$i]['lab']='Доходы за ноябрь';
                        $array[$i]['sum']=abs($v['su']);
                        $array[$i]['curs']=$v['cu'];
                        break;
                }
                case '12' : {
                        $array[++$i]['lab']='Доходы за декабрь';
                        $array[$i]['sum']=abs($v['su']);
                        $array[$i]['curs']=$v['cu'];
                        break;
                }
                default : {
                        $array[++$i]['lab']='Доходы';
                        $array[$i]['sum']=abs($v['su']);
                        $array[$i]['curs']=$v['cu'];
                }
            }
            //$array[]=array('Расходы'.' &nbsp;&nbsp;&nbsp; '.abs($v['su']),abs($v['su']));
        }
        /*foreach ($result as $v) {
            switch (substr($v['datef'],5,2)){
                case '01' : $array[]=array('Доходы за январь'.' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; '.abs($v['su']),abs($v['su']));break;
                case '02' : $array[]=array('Доходы за февраль'.' &nbsp;&nbsp;&nbsp;&nbsp; '.abs($v['su']),abs($v['su']));break;
                case '03' : $array[]=array('Доходы за март'.' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; '.abs($v['su']),abs($v['su']));break;
                case '04' : $array[]=array('Доходы за апрель'.' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; '.abs($v['su']),abs($v['su']));break;
                case '05' : $array[]=array('Доходы за май'.' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; '.abs($v['su']),abs($v['su']));break;
                case '06' : $array[]=array('Доходы за июнь'.' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; '.abs($v['su']),abs($v['su']));break;
                case '07' : $array[]=array('Доходы за июль'.' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; '.abs($v['su']),abs($v['su']));break;
                case '08' : $array[]=array('Доходы за август'.' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; '.abs($v['su']),abs($v['su']));break;
                case '09' : $array[]=array('Доходы за сентябрь'.' &nbsp;&nbsp;&nbsp; '.abs($v['su']),abs($v['su']));break;
                case '10' : $array[]=array('Доходы за октябрь'.' &nbsp;&nbsp;&nbsp;&nbsp; '.abs($v['su']),abs($v['su']));break;
                case '11' : $array[]=array('Доходы за ноябрь'.' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; '.abs($v['su']),abs($v['su']));break;
                case '12' : $array[]=array('Доходы за декабрь'.' &nbsp;&nbsp;&nbsp;&nbsp; '.abs($v['su']),abs($v['su']));break;
                default : $array[]=array('Доходы'.' &nbsp;&nbsp;&nbsp; '.abs($v['su']),abs($v['su']));}//*/
            //$array[]=array('Доходы'.' &nbsp;&nbsp;&nbsp; '.abs($v['su']),abs($v['su']));
        //}//*/
        $result = array();
        $result[0] = $array;
        $sql = "SELECT cur_char_code FROM currency
            WHERE cur_id = ?";
        $result[1] = $this->db->query($sql, $currency);


        return $result;
        /*$array = array();
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
            $data1[] = (float)$val['p'];
            $data2[] = (float)$val['d'];
            $labels[] = substr($key, 0, 7);
        }
        return array('p'=>$data1,'d'=>$data2,'l'=>$labels);//*/
    }

    function SelectDetailedIncome($date1='', $date2='', $account='', $cursshow=''){
        $arr = array();
        if ($account != null) {
        $sql = "SELECT op.id, c.cat_name, op.`date`,
                                    a.account_name, op.money, cur.cur_char_code
            FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=0  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ?
            AND a.account_id=? AND op.money>0 AND c.cat_name <> ''
            ORDER BY c.cat_name";   
        $arr[0] = $this->db->query($sql, $date1, $date2, $this->user->getId(), $account);
        } else{
        $sql = "SELECT op.id, c.cat_name, op.`date`,
                                    a.account_name, op.money, cur.cur_char_code
            FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=0  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ?
            AND op.money>0 AND c.cat_name <> ''
            ORDER BY c.cat_name";
        $arr[0] = $this->db->query($sql, $date1, $date2, $this->user->getId());
        }
        $sql = "SELECT cur_char_code FROM currency
            WHERE cur_id = ?";
        $arr[1] = $this->db->query($sql, $cursshow);
        return $arr;
        }
    

    function SelectDetailedWaste($date1='', $date2='', $account='', $cursshow=''){
        $arr = array();
        if ($account != 0) {
        $sql = "SELECT op.id, c.cat_name, op.`date`,
                                    a.account_name, op.money, cur.cur_char_code
            FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=1  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ?
            AND a.account_id=? AND op.money<0 AND c.cat_name <> ''
            ORDER BY c.cat_name";
        $arr[0] = $this->db->query($sql, $date1, $date2, $this->user->getId(), $account);
        } else {
            $sql = "SELECT op.id, c.cat_name, op.`date`,
                                    a.account_name, op.money, cur.cur_char_code
            FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=1  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ?
            AND op.money<0 AND c.cat_name <> ''
            ORDER BY c.cat_name";
        $arr[0] = $this->db->query($sql, $date1, $date2, $this->user->getId());
        $sql = "SELECT cur_char_code FROM currency
            WHERE cur_id = ?";
        $arr[1] = $this->db->query($sql, $cursshow);
        return $arr;
        }
    }

    function CompareWaste($date1='', $date2='', $date3='', $date4='', $account='', $cursshow=''){
        $arr = array();
        if ($account != 0) {
        $sql = "
            SELECT c.cat_name, cur.cur_char_code, sum(op.money) as su, 1 as per
                FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=1  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ?
            AND a.account_id=? 
            GROUP BY c.cat_name
            UNION
            SELECT c.cat_name, cur.cur_char_code, sum(op.money) as su, 2 as per
                FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=1  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ?
            AND a.account_id=?  
            GROUP BY c.cat_name";
        $arr[0] = $this->db->query($sql, $date1, $date2, $this->user->getId(), $account, $date3, $date4, $this->user->getId(), $account);
        }else{
        $sql = "
            SELECT c.cat_name, cur.cur_char_code, sum(op.money) as su, 1 as per
                FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=1  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ?
            GROUP BY c.cat_name
            UNION
            SELECT c.cat_name, cur.cur_char_code, sum(op.money) as su, 2 as per
                FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=1  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ?
            GROUP BY c.cat_name";
        $arr[0] = $this->db->query($sql, $date1, $date2, $this->user->getId(),  $date3, $date4, $this->user->getId());
        $sql = "SELECT cur_char_code FROM currency
            WHERE cur_id = ?";
        $arr[1] = $this->db->query($sql, $cursshow);
        return $arr;
        }
    }

    function CompareIncome($date1='', $date2='', $date3='', $date4='', $account='', $cursshow=''){
        $arr = array();
        if ($account != 0) {
        $sql = "


            SELECT c.cat_name, cur.cur_char_code, sum(op.money) as su, 1 as per
                FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=0  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ?
            AND a.account_id=?
            GROUP BY c.cat_name
            UNION
            SELECT c.cat_name, cur.cur_char_code, sum(op.money) as su, 2 as per
                FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=0  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ?
            AND a.account_id=?
            GROUP BY c.cat_name";
        $arr[0] = $this->db->query($sql, $date1, $date2, $this->user->getId(), $account, $date3, $date4, $this->user->getId(), $account);
        }else{
        $sql = "
            SELECT c.cat_name, cur.cur_char_code, sum(op.money) as su, 1 as per
                FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=0  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ?
            GROUP BY c.cat_name
            UNION
            SELECT c.cat_name, cur.cur_char_code, sum(op.money) as su, 2 as per
                FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=0  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ?
            GROUP BY c.cat_name";
        $arr[0] = $this->db->query($sql, $date1, $date2, $this->user->getId(),  $date3, $date4, $this->user->getId());
        $sql = "SELECT cur_char_code FROM currency
            WHERE cur_id = ?";
        $arr[1] = $this->db->query($sql, $cursshow);
        return $arr;

        }
    }

   function calcDaysDiff($dateFrom='',$dateTo='') {
	// Создаем объекты с датами
	$dtFromS = strtotime($dateFrom);
  	$dtToS = strtotime($dateTo);
  	// Получаем количество секунд с начала эпохи, вычисляем разницу и переводим ее в дни
  	$dtDiffS = $dtToS-$dtFromS;
  	$dtDiffM = $dtDiffS/60;
  	$dtDiffH = $dtDiffM/60;
  	$dtDiffD = $dtDiffH/24+1;

  	return $dtDiffD;
  }

    function AverageIncome($date1='', $date2='', $date3='', $date4='', $account='', $cursshow=''){
        //$mas[0] = calcDaysDiff($date1, $date2);
        //$mas[1] = calcDaysDiff($date3, $date4);

        $dtFromS = strtotime($date1);
  	$dtToS = strtotime($date2);
        $dtDiffS = $dtToS-$dtFromS;
  	$dtDiffM = $dtDiffS/60;
  	$dtDiffH = $dtDiffM/60;
  	$dtDiffD = $dtDiffH/24+1;
        $mas[0] = $dtDiffD;

        $dtFromS = strtotime($date3);
  	$dtToS = strtotime($date4);
        $dtDiffS = $dtToS-$dtFromS;
  	$dtDiffM = $dtDiffS/60;
  	$dtDiffH = $dtDiffM/60;
  	$dtDiffD = $dtDiffH/24+1;
        $mas[1] = $dtDiffD;//*/
        if ($account != 0) {
        $sql = "
            SELECT c.cat_name, cur.cur_char_code, sum(op.money) as su, 1 as per
                FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=0  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ?
            AND a.account_id=?
            GROUP BY c.cat_name
            UNION
            SELECT c.cat_name, cur.cur_char_code, sum(op.money) as su, 2 as per
                FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=0  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ?
            AND a.account_id=?
            GROUP BY c.cat_name";
        $que = $this->db->query($sql, $date1, $date2, $this->user->getId(), $account, $date3, $date4, $this->user->getId(), $account);
        }else{
        $sql = "
            SELECT c.cat_name, cur.cur_char_code, sum(op.money) as su, 1 as per
                FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=0  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ?
            GROUP BY c.cat_name
            UNION
            SELECT c.cat_name, cur.cur_char_code, sum(op.money) as su, 2 as per
                FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=0  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ?
            GROUP BY c.cat_name";
        $que = $this->db->query($sql, $date1, $date2, $this->user->getId(),  $date3, $date4, $this->user->getId());
        }
        $mas[2] = $que;
        $sql = "SELECT cur_char_code FROM currency
            WHERE cur_id = ?";
        $mas[3] = $this->db->query($sql, $cursshow);
        return $mas;
    }

    function AverageWaste($date1='', $date2='', $date3='', $date4='', $account='', $cursshow=''){
        //$mas[0] = calcDaysDiff($date1, $date2);
        //$mas[1] = calcDaysDiff($date3, $date4);

        $dtFromS = strtotime($date1);
  	$dtToS = strtotime($date2);
        $dtDiffS = $dtToS-$dtFromS;
  	$dtDiffM = $dtDiffS/60;
  	$dtDiffH = $dtDiffM/60;
  	$dtDiffD = $dtDiffH/24+1;
        $mas[0] = $dtDiffD;

        $dtFromS = strtotime($date3);
  	$dtToS = strtotime($date4);
        $dtDiffS = $dtToS-$dtFromS;
  	$dtDiffM = $dtDiffS/60;
  	$dtDiffH = $dtDiffM/60;
  	$dtDiffD = $dtDiffH/24+1;
        $mas[1] = $dtDiffD;//*/
        if ($account != 0) {
        $sql = "
            SELECT c.cat_name, cur.cur_char_code, sum(op.money) as su, 1 as per
                FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=1  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ?
            AND a.account_id=?
            GROUP BY c.cat_name
            UNION
            SELECT c.cat_name, cur.cur_char_code, sum(op.money) as su, 2 as per
                FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=1  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ?
            AND a.account_id=?
            GROUP BY c.cat_name";
        $que = $this->db->query($sql, $date1, $date2, $this->user->getId(), $account, $date3, $date4, $this->user->getId(), $account);
        }else{
        $sql = "
            SELECT c.cat_name, cur.cur_char_code, sum(op.money) as su, 1 as per
                FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON nvc.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=1  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ?
            GROUP BY c.cat_name
            UNION
            SELECT c.cat_name, cur.cur_char_code, sum(op.money) as su, 2 as per
                FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=1  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ?
            GROUP BY c.cat_name";
        $que = $this->db->query($sql, $date1, $date2, $this->user->getId(),  $date3, $date4, $this->user->getId());
        }
        $mas[2] = $que;
        $sql = "SELECT cur_char_code FROM currency
            WHERE cur_id = ?";
        $mas[3] = $this->db->query($sql, $cursshow);
        return $mas;
    }

}