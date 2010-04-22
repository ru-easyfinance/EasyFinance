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
    }

    /**
     * Получает список валют валют и их курсы
     * @return array
     */
    private function getCurrency( User $user )
    {
        $currency = array();
        // @XXX: Если ид валюты 4,6,9, то пересчитываем через них, иначе через рубль
        // Получаем список последних валют, и раскладываем их по id
        $sql = "SELECT currency_id AS id, currency_sum AS currency, cur_char_code AS char_code
        FROM daily_currency d
        LEFT JOIN users u ON id=?
        LEFT JOIN currency c ON c.cur_id=d.currency_id
        WHERE
        currency_from = IF (u.user_currency_default IN (4,6,9), u.user_currency_default, 1) AND
        currency_date = (SELECT MAX(currency_date) FROM daily_currency WHERE user_id=0)";

        $currency[1]['value']     = 1;
        $currency[1]['char_code'] = 'RUB'; //@XXX RUR???

        foreach ( Core::getInstance()->db->select($sql, $user->getId() ) as $value ) {
            $currency[$value['id']]['value'] = $value['currency'];
            $currency[$value['id']]['char_code'] = $value['char_code'];
        }

        return $currency;
    }

    /**
     * Возвращает сформированный JSON для круговой диаграммы
     * @param int $drain 0 - доход, 1 - расход
     * @param string mysqldate $start
     * @param string mysqldate $end
     * @param string | int $accounts Ид или список ид счетов через запятую. Например: 123,23,1234,324 или 34
     * @param int $currency - Ид валюты, в какой возвращать значения
     * @return json
     */
    function getPie ( $drain = 0, $start = '', $end = '', $account = 0, $currency_id = 0 )
    {
        // Получаем список операций сгруппированных по категориям и валютам
        $sql = "SELECT
                sum(o.money) AS money,
                cur.cur_char_code,
                cur.cur_id,
                IFNULL(c.cat_name, '') AS cat,
                c.cat_id
            FROM operation o
            LEFT JOIN accounts a ON a.account_id=o.account_id
            LEFT JOIN category c ON c.cat_id = o.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE o.user_id = ? AND o.accepted=1 AND o.drain = ? AND a.account_id IN({$account})
                AND `date` BETWEEN ? AND ?
            AND o.transfer = 0 AND ( o.tr_id < 1 OR ISNULL(o.tr_id) )
            GROUP BY o.cat_id, a.account_currency_id";

        $result = $this->db->select($sql, Core::getInstance()->user->getId(),
                $drain, $start, $end);

        $currencies = $this->getCurrency( $this->user );

        // Создаём чистый массив и наполняем его чистыми данными (конвертируя автоматом курс валюты)
        $return = array();
        foreach ( $result as $key => $value ) {

            $return[ $key ]['cat'] = $value['cat'];
            // Хак для рубля, которого нет в списке валют
            if ( $currency_id == 1) {
                $return[ $key ]['cur_char_code'] = 'RUB';
            } else {
                $return[ $key ]['cur_char_code'] = $currencies[ $currency_id ]['char_code'];
            }
            $return[ $key ]['cur_id'] = $currency_id;

            if ( (int) $value['cur_id'] == $currency_id ) {
                $money = $value['money'];
            } else {
                try {
                    $money = round($value['money'] / (float) $currencies[ $currency_id ]['value'], 2);
                } catch (Exception $e) {
                    throw new Exception($e->getMessage());
                }
            }

            if ( isset ( $return[ $value['cat_id'] ]['money'] ) ) {
                $return[ $key ]['money'] +=  $money;
            } else {
                $return[ $key ]['money'] =  $money;
            }
        }

        return array(
            0 => $return,
            1 => array( 
                array(
                    "cur_char_code" =>
                        isset( $return[ 0 ]['cur_char_code'] )? $return[ 0 ]['cur_char_code'] : ''
                    )
                )
        );
    }

    /**
     * Возвращает сформированный JSON для двойной диаграммы
     *
     * @param string mysqldate $start
     * @param string mysqldate $end
     * @param int|string $accounts
     * @param int $currency
     * @return array
     */
    function getBars($start = '', $end = '', $accounts = 0, $currency = 0)
    {

//SELECT
//    ABS(sum(o.money)) AS su,
//    cur.cur_id,
//    DATE_FORMAT(`date`,'%Y.%m.01') as `datef`,
//    o.drain
//FROM operation o
//LEFT JOIN accounts a ON a.account_id=o.account_id
//LEFT JOIN category c ON c.cat_id = o.cat_id
//LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
//WHERE o.user_id = 688 AND `date` BETWEEN '2010-01-01' AND '2010-04-31' AND o.accepted=1
//    #AND a.account_id IN(17114,12887,20520,584,583,596,592,8751)
//    AND o.transfer = 0
//GROUP BY drain, cur_id, `datef`


        $sql = "SELECT ABS(sum(o.money)) AS su, cur.cur_char_code AS cu, cur.cur_id,
                    DATE_FORMAT(`date`,'%Y.%m.01') as `datef`
                    , IFNULL(c.cat_name, '') AS cat
                FROM operation o
                    LEFT JOIN accounts a ON a.account_id=o.account_id
                    LEFT JOIN category c ON c.cat_id = o.cat_id
                    LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
                WHERE o.user_id = ? AND `date` BETWEEN ? AND ? AND o.accepted=1
                    AND drain='1' AND a.account_id IN({$accounts})
                    AND o.transfer = 0 AND ( o.tr_id < 1 OR ISNULL(o.tr_id) )
                GROUP BY drain, `datef`";
        $result = $this->db->select($sql, Core::getInstance()->user->getId(), $start, $end);

        if ($account > 0) {
            $sql = "SELECT sum(o.money) AS su, cur.cur_char_code AS cu, cur.cur_id, DATE_FORMAT(`date`,'%Y.%m.01') as `datef`
                , IFNULL(c.cat_name, '') AS cat FROM operation o
                LEFT JOIN accounts a ON a.account_id=o.account_id
                LEFT JOIN category c ON c.cat_id = o.cat_id
                LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
                WHERE o.user_id = ? AND `date` BETWEEN ? AND ? AND o.accepted=1 AND a.account_id = ? AND drain='0'
                AND o.transfer = 0 AND ( o.tr_id < 1 OR ISNULL(o.tr_id) ) 
                GROUP BY drain, `datef`";
            $result2 = $this->db->select($sql, Core::getInstance()->user->getId(), $start, $end, $account/*, $currency*/);
        } else {
            $sql = "SELECT sum(o.money) AS su, cur.cur_char_code AS cu, cur.cur_id, DATE_FORMAT(`date`,'%Y.%m.01') as `datef`
                , IFNULL(c.cat_name, '') AS cat FROM operation o
                LEFT JOIN accounts a ON a.account_id=o.account_id
                LEFT JOIN category c ON c.cat_id = o.cat_id
                LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
                WHERE o.user_id = ? AND `date` BETWEEN ? AND o.accepted=1 AND ? AND drain='0' AND a.account_id IN({$acclist})
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

        $diffYear = (bool)((substr($start, 0, 4) === substr($end, 0, 4)));

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

        $result = array();
        $result[0] = $array;
        $sql = "SELECT cur_char_code FROM currency WHERE cur_id = ?";
        $result[1] = $this->db->query($sql, $currency);

        return $result;
    }

    function SelectDetailedIncome($date1='', $date2='', $account='', $cursshow='', $acclist=''){
        $arr = array();
        if ($account != null) {
        $sql = "SELECT op.id, c.cat_name, op.`date`,
                                    a.account_name, op.money, cur.cur_char_code,cur.cur_id
            FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=0  AND (op.`date` BETWEEN ? AND ?) AND op.accepted=1 AND op.user_id= ?
            AND a.account_id=? AND op.money>0 AND c.cat_name <> ''
            AND op.transfer = 0 AND ( op.tr_id < 1 OR ISNULL(op.tr_id) ) 
            ORDER BY c.cat_name";   
        $arr[0] = $this->db->query($sql, $date1, $date2, $this->user->getId(), $account);
        } else{
        $sql = "SELECT op.id, c.cat_name, op.`date`,
            a.account_name, op.money, cur.cur_char_code, cur.cur_id
            FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=0  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ? AND op.accepted=1
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
                                    a.account_name, op.money, cur.cur_char_code, cur.cur_id
            FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=1  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ? AND op.accepted=1
            AND a.account_id=? AND c.cat_name <> ''
            AND op.transfer = 0 AND ( op.tr_id < 1 OR ISNULL(op.tr_id) ) 
            ORDER BY c.cat_name";
        $arr[0] = $this->db->query($sql, $date1, $date2, $this->user->getId(), $account);
        } else{
        $sql = "SELECT op.id, c.cat_name, op.`date`,
                                    a.account_name, op.money, cur.cur_char_code, cur.cur_id
            FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=1  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ? AND op.accepted=1
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
            SELECT c.cat_name, cur.cur_char_code, cur.cur_id, sum(op.money) as su, 1 as per
                FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=1  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ? AND op.accepted=1
            AND a.account_id=? AND cur_char_code is not null
            AND op.transfer = 0 AND ( op.tr_id < 1 OR ISNULL(op.tr_id) ) 
            GROUP BY c.cat_name
            UNION
            SELECT c.cat_name, cur.cur_char_code, cur.cur_id, sum(op.money) as su, 2 as per
                FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=1  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ? AND op.accepted=1
            AND a.account_id=? AND cur_char_code is not null
            AND op.transfer = 0 AND ( op.tr_id < 1 OR ISNULL(op.tr_id) ) 
            GROUP BY c.cat_name";
        $arr[0] = $this->db->query($sql, $date1, $date2, $this->user->getId(), $account, $date3, $date4, $this->user->getId(), $account);
        }else{
        $sql = "
            SELECT c.cat_name, cur.cur_char_code, cur.cur_id, sum(op.money) as su, 1 as per
                FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=1  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ? AND op.accepted=1
            AND cur_char_code is not null AND a.account_id IN({$acclist})
            AND op.transfer = 0 AND ( op.tr_id < 1 OR ISNULL(op.tr_id) ) 
            GROUP BY c.cat_name
            UNION
            SELECT c.cat_name, cur.cur_char_code, cur.cur_id, sum(op.money) as su, 2 as per
                FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=1  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ? AND op.accepted=1
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
            SELECT c.cat_name, cur.cur_char_code, cur.cur_id, sum(op.money) as su, 1 as per
                FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=0  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ? AND op.accepted=1
            AND a.account_id=?
            AND op.transfer = 0 AND ( op.tr_id < 1 OR ISNULL(op.tr_id) ) 
            GROUP BY c.cat_name
            UNION
            SELECT c.cat_name, cur.cur_char_code, cur.cur_id, sum(op.money) as su, 2 as per
                FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=0  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ? AND op.accepted=1
            AND a.account_id=?
            AND op.transfer = 0 AND ( op.tr_id < 1 OR ISNULL(op.tr_id) )
            GROUP BY c.cat_name";
        $arr[0] = $this->db->query($sql, $date1, $date2, $this->user->getId(), $account, $date3, $date4, $this->user->getId(), $account);
        }else{
        $sql = "
            SELECT c.cat_name, cur.cur_char_code, cur.cur_id, sum(op.money) as su, 1 as per
                FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=0  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ? AND op.accepted=1
            AND a.account_id IN({$acclist})
            AND op.transfer = 0 AND ( op.tr_id < 1 OR ISNULL(op.tr_id) ) 
            GROUP BY c.cat_name
            UNION
            SELECT c.cat_name, cur.cur_char_code, cur.cur_id, sum(op.money) as su, 2 as per
                FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=0  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ? AND op.accepted=1
            AND a.account_id IN({$acclist})
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
            SELECT c.cat_name, cur.cur_char_code, cur.cur_id, sum(op.money) as su, 1 as per
                FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=0  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ? AND op.accepted=1
            AND a.account_id=?
            AND op.transfer = 0 AND ( op.tr_id < 1 OR ISNULL(op.tr_id) )
            GROUP BY c.cat_name
            UNION
            SELECT c.cat_name, cur.cur_char_code, cur.cur_id, sum(op.money) as su, 2 as per
                FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=0  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ? AND op.accepted=1
            AND a.account_id=?
            AND op.transfer = 0 AND ( op.tr_id < 1 OR ISNULL(op.tr_id) ) 
            GROUP BY c.cat_name";
        $que = $this->db->query($sql, $date1, $date2, $this->user->getId(), $account, $date3, $date4, $this->user->getId(), $account);
        }else{
        $sql = "
            SELECT c.cat_name, cur.cur_char_code, cur.cur_id, sum(op.money) as su, 1 as per
                FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=0  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ? AND op.accepted=1
            AND a.account_id IN({$acclist})
            AND op.transfer = 0 AND ( op.tr_id < 1 OR ISNULL(op.tr_id) ) 
            GROUP BY c.cat_name
            UNION
            SELECT c.cat_name, cur.cur_char_code, cur.cur_id, sum(op.money) as su, 2 as per
                FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=0  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ? AND op.accepted=1
            AND a.account_id IN({$acclist})
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
            SELECT c.cat_name, cur.cur_char_code, cur.cur_id, sum(op.money) as su, 1 as per
                FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=1  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ? AND op.accepted=1
            AND a.account_id=?
            AND op.transfer = 0 AND ( op.tr_id < 1 OR ISNULL(op.tr_id) ) 
            GROUP BY c.cat_name
            UNION
            SELECT c.cat_name, cur.cur_char_code, cur.cur_id, sum(op.money) as su, 2 as per
                FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=1  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ? AND op.accepted=1
            AND a.account_id=?
            AND op.transfer = 0 AND ( op.tr_id < 1 OR ISNULL(op.tr_id) ) 
            GROUP BY c.cat_name";
        $que = $this->db->query($sql, $date1, $date2, $this->user->getId(), $account, $date3, $date4, $this->user->getId(), $account);
        }else{
        $sql = "
            SELECT c.cat_name, cur.cur_char_code, cur.cur_id, sum(op.money) as su, 1 as per
                FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=1  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ? AND op.accepted=1
            AND a.account_id IN({$acclist})
            AND op.transfer = 0 AND ( op.tr_id < 1 OR ISNULL(op.tr_id) ) 
            GROUP BY c.cat_name
            UNION
            SELECT c.cat_name, cur.cur_char_code, cur.cur_id, sum(op.money) as su, 2 as per
                FROM operation op
            LEFT JOIN accounts a ON a.account_id=op.account_id
            LEFT JOIN category c ON c.cat_id=op.cat_id
            LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
            WHERE  op.drain=1  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ? AND op.accepted=1
            AND a.account_id IN({$acclist})
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
