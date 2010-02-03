<?php

class Currency {
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



    function FormArray( &$data=''){
        $sql = "SELECT user_currency_list AS li, user_currency_default AS def FROM users WHERE id = ?";
        $li = $this->db->query($sql, $this->user);
        $cur_user_string = $li[0]['li'];
        $cur_user_array = unserialize($cur_user_string);
        $mas = "'".implode("','", $cur_user_array)."'";
        $sql = "SELECT c.cur_id as id,  c.cur_char_code as charCode, c.cur_name as abbr
            FROM currency c
            WHERE c.cur_id IN ($mas)";
        $li = $this->db->query($sql);

        foreach ($li as $key=>$v){
            $data[7][0]['tablename'] = 'Currencies';
                $data[7][$key+1]['ekey'] = $key;
                $data[7][$key+1]['charCode'] = $v['charCode'];
        }
    }
    
}