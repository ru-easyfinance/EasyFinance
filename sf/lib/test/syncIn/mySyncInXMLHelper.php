<?php

class mySyncInXMLHelper
{
    protected $test, $_xmlTemplate = false;
    public $_xml, $_xmlRecord;


    public function __construct()
    {
    }

    /**
     * Заполняет xml-представление объекта данными
     *
     * @param array $values
     * @param array $attributes
     * @see _xmlFillRecordWithValues
     * @see _xmlFillRecordWithAttributes
     * @return SimpleXMLElement
     */
    public function _xmlFillRecord($values = array(), $attributes = array(), SimpleXMLElement $record = null) {
        $record = $this->_xmlFillRecordWithValues($values, $record);
        $record = $this->_xmlFillRecordWithAttributes($attributes, $record);

        return $record;
    }


    /**
     * Заполнить/добавить данных в xml-представление объекта
     *
     * @param  array  $values Массив ключ->значение
     * @return SimpleXMLElement
     */
    public function _xmlFillRecordWithValues($values = array(), SimpleXMLElement $record = null)
    {
        if ($record === null) {
            $record = clone $this->_xmlRecord;
        }

        $values = array_merge(array(
            'user_id'     => 1,
            'type_id'     => 1,
            'currency_id' => 1,
            'name'        => 'account',
            'description' => 'Description',
            'created_at'  => $this->_makeDate(-1000),
            'updated_at'  => $this->_makeDate(0),
            'deleted_at'  => '',
        ), $values);

        foreach ($values as $k => $v) {
            $k = (string)$k;
            $record->$k = (string)$v;
        }

        return $record;
    }


    /**
     * Устанавливает атрибуты в xml-представление объекта
     *
     * @param  array  $values Массив ключ->значение
     * @return SimpleXMLElement
     */
    public function _xmlFillRecordWithAttributes($values = array(), SimpleXMLElement $record = null)
    {
        if ($record === null) {
            $record = clone $this->_xmlRecord;
        }

        $values = array_merge(array(
            'id'  => '',
            'cid' => '',
        ), $values);

        foreach ($values as $k => $v) {
            $record[$k] = $v;
        }

        return $record;
    }


    /**
     * Добавляет в шаблон xml данные одной записи
     *
     * @param SimpleXMLElement $record
     * @return SimpleXMLElement
     */
    public function _xmlAddRecord(SimpleXMLElement $record, SimpleXMLElement $xml = null)
    {
        if ($xml === null) {
            $xml = $this->_xml;
        }

        $node1 = dom_import_simplexml($xml);
        $dom_sxe = dom_import_simplexml($record);
        $node2 = $node1->ownerDocument->importNode($dom_sxe, true);
        $parent = $node1->getElementsByTagName("recordset")->item(0);
        $parent->appendChild($node2);

        return simplexml_import_dom($node1);
    }


    /**
     * Подготавливает кусочки XML строки из загруженного шаблона
     *
     * @return void
     */
    public function _xmlPrepare()
    {
        $this->_xml = clone $this->_xmlTemplate;

        $this->_xmlRecord = clone $this->_xml->recordset[0]->record[0];
        unset($this->_xml->recordset[0]->record[0]);
    }


    /**
     * Достает шаблон XML
     *
     * @return SimpleXMLElement|false
     */
    public function _xmlLoadTemplate($path)
    {
        if (false === $this->_xmlTemplate) {
            $this->_xmlTemplate = simplexml_load_file($path);
        }

        return $this->_xmlTemplate;
    }


    /**
     * Создать дату с указанным смещением от текущей
     *
     * @param  int    $shift - Смещение в секундах
     * @return string
     */
    protected function _makeDate($shift)
    {
        return date(DATE_ISO8601, time()+$shift);
    }

}
