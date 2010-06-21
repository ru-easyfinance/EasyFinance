<?php

class mySyncInXMLHelper
{
    protected static $xmlTemplate = '
        <request>
            <recordset>
                %s
            </recordset>
        </request>
    ';

    protected static $recordTemplate = '
        <record%s%s%s>
            %s
        </record>
    ';

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
     * @param  array     $fields
     */
    public function __construct($model, $default = array(), $fields = array())
    {
        $this->model  = new $model;
        $this->modelName = $model;
        $this->fields = $fields;

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
        $params = array_merge($this->model->toArray(false), $params);

        $id = '';
        if (!empty($params['id'])) {
            $id = " id=\"{$params['id']}\"";
        }

        $cid = '';
        if (array_key_exists("cid", $params)) {
            if (!empty($params['cid'])) {
                $cid = " cid=\"" . $this->getCID($params['cid']) . "\"";
            }
        } else {
            $cid = " cid=\"" . $this->getCID() . "\"";
        }

        $deleted = '';
        if (isset($params['deleted_at']) && $params['deleted_at'] !== null) {
            $deleted = ' deleted="deleted"';
        }

        $fields = $this->createFields($params);

        $record = sprintf(self::$recordTemplate, $id, $cid, $deleted, $fields);

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

        $fields = '';
        foreach ($this->fields as $tag) {

            if (isset($params[$tag])) {
                $value = $params[$tag];

                if (null !== $value) {
                    $fields .= sprintf("<%s>%s</%s>\n", $tag, $value, $tag);
                } else {
                    $fields .= sprintf("<%s />\n", $tag);
                }
            }
        }

        return $fields;
    }


    protected function getCID($id = 0)
    {
        $this->CID = (0 === $id) ? $this->CID : $id;
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
            sprintf(self::$xmlTemplate, $recordSet)
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

}
