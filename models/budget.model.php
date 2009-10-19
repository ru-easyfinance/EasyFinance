<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс модели для управления бюджетом
 * @category budget
 * @author Max Kamashev <max.kamashev@gmail.com>
 * @copyright http://home-money.ru/
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
    function loadBudget($start, $end)
    {

        $sql = "SELECT b.id, b.category, b.drain, b.currency, b.amount,
            DATE_FORMAT(b.date_start,'%d.%m.%Y') AS date_start, DATE_FORMAT(b.date_end,'%d.%m.%Y') AS date_end
                , (SELECT AVG(amount)
                    FROM budget t
                    WHERE (t.date_start BETWEEN ADDDATE(b.date_start, INTERVAL -3 MONTH) AND LAST_DAY(NOW()))
                    AND b.category = t.category AND b.user_id=t.user_id
                ) AS avg_3m
                FROM budget b
                WHERE b.user_id= ? AND b.date_start= ? AND b.date_end=LAST_DAY(NOW())";
        $array = $this->db->select($sql, Core::getInstance()->user->getId(), $start);


        $list = array();
        $category = Core::getInstance()->user->getUserCategory();
        $drain_all = 0; $profit_all = 0;
        foreach ($array as $var) {
            // Создаём родительскую категорию
            if ( !isset($list['c_'.$category[$var['category']]['cat_parent']]) ) {
                $list['c_'.$category[$var['category']]['cat_parent']] = array (
                    'name'         => $category[$var['category']]['cat_name'],
                    'category'     => $var['category'],
                    'total_drain'  => 0,
                    'total_profit' => 0,
                    'children'     => array()
                );
            }
            // Добавляем ребёнка к родителю
            $list['c_'.$category[$var['category']]['cat_parent']]['children'][] = array(
                'id'         => $var['id'],
                'name'       => $category[$var['category']]['cat_name'],
                'amount'     => $var['amount'],
                'cur'        => Core::getInstance()->currency[$var['currency']]['name'],
                'mean_drain' => round($var['avg_3m'],2),//средний расход
                'type'       => ($var['drain']==1)? 0 : 1 //расходная - 0,доходный-1
            );

            // Обновляем суммы
            if ($var['drain'] == 1) {
                $drain_all += $var['amount'];
                $list['c_'.$category[$var['category']]['cat_parent']]['total_drain'] += $var['amount'];
            } else {
                $profit_all += $var['amount'];
                $list['c_'.$category[$var['category']]['cat_parent']]['total_profit'] += $var['amount'];
            }
        }
        return array (
            'list' => $list,
            'main' => array (
                'drain_all'  => $drain_all,
                'profit_all' => $profit_all,
                'start'      => $var['date_start'],
                'end'        => $var['date_end']
            )
        );
    }

    /**
     * Добавляет новые данные в бюдждет
     */
    function add($data)
    {
        print_r($data);
    }
}