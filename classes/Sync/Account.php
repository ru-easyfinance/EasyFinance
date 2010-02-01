<?php

class Account {
    private $db = null;
    private $user = null;

    /**
     * конструктор инициализирует юзера и бд
     * @param integer $id
     * @param integer $db
     */
    function __construct($id, $db){
        $this->db = $db;
        $this->user = $id;
    }

    /**
     * функция по удалённому ид remotekey возвращает айдишник записи в системе easyfinance
     * @param <integer> $rem
     * @param <integer> $sys
     * @return <integer>
     */
    function findEkey($rem, $sys=1){
        $sql = "SELECT ekey FROM records_map WHERE remotekey=? AND tablename='Accounts' AND system=? AND user_id=?";
        $a = $this->db->selectRow($sql, $rem, $sys, $this->user);
        return $a['ekey'];
    }
    /**
     * функция содержит логику синхронизации. по массиву счетов и 3ём системным вносит изменения в БД
     * @param <array> $acc
     * @param <array> $rec
     * @param <array> $ch
     * @param <array> $del
     */
    function AccountSync($acc, $rec, $ch, $del){
        $acco = New SyncAccount_Model($this->db,$this->user);
        $op = New SyncOperation_Model($this->db, $this->user);
        foreach($acc as $k=>$v){
            $sql = "SELECT ekey FROM records_map WHERE tablename='Accounts' AND remotekey=? AND user_id=?";
            $toChangeRec = $this->db->query($sql, $v['remotekey'], $this->user);
            if ( $toChangeRec[0]['ekey'] ){//редактирование
                $numEkey = $toChangeRec[0]['ekey'];
                $acco->editAccount($numEkey,$acc[$k]['name'],$acc[$k]['cur'],$acc[$k]['date'],$acc[$k]['startbalance'],$acc[$k]['descr']);
            } else{//добавление
                $accou = $acco->addAccount(0,$acc[$k]['name'],$acc[$k]['cur'],$acc[$k]['date'],$acc[$k]['startbalance'],$acc[$k]['descr'],$this->user,$this->db);
                if ($accou > 0){
                    $oper = $op->addOperation(0, $accou, 0, $acc[$k]['date'], 0, $acc[$k]['startbalance'], 'Начальный остаток');
                    $a = RecordsMap_Model::AddRecordsMapString($this->user, 'Accounts', $v['remotekey'], $accou, 1, $this->db);
                }
            }
        }

        /*foreach ($rec as $key=>$v){
            if ($v['tablename']=="Accounts"){
                $ke = $v['remotekey'];
                $k;
                $rem;
                foreach ($acc as $key=>$v){
                    if ($v['remotekey']==$ke){
                        $rem=$v['remotekey'];
                        $k = $key;
                }}
                //$k = $rem;
                $accou = $acco->addAccount(0,$acc[$k]['name'],$acc[$k]['cur'],$acc[$k]['date'],$acc[$k]['startbalance'],$acc[$k]['descr'],$this->user,$this->db);
                //$accou = Account_Model::addAccount(0,$acc[$k]['name'],$acc[$k]['cur'],$acc[$k]['date'],$acc[$k]['startbalance'],$acc[$k]['descr'],$this->user,$this->db);
                if ($accou > 0){
                    $oper = $op->addOperation(0, $accou, 0, $acc[$k]['date'], 0, $acc[$k]['startbalance'], 'Начальный остаток');
                    $a = RecordsMap_Model::AddRecordsMapString($this->user, 'Accounts', $rem, $accou, 1, $this->db);
                }
            }
        }
        foreach ($ch as $key=>$v){
            if ($v['tablename']=="Accounts"){
                $ke = $v['remotekey'];
                $k;
                $rem;
                foreach ($acc as $key=>$v){
                    if ($v['remotekey']==$ke)
                        $k = $key;
                }
                $numEkey = $this->findEkey($ke);
                //echo($numEkey);
                $acco->editAccount($numEkey,$acc[$k]['name'],$acc[$k]['cur'],$acc[$k]['date'],$acc[$k]['startbalance'],$acc[$k]['descr']);
            }
        }*/
        foreach ($del as $key=>$v){
            if ($v['tablename']=="Accounts"){
                $ke = $v['remotekey'];
                $k;
                $rem;
                foreach ($acc as $key=>$v){
                    if ($v['remotekey']==$ke)
                        $rem=$v['remotekey'];
                }
                $numEkey = $this->findEkey($ke);
                if ($numEkey>0){
                    //$us, $tablename, $remkey, $system, $db
                    $acco->deleteAccount($numEkey);
                    RecordsMap_Model::DelRecordsMapString($this->user, 'Accounts', $rem, 1, $this->db);
                }
            }
        }

    }

    /**
     * Формирование массива счетов и системных таблиц, изменений с последний синхронизации
     * @param string $date
     * @param array $data
     * @return bool
     */
    function FormArray($date='', &$data=''){
        $acco = New SyncAccount_Model($this->db, $this->user);
        return $acco->formAccount($date, $data);
    }
}