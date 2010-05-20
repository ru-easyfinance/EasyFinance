<?php
class efCurrencyModel
{
    public static function loadAll()
    {
        $sql = "SELECT cur_id, rate, cur_char_code  FROM currency c WHERE cur_uses=1";
        return Core::getInstance()->db->select($sql);
    }
}
