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
        $this->myXMLPost(null, 400);
        $this->checkError('No data were sent');

        #Max: можно даже так:
        # $this->checkSyncInError(null, 400, 'No data were sent');
    }


    /**
     * Пустой xml, отсутствуют записи для обработки
     */
    public function testPostAccountEmptyXMLError()
    {
        #Max: Не вижу, что происходит в $this->getXMLHelper()->decorate()
        # Ну не нравится мне идея XML хелпера. Попробуй сделать категории с обычными строками
        $this->myXMLPost($this->getXMLHelper()->decorate(), 400);
        $this->checkError('No objects were sent');
    }


    /**
     * Входящий xml содержит слишком много записей
     */
    public function testPostAccountRecordsLimitError()
    {
        $this->browser->getContext(true);

        $this->assertNotNull(sfConfig::get('app_records_sync_limit'));
        $this->browser->setConfigValue('app_records_sync_limit', $max = 2);

        $xml = $this->getXMLHelper()->makeCollection($max+1);
        $this->myXMLPost($xml, 400);
        $this->checkError("More than 'limit' ({$max}) objects sent, " . $max + 1);

        # +1, прочитал
    }


    /**
     * Принять валидный xml
     */
    public function testPostAccountSingle()
    {
        $this->markTestIncomplete(
            'Доработать: проверка параметров сохраненного объекта.'
        );

        $xml = $this->getXMLHelper()->make();

        /**
         * А вот тут надо делать:
            $expectedData = array(... набиваем один счет ... )
            makeXml($expectedData)
            и
            ->with('model')->check('Account', $expectedData, 1);
         */
        $this
            ->myXMLPost($xml, 200)
            ->with('response')->begin()
                ->checkElement('resultset[type="account"] record[id][cid][success="true"]')
            ->end()
            ->with('doctrine')->check('Account', null, 1);
    }


    /**
     * Принять объект из xml, перезаписывающий существующий
     */
    public function testPostAccountReplace()
    {
        $this->markTestIncomplete(
            'Доработать: проверка параметров сохраненного объекта.'
        );

        $account = $this->helper->makeAccount($this->_user);

        $xml = $this->getXMLHelper()->make(array('id' => $account->getId()));

        $this
            ->myXMLPost($xml, 200)
            ->with('response')->begin()
                ->checkElement('resultset[type="account"] record[id][success="true"][cid]', 1)
                ->checkElement(sprintf('record[id*="%d"]', $account->getId()))
                #Max: ???
                // тут звездочка нужна? а слэшики?
                // @see http://www.symfony-project.org/jobeet/1_4/Doctrine/en/09
            ->end()
            ->with('doctrine')->check('Account', null, 1);
    }

#Max: тест: принять объект с SID, которого нет


    /**
     * Отвергать чужие записи
     */
    public function testPostAccountForeignUserRecord()
    {
        $user2 = $this->helper->makeUser();
        $account = $this->helper->makeAccount($user2);

        $xml = $this->getXMLHelper()->make(array('id' => $account->getId()));

        $this
            ->myXMLPost($xml, 200)
            ->with('response')->begin()
                ->checkElement('resultset[type="account"] record[id][success="false"][cid]', 1)
                ->checkElement(sprintf('record[id*="%d"]', $account->getId()))
            ->end();

        #Max: не понял, а как getXMLHelper узнал об ответе?
        $valid = $this->getXMLHelper()->toArray();
        $this->checkRecordError($valid['0']['cid'], '[Invalid.] Foreign account');
    }


    /**
     * Отвергать: Несуществующий тип (id) счета
     */
    public function testPostAccountTypeForeignKeyFail()
    {
        $id = Doctrine::getTable('AccountType')->createQuery('t')
            ->select("MAX(t.account_type_id)")
            ->execute(array(), Doctrine::HYDRATE_SINGLE_SCALAR) + 1;

        $xml = $this->getXMLHelper()->make(array('type_id' => $id,));

        $this
            ->myXMLPost($xml, 200)
            ->with('response')->begin()
                ->checkElement('resultset[type="account"] record[id][success="false"][cid]', 1)
            ->end();

        $valid = $this->getXMLHelper()->toArray();
        $this->checkRecordError($valid['0']['cid'], '[Invalid.] No such account type');
    }


    /**
     * Отвергать: Несуществующая валюта
     */
    public function testPostAccountCurrencyForeignKeyFail()
    {
        $id = Doctrine::getTable('Currency')->createQuery('t')
            ->select("MAX(t.id)")
            ->execute(array(), Doctrine::HYDRATE_SINGLE_SCALAR) + 1;

        $xml = $this->getXMLHelper()->make(array('currency_id' => $id,));

        $this
            ->myXMLPost($xml, 200)
            ->with('response')->begin()
                ->checkElement('resultset[type="account"] record[id][success="false"][cid]', 1)
            ->end();

        $valid = $this->getXMLHelper()->toArray();
        $this->checkRecordError($valid['0']['cid'], '[Invalid.] No such currency');
    }


#Max: все служебные методы объявляй в начале, так проще читать

    /**
     * Before Execute
     */
    protected function _start()
    {
        parent::_start();

        $this->_user = $this->helper->makeUser();
        $this->xmlHelper = new mySyncInXMLHelper('account', $this->_user->getId());
    }


    /**
     * Отправляет XML
     *
     * @param  int    $code     Код ответа сервера
     * @param  string $xml      XML-строка
     * @return sfTestFunctional Возвращает браузер @see sfTestBrowser
     */
    protected function myXMLPost($xml = null, $code = 200)
    {
        return $this
            ->postAndCheck("sync", "syncInAccount", null === $xml ? array() : array('body' => $xml), "sync_in_account", $code)
            ->with('response')->begin()
                ->isHeader("content-type", "/^text\/xml/")
                ->isValid()
            ->end();
    }


    /**
     * Проверяет сообщения ошибок
     *
     * @param  string $message  Сообщение ошибки
     * @param  string $code     Код ошибки
     * @return sfTestFunctional Возвращает браузер @see sfTestBrowser
     */
    protected function checkError($message, $code = 0)
    {
        return $this->browser
            ->with('response')->begin()
                ->checkElement("error code", (string) $code)
                ->checkElement("error message", $message)
            ->end();
    }


    /**
     * Проверяет ошибки обработки записей
     *
     * @param  int    $id       Идентификатор записи клиента
     * @param  string $message  Сообщение ошибки   @see mySincInAccounForm
     * @return sfTestFunctional Возвращает браузер @see sfTestBrowser
     */
    protected function checkRecordError($id, $message)
    {
        return $this->browser
            ->with('response')
            ->checkElement(sprintf('resultset record[cid*="%d"] error', $id), (string) $message);
    }

}
