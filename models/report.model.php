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
    private $_db = NULL;

    private $_user = NULL;

    private $_fullMonth;

    private $_shortMonth;

    /**
     * Конструктор
     * @return void
     */
    function  __construct()
    {

        $this->_fullMonth = array (
            1  => "Январь",
            2  => "Февраль",
            3  => "Март",
            4  => "Апрель",
            5  => "Май",
            6  => "Июнь",
            7  => "Июль",
            8  => "Август",
            9  => "Сентябрь",
            10 => "Октябрь",
            11 => "Ноябрь",
            12 => "Декабрь"
        );

        $this->_shortMonth = array (
            1  => "Янв",
            2  => "Февр",
            3  => "Март",
            4  => "Апр",
            5  => "Май",
            6  => "Июнь",
            7  => "Июль",
            8  => "Авг",
            9  => "Сент",
            10 => "Окт",
            11 => "Нояб",
            12 => "Дек"
        );

        $this->_db   = Core::getInstance()->db;
        $this->_user = Core::getInstance()->user;
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

        $result = $this->_db->select($sql, Core::getInstance()->user->getId(),
                $drain, $start, $end);

        $currencies = $this->getCurrency( $this->_user );

        // Создаём чистый массив и наполняем его чистыми данными (конвертируя автоматом курс валюты)
        $return = array();
        foreach ( $result as $key => $value ) {

            $return[ $key ]['cat'] = $value['cat'];
 
            $return[ $key ]['cur_char_code'] = $currencies[ $currency_id ]['char_code'];
            
            $return[ $key ]['cur_id'] = $currency_id;

            if ( (int) $value['cur_id'] == $currency_id ) {
                $money = $value['money'];
            } else {
                if (isset($currencies[$value['cur_id']])) {
                    $money = round($value['money'] * (float) $currencies[$value['cur_id']]['value'], 2);
                } else {
                    throw new Exception("Currency #{$value['cur_id']} not found");
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
     * @param int $currency_id
     * @return array
     */
    function getBars($start = '', $end = '', $accounts = 0, $currency_id = 0)
    {

        // Получаем все операции, за выбранный период
        $sql = "SELECT
                    ABS(sum(o.money)) AS money,
                    cur.cur_id,
                    DATE_FORMAT(`date`,'%Y-%m-01') as `datef`,
                    o.drain
                FROM operation o
                LEFT JOIN accounts a ON a.account_id=o.account_id
                LEFT JOIN category c ON c.cat_id = o.cat_id
                LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
                WHERE o.user_id = ? AND `date` BETWEEN ? AND ? AND o.accepted=1
                    AND a.account_id IN({$accounts})
                    AND o.transfer = 0
                GROUP BY drain, cur_id, `datef`";

        $result = $this->_db->select($sql, Core::getInstance()->user->getId(), $start, $end);

        $currencies = $this->getCurrency( $this->_user );

        $sort = array();

        foreach($result as $value) {

            if ( (int) $value['cur_id'] == $currency_id ) {
                $money = $value['money'];
            } else {
                if (isset($currencies[$value['cur_id']])) {
                    $money = round($value['money'] * (float) $currencies[$value['cur_id']]['value'], 2);
                } else {
                    throw new Exception("Currency #{$value['cur_id']} not found");
                }
            }

            // Доходы
            if ($value['drain'] == 0) {
                $sort[$value['datef']]['in']  = $money;

            // Расходы
            } else {
                $sort[$value['datef']]['was'] = $money;
            }

            $ts = strtotime($value['datef']);
            $sort[$value['datef']]['curs'] = $currencies[ $currency_id ]['char_code'];

            // Если у нас период захватывает разные годы
            if (date("Y", strtotime($start)) == date("Y", strtotime($end))) {
                $sort[$value['datef']]['lab']  = $this->_fullMonth[date("n", $ts)];
            // Иначе выводим просто месяц
            } else {
                $sort[$value['datef']]['lab']  = $this->_fullMonth[date("n", $ts)] . " " . date("Y", $ts);
            }
        }

        // Рисуем красивые заголовки
        foreach($sort as $key => $value) {
            if (count($sort) > 6) {

            }
        }

        return array(
            $sort,
            array(
                array(
                    "cur_char_code" =>
                        isset( $sort[ 0 ]['curs'] )? $sort[ 0 ]['curs'] : ''
                    )
                )
        );


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
        $arr[0] = $this->_db->query($sql, $date1, $date2, $this->_user->getId(), $account);
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
            $arr[0] = $this->_db->query($sql, $date1, $date2, $this->_user->getId());
        }
        $sql = "SELECT cur_char_code FROM currency
            WHERE cur_id = ?";
        $arr[1] = $this->_db->query($sql, $cursshow);
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
        $arr[0] = $this->_db->query($sql, $date1, $date2, $this->_user->getId(), $account);
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
        $arr[0] = $this->_db->query($sql, $date1, $date2, $this->_user->getId());
        }
        $sql = "SELECT cur_char_code FROM currency
            WHERE cur_id = ?";
        $arr[1] = $this->_db->query($sql, $cursshow);
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
        $arr[0] = $this->_db->query($sql, $date1, $date2, $this->_user->getId(), $account, $date3, $date4, $this->_user->getId(), $account);
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
        $arr[0] = $this->_db->query($sql, $date1, $date2, $this->_user->getId(),  $date3, $date4, $this->_user->getId());
        }
        $sql = "SELECT cur_char_code FROM currency
            WHERE cur_id = ?";
        $arr[1] = $this->_db->query($sql, $cursshow);
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
        $arr[0] = $this->_db->query($sql, $date1, $date2, $this->_user->getId(), $account, $date3, $date4, $this->_user->getId(), $account);
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
        $arr[0] = $this->_db->query($sql, $date1, $date2, $this->_user->getId(),  $date3, $date4, $this->_user->getId());
        }
        $sql = "SELECT cur_char_code FROM currency
            WHERE cur_id = ?";
        $arr[1] = $this->_db->query($sql, $cursshow);
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
        $que = $this->_db->query($sql, $date1, $date2, $this->_user->getId(), $account, $date3, $date4, $this->_user->getId(), $account);
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
        $que = $this->_db->query($sql, $date1, $date2, $this->_user->getId(),  $date3, $date4, $this->_user->getId());
        }
        $mas[2] = $que;
        $sql = "SELECT cur_char_code FROM currency
            WHERE cur_id = ?";
        $mas[3] = $this->_db->query($sql, $cursshow);
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
        $que = $this->_db->query($sql, $date1, $date2, $this->_user->getId(), $account, $date3, $date4, $this->_user->getId(), $account);
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
        $que = $this->_db->query($sql, $date1, $date2, $this->_user->getId(),  $date3, $date4, $this->_user->getId());
        }
        $mas[2] = $que;
        $sql = "SELECT cur_char_code FROM currency
            WHERE cur_id = ?";
        $mas[3] = $this->_db->query($sql, $cursshow);
        return $mas;
    }

}
