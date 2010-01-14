<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);

class SyncDebet_Model {

    private $db = null;
    private $user = null;

    /**
     * Инициализирует пользователя и бд
     * @param int $db
     * @param int $user
     */
    function __construct($db, $user){
        $this->db = $db;
        $this->user = $user;
    }

    /**
     * Добавляет запись о долге в бд. возвращает айди вставленной записи.
     * @param int $id
     * @param string $name
     * @param int $curid
     * @param string $date
     * @param int $amount
     * @param string $descr
     * @return int
     */
    function addDebet($id=0, $name='', $curid=0, $date='', $amount=0, $descr=''){
        $cur=1;
        if ($curid == 1) $cur=1;
        $sql = "INSERT INTO accounts (`account_name`, `account_type_id`, `account_description`,
            `account_currency_id`, `user_id`) VALUES (?,?,?,?,?);";
        $query = $this->db->query($sql, $name, 7, $descr, $cur, $this->user);
        $a = mysql_insert_id();//*/
        $type = "string_value";
            $sql = "INSERT INTO account_field_values (`field_value_id`, `account_fieldsaccount_field_id`,
                                                        `".$type."`, `accountsaccount_id`)
                    VALUES (?,?,?,?);";
            $this->db->query($sql, '0', $a, $name,67);
            $this->db->query($sql, '0', $a, $descr,68);
            $this->db->query($sql, '0', $a, $amount,69);
        return $a;
    }

    /**
     * Редактирует долг
     * @param int $id
     * @param string $name
     * @param int $curid
     * @param string $date
     * @param int $amount
     * @param string $descr
     * @return bool
     */
    function editDebet($id=0, $name='', $curid=0, $date='', $amount=0, $descr=''){
        $cur=1;
        if ($curid == 1) $cur=1;
        /*$sql = "UPDATE accounts SET (`account_name`, `account_type_id`, `account_description`,
            `account_currency_id`, `user_id`) VALUES (?,?,?,?,?) WHERE account_id =?;";*/
        $sql = "UPDATE accounts SET `account_name`=?, `account_type_id`=?, `account_description`=?,
            `account_currency_id`=?, `user_id`=? WHERE account_id =?;";
        //echo ($name.' '.$cur.' '.$id.' '.$descr.' '.$user_id.' '.$id);
        return $this->db->query($sql, $name, 7, $descr, $cur, $this->user, $id);
    }

    /**
     * Удаляет долг по его айдишнику
     * @param int $id
     * @return bool
     */
    function deleteDebet($id=0){
        //echo ($id.'<br>');
        $sql="DELETE FROM accounts WHERE account_id =?;";
        return $this->db->query($sql, $id);
    }
    /**
     * Формирует массив долгов на основе времени последней синхронизации
     * @param string $date
     * @param array $data
     */
    function formDebet($date='', &$data=''){
        $sql = "SELECT * FROM accounts WHERE user_id = ? AND account_type_id=7;";
        $a = $this->db->query($sql, $this->user);
        //echo($a[0]['cat_name']);
        foreach ($a as $key=>$v){
            $data[8][0]['tablename'] = 'Debets';
            $sql2 = "SELECT money, `date` FROM operation WHERE user_id=? AND account_id=? AND `dt_create` BETWEEN '$date' AND NOW()-100";
                $b = $this->db->query($sql2, $this->user, $a[$key]['account_id']);
            if ($b[0]['money'] != null){
                $data[8][$key+1]['easykey'] = (int)$a[$key]['account_id'];
                $data[8][$key+1]['name'] = $a[$key]['account_name'];
                $data[8][$key+1]['currency'] = (int)$a[$key]['account_currency_id'];

                $data[8][$key+1]['date'] = $b[0]['date'];
                $data[8][$key+1]['amount'] = (int)$b[0]['money'];
                //$data[8][$key+1]['descr'] = $a[$key]['account_description'];

                //добавляем в recordsmap
                /*$data[1][] = array (
                    'tablename' => 'Debets',
                    'ekey' => (int)$a[$key]['account_id']);*/
            }
        }
    }

}