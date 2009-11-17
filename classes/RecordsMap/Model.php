<?php
class RecordsMap_Model {
    /*function addOperation($id=0, $name='', $curid=0, $date='', $amount=0, $descr='' ){
        $sql = "INSERT INTO `operation` (`user_id`, `money`, `date`, `cat_id`, `account_id`,
                `drain`, `comment`, `dt_create`) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    }*/
    function AddRecordsMapString($us, $tablename, $remkey, $ekey, $system, $db){//$us, $tablename, $remkey, $ekey, $system, $db
        //echo ('12gd');
        //echo ($tablename.$remkey.$ekey.$system.$us);
        $sql = "INSERT INTO records_map (`user_id`, `tablename`, `remotekey`, `ekey`, `system`) VALUES
            (?, ?, ?, ?, ?);";
        $db->query($sql, $us, $tablename, $remkey, $ekey, $system);
            return mysql_insert_id();
    }
    function DelRecordsMapString($us, $tablename, $remkey, $system, $db){
        echo ($tablename.$remkey.$system.$us);
        $sql = "DELETE FROM records_map WHERE user_id=? AND tablename=? AND remotekey=? AND system=?";
        //return $db->query($sql, $us, $tablename, $remkey, $system);
    }

    function formRecordsMap($date='', $data1='', &$data='', $user_id='', $db=''){
        $sql = "SELECT * FROM records_map WHERE system=1 AND user_id=?";
        $a = $db->query($sql, $user_id);
        //echo ($data1[1][1]['remotekey']);
        foreach ($a as $k=>$v){
            foreach ($data1[1] as $key=>$value){
                if ( $v['remotekey'] == $value['remotekey'] && ($v['tablename'] == $value['tablename']) ){
                    $data[1][0]['type'] = 'service';
                    $data[1][0]['name'] = 'RecordsMap';
                    //echo (' = ');
                    $data[1][$k+1]['tablename'] = $v['tablename'];
                    $data[1][$k+1]['remotekey'] = (int)$v['remotekey'];
                    $data[1][$k+1]['ekey'] = (int)$v['ekey'];
                    continue;
                }//else echo(' != ');
                //echo ($value['remotekey']);
                //echo ($v['id']);
            }
            //если запись удалена
            switch ($v['tablename']){
                case ('Accounts') :{$tab='accounts';$tabid='account_id';};break;
                case ('Transfers') :{$tab='operation';$tabid='id';};break;
                case ('Categories') :{$tab='category';$tabid='cat_id';};break;
                case ('Currensies') :{$tab='currency';$tabid='cur_id';};break;
                case ('Debets') :{$tab='accounts';$tabid='account_id';};break;
                case ('Incomes') :{$tab='operation';$tabid='id';};break;
                case ('Outcomes') :{$tab='operation';$tabid='id';};break;
                case ('Plans') :{$tab='periodic';$tabid='id';};break;
                default : {$tab='accounts';$tabid='account_id';};break;
            }
            //echo ($tabid);
            $cou = "SELECT count(*) AS cou FROM ".$tab." WHERE ".$tabid." = ? AND user_id=?";
                $a = $db->query($cou, $v['ekey'],$user_id);
            //если запись удалена, т.е. не нашли в бд записи с айдишником указанным в recordsmap
            if ($a[0]['cou'] == 0){
                RecordsMap_Model::DelRecordsMapString($user_id, $v['tablename'], $v['remotekey'], 1, $db);

                $data[2][0]['type'] = 'service';
                $data[2][0]['name'] = 'DeletedRecords';

                $data[2][]['tablename'] = $v['tablename'];
                $data[2][]['remotekey'] = (int)$v['remotekey'];
                // удаляем из рекордс меп и записываем в делетедрекордс,
                //чтобы удалённый юзер тоже удалил соответствующие данные
            }else{
                //добавляем запись в changedrecords,
                //данные для апдейта тоже отсылаем.
            }
            
        }
        //echo ($a[$k]['id']);
    //echo ($a[0]['id']);
    }
    
}
