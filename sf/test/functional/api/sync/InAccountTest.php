<?php
require_once dirname(__FILE__).'/../../../bootstrap/all.php';


/**
 * Синхронизация: получить список объектов
 */
class api_sync_InAccountTest extends mySyncInFunctionalTestCase
{
    protected $app = 'api';


    /**
     * Возвращает стандартный валидный набор полей и значений объекта
     *
     * @return array
     */
    protected function getDefaultModelData()
    {
        return array(
            'id'          => null,
            'user_id'     => $this->_user->getId(),
            'type_id'     => 1,
            'currency_id' => 1,
            'name'        => 'Счет',
            'description' => 'Описание',
            'created_at'  => $this->_makeDate(-1000),
            'updated_at'  => $this->_makeDate(0),
            'deleted_at'  => null,
        );
    }


    /**
     * Вернуть название модели
     *
     * @return string
     */
    protected function getModelName()
    {
        return 'account';
    }


    /**
     * Отправляет XML
     *
     * @param  string $xml      XML-строка
     * @param  int    $code     Код ответа сервера
     * @return sfTestFunctional Возвращает браузер @see sfTestBrowser
     */
    protected function myXMLPost($xml = null, $code = 200)
    {
        return $this
            ->postAndCheckXML("sync", "syncInAccount", $xml, "sync_in_account", $code);
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
            ->checkElement(sprintf('resultset record[cid="%d"]', $id), (string) $message);
    }


    /**
     * Ошибка при отправке без авторизации
     * (пока без ID пользователя в query string)
     */
    public function testPostAccountAuthError()
    {
        $this->_user->setId(null);

        $this->checkSyncInError(null, 401, 'Authentification required');
    }


    /**
     * Ошибка при отправке пустого POST
     * нет данных на входе
     */
    public function testPostAccountEmptyPostError()
    {
        $this->checkSyncInError(null, 400, 'Expected XML data');
    }


    /**
     * Пустой xml, отсутствуют записи для обработки
     */
    public function testPostAccountEmptyXMLError()
    {
        $this->checkSyncInError($this->getXMLHelper()->getEmptyRequest(), 400, 'Expected at least one record');
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

        $this->checkSyncInError($xml, 400, "More than 'limit' ({$max}) objects sent, " . $max + 1);
    }


    /**
     * Принять валидный xml
     */
    public function testPostAccountSingle()
    {
        $expectedData = array(
            'user_id'     => $this->_user->getId(),
            'type_id'     => 2,
            'currency_id' => 4,
            'name'        => 'Мой счет',
            'description' => 'Описание счета',
            'created_at'  => $this->_makeDate(-10000),
            'updated_at'  => $this->_makeDate(-300),
            'deleted_at'  => null,
            'cid' => 5,
        );

        $xml = $this->getXMLHelper()->make($expectedData);

        $this
            ->myXMLPost($xml, 200)
            ->with('response')->begin()
                ->checkElement('resultset', 1)
                ->checkElement('resultset record', 1)
                ->checkElement('resultset[type="account"] record[id][success="true"]', 'OK')
                ->checkElement(sprintf('resultset record[cid="%d"]', $expectedData['cid']), 'OK')
            ->end();

        unset($expectedData['cid']); // у записи нет такого поля
        $this->browser
            ->with('model')->check('Account', $expectedData, 1);
    }


    /**
     * Принять объект из xml, перезаписывающий существующий
     */
    public function testPostAccountReplace()
    {
        $account = $this->helper->makeAccount($this->_user);
        $expectedData = array(
            'user_id'     => $this->_user->getId(),
            'id'          => $account->getId(),
            'name'        => 'Мой обновленный счет',
        );

        $xml = $this->getXMLHelper()->make($expectedData);

        $this
            ->myXMLPost($xml, 200)
            ->with('response')->begin()
                ->checkElement('resultset[type="account"] record[id][success="true"][cid]', 1)
                ->checkElement(sprintf('record[id*="%d"]', $account->getId()))
            ->end()
            ->with('model')->check('Account', $expectedData, 1);
    }


    /**
     * Принять объект с ИД, где ИД отсутствует в базе
     * (объекту будет присвоен новый ИД)
     */
    public function testPostAccountWithSID()
    {
        $xml = $this->getXMLHelper()->make(array('id' => 1));

        $this
            ->myXMLPost($xml, 200)
            ->with('response')->begin()
                ->checkElement('resultset[type="account"] record[id][success="true"][cid]', 1)
            ->end()
            ->with('model')->check('Account', null, 1);
    }


    /**
     * Отвергать чужие записи
     */
    public function testPostAccountForeignUserRecord()
    {
        $user2 = $this->helper->makeUser();
        $account = $this->helper->makeAccount($user2);

        $expectedData = array(
            'id'  => $account->getId(),
            'cid' => 8,
        );

        $xml = $this->getXMLHelper()->make($expectedData);

        $this
            ->myXMLPost($xml, 200)
            ->with('response')->begin()
                ->checkElement('resultset[type="account"] record[id][success="false"][cid]', 1)
                ->checkElement(sprintf('record[id="%d"]', $expectedData['id']))
            ->end();

        $this->checkRecordError($expectedData['cid'], '[Invalid.] Foreign account');
    }


    /**
     * Отвергать: Несуществующий тип (id) счета
     */
    public function testPostAccountTypeForeignKeyFail()
    {
        $id = Doctrine::getTable('AccountType')->createQuery('t')
            ->select("MAX(t.account_type_id)")
            ->execute(array(), Doctrine::HYDRATE_SINGLE_SCALAR) + 1;

        $xml = $this->getXMLHelper()->make(array('type_id' => $id, 'cid' => 4,));

        $this
            ->myXMLPost($xml, 200)
            ->with('response')->begin()
                ->checkElement('resultset[type="account"] record[id][success="false"][cid]', 1)
            ->end();

        $this->checkRecordError(4, '[Invalid.] No such account type');
    }


    /**
     * Отвергать: Несуществующая валюта
     */
    public function testPostAccountCurrencyForeignKeyFail()
    {
        $id = Doctrine::getTable('Currency')->createQuery('t')
            ->select("MAX(t.id)")
            ->execute(array(), Doctrine::HYDRATE_SINGLE_SCALAR) + 1;

        $xml = $this->getXMLHelper()->make(array('currency_id' => $id, 'cid' => 3,));

        $this
            ->myXMLPost($xml, 200)
            ->with('response')->begin()
                ->checkElement('resultset[type="account"] record[id][success="false"][cid]', 1)
            ->end();

        $this->checkRecordError(3, '[Invalid.] No such currency');
    }

}
