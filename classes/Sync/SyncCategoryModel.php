<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);

class SyncCategory_Model {

    private $db = null;
    private $user = null;

    /**
     * конструктор инициализирует юзера и бд.
     * @param int $db
     * @param int $user
     */
    function __construct($db, $user){
        $this->db = $db;
        $this->user = $user;
    }

    /**
     * Функция добавляет категорию в бд. возвращает айди вставленной записи
     * @param int $id
     * @param int $parent_id
     * @param string $name
     * @return int
     */
    function addCategory($id=0, $parent_id=0, $name=''){
        $sys = 0;// системная категория не установлена
        $sql = "INSERT INTO category(user_id, cat_parent, system_category_id, cat_name, type,
            dt_create) VALUES(?, ?, ?, ?, ?, NOW())";
        $query = $this->db->query($sql, $this->user, $parent_id, $sys, $name, 0);
        return mysql_insert_id();//*/
    }

    /**
     * Редактирует категорию
     * @param int $id
     * @param int $cat_id
     * @param int $parent_id
     * @param string $name
     * @return bool
     */
    function editCategory($id=0, $cat_id=0, $parent_id=0, $name=''){
        $sys = 0;// системная категория не установлена
        $sql = "UPDATE category SET cat_parent = ?, system_category_id = ? , cat_name = ?, type =?
            WHERE user_id = ? AND cat_id = ?";
        //echo ($name.' '.$cur.' '.$id.' '.$descr.' '.$user_id.' '.$id);
        return $this->db->query($sql, $parent_id, $sys, $name, 0, $this->user, $id);
    }

    /**
     * Функция удаляет категорию
     * @param int $id
     * @return bool
     */
    function deleteCategory($id=0){
        $sql="DELETE FROM category WHERE cat_id =?;";
        return $this->db->query($sql, $id);
    }

    /**
     * Формирует массив категорий на основе времени прошедшего с момента последней синхронизации
     * @param string $date
     * @param array $data
     */
    function formCategory($date='', &$data=''){
        $sql = "SELECT * FROM category WHERE user_id = ? AND `dt_create` BETWEEN '$date' AND NOW()-100;";
        $a = $this->db->query($sql, $this->user);
        foreach ($a as $key=>$v){
            $data[6][0]['tablename'] = 'Categories';
            $data[6][$key+1]['easykey'] = (int)$a[$key]['cat_id'];
            $data[6][$key+1]['parent'] = (int)$a[$key]['cat_parent'];
            $data[6][$key+1]['name'] = $a[$key]['cat_name'];
            $data[6][$key+1]['type'] = $a[$key]['type'];

            /*$data[1][]= array('tablename' => 'Categories',
                'ekey' => (int)$a[$key]['cat_id']);*/

        }
    }
}