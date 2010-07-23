<?php

/**
 * Класс модели для управления бюджетом
 * @category budget
 * @author Max Kamashev <max.kamashev@gmail.com>
 * @copyright http://easyfinance.ru/
 */
class Budget_Model {
    /**
     * Ссылка на экземпляр класса базы данных
     * @var DbSimple_Mysql
     */
    private $db = null;

    /**
     * Конструктор
     * @return void
     */
    function __construct()
    {
        $this->db = Core::getInstance()->db;
    }

    /**
     * Загружает весь список бюджета за указанный срок
     * @param date $start
     * @param date $end
     * @param int $user_id
     * @param int $category
     * @param int $currency_id
     * @return
     */
    function loadBudget($start = null, $end = null, $user_id = null, $category = null, $currency_id = null)
    {
        if ( ! $user_id ) {
            $user_id = Core::getInstance()->user->getId();
        }

        if ( ! $start ) {
            $start = date( 'Y-m-01');
        }

        if ( ! $end ) {
            $end   = date( 'Y-m-d',
                mktime(0, 0, 0, date('m', strtotime($start . ' 00:00:00')) +1, 0)
            );
        }

        // Считаем факт доходов и факт расходов
        $sqloper = "SELECT
                        sum(o.money) as money,
                        o.cat_id,
                        a.account_currency_id AS currency_id
                    FROM operation o
                    INNER JOIN accounts a
                    ON
                        a.account_id=o.account_id
                    WHERE
                        o.user_id = ?
                    AND
                        o.`type` IN (0,1)
                    AND
                        o.accepted=1
                    AND
                        o.deleted_at IS NULL
                    AND
                        a.deleted_at IS NULL
                    AND
                        o.date >= ? AND o.date <= ?
                    GROUP BY
                        o.cat_id, a.account_id";

        // Сумма операций по категориям
        $arrayoper = Core::getInstance()->db->select($sqloper, $user_id, $start, $end);

        $fact = array();
        foreach ($arrayoper as $value) {
            // Знак суммы имеет значение
            $money = new myMoney($value['money'], $value['currency_id']);
            $sum = sfConfig::get('ex')->convert($money, $currency_id)->getAmount();

            $fact[$value['cat_id']] = (float) @$fact[$value['cat_id']] + (float)$sum;
        }

        // Делаем выборку по всем запланированным доходам и расходам
        $sqlbudg = "SELECT
                        b.category,
                        b.drain,
                        b.currency,
                        b.amount,
                        DATE_FORMAT(b.date_start,'%d.%m.%Y') AS date_start,
                        DATE_FORMAT(b.date_end,'%d.%m.%Y') AS date_end,
                        (
                            SELECT AVG(ABS(money))
                            FROM operation o
                            WHERE
                                (o.date >= ADDDATE(b.date_start, INTERVAL -3 MONTH)
                                    AND o.date <= LAST_DAY(b.date_start))
                                AND b.category = o.cat_id
                                AND b.user_id=o.user_id
                                AND o.deleted_at IS NULL
                        ) AS avg_3m
                    FROM (budget b
                    INNER JOIN category c ON b.user_id= ? AND c.user_id = ? AND c.cat_id=b.category)
                    LEFT JOIN category cp ON cp.user_id= ? AND c.cat_id = cp.cat_parent
                    WHERE
                        b.user_id= ?
                    AND
                        b.date_start= ?
                    AND
                        b.date_end=LAST_DAY(b.date_start)
                    AND
                        c.deleted_at IS NULL
                    AND
                        cp.cat_id IS NULL
                    ORDER BY c.cat_parent";

        $arraybudg = Core::getInstance()->db->select($sqlbudg, $user_id, $user_id, $user_id, $user_id, $start);

        $list = array(
            'd' => array(),
            'p' => array()
        );

        foreach ($arraybudg as $var) {

            // Добавляем категорию в список
            if ($var['drain'] == 1) {
                $list['d'][$var['category']] = array(
                    'amount' => (float)$var['amount'], // Планируемая сумма
                    'money'  => 0,  // Фактическая сумма
                    'mean'   => (float)$var['avg_3m']  // Среднее за три месяца
                );
            } else {
                $list['p'][$var['category']] = array(
                    'amount' => (float)$var['amount'], // Планируемая сумма
                    'money'  => 0,  // Фактическая сумма
                    'mean'   => (float)$var['avg_3m']  // Среднее за три месяца
                );
            }
        }

        foreach ($fact as $key => $var){
            // Фактическая сумма
            if ( (float) $var <= 0)
            {
                $list['d'][$key]['money']  = abs((float)$var);
                $list['d'][$key]['amount'] = (float)@$list['d'][$key]['amount'];
                $list['d'][$key]['mean']   = (float)@$list['d'][$key]['mean'];
            } else {
                $list['p'][$key]['money']  = (float)abs($var);
                $list['p'][$key]['amount'] = (float)@$list['p'][$key]['amount'];
                $list['p'][$key]['mean']   = (float)@$list['p'][$key]['mean'];
            }
        }

        return array (
            'list' => $list
        );
    }

