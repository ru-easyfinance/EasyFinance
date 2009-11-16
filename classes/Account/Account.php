<?php
/**
 *
 */
class Account {
    private $db = null;
    private $user = null;
    
    function __construct($id){
        $this->db = DbSimple_Generic::connect( "mysql://" . SYS_DB_USER . ":" . SYS_DB_PASS . "@" . SYS_DB_HOST . "/" . SYS_DB_BASE );
        $this->user = $id;
    }

    /*
    *функция по удалённому ид remotekey возвращает айдишник записи в системе easyfinance
     */
    function findEkey($rem, $sys=1){
        $sql = "SELECT ekey FROM records_map WHERE remotekey=? AND tablename='Accounts' AND system=?";
        $a = $this->db->query($sql, $rem, $sys);
        return $a[0]['ekey'];
    }

    function AccountSync($acc, $rec, $ch, $del){
        //echo ('2');
        //($id=0, $name='', $curid=0, $date='', $amount=0, $descr='', $user_id){
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
                //echo ($ke.' '.$k.$acc[$k]['startbalance']);
                $accou = Account_Model::addAccount(0,$acc[$k]['name'],$acc[$k]['cur'],$acc[$k]['date'],$acc[$k]['startbalance'],$acc[$k]['descr'],$this->user,$this->db);
                if ($accou > 0){
                    $oper = Operation_Model::addOperation(0, $accou, 0, $acc[$k]['date'], 0, $acc[$k]['startbalance'], 'Начальный остаток',$this->user, $this->db);
                    $a = RecordsMap_Model::AddRecordsMapString($this->user, 'Accounts', $rem, $accou, 1, $this->db);
                }
            }
        }*/
        /*foreach ($ch as $key=>$v){
            if ($v['tablename']=="Accounts"){
                $ke = $v['remotekey'];
                $k;
                $rem;
                foreach ($acc as $key=>$v){
                    //echo ($key);
                    if ($v['remotekey']==$ke)
                        //echo ($key);
                        //$rem=$v['remotekey'];
                        $k = $key;
                }
                //echo ($k.' '.$acc[$k]['descr']);
                $numEkey = $this->findEkey($ke);
                //echo($numEkey);
                Account_model::editAccount($numEkey,$acc[$k]['name'],$acc[$k]['cur'],$acc[$k]['date'],$acc[$k]['startbalance'],$acc[$k]['descr'],$this->user,$this->db);
            }
        }*/
        /*foreach ($del as $key=>$v){
            if ($v['tablename']=="Accounts"){
                $ke = $v['remotekey'];
                $k;
                $rem;
                foreach ($acc as $key=>$v){
                    //echo ($key);
                    if ($v['remotekey']==$ke)
                        //echo ($key);
                        $rem=$v['remotekey'];
                        //$k = $key;
                }
                $numEkey = $this->findEkey($ke);
                //echo ($numEkey);
                if ($numEkey>0){
                    //$us, $tablename, $remkey, $system, $db
                    Account_model::deleteAccount($numEkey, $this->db);
                    RecordsMap_Model::DelRecordsMapString($this->user, 'Accounts', $rem, 1, $this->db);
                }
            }
        }*/
        
    }
    
    function FormArray($date='', &$data=''){
        return Account_Model::formAccount($date, $data, $this->user,$this->db);
    }
}