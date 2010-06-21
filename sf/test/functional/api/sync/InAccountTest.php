<?php
require_once dirname(__FILE__).'/BaseIn.php';


/**
 * Синхронизация: получить список объектов
 */
class api_sync_InAccountTest extends api_sync_in
{
    /**
     * Вернуть название модели
     *
     * @return string
     */
    protected function getModelName()
    {
        return 'Account';
    }


    /**
     * Получить список полей полей объекта, которые принимаются из XML
     */
    protected function getFields()
    {
        return array(
            'type_id',
            'currency_id',
            'name',
            'description',
            'created_at',
            'updated_at',
        );
    }


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
     * Принять новый счет
     */
    public function testNewAccount()
    {
        $expectedData = array(
          //'id'
            'cid'         => 5,
            'type_id'     => 2,
            'currency_id' => 4,
            'name'        => 'Мой счет',
            'description' => 'Описание счета',
            'created_at'  => $this->_makeDate(-10000),
            'updated_at'  => $this->_makeDate(-300),
            'deleted_at'  => null,
        );
        $xml = $this->getXMLHelper()->make($expectedData);

        // Отправить запрос
        $this->myXMLPost($xml, 200);

        // Создали счет в системе
        $recordData = $expectedData;
        unset($recordData['cid']);                      // у записи нет такого поля
        $recordData['user_id'] = $this->_user->getId(); // сохранили под нужным пользователем
        $this->browser
            ->with('model')->check('Account', $recordData, 1, $foundList);

        // Ответ
        $this->browser
            ->with('response')->begin()
                ->checkElement('resultset', 1)
                ->checkElement(sprintf('resultset[type="Account"] record[id="%d"][cid="%d"][success="true"]',
                        $foundList->getFirst()->getId(),
                        $expectedData['cid'])
                    , 'OK')
            ->end();
    }


    /**
     * Обновить существующий счет
     */
    public function testReplaceAccount()
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
                ->checkElement('resultset[type="Account"] record[id][success="true"][cid]', 1)
                ->checkElement(sprintf('record[id="%d"]', $account->getId()))
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
                ->checkElement('resultset[type="Account"] record[id][success="true"][cid]', 1)
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
                ->checkElement('resultset[type="Account"] record[id][success="false"][cid]', 1)
                ->checkElement(sprintf('record[id="%d"]', $expectedData['id']))
            ->end();

        $this->checkRecordError($expectedData['cid'], '[Invalid.] Foreign account');
    }


    /**
     * Отвергать: Несуществующий тип (id) счета
     */
    public function testPostAccountTypeForeignKeyFail()
    {
        $xml = $this->getXMLHelper()->make(array('type_id' => 9999, 'cid' => 4,));

        $this
            ->myXMLPost($xml, 200)
            ->with('response')->begin()
                ->checkElement('resultset[type="Account"] record[id][success="false"][cid]', 1)
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
                ->checkElement('resultset[type="Account"] record[id][success="false"][cid]', 1)
            ->end();

        $this->checkRecordError(3, '[Invalid.] No such currency');
    }

}
