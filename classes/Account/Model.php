<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * 
 */
class Account_Model {

    /**
     *
     * @var <type>
     */
    //private $db = null;
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
        /*foreach ($rec as $k=>$v){
            $this->addAccount($v['remotekey'],$v['name'],$v['cur'],$v['date'],$v['startbalance'],$v['descr']);
        }*/
        
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
    function addAccount($id=0, $name='', $curid=0, $date='', $amount=0, $descr='', $user_id='', $db=''){
        $cur=1;
        if ($curid == 1) $cur=1;
        $sql = "INSERT INTO accounts (`account_name`, `account_type_id`, `account_description`,
            `account_currency_id`, `user_id`) VALUES (?,?,?,?,?);";
        $query = $db->query($sql, $name, 1, $descr, $cur, $user_id);
        return mysql_insert_id();//*/
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
    function editAccount($id=0, $name='', $curid=0, $date='', $amount=0, $descr='', $user_id, $db=''){
        $cur=1;
        if ($curid == 1) $cur=1;
        /*$sql = "UPDATE accounts SET (`account_name`, `account_type_id`, `account_description`,
            `account_currency_id`, `user_id`) VALUES (?,?,?,?,?) WHERE account_id =?;";*/
        $sql = "UPDATE accounts SET `account_name`=?, `account_type_id`=?, `account_description`=?,
            `account_currency_id`=?, `user_id`=? WHERE account_id =?;";
        echo ($name.' '.$cur.' '.$id.' '.$descr.' '.$user_id.' '.$id);
        return $db->query($sql, $name, 1, $descr, $cur, $user_id, $id);
    }

    /**
     *
     * @param <type> $id
     * @return <type>
     */
    function deleteAccount($id=0, $db=''){
        //echo ($id.'<br>');
        $sql="DELETE FROM accounts WHERE account_id =?;";
        return $db->query($sql, $id);
    }

    function formAccount($date='', &$data='', $user_id='', $db=''){
        $sql = "SELECT * FROM accounts WHERE user_id = ? ;";
        $a = $db->query($sql, $user_id);
        //echo($a[0]['cat_name']);
        foreach ($a as $key=>$v){
            $data[4][0]['tablename'] = 'Accounts';
            $sql2 = "SELECT money, `date` FROM operation WHERE user_id=? AND account_id=? AND `dt_create` BETWEEN '$date' AND NOW()";
                $b = $db->query($sql2, $user_id, $a[$key]['account_id']);
            if ($b[0]['money'] != null){         
                $data[4][$key+1]['easykey'] = (int)$a[$key]['account_id'];
                $data[4][$key+1]['name'] = $a[$key]['account_name'];
                $data[4][$key+1]['cur'] = (int)$a[$key]['account_currency_id'];

                $data[4][$key+1]['date'] = $b[0]['date'];
                $data[4][$key+1]['startbalance'] = (int)$b[0]['money'];
                $data[4][$key+1]['descr'] = $a[$key]['account_description'];

                //добавляем в recordsmap
                $data[1][] = array (
                    'tablename' => 'Accounts',
                    'ekey' => (int)$a[$key]['account_id']);
            }
        }
    }
    
}