<?php
require_once dirname(__FILE__).'/../../../bootstrap/all.php';


/**
 * Синхронизация: получить список объектов
 */
class api_sync_InTest extends myFunctionalTestCase
{
    protected $app = 'api';

    protected $_xmlTemplate = false;
    protected $_xml, $_xmlRecord;


    /**
     * Ошибка при отправке пустого POST
     * нет данных на входе
     */
    public function testPostAccountEmptyPostError()
    {
        $this->browser
            ->post($this->generateUrl('sync_in_account'))
            ->with('request')->begin()
                ->isParameter('action', 'syncInAccount')
            ->end()
            ->with('response')->begin()
                ->isStatusCode(400)
                ->isValid()
                ->checkElement('error message', 'No data were sent')
            ->end();
    }


    /**
     * Пустой xml, отсутствуют записи для обработки
     */
    public function testPostAccountEmptyXMLError()
    {
        $this->browser
            ->post(
                $this->generateUrl('sync_in_account'),
                array('body' => $this->_xml->asXML())
            )
            ->with('request')->begin()
                ->isParameter('action', 'syncInAccount')
            ->end()
            ->with('response')->begin()
                ->isStatusCode(400)
                ->isValid()
                ->checkElement('error message', 'No objects were sent')
            ->end();
    }


    /**
     * Входящий xml содержит слишком много записей
     */
    public function testPostAccountRecordsLimitError()
    {
        $user = $this->helper->makeUser();

        $xml = $this->_xml;
        $this->browser->getContext(true);
        $this->assertNotNull(sfConfig::get('app_records_sync_limit'));
        $this->browser->setConfigValue('app_records_sync_limit', $max = 2);
        for ($i=0;$i<$max+1; $i++) {
            // TODO генерить в таком стиле $record = $this->_xmlFillRecordset(3);
            $record = $this->_xmlFillRecord(array(
                'user_id'     => $user->getId(),
                'name'        => 'Test name № ' . $i,
                'description' => 'Description № ' . $i,
            ), array(
                'id'  => $i+1,
                'cid' => $i+1,
            ));

            $xml = $this->_xmlAddRecord($record, $xml);
        }

        $this->browser
            ->info("    1.3. Переполненный xml")
            ->post(
                $this->generateUrl('sync_in_account'),
                array('body' => $xml->asXML())
            )
            ->with('request')->begin()
                ->isParameter('action', 'syncInAccount')
            ->end()
            ->with('response')->begin()
                ->isStatusCode(400)
                ->isValid()
                ->checkElement('error message', "More than 'limit' ({$max}) objects were sent")
            ->end();
    }


    /**
     * Принять валидный xml с парой объектов
     */
    public function testPostAccount()
    {
        $user = $this->helper->makeUser();

        $this->browser->setTester('doctrine', 'sfTesterDoctrine');

        $record = $this->_xmlFillRecord(array(
            'user_id'     => $user->getId(),
            'name'        => 'Test valid account',
        ),
        $attr = array(
            'id'  => 1,
            'cid' => 1,
        ));
        $xml = $this->_xmlAddRecord($record);

        $record = $this->_xmlFillRecord(array(
            'user_id'     => $user->getId(),
            'name'        => 'Test valid account #2',
            'deleted_at'  => $this->_makeDate(0),
        ), array(
            'id'  => 2,
            'cid' => 2,
        ));
        $xml = $this->_xmlAddRecord($record, $xml);

        $this->browser
            ->post(
                $this->generateUrl('sync_in_account'),
                array('body' => $xml->asXML())
            )
            ->with('request')->begin()
                ->isParameter('module', 'sync')
                ->isParameter('action', 'syncInAccount')
            ->end()
            ->with('response')->begin()
                ->isStatusCode('200')
                ->isValid()
                ->checkContains('<resultset type="account">')
                ->checkElement('resultset record')
                ->checkElement('record[id]', 2)
                ->checkElement('record[cid]', 2)
                ->checkElement('record[success]', 2)
            ->end()
            ->with('doctrine')->check('Account', null, 2);
            // TODO добавить массив значений для проверки
    }


    /**
     * Before Execute
     */
    protected function _start()
    {
        parent::_start();
        $this->_xmlLoadTemplate();
        $this->_xmlPrepare();
    }


    /**
     * Создать дату с указанным смещением от текущей
     *
     * @param  int    $shift - Смещение в секундах
     * @return string
     */
    private function _makeDate($shift)
    {
        return date(DATE_ISO8601, time()+$shift);
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
    protected function _xmlFillRecord($values = array(), $attributes = array(), SimpleXMLElement $record = null) {
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
    protected function _xmlFillRecordWithValues($values = array(), SimpleXMLElement $record = null)
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
            $record->$k = $v;
        }

        return $record;
    }


    /**
     * Устанавливает атрибуты в xml-представление объекта
     *
     * @param  array  $values Массив ключ->значение
     * @return SimpleXMLElement
     */
    protected function _xmlFillRecordWithAttributes($values = array(), SimpleXMLElement $record = null)
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
    protected function _xmlAddRecord(SimpleXMLElement $record, SimpleXMLElement $xml = null)
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
     * Достает шаблон XML
     *
     * @return SimpleXMLElement|false
     */
    protected function _xmlLoadTemplate()
    {
        if (false === $this->_xmlTemplate) {
            $this->_xmlTemplate = simplexml_load_file(dirname(__FILE__) . '/xml/testSyncInAccounts.xml');
        }

        return $this->_xmlTemplate;
    }


    /**
     * Подготавливает кусочки XML строки из загруженного шаблона
     *
     * @return void
     */
    protected function _xmlPrepare()
    {
        $this->_xml = clone $this->_xmlTemplate;

        $this->_xmlRecord = clone $this->_xml->recordset[0]->record[0];
        unset($this->_xml->recordset[0]->record[0]);
    }
}
