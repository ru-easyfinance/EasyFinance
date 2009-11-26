<?php
/**
 *
 */
class Debet {
    private $db = null;
    private $user = null;

    function __construct($id, $db){
        $this->db = $db;
        $this->user = $id;
    }

    /**
     * по удалённому айди возвращает айдишник записи в системе easyfinance.ru
     * @param int $rem
     * @param int $sys
     * @return int
     */
    function findEkey($rem, $sys=1){
        $sql = "SELECT ekey FROM records_map WHERE remotekey=? AND tablename='Debets' AND system=? AND user_id=?";
        $a = $this->db->query($sql, $rem, $sys, $this->user);
        return $a[0]['ekey'];
    }

    /**
     * Синхронизация долгов на основе системных массивов и массива долгов
     * @param array $acc
     * @param array $rec
     * @param array $ch
     * @param array $del
     */
    function DebetSync($acc, $rec, $ch, $del){
        $acco = New SyncDebet_Model($this->db, $this->user);
        $op = New SyncOperation_Model($this->db, $this->user);
        foreach ($rec as $key=>$v){
            if ($v['tablename']=="Debets"){
                $ke = $v['remotekey'];
                $k;
                $rem;
                foreach ($acc as $key=>$v){
                    if ($v['remotekey']==$ke){
                        $rem=$v['remotekey'];
                        $k = $key;
                }}
                $accou = $acco->addDebet(0,$acc[$k]['name'],$acc[$k]['currency'],$acc[$k]['date'],$acc[$k]['amount'],'');
                if ($accou > 0){
                    $oper = $op->addOperation(0, $accou, 0, $acc[$k]['date'], 0, $acc[$k]['amount'], 'Начальный остаток');
                    $a = RecordsMap_Model::AddRecordsMapString($this->user, 'Debets', $rem, $accou, 1, $this->db);
                }
            }
        }
        foreach ($ch as $key=>$v){
            if ($v['tablename']=="Debets"){
                $ke = $v['remotekey'];
                $k;
                $rem;
                foreach ($acc as $key=>$v){
                    if ($v['remotekey']==$ke)
                        $k = $key;
                }
                $numEkey = $this->findEkey($ke);
                $acco->editDebet($numEkey,$acc[$k]['name'],$acc[$k]['currency'],$acc[$k]['date'],$acc[$k]['amount'],'');
            }
        }
        foreach ($del as $key=>$v){
            if ($v['tablename']=="Debets"){
                $ke = $v['remotekey'];
                $k;
                $rem;
                foreach ($acc as $key=>$v){
                    if ($v['remotekey']==$ke)
                        $rem=$v['remotekey'];
                }
                $numEkey = $this->findEkey($ke);
                if ($numEkey>0){
                    $acco->deleteDebet($numEkey);
                    RecordsMap_Model::DelRecordsMapString($this->user, 'Debets', $rem, 1, $this->db);
                }
            }
        }

    }

    /**
     * Формирует возвращаемый массив долгов и системные таблицы
     * @param string $date
     * @param string $data
     * @return bool
     */
    function FormArray($date='', &$data=''){
        $acc = New SyncDebet_Model($this->db, $this->user);
        return $acc->formDebet($date, $data);
    }
}