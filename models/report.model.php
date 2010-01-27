<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс модели для управления отчётами
 * @category report
 * @author Max Kamashev (ukko) <max.kamashev@gmail.com>
 * @copyright http://easyfinance.ru/
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
    function getPie($drain = 0, $start = '', $end = '', $account = 0, $currency=0, $acclist='')
    {

        if ($account > 0) {
            $sql = "SELECT sum(o.money) AS money, cur.cur_char_code, IFNULL(c.cat_name, '') AS cat FROM operation o
                LEFT JOIN accounts a ON a.account_id=o.account_id
                LEFT JOIN category c ON c.cat_id = o.cat_id
                LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
                WHERE o.user_id = ? AND o.account_id = ? AND o.drain = ?
                    AND `date` BETWEEN ? AND ?
                    AND o.transfer = 0 AND ( o.tr_id < 1 OR ISNULL(o.tr_id) ) 
                GROUP BY o.cat_id";//AND a.account_currency_id = ?
            $result = $this->db->select($sql, Core::getInstance()->user->getId(),
                $account, $drain, $start, $end/*, $currency*/);

        } else {
            $sql = "SELECT sum(o.money) AS money, cur.cur_char_code, IFNULL(c.cat_name, '') AS cat FROM operation o
                LEFT JOIN accounts a ON a.account_id=o.account_id
                LEFT JOIN category c ON c.cat_id = o.cat_id
                LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
                WHERE o.user_id = ? AND o.drain = ? AND a.account_id IN({$acclist})
                    AND `date` BETWEEN ? AND ?
                AND o.transfer = 0 AND ( o.tr_id < 1 OR ISNULL(o.tr_id) )
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

            //текстовые отчёты отправлю вторым индексом массива
            if ($drain==0)
                $arr[2] = $this->SelectDetailedIncome($start, $end, $account, $currency, $acclist);
            if ($drain==1)
                $arr[2] = $this->SelectDetailedWaste($start, $end, $account, $currency, $acclist);

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
    function getBars($start = '', $end = '', $account=0, $currency=0, $acclist='')
    {
        $diffYear = (bool)( ( substr($start,0,4) == substr($end,0,4) ) );
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
            $sql = "SELECT sum(o.money) AS su, cur.cur_char_code AS cu, DATE_FORMAT(`date`,'%Y.%m.01') as `datef`
                , IFNULL(c.cat_name, '') AS cat FROM operation o
                LEFT JOIN accounts a ON a.account_id=o.account_id
                LEFT JOIN category c ON c.cat_id = o.cat_id
                LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
                WHERE o.user_id = ? AND `date` BETWEEN ? AND ? AND a.account_id = ? AND drain='1'
                AND o.transfer = 0 AND ( o.tr_id < 1 OR ISNULL(o.tr_id) ) 
                GROUP BY drain, `datef`";
            $result = $this->db->select($sql, Core::getInstance()->user->getId(), $start, $end, $account/*, $currency*/);
        } else {
            $sql = "SELECT sum(o.money) AS su, cur.cur_char_code AS cu, DATE_FORMAT(`date`,'%Y.%m.01') as `datef`
                , IFNULL(c.cat_name, '') AS cat FROM operation o
                LEFT JOIN accounts a ON a.account_id=o.account_id
                LEFT JOIN category c ON c.cat_id = o.cat_id
                LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
                WHERE o.user_id = ? AND `date` BETWEEN ? AND ? AND drain='1' AND a.account_id IN({$acclist})
                AND o.transfer = 0 AND ( o.tr_id < 1 OR ISNULL(o.tr_id) ) 
                GROUP BY drain, `datef`";
            $result = $this->db->select($sql, Core::getInstance()->user->getId(), $start, $end/*, $currency*/);
        }

        if ($account > 0) {
            $sql = "SELECT sum(o.money) AS su, cur.cur_char_code AS cu, DATE_FORMAT(`date`,'%Y.%m.01') as `datef`
                , IFNULL(c.cat_name, '') AS cat FROM operation o
                LEFT JOIN accounts a ON a.account_id=o.account_id
                LEFT JOIN category c ON c.cat_id = o.cat_id
                LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
                WHERE o.user_id = ? AND `date` BETWEEN ? AND ? AND a.account_id = ? AND drain='0' 
                AND o.transfer = 0 AND ( o.tr_id < 1 OR ISNULL(o.tr_id) ) 
                GROUP BY drain, `datef`";
            $result2 = $this->db->select($sql, Core::getInstance()->user->getId(), $start, $end, $account/*, $currency*/);
        } else {
            $sql = "SELECT sum(o.money) AS su, cur.cur_char_code AS cu, DATE_FORMAT(`date`,'%Y.%m.01') as `datef`
                , IFNULL(c.cat_name, '') AS cat FROM operation o
                LEFT JOIN accounts a ON a.account_id=o.account_id
                LEFT JOIN category c ON c.cat_id = o.cat_id
                LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
                WHERE o.user_id = ? AND `date` BETWEEN ? AND ? AND drain='0' AND a.account_id IN({$acclist})
                AND o.transfer = 0 AND ( o.tr_id < 1 OR ISNULL(o.tr_id) ) 
                GROUP BY drain, `datef`";//AND a.account_currency_id = ?
            $result2 = $this->db->select($sql, Core::getInstance()->user->getId(), $start, $end/*, $currency*/);
        }

        $i = -1;
        $array=array();


        $arr = array();
        $coun = 0 ;
        $count = 0;
        $mon = array ("Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь");
        $short = array ("Янв", "Февр", "Март", "Апр", "Май", "Июнь", "Июль", "Авг", "Сент", "Окт", "Нояб", "Дек");
        $monc = array ("01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12");
        $year = array ("09","10");
        for ($coun=0; $coun<12; $coun++){//начальная инициализация
            $array[$coun]['lab'] = '';//лейбл. название месяца
            $array[$coun]['was'] = 0;//расходы
            $array[$coun]['in'] = 0;//доходы
        }

        for ($coun=0; $coun<12; $coun++){
            foreach ($result as $v){
                if ( substr($v['datef'],5,2) == $monc[$coun] ) {
                    $count++;
                }
            }
        }

        for ($coun=0; $coun<12; $coun++){
            //$array[$coun]['lab'] = $mon[$coun];
            foreach ($result as $v){
                if ( substr($v['datef'],5,2) == $monc[$coun] ) {
                    if ($diffYear)
                        $array[$coun]['lab'] = ($count<=6) ? $mon[$coun] : $short[$coun];
                    else{
                        if ( substr ($v['datef'],2,2) == '09')
                            $numYear = 0;
                        if ( substr ($v['datef'],2,2) == '10')
                            $numYear = 1;    
                        $array[$coun]['lab'] = ( ($count<=6) ? $mon[$coun] : $short[$coun] ) . $year[$numYear];
                    }
                    $array[$coun]['was'] = $v['su'];
                    $array[$coun]['curs']= $v['cu'];
                }
            }
        }
        for ($coun=0; $coun<12; $coun++){
            foreach ($result2 as $v){
                if ( substr($v['datef'],5,2) == $monc[$coun] ) {
                     $array[$coun]['in'] = $v['su'];//$v['sum']
                }
            }
        }
        //тут заголовки идут в виде январь10 февраль10 декабрь09
        //алгоритм прост . берём первый с текущим годом - и в конец. и т.д.
        //т.о. сначала перенесём январь, а за ним февраль.
        $year = substr($end,2,2);//последние две цифры конечного года
        if ( ! $diffYear ){
            while ( ( substr($array[0]['lab'],-2) == (string)$year) ){
                $array = array_merge ( array_slice( $array, 1 ) , array($array[0]) );
            }
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

    function SelectDetailedIncome($date1='', $date2='', $account='', $cursshow='', $acclist=''){
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
            AND op.transfer = 0 AND ( op.tr_id < 1 OR ISNULL(op.tr_id) ) 
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
            AND op.money>0 AND c.cat_name <> '' AND a.account_id IN({$acclist})
            AND op.transfer = 0 AND ( op.tr_id < 1 OR ISNULL(op.tr_id) ) 
            ORDER BY c.cat_name";
        $arr[0] = $this->db->query($sql, $date1, $date2, $this->user->getId());
        }
        $sql = "SELECT cur_char_code FROM currency
            WHERE cur_id = ?";
        $arr[1] = $this->db->query($sql, $cursshow);
        return $arr;
        }
    

    function SelectDetailedWaste($date1='', $date2='', $account='', $cursshow='', $acclist=''){
        $arr = array();
        if ($account != null) {
        $sql = "SELECT op.id, c.cat_name, op.`date`,
                                    a.account_name, op.money, cur.cur_char_code
            FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=1  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ?
            AND a.account_id=? AND c.cat_name <> ''
            AND op.transfer = 0 AND ( op.tr_id < 1 OR ISNULL(op.tr_id) ) 
            ORDER BY c.cat_name";
        $arr[0] = $this->db->query($sql, $date1, $date2, $this->user->getId(), $account);
        } else{
        $sql = "SELECT op.id, c.cat_name, op.`date`,
                                    a.account_name, op.money, cur.cur_char_code
            FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=1  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ?
            AND c.cat_name <> '' AND a.account_id IN({$acclist})
            AND op.transfer = 0 AND ( op.tr_id < 1 OR ISNULL(op.tr_id) ) 
            ORDER BY c.cat_name";
        $arr[0] = $this->db->query($sql, $date1, $date2, $this->user->getId());
        }
        $sql = "SELECT cur_char_code FROM currency
            WHERE cur_id = ?";
        $arr[1] = $this->db->query($sql, $cursshow);
        return $arr;
    }

    function CompareWaste($date1='', $date2='', $date3='', $date4='', $account='', $cursshow='', $acclist=''){
        $arr = array();
        if ($account != 0) {
        $sql = "
            SELECT c.cat_name, cur.cur_char_code, sum(op.money) as su, 1 as per
                FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=1  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ?
            AND a.account_id=? AND cur_char_code is not null
            AND op.transfer = 0 AND ( op.tr_id < 1 OR ISNULL(op.tr_id) ) 
            GROUP BY c.cat_name
            UNION
            SELECT c.cat_name, cur.cur_char_code, sum(op.money) as su, 2 as per
                FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=1  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ?
            AND a.account_id=? AND cur_char_code is not null
            AND op.transfer = 0 AND ( op.tr_id < 1 OR ISNULL(op.tr_id) ) 
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
            AND cur_char_code is not null AND a.account_id IN({$acclist})
            AND op.transfer = 0 AND ( op.tr_id < 1 OR ISNULL(op.tr_id) ) 
            GROUP BY c.cat_name
            UNION
            SELECT c.cat_name, cur.cur_char_code, sum(op.money) as su, 2 as per
                FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=1  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ?
            AND cur_char_code is not null AND a.account_id IN({$acclist})
            AND op.transfer = 0 AND ( op.tr_id < 1 OR ISNULL(op.tr_id) ) 
            GROUP BY c.cat_name";
        $arr[0] = $this->db->query($sql, $date1, $date2, $this->user->getId(),  $date3, $date4, $this->user->getId());
        }
        $sql = "SELECT cur_char_code FROM currency
            WHERE cur_id = ?";
        $arr[1] = $this->db->query($sql, $cursshow);
        return $arr;
    }

    function CompareIncome($date1='', $date2='', $date3='', $date4='', $account='', $cursshow='', $acclist=''){
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
            AND op.transfer = 0 AND ( op.tr_id < 1 OR ISNULL(op.tr_id) ) 
            GROUP BY c.cat_name
            UNION
            SELECT c.cat_name, cur.cur_char_code, sum(op.money) as su, 2 as per
                FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=0  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ?
            AND a.account_id=?
            AND op.transfer = 0 AND ( op.tr_id < 1 OR ISNULL(op.tr_id) )
            GROUP BY c.cat_name";
        $arr[0] = $this->db->query($sql, $date1, $date2, $this->user->getId(), $account, $date3, $date4, $this->user->getId(), $account);
        }else{
        $sql = "
            SELECT c.cat_name, cur.cur_char_code, sum(op.money) as su, 1 as per
                FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=0  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ? AND a.account_id IN({$acclist})
            AND op.transfer = 0 AND ( op.tr_id < 1 OR ISNULL(op.tr_id) ) 
            GROUP BY c.cat_name
            UNION
            SELECT c.cat_name, cur.cur_char_code, sum(op.money) as su, 2 as per
                FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=0  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ? AND a.account_id IN({$acclist})
            AND op.transfer = 0 AND ( op.tr_id < 1 OR ISNULL(op.tr_id) ) 
            GROUP BY c.cat_name";
        $arr[0] = $this->db->query($sql, $date1, $date2, $this->user->getId(),  $date3, $date4, $this->user->getId());        
        }
        $sql = "SELECT cur_char_code FROM currency
            WHERE cur_id = ?";
        $arr[1] = $this->db->query($sql, $cursshow);
        return $arr;
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

    function AverageIncome($date1='', $date2='', $date3='', $date4='', $account='', $cursshow='', $acclist=''){
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
            AND op.transfer = 0 AND ( op.tr_id < 1 OR ISNULL(op.tr_id) )
            GROUP BY c.cat_name
            UNION
            SELECT c.cat_name, cur.cur_char_code, sum(op.money) as su, 2 as per
                FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=0  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ?
            AND a.account_id=?
            AND op.transfer = 0 AND ( op.tr_id < 1 OR ISNULL(op.tr_id) ) 
            GROUP BY c.cat_name";
        $que = $this->db->query($sql, $date1, $date2, $this->user->getId(), $account, $date3, $date4, $this->user->getId(), $account);
        }else{
        $sql = "
            SELECT c.cat_name, cur.cur_char_code, sum(op.money) as su, 1 as per
                FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=0  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ? AND a.account_id IN({$acclist})
            AND op.transfer = 0 AND ( op.tr_id < 1 OR ISNULL(op.tr_id) ) 
            GROUP BY c.cat_name
            UNION
            SELECT c.cat_name, cur.cur_char_code, sum(op.money) as su, 2 as per
                FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=0  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ? AND a.account_id IN({$acclist})
            AND op.transfer = 0 AND ( op.tr_id < 1 OR ISNULL(op.tr_id) ) 
            GROUP BY c.cat_name";
        $que = $this->db->query($sql, $date1, $date2, $this->user->getId(),  $date3, $date4, $this->user->getId());
        }
        $mas[2] = $que;
        $sql = "SELECT cur_char_code FROM currency
            WHERE cur_id = ?";
        $mas[3] = $this->db->query($sql, $cursshow);
        return $mas;
    }

    function AverageWaste($date1='', $date2='', $date3='', $date4='', $account='', $cursshow='', $acclist=''){
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
            AND op.transfer = 0 AND ( op.tr_id < 1 OR ISNULL(op.tr_id) ) 
            GROUP BY c.cat_name
            UNION
            SELECT c.cat_name, cur.cur_char_code, sum(op.money) as su, 2 as per
                FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=1  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ?
            AND a.account_id=?
            AND op.transfer = 0 AND ( op.tr_id < 1 OR ISNULL(op.tr_id) ) 
            GROUP BY c.cat_name";
        $que = $this->db->query($sql, $date1, $date2, $this->user->getId(), $account, $date3, $date4, $this->user->getId(), $account);
        }else{
        $sql = "
            SELECT c.cat_name, cur.cur_char_code, sum(op.money) as su, 1 as per
                FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=1  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ? AND a.account_id IN({$acclist})
            AND op.transfer = 0 AND ( op.tr_id < 1 OR ISNULL(op.tr_id) ) 
            GROUP BY c.cat_name
            UNION
            SELECT c.cat_name, cur.cur_char_code, sum(op.money) as su, 2 as per
                FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=1  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ? AND a.account_id IN({$acclist})
            AND op.transfer = 0 AND ( op.tr_id < 1 OR ISNULL(op.tr_id) ) 
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
