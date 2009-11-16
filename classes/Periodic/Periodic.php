<?php
/**
 *
 */
class Periodic {
    private $db = null;
    private $user = null;
    
    function __construct($id){
        $this->db = DbSimple_Generic::connect( "mysql://" . SYS_DB_USER . ":" . SYS_DB_PASS . "@" . SYS_DB_HOST . "/" . SYS_DB_BASE );
        $this->user = $id;
        //echo ('12342345423543 kjfke');
    }

    /*
    *функция по удалённому ид remotekey возвращает айдишник записи в системе easyfinance
     */
    function findEkey($rem, $from='', $sys=1){
        $sql = "SELECT ekey FROM records_map WHERE remotekey=? AND tablename=? AND system=?";
        $a = $this->db->query($sql, $rem, $from, $sys);
        return $a[0]['ekey'];
    }

    function PeriodicSync($cat, $rec, $ch, $del){
        //echo ('2');
        foreach ($rec as $key=>$v){
            if ($v['tablename']=="Plans"){
                $ke = $v['remotekey'];
                $k;
                $rem;
                foreach ($tr as $key=>$v){
                    if ($v['remotekey']==$ke){
                        $rem=$v['remotekey'];
                        $k = $key;
                    }
                //$k=$rem;
                    //echo($ke.'<br>');
                    $efrom = $this->findEkey($tr[$k]['acfrom'], 'Accounts');
                    $eto = $this->findEkey($tr[$k]['acto'], 'Accounts');
                //curid - операция в валюте счёта
                        //($id=0, $acc_from=0, $amount=0, $date='', $descr='', $acc_to=0, $user, $db  ){
                echo ('from = '.$efrom.' sum = '.$tr[$k]['amount'].' data = '.$tr[$k]['date'].' описание = '.$tr[$k]['descr'].' to = '.$eto);
                /*$tran = Periodic_Model::addPeriodic(0, $efrom, $tr[$k]['amount'], $tr[$k]['date'], $tr[$k]['descr'], $eto, $this->user, $this->db);
                if ($tran>0)
                $a = RecordsMap_Model::AddRecordsMapString($this->user, 'Plans', $rem, $tran, 1, $this->db);
                */
                }
            }
        }

        /*foreach ($ch as $key=>$v){
            if ($v['tablename']=="Transfers"){
                $ke = $v['remotekey'];
                $k;
                $rem;
                foreach ($tr as $key=>$v){
                    if ($v['remotekey']==$ke){
                        $rem=$v['remotekey'];
                        $k = $key;
                    }
                //$k=$rem;
                    //echo($ke.'<br>');
                    $efrom = $this->findEkey($tr[$k]['acfrom'], 'Accounts');
                    $eto = $this->findEkey($tr[$k]['acto'], 'Accounts');
                    $eop = $this->findEkey($tr[$k]['remotekey'], 'Transfers');
                    //$eacc = $this->findEkey($op[$k]['account'], 'Accounts');
                    //$ecat = $this->findEkey($op[$k]['category'], 'Categories');
                    //$eop = $this->findEkey($op[$k]['remotekey'], 'Incomes');
                //curid - операция в валюте счёта
                        //($id=0, $acc_id=0, $darain=0, $date='', $catid='', $amount=0, $descr='', $user, $db ){
                //echo ('acc = '.$eacc.' cat = '.$ecat.' amount = '.$op[$k]['amount'].' descr ='.$op[$k]['descr'].' data='.$op[$k]['date']);
                echo ($eop.'  from = '.$efrom.' sum = '.$tr[$k]['amount'].' data = '.$tr[$k]['date'].' описание = '.$tr[$k]['descr'].' to = '.$eto);
                    //$tran = Transfer_Model::editTransfer($eop, $efrom, $tr[$k]['amount'], $tr[$k]['date'], $tr[$k]['descr'], $eto, $this->user, $this->db);

                }
            }
        }//*/
        /*foreach ($del as $key=>$v){
            if ($v['tablename']=="Transfers"){
                $ke = $v['remotekey'];
                $k;
                $rem;
                foreach ($tr as $key=>$v){
                    if ($v['remotekey']==$ke){
                        $rem=$v['remotekey'];
                        $k = $key;
                    }
                    $eop = $this->findEkey($tr[$k]['remotekey'], 'Transfers');
                //curid - операция в валюте счёта
                        //($id=0, $acc_id=0, $darain=0, $date='', $catid='', $amount=0, $descr='', $user, $db ){
                //echo ('acc = '.$eacc.' cat = '.$ecat.' amount = '.$op[$k]['amount'].' descr ='.$op[$k]['descr'].' data='.$op[$k]['date']);
                $oper = Transfer_Model::deleteTransfer($eop, $this->user, $this->db);
                RecordsMap_Model::DelRecordsMapString($this->user, 'Transfers', $rem, 1, $this->db);

                }
            }
        }*/
        
    }
    function FormArray(){
        
    }
}