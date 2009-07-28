<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс модели для управления операциями
 * @category operation
 * @author Max Kamashev (ukko) <max.kamashev@gmail.com>
 * @copyright http://home-money.ru/
 * @version SVN $Id$
 */
class Operation_Model {
    /**
     * Ссылка на экземпляр класса базы данных
     * @var <DbSimple_Mysql>
     */
    private $db             = null;

    /**
     * Ссылка на экземпляр класса пользователя
     * @var <User>
     */
    private $user           = null;

    /**
     *
     * @var <type>
     */
    private $account_money  = 0;

    /**
     *
     * @var <type>
     */
    private $user_money     = Array();

    /**
     * 
     * @var <type>
     */
    private $total_sum      = 0;

    /**
     * Конструктор
     * @return bool
     */
    function __construct()
    {
        $this->db = Core::getInstance()->db;
        $this->user = Core::getInstance()->user;
        $this->load();
        return true;
    }

    /**
     * Загрузка данных из сессии
     * @return void
     */
    function load()
    {
        if (isset($_SESSION['user_money'])) {
            $this->user_money = $_SESSION['user_money'];
        } else {
            $this->user_money = array();
        }
        $this->account_money = (int)@$_SESSION['account_money'];
        $this->total_sum = (int)@$_SESSION['total_sum'];
    }

    /**
     * Сохранение данных в сессии
     * @return void
     */
    function save()
    {
        $_SESSION['user_money']    = $this->user_money;
        $_SESSION['account_money'] = $this->account_money;
        $_SESSION['total_sum']     = $this->total_sum;
    }

    /**
     * Получение списка транзакций
     * @param <date> $dateFrom
     * @param <date> $dateTo
     * @param <int> $currentCategory
     * @param <int> $currentAccount
     * @return <array> mixed
     */
    function getOperationList($dateFrom, $dateTo, $currentCategory, $currentAccount)
    {
        //@FIXME Оптимизировать запросы, если возможно
        $order = "AND (m.`date` BETWEEN '{$dateFrom}' AND '{$dateTo}')";
        $order1 = "AND (t.`date` BETWEEN '{$dateFrom}' AND '{$dateTo}')";
        if (!empty($currentCategory)) {
            $order .= " AND (m.cat_id = {$currentCategory} OR c.cat_parent = {$currentCategory})";
            $order1 .= " AND (cy.cat_id = {$currentCategory} OR cy.cat_parent = {$currentCategory})";
        }

        $limit = "";

        if (IS_DEMO) {
            $sql = "SELECT m.id, m.user_id, m.money, DATE_FORMAT(m.date,'%d.%m.%Y') as `date`,
                       m.cat_id, m.bill_id, c.cat_name, c.cat_parent, b.bill_name, m.drain, m.comment,
                       b.bill_currency, cu.cur_name, m.transfer, m.tr_id,
                       bt.bill_name as cat_transfer
                    FROM money m
                    LEFT JOIN category c on c.cat_id = m.cat_id
                    LEFT JOIN bill b on b.bill_id = m.bill_id and b.user_id = ?
                    LEFT JOIN bill bt on bt.bill_id = m.transfer and bt.user_id = ?
                    LEFT JOIN currency cu on cu.cur_id = b.bill_currency
                    WHERE m.bill_id = ?
                           AND m.user_id = ?
                           ".$order."
                    ORDER BY m.`date` DESC, m.id DESC " . $limit;
            return $this->db->select($sql, $this->user->getId(), $this->user->getId(), $currentAccount, $this->user->getId());
        }else{
            $sql = "SELECT m.id, m.user_id, m.money, DATE_FORMAT(m.date,'%d.%m.%Y') as `date`,
                    m.cat_id, m.bill_id, c.cat_name, c.cat_parent, b.bill_name, m.drain, m.comment,
                    b.bill_currency, cu.cur_name, m.transfer, m.tr_id,
                    bt.bill_name as cat_transfer, 0 AS virt
                FROM money m
                    LEFT JOIN category c on c.cat_id = m.cat_id
                    LEFT JOIN bill b on b.bill_id = m.bill_id
                    LEFT JOIN bill bt on bt.bill_id = m.transfer
                    LEFT JOIN currency cu on cu.cur_id = b.bill_currency
                WHERE m.bill_id = ?
                    AND m.user_id = ?
                    {$order}
                UNION
                    SELECT t.id, t.user_id, t.money, DATE_FORMAT(t.date,'%d.%m.%Y'), tt.id,
                    t.bill_id, (IF(LENGTH(tt.title)>30,CONCAT(LEFT(tt.title, 30),'..'),tt.title)), cy.cat_parent, bl.bill_name , 0, t.comment, bl.bill_currency, '', '', '', '', 1 AS virt
                    FROM target_bill t
                        LEFT JOIN target tt ON t.target_id=tt.id
                        LEFT JOIN bill bl ON t.bill_id=bl.bill_id
                        LEFT JOIN category cy ON cy.cat_id = tt.category_id
                    WHERE t.user_id= ? {$order1}
                ORDER BY `date` DESC, `id` DESC";
            $a = $this->db->select($sql, $currentAccount, $this->user->getId(), $this->user->getId());
            die(var_dump($a));
            return $a;
        }
    }

    /**
     * Возвращает все деньги пользователя по определённому счёту
     * @param <string> $bill_id Ид счёта
     * @return <int>
     */
    function getTotalSum($bill_id)
    {
        $sql = "SELECT SUM(money) as sum FROM money WHERE user_id = ? AND bill_id = ?";
        $this->total_sum = $this->db->selectCell($sql, $this->user->getId(), $bill_id);
        return $this->total_sum;
    }
}