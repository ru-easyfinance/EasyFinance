<?php
class SyncOperation_Model {

    private $db = null;
    private $user = null;
    /**
     * конструктор инициализирует пользователя и дб
     * @param int $db
     * @param int $user
     */
    function __construct($db, $user){
        $this->db = $db;
        $this->user = $user;
    }
    /**
     * Добавляет операцию. возвращает айди вставленной в бд записи.
     * @param int $id
     * @param int $acc_id
     * @param int $drain
     * @param string $date
     * @param int $catid
     * @param int $amount
     * @param string $descr
     * @return int
     */
    function addOperation($id=0, $acc_id=0, $drain=0, $date='', $catid='', $amount=0, $descr=''){
        $sql = "INSERT INTO `operation` (`user_id`, `money`, `date`, `cat_id`, `account_id`,
                `drain`, `comment`, `dt_create`) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $query = $this->db->query($sql, $this->user, $amount, $date, $catid, $acc_id, $drain, $descr);
        return mysql_insert_id();
    }
    /**
     * Редактирует операцию
     * @param int $id
     * @param int $acc_id
     * @param int $drain
     * @param string $date
     * @param int $catid
     * @param int $amount
     * @param string $descr
     * @return bool
     */
    function editOperation($id=0, $acc_id=0, $drain=0, $date='', $catid='', $amount=0, $descr=''){
        $sql = "UPDATE operation SET money=?, date=?, cat_id=?, account_id=?, drain=?, comment=?
                WHERE user_id = ? AND id = ?";
        return $this->db->query($sql, $amount, $date, $catid, $acc_id, $drain, $descr, $this->user,$id);
    }
    /**
     * удаляет операцию
     * @param int $id
     * @return bool
     */
    function deleteOperation($id=0){
        $sql = "DELETE FROM operation WHERE id= ? AND user_id= ?";
        return $this->db->query($sql, $id, $this->user);
        }
    /**
     * Формирует массив операций совершённых с момента последней синхронизации
     * @param string $date
     * @param array $data
     */
    function formOperation($date='', &$data=''){
        $sql = "SELECT * FROM operation WHERE user_id = ? AND tr_id is null AND drain = 0 AND `dt_create` BETWEEN '$date' AND NOW()-100;";
        $a = $this->db->query($sql, $this->user);
        //echo($a[0]['cat_name']);
        foreach ($a as $key=>$v) if ($v['comment']<>'Начальный остаток'){
            $data[9][0]['tablename'] = 'Incomes';
            $data[9][$key+1]['easykey'] = (int)$a[$key]['id'];
            $data[9][$key+1]['date'] = $a[$key]['date'];
            $data[9][$key+1]['category'] = (int)$a[$key]['cat_id'];

            $sql2 = "SELECT cat_parent FROM category WHERE user_id=? AND cat_id=?";
            $b = $this->db->query($sql2, $this->user, $a[$key]['cat_id']);
            $data[9][$key+1]['parent'] = (int)$b[0]['cat_parent'];

            $data[9][$key+1]['account'] = (int)$a[$key]['account_id'];
            $data[9][$key+1]['amount'] = (int)$a[$key]['money'];
            $data[9][$key+1]['descr'] = $a[$key]['comment'];

            //добавление в рекордс меп.
            $data[1][] = array(
                'tablename' => 'Incomes',
                'ekey' => (int)$a[$key]['id']);
        }
        //теперь расходы
        $sql = "SELECT * FROM operation WHERE user_id = ? AND tr_id is null AND money < 0 AND `dt_create` BETWEEN '$date' AND NOW()-100;";
        $a = $this->db->query($sql, $user_id);
        //echo($a[0]['cat_name']);
        foreach ($a as $key=>$v){
            $data[10][0]['tablename'] = 'Outcomes';
            $data[10][$key+1]['easykey'] = (int)$a[$key]['id'];
            $data[10][$key+1]['date'] = $a[$key]['date'];
            $data[10][$key+1]['category'] = $a[$key]['cat_id'];

            $sql2 = "SELECT cat_parent FROM category WHERE user_id=? AND cat_id=?";
            $b = $this->db->query($sql2, $this->user, $a[$key]['cat_id']);
            $data[10][$key+1]['parent'] = (int)$b[0]['cat_parent'];

            $data[10][$key+1]['account'] = (int)$a[$key]['account_id'];
            $data[10][$key+1]['amount'] = (int)abs($a[$key]['money']);
            $data[10][$key+1]['descr'] = $a[$key]['comment'];

            //добавление в рекордс меп.
            /*$data[1][] = array(
                'tablename' => 'Outcomes',
                'ekey' => (int)$a[$key]['id']);*/
        }

        //return $data;
    }
}
