<?php

class Category {
    private $db = null;
    private $user = null;

    /**
     * Конструктор класса, инициализирует юзера и дб.
     * @param int $id
     * @param int $db
     */
    function __construct($id, $db){
        $this->db = $db;
        $this->user = $id;
    }

    /**
     * По удалённому айди возвращает айди записи в системе easyfinance
     * @param integer $rem
     * @param integer $sys
     * @return integer
     */
    function findEkey($rem, $sys=1){
        $sql = "SELECT ekey FROM records_map WHERE remotekey=? AND tablename='Categories' AND system=? AND user_id=?";
        $a = $this->db->query($sql, $rem, $sys, $this->user);
        return $a[0]['ekey'];
    }

    /**
     * Синхронизация категорий. на основе системных таблиц производит изменения в бд.
     * @param array $cat
     * @param array $rec
     * @param array $ch
     * @param array $del
     */
    function CategorySync($cat, $rec, $ch, $del, &$data){
        $cate = New SyncCategory_Model($this->db, $this->user);

        foreach ($cat as $k=>$v){
        $sql = "SELECT ekey FROM records_map WHERE tablename='Categories' AND remotekey=? AND user_id=?";
            $toChangeRec = $this->db->query($sql, $v['remotekey'], $this->user);
            if ( $toChangeRec[0]['ekey'] != null){//редактирование
                $numEkey = $this->findEkey($v['remotekey']);
                $parent = $this->findEkey($cat[$k]['parent']);
                $cate->editCategory($numEkey,$cat[$k]['remotekey'],$parent,$cat[$k]['name'],$cat[$k]['type']);
            } else {

               // foreach ($cat as $k=>$v){
                    if ( $v['parent'] == '0' ){
                        $categ = $cate->addCategory(0,$cat[$k]['parent'],$cat[$k]['name'], $cat[$k]['type']);
                        if ($categ>0)
                        $a = RecordsMap_Model::AddRecordsMapString($this->user, 'Categories', $v['remotekey'], $categ, 1, $this->db);
                        $data[1][0]['type'] = 'service';
                        $data[1][0]['name'] = 'RecordsMap';
                        $data[1][] = array(
                            'tablename' => 'Categories',
                            'kkey' => $v['remotekey'],
                            'ekey' => (int)$categ,
                        );
                    }
                //}
                //foreach ($cat as $k=>$v){
                    if ( $v['parent'] != '0' ){
                        $id = $this->findEkey($cat[$k]['parent']);
                        $categ = $cate->addCategory(0,$id,$cat[$k]['name'],$cat[$k]['type']);
                        if ($categ > 0)
                        $a = RecordsMap_Model::AddRecordsMapString($this->user, 'Categories', $v['remotekey'], $categ, 1, $this->db);
                        $data[1][0]['type'] = 'service';
                        $data[1][0]['name'] = 'RecordsMap';
                        $data[1][] = array(
                            'tablename' => 'Categories',
                            'kkey' => $v['remotekey'],
                            'ekey' => (int)$categ,
                        );
                    }
                //}
            }
        }

        /*foreach ($rec as $key=>$v){
            if ($v['tablename']=="Categories"){
                $ke = $v['remotekey'];
                $k;
                $rem;
                foreach ($cat as $key=>$v){
                    if ($v['remotekey']==$ke AND $v['parent']==0){
                        $rem=$v['remotekey'];

                $k=$rem;
                //если категория родительская то добавляем её.

                $categ = $cate->addCategory(0,$cat[$k]['parent'],$cat[$k]['name']);
                if ($categ>0)
                $a = RecordsMap_Model::AddRecordsMapString($this->user, 'Categories', $rem, $categ, 1, $this->db);
                }
            }
        }
        }
        foreach ($rec as $key=>$v){
            if ($v['tablename']=="Categories"){
                $ke = $v['remotekey'];
                $k;
                $rem;
                foreach ($cat as $key=>$v){
                    if ($v['remotekey']==$ke AND $v['parent']!=0){
                        $rem=$v['remotekey'];

                $k=$rem;

                    //а если дочерняя, то ищем id шник в remotekey
                    $id = $this->findEkey($cat[$k]['parent']);
                    $categ = $cate->addCategory(0,$id,$cat[$k]['name']);


                if ($categ > 0)
                $a = RecordsMap_Model::AddRecordsMapString($this->user, 'Categories', $rem, $categ, 1, $this->db);
                }
            }
        }
        }*/

        /*foreach ($ch as $key=>$v){
            if ($v['tablename']=="Categories"){
                $ke = $v['remotekey'];
                $k;
                $rem;
                foreach ($cat as $key=>$v){
                    if ($v['remotekey']==$ke)
                        $k = $key;
                }
                $numEkey = $this->findEkey($ke);
                $cate->editCategory($numEkey,$cat[$k]['remotekey'],$cat[$k]['parent'],$cat[$k]['name']);
            }
        }*/
        foreach ($del as $key=>$v){
            if ($v['tablename']=="Categories"){
                $ke = $v['remotekey'];
                $k;
                $rem;
                foreach ($cat as $key=>$v){
                    if ($v['remotekey']==$ke)
                        $rem=$v['remotekey'];
                }
                $numEkey = $this->findEkey($ke);
                if ($numEkey>0){
                    $cate->deleteCategory($numEkey, $this->db);
                    RecordsMap_Model::DelRecordsMapString($this->user, 'Categories', $rem, 1, $this->db);
                }
            }
        }
    }
    /**
     * Фромирует массив категорий и системных таблиц, изменений с момента последней синхронизации
     * @param string $date
     * @param string $data
     * @return bool
     */
    function FormArray($date='', &$data=''){
        $cat = New SyncCategory_Model($this->db, $this->user);
        return $cat->formCategory($date, $data);
    }
}