    /**
     * Добавляет новые данные в бюдждет
     * @param array mixed $data
     * @param date $date
     * @return array
     */
    function add($data, $date)
    {
        $sql = '';
        $delsql = '';
        $cat = Core::getInstance()->user->getUserCategory();
        foreach ($data as $key => $value) {
            if ((string)$key == 'd') {
                $drain = 1;
            } elseif ((string)$key == 'p') {
                $drain = 0;
            }

            foreach ($value as $k => $v) {
                if ($cat[$k]['type'] == 0 || ($cat[$k]['type'] == 1 && $drain == 0) || ($cat[$k]['type'] == -1 && $drain == 1)) {

                        $key = (string)(''.Core::getInstance()->user->getId().'-'.$k.'-'.$drain.'-'.$date);

                        if ( $v ) {
                            if (!empty ($sql)) $sql .= ',';
                            $sql .= '("' . Core::getInstance()->user->getId() . '","' . (int)$k . '","' .
                                $drain . '","' . $v . '","' . $date . '", LAST_DAY("'.$date.'"), NOW(),"'.$key.'")';
                        } elseif ( $v == 0 ) {
                            if (!empty($delsql)) $delsql .= ',';
                            $delsql .= "'$key'";
                        }
                }
            }
        }

        if ( !( empty ($delsql) ) ) {
            $delsql = "DELETE FROM `budget` WHERE `key` IN (" . $delsql . ")";
            $this->db->query($delsql);
        }

        if (!empty ($sql)) {
            $sql = "REPLACE INTO budget (`user_id`,`category`,`drain`,
                `amount`,`date_start`,`date_end`,`dt_create`,`key`) VALUES " . $sql;
            $this->db->query($sql);
            Core::getInstance()->user->initUserBudget();
            Core::getInstance()->user->save();
            return array('result' => array('text' => ''));
        }

        return array(
            'error' => array(
                'text' => 'Ничего не добавлено'
            )
        );
    }

    /**
     * Редактирует данные в бюджете
     * @param string $type "p"|"d" Доход или расход
     * @param int $id Ид категории
     * @param float $value
     * @param date $date
     * @return array mixed Если пустой массив, значит нет ошибок, иначе в массиве возвратятся ошибки
     */
    function edit($type, $id, $value, $date)
    {
        $sql = "UPDATE budget SET amount = ? WHERE `key`= ?";
        $key = '' . Core::getInstance()->user->getId() . '-' . $id . '-'
            . ((trim($type) == 'd')? 1 : 0) . '-' . $date;
        if (@$this->db->query($sql, $value, $key)) {
            Core::getInstance()->user->initUserBudget();
            Core::getInstance()->user->save();
            return array('result' => array('text' => ''));
        } else {
            $key = '' . Core::getInstance()->user->getId() . '-' . $id . '-'
            . ((trim($type) == 'd')? 1 : 0) . '-' . $date;
            $sql = '("' . Core::getInstance()->user->getId() . '","' . $id . '","' .
                            ((trim($type) == 'd')? 1 : 0) . '","' . $value . '","' . $date . '", LAST_DAY("'.$date.'"), NOW(),"'.$key.'")';
            $sql = "INSERT INTO budget (`user_id`,`category`,`drain`,
                `amount`,`date_start`,`date_end`,`dt_create`,`key`) VALUES
                " . $sql;
            if (!@$this->db->query($sql)){
                if ( substr(mysql_error(),0,16) == "Duplicate entry " )
                    return array(
                        'error' => array(
                        'text' => 'Ошибка при редактировании бюджета'
                     )
            );

            }
                return array('result' => array('text' => ''));
            /*} else
             return array(
                'error' => array(
                    'text' => 'Ошибка при редактировании бюджета'
                 )
            );//*/
        }
    }

    /**
     * Удаляет категорию в бюджете
     * @param int $category
     * @param DATETIME $date MYSQL
     * @param string $type "p"|"d" Доход или расход
     */
    function del($category, $date, $type)
    {
        $sql = "DELETE FROM budget WHERE `key` = ?";
        $key = '' . Core::getInstance()->user->getId() . '-' . $category . '-' . ((trim($type) == 'd')? 1 : 0) . '-' . $date;
        if (!@$this->db->query($sql, $key)) {
            Core::getInstance()->user->initUserBudget();
            Core::getInstance()->user->save();
            return array(
                'error' => array(
                    'text' => 'Ошибка при удалении бюджета'
                )
            );
        } else {
            return array('result' => array('text' =>''));
        }
    }
}
