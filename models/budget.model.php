<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс модели для управления бюджетом
 * @category budget
 * @author Max Kamashev <max.kamashev@gmail.com>
 * @copyright http://easyfinance.ru/
 * @version SVN $Id: budget.model.php 119 2009-08-04 15:40:26Z korogen $
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
     * Загружает весь список бюджета
     * @param date $start
     * @param date $end
     */
    function loadBudget($start, $end, $user_id, $category)
    {
        if ($user_id == 0) {
            $user_id = Core::getInstance()->user->getId();
        }
        
        if (empty ($start)) {
            $start = date('Y-m-01');
        }

        if (!$category) {
            $category = Core::getInstance()->user->getUserCategory();
        }

//        $sql = "SELECT c.cat_id as category
//            , b.drain, b.currency, b.amount,
//                    DATE_FORMAT(b.date_start,'%d.%m.%Y') AS date_start,
//                    DATE_FORMAT(b.date_end,'%d.%m.%Y') AS date_end
//            , (SELECT AVG(amount) FROM budget t
//                WHERE (t.date_start >= ADDDATE(b.date_start, INTERVAL -3 MONTH)
//                    AND t.date_start <= LAST_DAY(b.date_start))
//                AND b.category = t.category AND b.user_id=t.user_id
//            ) AS avg_3m
//            , (SELECT SUM(o.money) FROM operation o
//                WHERE (o.transfer = NULL OR o.transfer = 0) AND
//            c.cat_id = o.cat_id
//            AND o.date >= ? AND o.date <= LAST_DAY(o.date)
//            ) AS money
//            FROM category c
//            LEFT JOIN budget b
//            ON c.cat_id=b.category AND b.date_start= ? AND b.date_end=LAST_DAY(b.date_start)
//            WHERE c.user_id= ? ORDER BY cat_parent";

        $sql = "SELECT b.category, b.drain, b.currency, b.amount,
                DATE_FORMAT(b.date_start,'%d.%m.%Y') AS date_start,
                DATE_FORMAT(b.date_end,'%d.%m.%Y') AS date_end
                , (SELECT AVG(amount) FROM budget t
                        WHERE (t.date_start >= ADDDATE(b.date_start, INTERVAL -3 MONTH)
                            AND t.date_start <= LAST_DAY(b.date_start))
                        AND b.category = t.category AND b.user_id=t.user_id
                ) AS avg_3m
                , (SELECT SUM(o.money) FROM operation o
                    WHERE (o.transfer = NULL OR o.transfer = 0) AND b.category = o.cat_id
                    AND o.date >= ? AND o.date <= LAST_DAY(o.date)
                ) AS money
        FROM budget b
        LEFT JOIN category c ON c.cat_id=b.category
        WHERE b.user_id= ? AND b.date_start= ? AND b.date_end=LAST_DAY(b.date_start)
        ORDER BY c.cat_parent ;";

        $array = Core::getInstance()->db->select($sql, $start, $user_id, $start);

        $list = array(
            'd' => array(),
            'p' => array()
        );
        $drain_all = 0; $profit_all = 0;

        foreach ($array as $var) {

            // Если это родительская категория, то подсчитываем общую сумму
            if ( (int)$category[$var['category']]['cat_parent'] == 0 ) {
                if ($var['drain'] == 1) {
                    $drain_all += (float)$var['amount'];
                } else {
                    $profit_all += (float)$var['amount'];
                }
            }
            
            // Добавляем категорию в список
            if ($var['drain'] == 1) {
                $list['d'][$var['category']] = array(
                    'amount' => (float)$var['amount'],
                    'money'  => (float)$var['money']
                );
            } else {
                $list['p'][$var['category']] = array(
                    'amount' => (float)$var['amount'],
                    'money'  => (float)$var['money']
                );
            }

//            // Создаём родительскую категорию
//            if ( (int)$category[$var['category']]['cat_parent'] == 0 ) {
//                $list['c_'.$var['category']] = array (
//                    'name'         => $category[$var['category']]['cat_name'],
//                    'category'     => $var['category'],
//                    'total_drain'  => 0,
//                    'total_profit' => 0,
//                    'children'     => array()
//                );
//                if ($var['drain'] == 1) {
//                    $list['c_'.$var['category']]['total_drain'] = (float)$var['amount'];
//                    $drain_all += (float)$var['amount'];
//                } else {
//                    $list['c_'.$var['category']]['total_profit'] = (float)$var['amount'];
//                    $profit_all += (float)$var['amount'];
//                }
//            } else {
//                if (is_null($var['drain'])) {
//                    $drain = -1;
//                }elseif ((int)$var['drain'] === 1) {
//                    $drain = 0;
//                } elseif ((int)$var['drain'] === 0) {
//                    $drain = 1;
//                }
//                // Добавляем ребёнка к родителю
//                $list['c_'.$category[$var['category']]['cat_parent']]['children'][] = array(
//                    'id'         => (int)$var['category'],
//                    'category'   => (int)$var['category'],
//                    'name'       => (string)$category[$var['category']]['cat_name'],
//                    'amount'     => (float)$var['amount'],
//                    'cur'        => Core::getInstance()->currency[$var['currency']]['abbr'],
//                    'mean_drain' => round((int)$var['avg_3m'],2),//средний расход
//                    'type'       => $drain, //расходная - 0, доходный - 1, -1 - нул
//                    'money'      => (float)$var['money']
//                );
//            }
        }

        return array (
            'list' => $list,
            'main' => array (
                'drain_all'  => $drain_all,
                'profit_all' => $profit_all,
                'start'      => $start,
                'end'        => $end
            )
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
        $cat = Core::getInstance()->user->getUserCategory();
        foreach ($data as $key => $value) {
            if ((string)$key == 'r') {
                $drain = 1;
            } elseif ((string)$key == 'd') {
                $drain = 0;
            }

            foreach ($value as $k => $v) {
                if ($cat[$k]['type'] == 0 ||
                    ($cat[$k]['type'] == 1 && $drain == 0) || ($cat[$k]['type'] == -1 && $drain == 1)) {

                        $key = (string)(''.Core::getInstance()->user->getId().'-'.$k.'-'.$drain.'-'.$date);
                        if (!empty ($sql)) $sql .= ',';
                        $sql .= '("' . Core::getInstance()->user->getId() . '","' . (int)$k . '","' .
                            $drain . '","' . $v . '","' . $date . '", LAST_DAY("'.$date.'"), NOW(),"'.$key.'")';
                }
            }
        }
        if (!empty ($sql)) {
            $sql = "REPLACE INTO budget (`user_id`,`category`,`drain`,
                `amount`,`date_start`,`date_end`,`dt_create`,`key`) VALUES " . $sql;
            $this->db->query($sql);
            return array();
        }
        return false;
    }
}
