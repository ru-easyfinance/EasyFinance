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
     * Конструктор
     * @param array $data
     * @return void
     */
    function __construct($data)
    {
        $fields = array(
            'surname'=> '',
            'name' => '',
            'patronymic' => '',
            'phone'=> '',
            'user_id' => Core::getInstance()->user->getId()
        );

        $data = array_merge($fields, (array)$data);

        $this->_data = array_intersect_key($data, $fields);
    }

    /**
     * Возвращает данные в виде массива
     * @return array
     */
    public function toArray()
    {
        return $this->_data;
    }

    /**
     * Сохраняет данные в БД
     * @return bool
     */
    public function save()
    {
        $sql = "INSERT INTO anketa_tks(".
            implode(',', array_keys($this->_data)).", created_at) VALUES (?a, NOW())";
        return (bool)Core::getInstance()->db->query($sql, array_values($this->_data));
    }

}