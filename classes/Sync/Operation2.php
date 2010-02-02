<?php

class Operation2 {
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
     * Функция возвращает айди записи в easyfinance.ru по удалённому айди
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
     * Произволит изменения в бд в соответствии с масивом операций и системными массивами
     * @param array $op
     * @param array $rec
     * @param array $ch
     * @param array $del
     */
    function OperationSync($op, $rec, $ch, $del, &$data){
        $opw = New SyncOperation_Model($this->db, $this->user);
        foreach($op as $k=>$v) {
            $sql = "SELECT ekey FROM records_map WHERE tablename='Outcomes' AND remotekey=? AND user_id=?";
            $toChangeRec = $this->db->query($sql, $v['remotekey'], $this->user);
            if ( $toChangeRec[0]['ekey'] ){//редактирование
                
                $eacc = $this->findEkey($op[$k]['account'], 'Accounts');
                $ecat = $this->findEkey($op[$k]['category'], 'Categories');
                $eop = $this->findEkey($op[$k]['remotekey'], 'Outcomes');

                $oper = $opw->EditOperation($eop, $eacc, 0, $op[$k]['date'], $ecat, '-'.$op[$k]['amount'], $op[$k]['descr']);
            } else {//добавление
                $eacc = $this->findEkey($op[$k]['account'], 'Accounts');
                $ecat = $this->findEkey($op[$k]['category'], 'Categories');

                $oper = $opw->addOperation(0, $eacc, 0, $op[$k]['date'], $ecat, '-'.$op[$k]['amount'], $op[$k]['descr']);
                if ($oper>0)
                    $a = RecordsMap_Model::AddRecordsMapString($this->user, 'Outcomes', $v['remotekey'], $oper, 1, $this->db);
                $data[1][0]['type'] = 'service';
                $data[1][0]['name'] = 'RecordsMap';
                $data[1][] = array(
                    'tablename' => 'Outcomes',
                    'kkey' => (int)$v['remotekey'],
                    'ekey' => (int)$oper,
                );
        }
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
                    $eacc = $this->findEkey($op[$k]['account'], 'Accounts');
                    $ecat = $this->findEkey($op[$k]['category'], 'Categories');

                $oper = $opw->addOperation(0, $eacc, 0, $op[$k]['date'], $ecat, $op[$k]['amount'], $op[$k]['descr']);
                if ($oper>0)
                    $a = RecordsMap_Model::AddRecordsMapString($this->user, 'Incomes', $rem, $oper, 1, $this->db);
                }
            }
        }

        foreach ($ch as $key=>$v){
            if ($v['tablename']=="Incomes"){
                $ke = $v['remotekey'];
                $k;
                $rem;
                foreach ($op as $key=>$v){
                    if ($v['remotekey']==$ke){
                        $rem=$v['remotekey'];
                        $k = $key;
                    }
                    $eacc = $this->findEkey($op[$k]['account'], 'Accounts');
                    $ecat = $this->findEkey($op[$k]['category'], 'Categories');
                    $eop = $this->findEkey($op[$k]['remotekey'], 'Incomes');

                $oper = $opw->EditOperation($eop, $eacc, 0, $op[$k]['date'], $ecat, $op[$k]['amount'], $op[$k]['descr']);

                }
            }
        }*/
        foreach ($del as $key=>$v){
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

                    $oper = $opw->deleteOperation($eop);
                    RecordsMap_Model::DelRecordsMapString($this->user, 'Outcomes', $rem, 1, $this->db);
                }
            }
        }

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
                    $eacc = $this->findEkey($op[$k]['account'], 'Accounts');
                    $ecat = $this->findEkey($op[$k]['category'], 'Categories');
                    $oper = $opw->addOperation(0, $eacc, 1, $op[$k]['date'], $ecat, -$op[$k]['amount'], $op[$k]['descr']);
                    if ($oper>0)
                        $a = RecordsMap_Model::AddRecordsMapString($this->user, 'Outcomes', $rem, $oper, 1, $this->db);

                }
            }
        }

        foreach ($ch as $key=>$v){
            if ($v['tablename']=="Outcomes"){
                $ke = $v['remotekey'];
                $k;
                $rem;
                foreach ($op as $key=>$v){
                    if ($v['remotekey']==$ke){
                        $rem=$v['remotekey'];
                        $k = $key;
                    }
                    $eacc = $this->findEkey($op[$k]['account'], 'Accounts');
                    $ecat = $this->findEkey($op[$k]['category'], 'Categories');
                    $eop = $this->findEkey($op[$k]['remotekey'], 'Outcomes');
                    $oper = $opw->EditOperation($eop, $eacc, 1, $op[$k]['date'], $ecat, -$op[$k]['amount'], $op[$k]['descr']);
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
                    $oper = $opw->deleteOperation($eop, $this->user, $this->db);
                    RecordsMap_Model::DelRecordsMapString($this->user, 'Outcomes', $rem, 1, $this->db);
                }
            }*/
        }
    }
    /**
     * Формирует массив Доходов и расходов, в соответствии с временем последней синхронизации
     * @param string $date
     * @param array $data
     * @return bool
     */
    function FormArray($date='', &$data=''){
        $opw = New SyncOperation_Model($this->db, $this->user);
        return $opw->formOperation($date, $data);
    }

    }