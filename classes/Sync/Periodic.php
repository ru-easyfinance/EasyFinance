<?php

class Periodic {
    private $db = null;
    private $user = null;

    /**
     * конструктор инициализирует юзера и бд
     * @param int $id
     * @param int $db
     */
    function __construct($id, $db){
        $this->db = $db;
        $this->user = $id;
    }

    /**
     * По удалённому айдишнику возвращает идентификатор записи в системе easyfinance
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
     * Синхронизация периодических операций на основе системных массивов
     * @param arry $tr
     * @param array $rec
     * @param arrau $ch
     * @param array $del
     */
    function PeriodicSync($tr, $rec, $ch, $del){
        $per = New SyncPeriodic_Model($this->db, $this->user);
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

                    $cat = $this->findEkey($tr[$k]['category'], 'Categories');
                    $acc = $this->findEkey($tr[$k]['account'], 'Accounts');

                    $tran = $per->addPeriodic(0, $tr[$k]['name'], $tr[$k]['date'], $tr[$k]['period'], $tr[$k]['count'], $cat, $acc, $tr[$k]['amount'], $tr[$k]['descr']);
                    if ($tran>0)
                        $a = RecordsMap_Model::AddRecordsMapString($this->user, 'Plans', $rem, $tran, 1, $this->db);

                }
            }
        }

        foreach ($ch as $key=>$v){
            if ($v['tablename']=="Plans"){
                $ke = $v['remotekey'];
                $k;
                $rem;
                foreach ($tr as $key=>$v){
                    if ($v['remotekey']==$ke){
                        $rem=$v['remotekey'];
                        $k = $key;
                    }
                    $tran = $this->findEkey($tr[$k]['remotekey'], 'Plans');
                    $cat = $this->findEkey($tr[$k]['category'], 'Categories');
                    $acc = $this->findEkey($tr[$k]['account'], 'Accounts');

                    //echo('<br>'.$k.' '.'rem ='.$tran.'имя = '.$tr[$k]['name'].'дата = '.$tr[$k]['date'].'период ='.$tr[$k]['period'].'каунт = '.$tr[$k]['count'].'кат ='.$cat.'acc = '.$acc.'sum = '.$tr[$k]['amount'].'коммент = '.$tr[$k]['descr']);
                    $tran = $per->EditPeriodic($tran, $tr[$k]['name'], $tr[$k]['date'], $tr[$k]['period'], $tr[$k]['count'], $cat, $acc, $tr[$k]['amount'], $tr[$k]['descr']);

                }
            }
        }//*/
        foreach ($del as $key=>$v){
            if ($v['tablename']=="Plans"){
                $ke = $v['remotekey'];
                $k;
                $rem;
                foreach ($tr as $key=>$v){
                    if ($v['remotekey']==$ke){
                        $rem=$v['remotekey'];
                        $k = $key;
                    }
                    $tran = $this->findEkey($tr[$k]['remotekey'], 'Plans');

                    //echo ('acc = '.$eacc.' cat = '.$ecat.' amount = '.$op[$k]['amount'].' descr ='.$op[$k]['descr'].' data='.$op[$k]['date']);
                    $tri = $per->deletePeriodic($tran);
                    RecordsMap_Model::DelRecordsMapString($this->user, 'Plans', $rem, 1, $this->db);

                }
            }
        }

    }
    /**
     * Функция готовит записи на основе времени , прошедшего с момента последней синхронизации
     * @param string $date
     * @param array $data
     * @return bool
     */
    function FormArray($date='', &$data=''){
        $per = New SyncPeriodic_Model($this->db, $this->user);
        return $per->formPeriodic($date, $data);
    }
}