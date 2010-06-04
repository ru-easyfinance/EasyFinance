<?php
require_once dirname(__FILE__).'/../../../bootstrap/all.php';


/**
 * Синхронизация: получить список объектов
 */
class api_sync_InTest extends mySyncInFunctionalTestCase
{
    protected $app = 'api';


    /**
     * Ошибка при отправке пустого POST
     * нет данных на входе
     */
    public function testPostAccountEmptyPostError()
    {
        $this
            ->myXMLPost(null, 400)
            ->with('response')->begin()
                ->isValid()
                ->checkElement('error message', 'No data were sent')
            ->end();
    }


    /**
     * Пустой xml, отсутствуют записи для обработки
     */
    public function testPostAccountEmptyXMLError()
    {
        $this
            ->myXMLPost($this->xmlHelper->_xml->asXML(), 400)
            ->with('response')->begin()
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

        $xml = $this->xmlHelper->_xml;
        $this->browser->getContext(true);
        $this->assertNotNull(sfConfig::get('app_records_sync_limit'));
        $this->browser->setConfigValue('app_records_sync_limit', $max = 2);
        for ($i=0;$i<$max+1; $i++) {
            // TODO генерить в таком стиле $record = $this->xmlHelper->_xmlFillRecordset(3);
            $record = $this->xmlHelper->_xmlFillRecord(array(
                'user_id'     => $user->getId(),
                'name'        => 'Test name № ' . $i,
                'description' => 'Description № ' . $i,
            ), array(
                'id'  => $i+1,
                'cid' => $i+1,
            ));

            $xml = $this->xmlHelper->_xmlAddRecord($record, $xml);
        }

        $this
            ->myXMLPost($xml->asXML(), 400)
            ->with('response')->begin()
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

        $record = $this->xmlHelper->_xmlFillRecord(array(
            'user_id'     => $user->getId(),
            'name'        => 'Test valid account',
        ),
        $attr = array(
            'id'  => 1,
            'cid' => 1,
        ));
        $xml = $this->xmlHelper->_xmlAddRecord($record);

        $record = $this->xmlHelper->_xmlFillRecord(array(
            'user_id'     => $user->getId(),
            'name'        => 'Test valid account #2',
            'deleted_at'  => $this->_makeDate(0),
        ), array(
            'id'  => 2,
            'cid' => 2,
        ));
        $xml = $this->xmlHelper->_xmlAddRecord($record, $xml);

        $this
            ->myXMLPost($xml->asXML(), 200)
            ->with('response')->begin()
                ->isValid()
                ->checkContains('<resultset type="account">')
                ->checkElement('resultset record')
                ->checkElement('record[id]', 2)
                ->checkElement('record[cid]', 2)
                ->checkElement('record[success]', 2)
            ->end()
            ->with('doctrine')->check('Account', null, 2);

        $this->markTestIncomplete(
            'Доработать: проверка параметров сохраненного объекта.'
        );
    }


    /**
     * Принять объект из xml, перезаписывающий существующий
     */
    public function testPostAccountReplace()
    {
        $user = $this->helper->makeUser();
        $account = $this->helper->makeAccount($user);

        $record = $this->xmlHelper->_xmlFillRecord(array(
            'user_id'     => $user->getId(),
            'name'        => 'Test valid account',
        ),
        $attr = array(
            'id'  => $account->getId(),
            'cid' => 1,
        ));
        $xml = $this->xmlHelper->_xmlAddRecord($record);

        $this
            ->myXMLPost($xml->asXML(), 200)
            ->with('response')->begin()
                ->isValid()
                ->checkContains('<resultset type="account">')
                ->checkElement('resultset record[id][success="true"][cid]', 1)
                ->checkElement(sprintf('record[id*="%d"]', $account->getId()))
                // тут звездочка нужна? а слэшики?
                // @see http://www.symfony-project.org/jobeet/1_4/Doctrine/en/09
            ->end()
            ->with('doctrine')->check('Account', null, 1);
    }


    /**
     * Before Execute
     */
    protected function _start()
    {
        parent::_start();
        $this->xmlHelper->_xmlLoadTemplate(dirname(__FILE__) . '/xml/testSyncInAccounts.xml');
        $this->xmlHelper->_xmlPrepare();
    }


    /**
     * Отправляет XML
     *
     * @param $code integer Код ответа http
     */
    protected function myXMLPost($xml = null, $code = 200)
    {
        return $this
            ->postAndCheck("sync", "syncInAccount", null === $xml ? array() : array('body' => $xml), "sync_in_account", $code);
    }

}
