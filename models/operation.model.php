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
    private $db = null;

    /**
     * Ссылка на экземпляр класса пользователя
     * @var <User>
     */
    private $user = null;

    /**
     *
     * @var <type>
     */
    private $account_money = 0;

    /**
     *
     * @var <type>
     */
    private $user_money = Array();

    /**
     * 
     * @var <type>
     */
    private $total_sum = 0;

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
        if (IS_DEMO) { //@TODO DEMO
        }else{
/*
    UNION
        SELECT t.id, t.user_id, t.money, DATE_FORMAT(t.date,'%d.%m.%Y'), tt.id,
        t.bill_id, (IF(LENGTH(tt.title)>30,CONCAT(LEFT(tt.title, 30),'..'),tt.title)), cy.cat_parent, bl.bill_name , 0, t.comment, bl.bill_currency, '', '', '', '', 1 AS virt
        FROM target_bill t
            LEFT JOIN target tt ON t.target_id=tt.id
            LEFT JOIN bill bl ON t.bill_id=bl.bill_id
            LEFT JOIN category cy ON cy.cat_id = tt.category_id
        WHERE t.user_id= ? {$order1}
    ORDER BY `date` DESC, `id` DESC";
 */
            $category = Core::getInstance()->user->getUserCategory();
            $sql = "SELECT o.id, o.user_id, o.money, DATE_FORMAT(o.date,'%d.%m.%Y') as `date`, ".
            "o.cat_id, o.account_id, o.drain, o.comment, o.transfer, o.tr_id, 0 AS virt ".
            "FROM operation o ".
            "WHERE o.account_id = ? ".
                "AND o.user_id = ? ".
                "AND (o.`date` BETWEEN ? AND ?) ";
                if (!empty($currentCategory)) {
                    $sql .= " AND (o.cat_id = '{$currentCategory}') ";
                }
            $accounts = Core::getInstance()->user->getUserAccounts();
            $operations = $this->db->select($sql, $currentAccount, $this->user->getId(), $dateFrom, $dateTo);
            foreach ($operations as $key => $val) {
                $val['cat_name'] = $category[$val['cat_id']]['cat_name'];
                $val['cat_parent'] = $category[$val['cat_id']]['cat_parent'];
                $val['account_name'] = $accounts[$val['account_id']]['account_name'];
                $val['account_currency_id'] = $accounts[$val['account_id']]['account_currency_id'];
                $val['cat_transfer'] = $accounts[$val['account_id']]['account_currency_id'];
                //$val['cur_name'] = $accounts[$val['cur_id']]['cur_name'];
                $operations[$key] = $val;
            }
            //bt.account_name as cat_transfer, //@TODO
            return $operations;
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