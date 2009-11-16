<?php
/**
 *
 */
class Category {
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
    function findEkey($rem, $sys=1){
        $sql = "SELECT ekey FROM records_map WHERE remotekey=? AND tablename='Categories' AND system=?";
        $a = $this->db->query($sql, $rem, $sys);
        return $a[0]['ekey'];
    }

    function CategorySync($cat, $rec, $ch, $del){
        //echo ('2');
        //($id=0, $name='', $curid=0, $date='', $amount=0, $descr='', $user_id){
        /*if ($cat[$k]['parent'] == 0){
                    $categ = Category_Model::addCategory(0,$cat[$k]['parent'],$cat[$k]['name'],$this->user,$this->db);
                }/*else{
                    //а если дочерняя, то ищем id шник в remotekey
                    $id = $this->findEkey($cat[$k]['parent']);
                    $categ = Category_Model::addCategory(0,$id,$cat[$k]['name'],$this->user,$this->db);
                }*/
        /*foreach ($rec as $key=>$v){
            if ($v['tablename']=="Categories"){
                $ke = $v['remotekey'];
                $k;
                $rem;
                foreach ($cat as $key=>$v){
                    if ($v['remotekey']==$ke AND $v['parent']==0){
                        $rem=$v['remotekey'];
                        //$k = $key;
                
                $k=$rem;
                //если категория родительская то добавляем её.
                

                $categ = Category_Model::addCategory(0,$cat[$k]['parent'],$cat[$k]['name'],$this->user,$this->db);
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
                        //$k = $key;
                
                $k=$rem;
                //если категория родительская то добавляем её.
                
                    //а если дочерняя, то ищем id шник в remotekey
                    $id = $this->findEkey($cat[$k]['parent']);
                    $categ = Category_Model::addCategory(0,$id,$cat[$k]['name'],$this->user,$this->db);
                

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
                    //echo ($key);
                    if ($v['remotekey']==$ke)
                        //echo ($key);
                        //$rem=$v['remotekey'];
                        $k = $key;
                }
                //echo ($k.' '.$acc[$k]['descr']);
                $numEkey = $this->findEkey($ke);
                Category_Model::editCategory($numEkey,$cat[$k]['remotekey'],$cat[$k]['parent'],$cat[$k]['name'],$this->user,$this->db);
            }
        }*/
        /*foreach ($del as $key=>$v){
            if ($v['tablename']=="Categories"){
                $ke = $v['remotekey'];
                $k;
                $rem;
                foreach ($cat as $key=>$v){
                    //echo ($key);
                    if ($v['remotekey']==$ke)
                        //echo ($key);
                        $rem=$v['remotekey'];
                        //$k = $key;
                }
                $numEkey = $this->findEkey($ke);
                //echo ('num '.$numEkey);
                if ($numEkey>0){
                    //$us, $tablename, $remkey, $system, $db
                    Category_Model::deleteCategory($numEkey, $this->db);
                    RecordsMap_Model::DelRecordsMapString($this->user, 'Categories', $rem, 1, $this->db);
                }
            }
        }*/
        
    }
    function FormArray($date='', &$data=''){
        return Category_Model::formCategory($date, $data, $this->user,$this->db);
        //echo ($data[9]);
    }
}