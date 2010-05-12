<?php
/**
 * Модель для Анкеты ТКС
 *
 * @author ukko <max.kamashev@easyfinance.ru>
 */
class Promo_Tks_Model
{
    private $_data = array();

    /**
     *
     * @param array $data
     */
    function __construct($data)
    {
        $fields = array(
            'surname'=> '',
            'name' => '',
            'patronymic' => '',
            'phone'=> '',
        );

        $data = array_merge($fields, (array)$data);

        $this->_data = array_intersect_key($data, $fields);
    }

    public function toArray()
    {
        return $this->_data;
    }

    /**
     *
     */
    public function save()
    {
        $sql = "INSERT INTO anketa_tks (". implode(',', array_keys($this->_data)) .") VALUES (?a)";
        Core::getInstance()->db->query($sql, array_values($this->_data));
    }

}