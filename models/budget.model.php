<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс модели для управления бюджетом
 * @category budget
 * @author Max Kamashev <max.kamashev@gmail.com>
 * @copyright http://easyfinance.ru/
 * @version SVN $Id: $
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
     * @return
     */
    function loadBudget($start = null, $end = null, $user_id = null, $category = null)
    {
        if ( ! $user_id ) {
            $user_id = Core::getInstance()->user->getId();
        }

        // Считаем факт доходов и факт расходов
        $sqloper = "SELECT sum(o.money) as money, o.cat_id FROM operation o
            WHERE o.user_id = ? AND o.transfer=0
                AND o.date >= ? AND o.date <= ?
            AND o.account_id IN (SELECT account_id FROM accounts WHERE user_id = ? )
                GROUP BY o.cat_id";

        $arrayoper = Core::getInstance()->db->select($sqloper, $user_id, $start, $end, $user_id);

        // Делаем выборку по всем запланированным доходам и расходам
        $sqlbudg = "SELECT b.category, b.drain, b.currency, b.amount,
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
                    AND o.account_id IN (SELECT account_id FROM accounts WHERE user_id =".$user_id." )
                ) AS money
        FROM budget b
        LEFT JOIN category c ON c.cat_id=b.category
        WHERE b.user_id= ? AND b.date_start= ? AND b.date_end=LAST_DAY(b.date_start) AND c.visible=1
        ORDER BY c.cat_parent ;";


        $arraybudg = Core::getInstance()->db->select($sqlbudg, $start, $user_id, $start);
        


        $list = array(
            'd' => array(),
            'p' => array()
        );
        
        $drain_all = 0; $profit_all = 0;
        $real_drain = 0; $real_profit = 0;

        foreach ($arraybudg as $var) {
           
            // Добавляем категорию в список
            if ($var['drain'] == 1) {
                $list['d'][$var['category']] = array(
                    'amount' => (float)$var['amount'], // Планируемая сумма
                    'money'  => (float)$var['money'],  // Фактическая сумма
                    'mean'   => (float)$var['avg_3m']  // Среднее за три месяца
                );
            } else {
                $list['p'][$var['category']] = array(
                    'amount' => (float)$var['amount'], // Планируемая сумма
                    'money'  => (float)$var['money'],  // Фактическая сумма
                    'mean'   => (float)$var['avg_3m']  // Среднее за три месяца
                );
            }
        }

        foreach ($arrayoper as $var){
            if ( !(isset($list['p'][$var['cat_id']]) || (isset($list['d'][$var['cat_id']] ) ) ) ){

                if (($var['money'] <= 0))
                {
                    $list['d'][$var['cat_id']] = array(
                        'amount' => 0, // Планируемая сумма
                        'money'  => (float)$var['money'],  // Фактическая сумма
                        'mean'   => 0  // Среднее за три месяца
                    );
                } else {
                    $list['p'][$var['cat_id']] = array(
                        'amount' => 0, // Планируемая сумма
                        'money'  => (float)abs($var['money']),  // Фактическая сумма
                        'mean'   => 0  // Среднее за три месяца
                    );
                }
            }
        }
        
        return array (
            'list' => $list,
            'main' => array () /** @deprecated */
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
