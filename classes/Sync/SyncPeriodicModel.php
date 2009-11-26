<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);

/**
 * Модель для  рег.транзакций
 */
class SyncPeriodic_Model {

    //private  static $db = null;self::$db->query();
    private $user = null;
    private $db = null;

    /**
     * конструктор инициализирует юзера и дб
     * @param int $db
     * @param int $user
     */
    function __construct($db, $user){
        $this->db = $db;
        $this->user = $user;
    }
    /**
     * Добавляет регулярную транзакцию. возвращает айди вставленной записи
     * @param int $id
     * @param string $name
     * @param string $date
     * @param int $period
     * @param int $count
     * @param int $category
     * @param int $account
     * @param int $amount
     * @param string $descr
     * @return int
     */
    function addPeriodic($id=0, $name='', $date='', $period=0, $count=0, $category=0, $account=0, $amount=0, $descr=''){
        //$sys = 0;// системная категория не установлена
        $sql = "INSERT periodic(user_id, category, account, drain, title, date, amount, type_repeat,
            count_repeat, comment, dt_create, infinity) VALUES(?,?,?,?,?,?,?,?,?,?,NOW(),?)";
        $query = $this->db->query($sql, $this->user, $category, $account, 1, $name, $date, $amount, $period, $count, $descr, 0);
        return mysql_insert_id();
    }

    /**
     * Редактирует регулярную транзакцию
     * @param int $id
     * @param string $name
     * @param string $date
     * @param int $period
     * @param int $count
     * @param int $category
     * @param int $account
     * @param int $amount
     * @param string $descr
     * @return bool
     */
    function editPeriodic($id=0, $name='', $date='', $period=0, $count=0, $category=0, $account=0, $amount=0, $descr=''){
        $sql = "UPDATE periodic SET category = ?, account = ?, drain = ?, title = ?, date = ?, amount = ?,
            type_repeat = ?, count_repeat = ?, comment = ?, infinity = ? WHERE id = ? AND user_id = ?";
        //echo ($name.' '.$cur.' '.$id.' '.$descr.' '.$user_id.' '.$id);
        return $this->db->query($sql, $category, $account, 1, $name, $date, $amount, $period, $count, $descr, 0, $id, $this->user);
    }

    /**
     * удаляет регулярную транзакцию
     * @param int $id
     * @return bool
     */
    function deletePeriodic($id=0){
        $sql="DELETE FROM periodic WHERE id =? AND user_id=?;";
        return $this->db->query($sql, $id, $this->user);
    }
    /**
     * Формирует массив регулярных транзаций, произведённых в системе с момента последней синхронизации
     * @param syring $date
     * @param array $data
     */
    function formPeriodic($date='', &$data=''){
        $sql = "SELECT * FROM periodic WHERE user_id = ? AND `dt_create` BETWEEN '$date' AND NOW()-100;";
        $a = $this->db->query($sql, $this->user);
        //echo($a[0]['cat_name']);
        foreach ($a as $key=>$v){
            $data[11][0]['tablename'] = 'Plans';

            $this->PlansList[$k+1]['name']=$qw[$i][$k]['name'];
                    $this->PlansList[$k+1]['remotekey']=$qw[$i][$k]['remotekey'];
                    $this->PlansList[$k+1]['date']=formatIsoDateToNormal($qw[$i][$k]['date']);
                    $this->PlansList[$k+1]['period']=$qw[$i][$k]['period'];
                    $this->PlansList[$k+1]['count']=$qw[$i][$k]['count'];
                    $this->PlansList[$k+1]['category']=$qw[$i][$k]['category'];
                    $this->PlansList[$k+1]['account']=$qw[$i][$k]['account'];
                    $this->PlansList[$k+1]['amount']=$qw[$i][$k]['amount'];
                    $this->PlansList[$k+1]['descr']=$qw[$i][$k]['descr'];


                $data[11][$key+1]['name'] = $a[$key]['title'];
                $data[11][$key+1]['easykey'] = (int)$a[$key]['id'];
                $data[11][$key+1]['date'] = $a[$key]['date'];
                $data[11][$key+1]['period'] = $a[$key]['type_repeat'];
                $data[11][$key+1]['count'] = $a[$key]['count_repeat'];
                $data[11][$key+1]['category'] = $a[$key]['category'];
                $data[11][$key+1]['account'] = $a[$key]['account'];
                $data[11][$key+1]['amount'] = $a[$key]['amount'];
                $data[11][$key+1]['descr'] = $a[$key]['comment'];

                //добавляем в recordsmap
                $data[1][] = array (
                    'tablename' => 'Plans',
                    'ekey' => (int)$a[$key]['id']);

        }
    }


}