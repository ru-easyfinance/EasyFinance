<?php
/**
 *
 */
class Operation {
    private $db = null;
    private $user = null;
    
    function __construct($id){
        $this->db = DbSimple_Generic::connect( "mysql://" . SYS_DB_USER . ":" . SYS_DB_PASS . "@" . SYS_DB_HOST . "/" . SYS_DB_BASE );
        $this->user = $id;
    }

    /*
    *функция по удалённому ид remotekey возвращает айдишник записи в системе easyfinance
     */
    function findEkey($rem, $from='', $sys=1){
        $sql = "SELECT ekey FROM records_map WHERE remotekey=? AND tablename=? AND system=?";
        $a = $this->db->query($sql, $rem, $from, $sys);
        return $a[0]['ekey'];
    }

    function OperationSync($op, $rec, $ch, $del){
        //if ($cat[$k]['parent'] == 0){
        /*foreach ($rec as $key=>$v){
            if ($v['tablename']=="Incomes"){
                $ke = $v['remotekey'];
                $k;
                $rem;
                foreach ($op as $key=>$v){
                    if ($v['remotekey']==$ke){
                        $rem=$v['remotekey'];
                        $k = $key;
                    }
                //$k=$rem;
                    echo($ke.'<br>');
                    $eacc = $this->findEkey($op[$k]['account'], 'Accounts');
                    $ecat = $this->findEkey($op[$k]['category'], 'Categories');
                //curid - операция в валюте счёта
                        //($id=0, $acc_id=0, $darain=0, $date='', $catid='', $amount=0, $descr='', $user, $db ){
                //echo ('acc = '.$eacc.' cat = '.$ecat.' amount = '.$op[$k]['amount'].' descr ='.$op[$k]['descr'].' data='.$op[$k]['date']);
                $oper = Operation_Model::addOperation(0, $eacc, 0, $op[$k]['date'], $ecat, $op[$k]['amount'], $op[$k]['descr'],$this->user, $this->db);
                if ($oper>0)
                $a = RecordsMap_Model::AddRecordsMapString($this->user, 'Incomes', $rem, $oper, 1, $this->db);
                
                }
            }
        }*/

        /*foreach ($ch as $key=>$v){
            if ($v['tablename']=="Incomes"){
                $ke = $v['remotekey'];
                $k;
                $rem;
                foreach ($op as $key=>$v){
                    if ($v['remotekey']==$ke){
                        $rem=$v['remotekey'];
                        $k = $key;
                    }
                //$k=$rem;
                    //echo($ke.'<br>');
                    $eacc = $this->findEkey($op[$k]['account'], 'Accounts');
                    $ecat = $this->findEkey($op[$k]['category'], 'Categories');
                    $eop = $this->findEkey($op[$k]['remotekey'], 'Incomes');
                //curid - операция в валюте счёта
                        //($id=0, $acc_id=0, $darain=0, $date='', $catid='', $amount=0, $descr='', $user, $db ){
                //echo ('acc = '.$eacc.' cat = '.$ecat.' amount = '.$op[$k]['amount'].' descr ='.$op[$k]['descr'].' data='.$op[$k]['date']);
                $oper = Operation_Model::EditOperation($eop, $eacc, 0, $op[$k]['date'], $ecat, $op[$k]['amount'], $op[$k]['descr'],$this->user, $this->db);
                
                }
            }
        }*/
        /*foreach ($del as $key=>$v){
            if ($v['tablename']=="Incomes"){
                $ke = $v['remotekey'];
                $k;
                $rem;
                foreach ($op as $key=>$v){
                    if ($v['remotekey']==$ke){
                        $rem=$v['remotekey'];
                        $k = $key;
                    }
                    $eop = $this->findEkey($op[$k]['remotekey'], 'Incomes');
                //curid - операция в валюте счёта
                        //($id=0, $acc_id=0, $darain=0, $date='', $catid='', $amount=0, $descr='', $user, $db ){
                //echo ('acc = '.$eacc.' cat = '.$ecat.' amount = '.$op[$k]['amount'].' descr ='.$op[$k]['descr'].' data='.$op[$k]['date']);
                $oper = Operation_Model::deleteOperation($eop, $this->user, $this->db);
                RecordsMap_Model::DelRecordsMapString($this->user, 'Incomes', $rem, 1, $this->db);

                }
            }
        }*/

        /*foreach ($rec as $key=>$v){
            if ($v['tablename']=="Outcomes"){
                $ke = $v['remotekey'];
                $k;
                $rem;
                foreach ($op as $key=>$v){
                    if ($v['remotekey']==$ke){
                        $rem=$v['remotekey'];
                        $k = $key;
                    }
                //$k=$rem;
                    echo($ke.'<br>');
                    $eacc = $this->findEkey($op[$k]['account'], 'Accounts');
                    $ecat = $this->findEkey($op[$k]['category'], 'Categories');
                //curid - операция в валюте счёта
                        //($id=0, $acc_id=0, $darain=0, $date='', $catid='', $amount=0, $descr='', $user, $db ){
                //echo ('acc = '.$eacc.' cat = '.$ecat.' amount = '.$op[$k]['amount'].' descr ='.$op[$k]['descr'].' data='.$op[$k]['date']);
                $oper = Operation_Model::addOperation(0, $eacc, 1, $op[$k]['date'], $ecat, -$op[$k]['amount'], $op[$k]['descr'],$this->user, $this->db);
                if ($oper>0)
                $a = RecordsMap_Model::AddRecordsMapString($this->user, 'Outcomes', $rem, $oper, 1, $this->db);

                }
            }
        }*/

        /*foreach ($ch as $key=>$v){
            if ($v['tablename']=="Outcomes"){
                $ke = $v['remotekey'];
                $k;
                $rem;
                foreach ($op as $key=>$v){
                    if ($v['remotekey']==$ke){
                        $rem=$v['remotekey'];
                        $k = $key;
                    }
                //$k=$rem;
                    //echo($ke.'<br>');
                    $eacc = $this->findEkey($op[$k]['account'], 'Accounts');
                    $ecat = $this->findEkey($op[$k]['category'], 'Categories');
                    $eop = $this->findEkey($op[$k]['remotekey'], 'Outcomes');
                //curid - операция в валюте счёта
                        //($id=0, $acc_id=0, $darain=0, $date='', $catid='', $amount=0, $descr='', $user, $db ){
                //echo ('acc = '.$eacc.' cat = '.$ecat.' amount = '.$op[$k]['amount'].' descr ='.$op[$k]['descr'].' data='.$op[$k]['date']);
                $oper = Operation_Model::EditOperation($eop, $eacc, 1, $op[$k]['date'], $ecat, -$op[$k]['amount'], $op[$k]['descr'],$this->user, $this->db);

                }
            }
        }*/
        /*foreach ($del as $key=>$v){
            if ($v['tablename']=="Outcomes"){
                $ke = $v['remotekey'];
                $k;
                $rem;
                foreach ($op as $key=>$v){
                    if ($v['remotekey']==$ke){
                        $rem=$v['remotekey'];
                        $k = $key;
                    }
                    $eop = $this->findEkey($op[$k]['remotekey'], 'Outcomes');
                //curid - операция в валюте счёта
                        //($id=0, $acc_id=0, $darain=0, $date='', $catid='', $amount=0, $descr='', $user, $db ){
                //echo ('acc = '.$eacc.' cat = '.$ecat.' amount = '.$op[$k]['amount'].' descr ='.$op[$k]['descr'].' data='.$op[$k]['date']);
                $oper = Operation_Model::deleteOperation($eop, $this->user, $this->db);
                RecordsMap_Model::DelRecordsMapString($this->user, 'Outcomes', $rem, 1, $this->db);

                }
            }
        }*/
    }
    
    function FormArray($date='', &$data=''){
        return Operation_Model::formOperation($date, $data, $this->user,$this->db);
        //echo ($a[9][1]['account']);
    }
    
    }