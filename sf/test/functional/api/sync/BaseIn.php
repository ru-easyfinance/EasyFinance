<?php
require_once dirname(__FILE__).'/../../../bootstrap/all.php';


/**
 * Sync: базовые тесты для всех входящих синхронизаций
 * Max: TODO: надо отказаться от наследования тестов
 */
abstract class api_sync_in extends mySyncInFunctionalTestCase
{
    protected $app = 'api';


    /**
     * Ошибка при отправке пустого POST
     * нет данных на входе
     */
    final public function testEmptyPostError()
    {
        $this->checkSyncInError(null, 400, 'Expected XML data');
    }


    /**
     * XML должен прийти просто текстом
     */
    final public function testXMLHeaderError()
    {
        $this->checkSyncInError(urlencode("<?xml version=\"1.0\" encoding=\"utf-8\"?>\n"), 400, 'Expected valid text/xml');
    }


    /**
     * Ошибка разбора XML парсером libxml
     */
    final public function testXMLParserError()
    {
        $this->checkSyncInError("<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<record>\nsome unescaped & data\n</record>\n", 400, 'Expected valid text/xml: see xmlsoft.org');
    }


    /**
     * Пустой xml, отсутствуют записи для обработки
     */
    final public function testEmptyXMLError()
    {
        $this->checkSyncInError($this->getXMLHelper()->getEmptyRequest(), 400, 'Expected at least one record');
    }


    /**
     * Входящий xml содержит слишком много записей
     */
    final public function testRecordsLimitError()
    {
        $this->browser->getContext(true);

        $this->assertNotNull(sfConfig::get('app_records_sync_limit'));
        $this->browser->setConfigValue('app_records_sync_limit', $max = 2);

        $xml = $this->getXMLHelper()->makeCollection($max+1);

        $this->checkSyncInError($xml, 400, "More than 'limit' ({$max}) objects sent, " . $max + 1);
    }


    /**
     * Клиент не передал CID
     */
    final public function testNoCid()
    {
        $xml = $this->getXMLHelper()->make(array('cid' => null,));

        $this->checkSyncInError($xml, 400, "Request is NOT well-formed: no client ids");
    }


    /**
     * Принять группу новых объектов
     */
    final public function testNewCollection()
    {
        $xml = $this->getXMLHelper()->makeCollection(5);

        $this
            ->myXMLPost($xml, 200)
            ->with('response')->begin()
                ->checkElement('resultset record[id][success="true"][cid]', 5)
            ->end();
    }


    /**
     * Принять "удаленную" запись
     */
    public function testOperationDeleted()
    {
        $expectedData = array(
            'updated_at'  => $this->_makeDate(0),
            'deleted_at'  => $this->_makeDate(0),
        );

        $xml = $this->getXMLHelper()->make($expectedData);

        $this
            ->myXMLPost($xml, 200)
            ->with('response')->begin()
                ->checkElement('resultset record[id][success="true"][cid]', 1)
            ->end()
            ->with('model')->check($this->getModelName(), $expectedData, 1);
    }

}
