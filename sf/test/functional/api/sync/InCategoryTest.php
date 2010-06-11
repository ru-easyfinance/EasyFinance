<?php
require_once dirname(__FILE__).'/../../../bootstrap/all.php';


/**
 * Синхронизация: получить список категорий
 */
class api_sync_InCategoryTest extends mySyncInFunctionalTestCase
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
            'id'         => null,
            'root_id'    => 1,
            'parent_id'  => 0,
            'name'       => 'Категория',
            'type'       => 1,
            'cat_active' => 1,
            'visible'    => 1,
            'custom'     => 1,
            'dt_create'  => $this->_makeDate(-1000),
            'dt_update'  => $this->_makeDate(0),
        );
    }


    /**
     * Вернуть название модели
     *
     * @return string
     */
    protected function getModelName()
    {
        return 'category';
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
            ->postAndCheckXML("sync", "syncInCategory", $xml, "sync_in_category", $code);
    }


    /**
     * Ошибка при отправке без авторизации
     * (пока без ID пользователя в query string)
     */
    public function testPostCategoryAuthError()
    {
        $this->_user->setId(null);

        $this->checkSyncInError(null, 401, 'Authentification required');
    }


    /**
     * Ошибка при отправке пустого POST
     * нет данных на входе
     */
    public function testPostCategoryEmptyPostError()
    {
        $this->checkSyncInError(null, 400, 'Expected XML data');
    }


    /**
     * Пустой xml, отсутствуют записи для обработки
     */
    public function testPostCategoryEmptyXMLError()
    {
        $this->checkSyncInError($this->getXMLHelper()->getEmptyRequest(), 400, 'Expected at least one record');
    }


    /**
     * Входящий xml содержит слишком много записей
     */
    public function testPostCategoryRecordsLimitError()
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
    public function testPostCategorySingle()
    {
        $expectedData = array(
            'name'      => 'Какая-то категория',
            'dt_create' => $this->_makeDate(-10000),
            'dt_update' => $this->_makeDate(-300),
            'cid'       => 5,
        );

        $xml = $this->getXMLHelper()->make($expectedData);

        $this
            ->myXMLPost($xml, 200)
            ->with('response')->begin()
                ->checkElement('resultset', 1)
                ->checkElement('resultset record', 1)
                ->checkElement('resultset[type="category"] record[id][success="true"]', 'OK')
                ->checkElement(sprintf('resultset record[cid="%d"]', $expectedData['cid']), 'OK')
            ->end();

        unset($expectedData['cid']); // у записи нет такого поля
        $this->browser
            ->with('model')->check('Category', $expectedData, 1);
    }

}
