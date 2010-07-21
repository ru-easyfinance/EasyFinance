<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс модели для управления отчётами
 *
 * @category report
 * @author Max Kamashev (ukko) <max.kamashev@gmail.com>
 * @copyright http://easyfinance.ru/
 */
class Report_Model
{
    /**
     * Ссылка на экземпляр DBSimple
     * @var DbSimple_Mysql
     */
    private $_db = NULL;

    /**
     * Ссылка на экземпляр класса пользователя
     * @var oldUser
     */
    private $_user = NULL;

    /**
     * Список месяцев
     * @var array
     */
    private $_fullMonth;

    /**
     * Список месяцев с короткими названиями
     * @var array
     */
    private $_shortMonth;

    /**
     * Обменник для валют
     * @var myCurrencyExchange
     */
    private $_ex = null;


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
            2  => "Фев",
            3  => "Мар",
            4  => "Апр",
            5  => "Май",
            6  => "Июн",
            7  => "Июл",
            8  => "Авг",
            9  => "Сен",
            10 => "Окт",
            11 => "Ноя",
            12 => "Дек"
        );

        $this->_db   = Core::getInstance()->db;
        $this->_user = Core::getInstance()->user;
        $this->ex    = sfConfig::get('ex');
    }


    /**
     * Доходы, Расходы
     *
     * @param int               $type       Тип операции. 0 - расход, 1 - доход
     * @param string mysqldate  $start      Начало периода в формате "YYYY-MM-DD"
     * @param string mysqldate  $end        Окончание периода в формате "YYYY-MM-DD"
     * @param string | int      $accounts   Ид или список ид счетов через запятую. Например: 123,23,1234,324 или 34
     * @param int               $currency   Ид валюты, в какой возвращать значения
     * @return array
     */
    function getPie ( $type = 0, $start = '', $end = '', $account = 0, $currencyId = 0 )
    {
        // Получаем список операций сгруппированных по категориям и валютам
        $sql = "SELECT
                    sum(o.money) AS money,
                    cur.cur_char_code,
                    cur.cur_id,
                    IFNULL(c.cat_name, '') AS cat,
                    c.cat_id
                FROM operation o
                INNER JOIN accounts a
                    ON a.account_id=o.account_id
                INNER JOIN category c
                    ON c.cat_id = o.cat_id
                INNER JOIN currency cur
                    ON cur.cur_id = a.account_currency_id
                WHERE
                    o.user_id = ?
                    AND o.accepted=1
                    AND o.deleted_at IS NULL
                    AND a.account_id IN({$account})
                    AND `date` BETWEEN ? AND ?
                    AND o.`type` = ?
                GROUP BY o.cat_id, a.account_currency_id";

        $array = $this->_db->select($sql, Core::getInstance()->user->getId(), $start, $end, $type);

        // Создаём чистый массив и наполняем его чистыми данными (конвертируя автоматом курс валюты)
        $result = array();

        foreach ($array as $key => $value ) {

            $result[ $key ]['cat'] = $value['cat'];

            $money = new myMoney(abs($value['money']), $value['cur_id']);
            $amount = $this->ex->convert($money, $currencyId)->getAmount();

            if ( isset ( $result[ $key ]['money'] ) ) {
                $result[ $key ]['money'] += $amount;
            } else {
                $result[ $key ]['money'] = $amount;
            }

        }

        return array( 0 => $result );
    }


    /**
     * Сравнение расходов и доходов
     *
     * @param string mysqldate  $start      Дата начала периода  в формате "YYYY-MM-DD"
     * @param string mysqldate  $end        Дата окончания периода в формате "YYYY-MM-DD"
     * @param int|string        $accounts   Ид счёта, или несколько счетов через запятую
     * @param int               $currencyId Ид валюты в которой ожидается результат
     * @return array
     */
    function getBars($start = '', $end = '', $accounts = 0, $currencyId = 0)
    {

        // Получаем все операции, за выбранный период
        $sql = "SELECT
                    ABS(sum(o.money)) AS money,
                    cur.cur_id,
                    DATE_FORMAT(`date`,'%Y-%m-01') as `datef`,
                    o.`type`
                FROM operation o
                LEFT JOIN accounts a
                    ON a.account_id=o.account_id
                LEFT JOIN category c
                    ON c.cat_id = o.cat_id
                LEFT JOIN currency cur
                    ON cur.cur_id = a.account_currency_id
                WHERE
                    o.user_id = ?
                    AND `date` BETWEEN ? AND ?
                    AND o.accepted=1
                    AND o.deleted_at IS NULL
                    AND a.account_id IN({$accounts})
                GROUP BY o.`type`, cur_id, `datef`";

        $result = $this->_db->select($sql, Core::getInstance()->user->getId(), $start, $end);

        $sort = array();

        foreach($result as $value) {

            $money = new myMoney(abs($value['money']), $value['cur_id']);
            $amount = $this->ex->convert($money, $currencyId)->getAmount();

            // Доходы
            if ($value['type'] == 1) {
                if (isset($sort[$value['datef']]['in'])) {
                    $sort[$value['datef']]['in']  += $amount;
                } else {
                    $sort[$value['datef']]['in']  = $amount;
                }

            // Расходы
            } else {
                if (isset($sort[$value['datef']]['was'])) {
                    $sort[$value['datef']]['was'] += $amount;
                } else {
                    $sort[$value['datef']]['was'] = $amount;
                }
            }

            $ts = strtotime($value['datef']);

            // Если у нас период захватывает разные годы
            if (date("Y", strtotime($start)) == date("Y", strtotime($end))) {
                $sort[$value['datef']]['lab']  = $this->_fullMonth[date("n", $ts)];
            // Иначе выводим просто месяц
            } else {
                $sort[$value['datef']]['lab']  = $this->_fullMonth[date("n", $ts)] . " " . date("Y", $ts);
            }
        }

        return array($sort);
    }


    /**
     * Детальные доходы, Детальные расходы
     *
     * @param int               $type           0 - Расход, 1 - Доход
     * @param string mysqldate  $date1          Например: '2010-05-22'
     * @param string mysqldate  $date2          Например: '2010-05-22'
     * @param int | string      $accounts       Ид счёта, или ид счетов через запятую, по которым нужно выдать результат
     * @param int               $currencyId     Ид валюты, в которой ожидается результат
     * @return array
     */
    function SelectDetailed($type, $date1 = '', $date2 = '', $accounts = '', $currencyId = 0){

        $sql = "SELECT
                    c.cat_name,
                    op.`date`,
                    a.account_name,
                    op.money,
                    cur.cur_id
                FROM operation op
                LEFT JOIN accounts a
                    ON a.account_id=op.account_id
                LEFT JOIN category c
                    ON c.cat_id=op.cat_id
                LEFT JOIN currency cur
                    ON cur.cur_id = a.account_currency_id
                WHERE
                    op.user_id= ?
                    AND (op.`date` BETWEEN ? AND ?)
                    AND op.accepted=1
                    AND op.deleted_at IS NULL
                    AND c.cat_name <> ''
                    AND a.account_id IN({$accounts})
                    AND op.`type` = ?
                ORDER BY c.cat_name";

            $result = $this->_db->query($sql, $this->_user->getId(), $date1, $date2, $type);

            foreach($result as $key => $value) {

                $result[$key] = $value;

                $money = new myMoney(abs($value['money']), $value['cur_id']);
                $result[$key]['money'] = $this->ex->convert($money, $currencyId)->getAmount();

            }

        return array($result);
    }


    /**
     * Сравнение расходов за периоды, Сравнение доходов за периоды
     *
     * @param int               $type           Тип операции. 0 - Расход, 1 - Доход
     * @param string mysqldate  $date1          Дата начала первого периода в формате "YYYY-MM-DD"
     * @param string mysql date $date2          Дата окончания первого периода в формате "YYYY-MM-DD"
     * @param string mysql date $date3          Дата начала второго периода в формате "YYYY-MM-DD"
     * @param string mysql date $date4          Дата окончания второго периода в формате "YYYY-MM-DD"
     * @param int | string      $accounts       Счёт в виде числа, или несколько чисел разделённых запятой
     * @param int               $currencyId     Ид валюты в которой выводить результат
     * @return array
     */
    function CompareForPeriods($type, $date1='', $date2='', $date3='', $date4='', $accounts = '', $currencyId = 0)
    {

        $sql = "SELECT
                    c.cat_name,
                    c.cat_id,
                    cur.cur_id,
                    sum(abs(op.money)) as su,
                    1 as per
                FROM operation op
                LEFT JOIN accounts a
                    ON a.account_id=op.account_id
                LEFT JOIN category c
                    ON c.cat_id=op.cat_id
                LEFT JOIN currency cur
                    ON cur.cur_id = a.account_currency_id
                WHERE
                    op.`type`= ?
                    AND (op.`date` BETWEEN ? AND ?)
                    AND op.user_id= ? AND op.accepted=1
                    AND a.account_id IN({$accounts})
                GROUP BY c.cat_name, a.account_id

                UNION

                SELECT
                    c.cat_name,
                    c.cat_id,
                    cur.cur_id,
                    sum(abs(op.money)) as su,
                    2 as per
                FROM operation op
                LEFT JOIN accounts a
                    ON a.account_id=op.account_id
                LEFT JOIN category c
                    ON c.cat_id=op.cat_id
                LEFT JOIN currency cur
                    ON cur.cur_id = a.account_currency_id
                WHERE
                    op.`type`= ?
                    AND (op.`date` BETWEEN ? AND ?)
                    AND op.user_id= ?
                    AND op.accepted=1
                    AND op.deleted_at IS NULL
                    AND a.account_id IN({$accounts})
                GROUP BY c.cat_name, a.account_id";

        $rows = $this->_db->query($sql, $type, $date1, $date2, $this->_user->getId(),
            $type, $date3, $date4, $this->_user->getId());

        $result = array();
        foreach($rows as $key => $value) {

            $money = new myMoney(abs($value['su']), $value['cur_id']);

            $tempId = $value['per'].$value['cat_id'];

            $result[$tempId] = $value;
            unset($result[$tempId]['cur_id']);
            unset($result[$tempId]['su']);

            if (isset($result[$tempId]['su'])) {
                $result[$tempId]['su'] += $this->ex->convert($money, $currencyId)->getAmount();
            } else {
                $result[$tempId]['su'] = $this->ex->convert($money, $currencyId)->getAmount();
            }

        }

        return array($result);
    }
}
