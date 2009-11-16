<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * 
 */
class Periodic_Model {

    /**
     *
     * @var <type>
     */
    //private $db = null;
    //private $user = null;


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


    function addPeriodic($id=0, $categ_id=0, $name='', $user_id='', $db=''){
        //$sys = 0;// системная категория не установлена
        $sql = "INSERT periodic(user_id, category, account, drain, title, date, amount, type_repeat,
            count_repeat, comment, dt_create, infinity) VALUES(?,?,?,?,?,?,?,?,?,?,NOW(),?)";
        /*$query = $db->query($sql, $user_id, $parent_id, $acc_id, 1, $name, , 0);
        return mysql_insert_id();//*/
    }


    function editPeriodic($id=0, $cat_id=0, $parent_id=0, $name='', $user_id='', $db=''){
        //$sys = 0;// системная категория не установлена
        $sql = "UPDATE periodic SET category = ?, account = ?, drain = ?, title = ?, date = ?, amount = ?,
            type_repeat = ?, count_repeat = ?, comment = ?, infinity = ? WHERE id = ? AND user_id = ?";
        //echo ($name.' '.$cur.' '.$id.' '.$descr.' '.$user_id.' '.$id);
        //return $db->query($sql, $parent_id, $sys, $name, 0, $user_id, $id);
    }


    function deletePeriodic($id=0, $db=''){
        //echo ($id.'<br>');
        $sql="DELETE FROM category WHERE cat_id =?;";
        return $db->query($sql, $id);
    }

    
}