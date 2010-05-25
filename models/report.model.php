<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс модели для управления отчётами
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

    private $_user = NULL;

    private $_fullMonth;

    private $_shortMonth;

    /**
     * Обменник для валют
     * @var efCurrencyExchange
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
        $this->ex = sfConfig::get('ex');
    }


    /**
     * Получает список валют валют и их курсы
     * @return array
     */
    private function getCurrency( oldUser $user )
    {
        $currency = array();
        // @XXX: Если ид валюты 4,6,9, то пересчитываем через них, иначе через рубль
        // Получаем список последних валют, и раскладываем их по id
        $sql = "SELECT currency_id AS id, currency_sum AS currency, cur_char_code AS char_code
        FROM daily_currency d
        LEFT JOIN currency c ON c.cur_id=d.currency_id
        WHERE
        currency_from = ?
            AND currency_date = (SELECT MAX(currency_date) FROM daily_currency WHERE user_id=0)
        ORDER BY id";

        if ($user->getUserProps('user_currency_default') == 1) {
            $currency[1]['value']     = 1;
            $currency[1]['char_code'] = 'RUB'; //@XXX RUR???
        } elseif($user->getUserProps('user_currency_default') == 4) {
            $currency[4]['value']     = 4;
            $currency[4]['char_code'] = 'UAH';
        } elseif($user->getUserProps('user_currency_default') == 6) {
            $currency[6]['value']     = 6;
            $currency[6]['char_code'] = 'BYR';
        } elseif($user->getUserProps('user_currency_default') == 9) {
            $currency[9]['value']     = 9;
            $currency[9]['char_code'] = 'KZT';
        }


        foreach(Core::getInstance()->db->select($sql, $user->getUserProps('user_currency_default')) as $value){
            $currency[$value['id']]['value'] = $value['currency'];
            $currency[$value['id']]['char_code'] = $value['char_code'];
        }

        return $currency;
    }


    /**
     * Доходы, Расходы
     *
     * @param int $drain 0 - доход, 1 - расход
     * @param string mysqldate $start
     * @param string mysqldate $end
     * @param string | int $accounts Ид или список ид счетов через запятую. Например: 123,23,1234,324 или 34
     * @param int $currency - Ид валюты, в какой возвращать значения
     * @return array
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

        $array = $this->_db->select($sql, Core::getInstance()->user->getId(), $drain, $start, $end);

        // Создаём чистый массив и наполняем его чистыми данными (конвертируя автоматом курс валюты)
        $result = array();

        foreach ($array as $key => $value ) {

            $result[ $key ]['cat'] = $value['cat'];

            $money = new efMoney(abs($value['money']), $value['cur_id']);
            $amount = $this->ex->convert($money, $currency_id)->getAmount();

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

        $sort = array();

        foreach($result as $value) {

            $money = new efMoney(abs($value['money']), $value['cur_id']);
            $amount = $this->ex->convert($money, $currency_id)->getAmount();

            // Доходы
            if ($value['drain'] == 0) {
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
     * @param int $drain                0 - Доход, 1 - Расход
     * @param string mysqldate $date1   Например: '2010-05-22'
     * @param string mysqldate $date2   Например: '2010-05-22'
     * @param int|string $accounts      Ид счёта, или ид счетов через запятую, по которым нужно выдать результат
     * @param int $currency_id          Ид валюты, в которой ожидается результат
     * @return array
     */
    function SelectDetailed($drain, $date1 = '', $date2 = '', $accounts = '', $currency_id = 0){

        $sql = "SELECT c.cat_name, op.`date`, a.account_name, op.money, cur.cur_id
                FROM operation op
                LEFT JOIN accounts a ON a.account_id=op.account_id
                LEFT JOIN category c ON c.cat_id=op.cat_id
                LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
                WHERE  op.drain=?  AND (op.`date` BETWEEN ? AND ?) AND op.user_id= ? AND op.accepted=1
                    AND c.cat_name <> '' AND a.account_id IN({$accounts})
                    AND op.transfer = 0 AND ( op.tr_id < 1 OR ISNULL(op.tr_id) )
                ORDER BY c.cat_name";

            $result = $this->_db->query($sql, $drain, $date1, $date2, $this->_user->getId());

            foreach($result as $key => $value) {

                $result[$key] = $value;

                $money = new efMoney(abs($value['money']), $value['cur_id']);
                $result[$key]['money'] = $this->ex->convert($money, $currency_id)->getAmount();

            }

        return array($result);
    }


    /**
     * Сравнение расходов за периоды, Сравнение доходов за периоды
     *
     * @param int $drain
     * @param string mysql date $date1
     * @param string mysql date $date2
     * @param string mysql date $date3
     * @param string mysql date $date4
     * @param int|string $accounts
     * @param int $currency_id
     * @return array
     */
    function CompareForPeriods($drain, $date1='', $date2='', $date3='', $date4='', $accounts = '', $currency_id = 0)
    {

        $sql = "SELECT c.cat_name, c.cat_id, cur.cur_id, sum(abs(op.money)) as su, 1 as per
                FROM operation op
                LEFT JOIN accounts a ON a.account_id=op.account_id
                LEFT JOIN category c ON c.cat_id=op.cat_id
                LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
                WHERE op.drain= ?
                    AND (op.`date` BETWEEN ? AND ?)
                    AND op.user_id= ? AND op.accepted=1
                    AND a.account_id IN({$accounts})
                    AND op.transfer = 0 AND ( op.tr_id < 1 OR ISNULL(op.tr_id) )
                GROUP BY c.cat_name, a.account_id
                UNION
                SELECT c.cat_name, c.cat_id, cur.cur_id, sum(abs(op.money)) as su, 2 as per
                FROM operation op
                LEFT JOIN accounts a ON a.account_id=op.account_id
                LEFT JOIN category c ON c.cat_id=op.cat_id
                LEFT JOIN currency cur ON cur.cur_id = a.account_currency_id
                WHERE op.drain= ?
                    AND (op.`date` BETWEEN ? AND ?)
                    AND op.user_id= ? AND op.accepted=1
                    AND a.account_id IN({$accounts})
                    AND op.transfer = 0 AND ( op.tr_id < 1 OR ISNULL(op.tr_id) )
                GROUP BY c.cat_name, a.account_id";

        $rows = $this->_db->query($sql, $drain, $date1, $date2, $this->_user->getId(),
            $drain, $date3, $date4, $this->_user->getId());

        $result = array();
        foreach($rows as $key => $value) {

            $money = new efMoney(abs($value['su']), $value['cur_id']);

            $tempId = $value['per'].$value['cat_id'];

            $result[$tempId] = $value;
            unset($result[$tempId]['cur_id']);
            unset($result[$tempId]['su']);

            if (isset($result[$tempId]['su'])) {
                $result[$tempId]['su'] += $this->ex->convert($money, $currency_id)->getAmount();
            } else {
                $result[$tempId]['su'] = $this->ex->convert($money, $currency_id)->getAmount();
            }

        }

        return array($result);
    }
}
