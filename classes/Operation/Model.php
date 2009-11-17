<?php
class Operation_Model {
    function addOperation($id=0, $acc_id=0, $drain=0, $date='', $catid='', $amount=0, $descr='', $user, $db ){
        $sql = "INSERT INTO `operation` (`user_id`, `money`, `date`, `cat_id`, `account_id`,
                `drain`, `comment`, `dt_create`) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $query = $db->query($sql, $user, $amount, $date, $catid, $acc_id, $drain, $descr);
        return mysql_insert_id();
    }
    function editOperation($id=0, $acc_id=0, $darain=0, $date='', $catid='', $amount=0, $descr='', $user, $db){
        $sql = "UPDATE operation SET money=?, date=?, cat_id=?, account_id=?, drain=?, comment=?
                WHERE user_id = ? AND id = ?";
        return $db->query($sql, $amount, $date, $catid, $acc_id, $drain, $descr, $user,$id);
    }
    function deleteOperation($id=0, $user=0, $db=''){
        $sql = "DELETE FROM operation WHERE id= ? AND user_id= ?";
        return $db->query($sql, $id, $user);
        }

    function formOperation($date='', &$data='', $user_id='', $db=''){
        //echo ('время синхронизации'.$date);
        //echo ($date);
        $sql = "SELECT * FROM operation WHERE user_id = ? AND tr_id is null AND drain = 0 AND `dt_create` BETWEEN '$date' AND NOW()-100;";
        $a = $db->query($sql, $user_id);
        //echo($a[0]['cat_name']);
        foreach ($a as $key=>$v){
            $data[9][0]['tablename'] = 'Incomes';
            $data[9][$key+1]['easykey'] = (int)$a[$key]['id'];
            $data[9][$key+1]['date'] = $a[$key]['date'];
            $data[9][$key+1]['category'] = (int)$a[$key]['cat_id'];

            $sql2 = "SELECT cat_parent FROM category WHERE user_id=? AND cat_id=?";
            $b = $db->query($sql2, $user_id, $a[$key]['cat_id']);
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
        $sql = "SELECT * FROM operation WHERE user_id = ? AND tr_id is null AND drain = 1 AND `dt_create` BETWEEN '$date' AND NOW()-100;";
        $a = $db->query($sql, $user_id);
        //echo($a[0]['cat_name']);
        foreach ($a as $key=>$v){
            $data[10][0]['tablename'] = 'Outcomes';
            $data[10][$key+1]['easykey'] = (int)$a[$key]['id'];
            $data[10][$key+1]['date'] = $a[$key]['date'];
            $data[10][$key+1]['category'] = $a[$key]['cat_id'];

            $sql2 = "SELECT cat_parent FROM category WHERE user_id=? AND cat_id=?";
            $b = $db->query($sql2, $user_id, $a[$key]['cat_id']);
            $data[10][$key+1]['parent'] = (int)$b[0]['cat_parent'];

            $data[10][$key+1]['account'] = (int)$a[$key]['account_id'];
            $data[10][$key+1]['amount'] = (int)$a[$key]['money'];
            $data[10][$key+1]['descr'] = $a[$key]['comment'];

            //добавление в рекордс меп.
            $data[1][] = array(
                'tablename' => 'Outcomes',
                'ekey' => (int)$a[$key]['id']);
        }

        return $data;
    }
}
