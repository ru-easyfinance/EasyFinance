<?php
class SyncTransfer_Model {

    private $db = null;
    private $user = null;

    /**
     * конструктор инициализирует пользователя и бд
     * @param int $db
     * @param int $user
     */
    function __construct($db, $user){
        $this->db = $db;
        $this->user = $user;
    }
    /**
     * Добавляет запись о трансфере . возвращает айдишник вставленной записи
     * @param int $id
     * @param int $acc_from
     * @param int $amount
     * @param string $date
     * @param string $descr
     * @param int $acc_to
     * @return int
     */
    function addTransfer($id=0, $acc_from=0, $amount=0, $date='', $descr='', $acc_to=0 ){
        $sql = "INSERT INTO operation
                    (user_id, money, date, cat_id, account_id, tr_id, comment, transfer)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $this->db->query($sql, $this->user, $amount, $date, 0, $acc_from, 1,
            $descr, $acc_to);
        return mysql_insert_id();
    }
    /**
     * Редактирует трансфер
     * @param int $id
     * @param int $acc_from
     * @param int $amount
     * @param string $date
     * @param string $descr
     * @param int $acc_to
     * @return bool
     */
    function editTransfer($id=0, $acc_from=0, $amount=0, $date='', $descr='', $acc_to=0){
        $sql = "UPDATE operation SET money=?, date=?, account_id=?, comment=?, transfer=?
            WHERE user_id=? AND id=?";
        return $this->db->query($sql, $amount, $date, $acc_from, $descr, $acc_to, $this->user,$id);
    }
    /**
     * удаляет трансфер
     * @param int $id
     * @return bool
     */
    function deleteTransfer($id=0){
        $sql = "DELETE FROM operation WHERE id= ? AND user_id= ?";
        return $db->query($sql, $id, $this->user);
        }
    /**
     * Формирует результирующий массив переводов, произошедших с момента последней синхронизации
     * @param string $date
     * @param array $data
     */
    function formTransfer($date='', &$data=''){
        $sql = "SELECT * FROM operation WHERE user_id = ? AND tr_id=1 AND `dt_create` BETWEEN '$date' AND NOW()-100;";
        $a = $this->db->query($sql, $this->user);
        foreach ($a as $key=>$v){
            $data[5][0]['tablename'] = 'Transfers';
            $data[5][$key+1]['easykey'] = (int)$a[$key]['id'];
            $data[5][$key+1]['date'] = $a[$key]['date'];
            $data[5][$key+1]['acfrom'] = (int)$a[$key]['account_id'];
            $data[5][$key+1]['amount'] = (int)$a[$key]['money'];
            $data[5][$key+1]['acto'] = (int)$a[$key]['transfer'];
            $data[5][$key+1]['descr'] = $a[$key]['comment'];


            //добавление в рекордс меп.
            /*$data[1][] = array(
                'tablename' => 'Transfers',
                'ekey' => (int)$a[$key]['id']);*/
        }
    }
}
