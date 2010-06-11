<?php

class mySyncInXMLHelper
{
    protected static $xmlTemplate = "<request>\n    <recordset type=\"%s\">\n%s    </recordset>\n</request>\n";
    protected static $recordTemplate = "        <record id=\"%s\" cid=\"%s\">\n%s        </record>\n";

    protected $CID = 1;

    protected $model = '';
    protected $modelName = '';
    protected $records = array();

    protected $inCollection = false;


    /**
     * Конструктор
     *
     * @param  string    $model
     * @param  array     $default
     */
    public function __construct($model, $default = array())
    {
        $this->model  = new $model;
        $this->modelName = $model;

        $this->model->fromArray($default, false);
    }

    /**
     * Создать 1 строку
     *
     * @param  array $params   Параметры создаваемого объекта
     * @return string
     */
    public function make($params = array())
    {
        $cid = $params['cid'] = isset($params['cid']) ? $this->getCID($params['cid']) : $this->getCID();
        $this->addRecord(array_merge($this->model->toArray(false), $params));

        $id = '';
        if (isset($params['id'])) {
            $id = $params['id'];
        }

        $fields = $this->createFields($params);

        $record = sprintf(self::$recordTemplate, $id, $cid, $fields);

        if ($this->inCollection) {
            return $record;
        }

        return $this->decorate($record);
    }


    /**
     * Создать набор стандартных счетов
     *
     * @param  int    $count
     * @return string
     */
    public function makeCollection($count = 1, $params = array())
    {
        $this->inCollection = true;

        $collection = '';
        for ($i=0;$i<$count;$i++) {
            $collection .= $this->make($params);
        }

        $this->inCollection = false;

        return $this->decorate($collection);
    }


    /**
     * Создать набор тегов одной записи
     *
     * @param  array  $params
     * @return string
     */
    protected function createFields($params = array())
    {
        $params = array_merge($this->model->toArray(false), $params);
        unset($params['id'], $params['cid']);

        $fields = '';
        foreach ($params as $tag => $value) {
            if ($value) {
                $fields .= sprintf("            <%s>%s</%s>\n", $tag, $value, $tag);
            } else {
                $fields .= sprintf("            <%s />\n", $tag);
            }
        }

        return $fields;
    }


    /**
     * Добавляет переданные параметры в кэш
     *
     * @param  array $record
     * @return void
     */
    protected function addRecord(array $record)
    {
        unset($record['cid']);
        $this->records[] = $record;
    }


    /**
     * Возвращает набор данных, добавленных в XML в виде массива
     *
     * @return array
     */
    public function toArray()
    {
        return $this->records;
    }


    public function reset()
    {
        $this->records = array();
    }


    protected function getCID($id = null)
    {
        $this->CID = (null === $id) ? $this->CID : $id;
        $CID = $this->CID;
        $this->CID++;
        return $CID;
    }


    /**
     * Объединяет шаблон и данные - records
     */
    protected function decorate($recordSet = '')
    {
        return sprintf(
            "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n%s",
            sprintf(self::$xmlTemplate, $this->modelName, $recordSet)
        );
    }


    /**
     * Вернуть XML без данных
     *
     * @return string
     */
    public function getEmptyRequest()
    {
        return $this->decorate();
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
