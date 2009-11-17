<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * 
 */
class Category_Model {

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


    function addCategory($id=0, $parent_id=0, $name='', $user_id='', $db=''){
        $sys = 0;// системная категория не установлена
        $sql = "INSERT INTO category(user_id, cat_parent, system_category_id, cat_name, type,
            dt_create) VALUES(?, ?, ?, ?, ?, NOW())";
        $query = $db->query($sql, $user_id, $parent_id, $sys, $name, 0);
        return mysql_insert_id();//*/
    }


    function editCategory($id=0, $cat_id=0, $parent_id=0, $name='', $user_id='', $db=''){
        $sys = 0;// системная категория не установлена
        $sql = "UPDATE category SET cat_parent = ?, system_category_id = ? , cat_name = ?, type =?
            WHERE user_id = ? AND cat_id = ?";
        //echo ($name.' '.$cur.' '.$id.' '.$descr.' '.$user_id.' '.$id);
        return $db->query($sql, $parent_id, $sys, $name, 0, $user_id, $id);
    }


    function deleteCategory($id=0, $db=''){
        //echo ($id.'<br>');
        $sql="DELETE FROM category WHERE cat_id =?;";
        return $db->query($sql, $id);
    }

    function formCategory($date='', &$data='', $user_id='', $db=''){
        //echo ('время синхронизации'.$date);
        //echo ($date);

        $sql = "SELECT * FROM category WHERE user_id = ? AND `dt_create` BETWEEN '$date' AND NOW()-100;";
        $a = $db->query($sql, $user_id);
        //echo($a[0]['cat_name']);
        foreach ($a as $key=>$v){
            $data[6][0]['tablename'] = 'Categories';
            $data[6][$key+1]['easykey'] = (int)$a[$key]['cat_id'];
            $data[6][$key+1]['parent'] = (int)$a[$key]['cat_parent'];
            $data[6][$key+1]['name'] = $a[$key]['cat_name'];

            $data[1][]= array('tablename' => 'Categories',
                'ekey' => (int)$a[$key]['cat_id']);

        }
    }
}