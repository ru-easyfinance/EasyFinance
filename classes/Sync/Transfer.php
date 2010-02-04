<?php

class Transfer {
    private $db = null;
    private $user = null;

    /**
     * Конструктор инициализирует пользователя и дб
     * @param int $id
     * @param int $db
     */
    function __construct($id, $db){
        $this->db = $db;
        $this->user = $id;
    }

    /**
     * Возвращает айди записи в системе easyfinance по удалённому ключу
     * @param int $rem
     * @param string $from
     * @param int $sys
     * @return int
     */
    function findEkey($rem, $from='', $sys=1){
        $sql = "SELECT ekey FROM records_map WHERE remotekey=? AND tablename=? AND system=? AND user_id=?";
        $a = $this->db->query($sql, $rem, $from, $sys, $this->user);
        return $a[0]['ekey'];
    }

    /**
     * Вносит изменения в бд на основе системных массивов
     * @param array $tr
     * @param array $rec
     * @param array $ch
     * @param array $del
     */
    function TransferSync($tr, $rec, $ch, $del, &$data){
        $tra = New SyncTransfer_Model($this->db, $this->user);
        foreach ($tr as $k=>$v){
        $sql = "SELECT ekey FROM records_map WHERE tablename='Transfers' AND remotekey=? AND user_id=?";
            $toChangeRec = $this->db->query($sql, $v['remotekey'], $this->user);
            if ( $toChangeRec[0]['ekey'] != null){//редактирование
                $efrom = $this->findEkey($tr[$k]['acfrom'], 'Accounts');
                $eto = $this->findEkey($tr[$k]['acto'], 'Accounts');
                $eop = $this->findEkey($tr[$k]['remotekey'], 'Transfers');
                $tran = $tra->editTransfer($eop, $efrom, $tr[$k]['amount'], $tr[$k]['date'], $tr[$k]['descr'], $eto);
            } else {
                $efrom = $this->findEkey($tr[$k]['acfrom'], 'Accounts');
                $eto = $this->findEkey($tr[$k]['acto'], 'Accounts');
                //echo ('from = '.$efrom.' sum = '.$tr[$k]['amount'].' data = '.$tr[$k]['date'].' описание = '.$tr[$k]['descr'].' to = '.$eto);
                $tran = $tra->addTransfer(0, $efrom, $tr[$k]['amount'], $tr[$k]['date'], $tr[$k]['descr'], $eto);
                if ($tran>0)
                    $a = RecordsMap_Model::AddRecordsMapString($this->user, 'Transfers', $v['remotekey'], $tran, 1, $this->db);
                $data[1][0]['type'] = 'service';
                $data[1][0]['name'] = 'RecordsMap';
                $data[1][] = array(
                    'tablename' => 'Transfers',
                    'kkey' => $v['remotekey'],
                    'ekey' => (int)$tran,
                );
            }
        }
        /*foreach ($rec as $key=>$v){
            if ($v['tablename']=="Transfers"){
                $ke = $v['remotekey'];
                $k;
                $rem;
                foreach ($tr as $key=>$v){
                    if ($v['remotekey']==$ke){
                        $rem=$v['remotekey'];
                        $k = $key;
                    }
                    $efrom = $this->findEkey($tr[$k]['acfrom'], 'Accounts');
                    $eto = $this->findEkey($tr[$k]['acto'], 'Accounts');
                    //echo ('from = '.$efrom.' sum = '.$tr[$k]['amount'].' data = '.$tr[$k]['date'].' описание = '.$tr[$k]['descr'].' to = '.$eto);
                    $tran = $tra->addTransfer(0, $efrom, $tr[$k]['amount'], $tr[$k]['date'], $tr[$k]['descr'], $eto);
                    if ($tran>0)
                        $a = RecordsMap_Model::AddRecordsMapString($this->user, 'Transfers', $rem, $tran, 1, $this->db);

                }
            }
        }

        foreach ($ch as $key=>$v){
            if ($v['tablename']=="Transfers"){
                $ke = $v['remotekey'];
                $k;
                $rem;
                foreach ($tr as $key=>$v){
                    if ($v['remotekey']==$ke){
                        $rem=$v['remotekey'];
                        $k = $key;
                    }
                    $efrom = $this->findEkey($tr[$k]['acfrom'], 'Accounts');
                    $eto = $this->findEkey($tr[$k]['acto'], 'Accounts');
                    $eop = $this->findEkey($tr[$k]['remotekey'], 'Transfers');
                    //echo ($eop.'  from = '.$efrom.' sum = '.$tr[$k]['amount'].' data = '.$tr[$k]['date'].' описание = '.$tr[$k]['descr'].' to = '.$eto);
                    $tran = $tra->editTransfer($eop, $efrom, $tr[$k]['amount'], $tr[$k]['date'], $tr[$k]['descr'], $eto);

                }
            }
        }//*/
        foreach ($del as $key=>$v){
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
                $oper = $tra->deleteTransfer($eop);
                RecordsMap_Model::DelRecordsMapString($this->user, 'Transfers', $rem, 1, $this->db);

                }
            }
        }

    }
    /**
     * Создаёт массив переводов на основе времени последней синхронизации
     * @param string $date
     * @param array $data
     * @return bool
     */
    function FormArray($date='', &$data=''){
        $tra = New SyncTransfer_Model($this->db, $this->user);
        return $tra->formTransfer($date, $data);
    }
    }