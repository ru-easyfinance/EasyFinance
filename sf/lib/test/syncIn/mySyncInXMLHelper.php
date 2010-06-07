<?php

class mySyncInXMLHelper
{
    protected static $xmlTemplate = "<request>\n    <recordset type=\"%s\" user=\"%d\">\n%s    </recordset>\n</request>\n";
    protected static $recordTemplate = "        <record id=\"%s\" cid=\"%s\">\n%s        </record>\n";

    protected $CID = 1;

    protected $userId = null;
    protected $model = '';
    protected $modelName = '';
    protected $records = array();

    protected $inCollection = false;


    public function __construct($model, $userId)
    {
        $this->model  = new $model;
        $this->modelName = $model;
        $this->userId = $userId;

        $this->model->fromArray(array(
            'type_id'     => 1,
            'currency_id' => 1,
            'name'        => 'Счет',
            'description' => 'Описание счета',
            'created_at'  => $this->_makeDate(-1000),
            'updated_at'  => $this->_makeDate(0),
            'deleted_at'  => '',
        ));
    }

    /**
     * Создать 1 строку
     */
    public function make($params = array())
    {
        $cid = $this->getCID();
        $params['cid'] = $cid;
        $this->addRecords(array(array_merge($params, $this->model->toArray(false))));

        $id = '';
        if (isset($params['id'])) {
            $id = $params['id'];
            unset($params['id']);
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
     *
     */
    protected function createFields($params = array())
    {
        $params = array_merge($this->model->toArray(false), $params);
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
     *
     */
    protected function addRecords($records)
    {
        $this->records += (array) $records;
    }


    /**
     *
     */
    public function toArray()
    {
        return $this->records;
    }


    public function reset()
    {
        $this->records = array();
    }


    protected function getCID()
    {
        $CID = $this->CID;
        $this->CID++;
        return $CID;
    }


    /**
     *
     */
    public function decorate($recordSet = '')
    {
        return sprintf(
            "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n%s",
            sprintf(self::$xmlTemplate, $this->modelName, $this->userId , $recordSet)
        );
    }


    /**
     *
     */
    public function getUserId()
    {
        return $this->userId;
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
