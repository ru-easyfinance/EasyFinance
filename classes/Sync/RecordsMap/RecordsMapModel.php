<?php
class RecordsMap_Model {
    /**
     * Функция возвращает айдишник вставленной в базу записи
     * @param int $us
     * @param string $tablename
     * @param int $remkey
     * @param int $ekey
     * @param int $system
     * @param int $db
     * @return int
     */
    function AddRecordsMapString($us, $tablename, $remkey, $ekey, $system, $db){
        //echo ($tablename.$remkey.$ekey.$system.$us);
        $sql = "INSERT INTO records_map (`user_id`, `tablename`, `remotekey`, `ekey`, `system`) VALUES
            (?, ?, ?, ?, ?);";
        $db->query($sql, $us, $tablename, $remkey, $ekey, $system);
           return mysql_insert_id();
    }
    /**
     * Функция удаляет запись в таблице RecordsMap
     * @param int $us
     * @param string $tablename
     * @param int $remkey
     * @param int $system
     * @param int $db
     * @return bool
     */
    function DelRecordsMapString($us, $tablename, $remkey, $system, $db){
        //echo ($tablename.$remkey.$system.$us);
        $sql = "DELETE FROM records_map WHERE user_id=? AND tablename=? AND remotekey=? AND system=?";
        return $db->query($sql, $us, $tablename, $remkey, $system);
    }


    function writeRecMap($data='', $user_id='', $db=''){
        foreach ($data as $k=>$v){ 
            if ( $k && ( $v['tablename'] != 'Currencies' ) )
                self::AddRecordsMapString($user_id, $v['tablename'], $v['kkey'], $v['ekey'], 1, $db);
        }
    }
    /**
     * Формирует массив данных RecordsMap
     * @param string $date
     * @param array $data1
     * @param array $data
     * @param int $user_id
     * @param int $db
     */
    function formRecordsMap($date='', $data1='', &$data='', $user_id='', $db=''){
        //self::writeRecMap($data1[1], $user_id, $db);
        $sql = "SELECT * FROM records_map WHERE system=1 AND user_id=?";
        $a = $db->query($sql, $user_id);
        foreach ($a as $k=>$v){
            /*foreach ($data1[1] as $key=>$value){
                if ( $v['remotekey'] == $value['remotekey'] && ($v['tablename'] == $value['tablename']) ){
                    $data[1][0]['type'] = 'service';
                    $data[1][0]['name'] = 'RecordsMap';
                    $data[1][$k+1]['tablename'] = $v['tablename'];
                    $data[1][$k+1]['kkey'] = (int)$v['remotekey'];
                    $data[1][$k+1]['ekey'] = (int)$v['ekey'];
                    continue;
                }
            }*/
            //если запись удалена
            switch ($v['tablename']){
                case ('Accounts') :{$tab='accounts';$tabid='account_id';};break;
                case ('Transfers') :{$tab='operation';$tabid='id';};break;
                case ('Categories') :{$tab='category';$tabid='cat_id';};break;
                case ('Currensies') :{$tab='currency';$tabid='cur_id';};break;
                case ('Debets') :{$tab='accounts';$tabid='account_id';};break;
                case ('Incomes') :{$tab='operation';$tabid='id';};break;
                case ('Outcomes') :{$tab='operation';$tabid='id';};break;
                default : {$tab='accounts';$tabid='account_id';};break;
            }
            $visCat = " ";//дополнительная строка для 'удалённых' категорий
            if ( $v['tablename'] == 'Categories')
                $visCat = " visible=0 AND ";
            //die($v['ekey']);
            $cou = "SELECT count(*) AS cou FROM ".$tab." WHERE ".$visCat.$tabid." = ? AND user_id=?";
                $a = $db->query($cou, $v['ekey'],$user_id);
            //если запись удалена, т.е. не нашли в бд записи с айдишником указанным в recordsmap
            /*$cat = false;//категория
                if ( $v['tablename'] == 'Categories' )
                    if ($a[0]['visible'] == 0)
                        $cat = true;*/
            //die(print_r($a));
            //die($a[0]['cou']);
            if ( (int)$a[0]['cou'] == 1 ){
                //RecordsMap_Model::DelRecordsMapString($user_id, $v['tablename'], $v['remotekey'], 1, $db);

                $data[3][0]['type'] = 'service';
                $data[3][0]['name'] = 'DeletedRecords';

                $data[3][] = array( 'tablename' => $v['tablename'],
                'kkey' => $v['remotekey']);
                // удаляем из рекордс меп и записываем в делетедрекордс,
                //чтобы удалённый юзер тоже удалил соответствующие данные
            }else{
                //добавляем запись в changedrecords,
                //данные для апдейта тоже отсылаем.
            }
            
        }
    }
    
}
