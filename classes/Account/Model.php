<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * 
 */
class Account_Model {

    /**
     *
     * @var <type>
     */
    private $db = null;
    //private $user = null;

    /**
     *
     * @param <type> $acc
     * @param <type> $rec
     * @param <type> $ch
     * @param <type> $del
     */
    function __construct($acc, $rec, $ch, $del){
        //echo ('12');
        //$this->db=DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
        //return true;
        echo ('работаем с категориями<br>');
        //echo ($acc[0]['name'].'<br>');
        foreach ($rec as $k=>$v){
            $this->addAccount($v['remotekey'],$v['name'],$v['cur'],$v['date'],$v['startbalance'],$v['descr']);
        }
        
    }

    /**
     *
     * @param <type> $id
     * @param <type> $name
     * @param <type> $curid
     * @param <type> $date
     * @param <type> $amount
     * @param <type> $descr
     * @param <type> $user_id
     */
    function addAccount($id=0, $name='', $curid=0, $date='', $amount=0, $descr='', $user_id){
        echo ('123');
        /* $cur=1;
        if ($curid == 1) $cur=1;
        $sql = "INSERT INTO accounts (`account_name`, `account_type_id`, `account_description`,
            `account_currency_id`, `user_id`) VALUES (?,?,?,?,?);";
        return $this->db->query($sql, $name, 1, $descr, $cur, $user_id);*/
    }

    /**
     *
     * @param <type> $id
     * @param <type> $name
     * @param <type> $curid
     * @param <type> $date
     * @param <type> $amount
     * @param <type> $descr
     * @param <type> $user_id
     * @return <type>
     */
    function editAccount($id=0, $name='', $curid=0, $date='', $amount=0, $descr='', $user_id){
        $sql = "UPDATE accounts SET (`account_name`, `account_type_id`, `account_description`,
            `account_currency_id`, `user_id`) VALUES (?,?,?,?,?) WHERE account_id =?;";
        return $this->db->query($sql, $name, 1, $descr, $cur, $user_id, $id);
    }

    /**
     *
     * @param <type> $id
     * @return <type>
     */
    function deleteAccount($id=0){
        $sql="DELETE FROM accounts WHERE account_id =?;";
        return $this->db->query($sql, $id);
    }
}