<?php
//echo ('12');
class Account_Model {

    private $db = null;
    //private $user = null;

    function __construct($acc, $rec, $ch, $del){
        //echo ('12');
        //$this->db=DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
        //return true;
        echo ('работаем с категориями<br>');
        //echo ($acc[0]['name'].'<br>');
        foreach ($rec as $k=>$v){
            $this->addAccount($v['remotekey'],$v['name'],$v['cur'],$v['date'],$v['startbalance'],$v['descr']);
        }
        
    }//*/
    function addAccount($id=0, $name='', $curid=0, $date='', $amount=0, $descr='', $user_id){
        echo ('123');
        /* $cur=1;
        if ($curid == 1) $cur=1;
        $sql = "INSERT INTO accounts (`account_name`, `account_type_id`, `account_description`,
            `account_currency_id`, `user_id`) VALUES (?,?,?,?,?);";
        return $this->db->query($sql, $name, 1, $descr, $cur, $user_id);*/
    }
    function editAccount($id=0, $name='', $curid=0, $date='', $amount=0, $descr='', $user_id){
        $sql = "UPDATE accounts SET (`account_name`, `account_type_id`, `account_description`,
            `account_currency_id`, `user_id`) VALUES (?,?,?,?,?) WHERE account_id =?;";
        return $this->db->query($sql, $name, 1, $descr, $cur, $user_id, $id);
    }
    function deleteAccount($id=0){
        $sql="DELETE FROM accounts WHERE account_id =?;";
        return $this->db->query($sql, $id);
    }
}
?>