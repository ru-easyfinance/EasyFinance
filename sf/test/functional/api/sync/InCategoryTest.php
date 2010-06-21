<?php
require_once dirname(__FILE__).'/BaseIn.php';


/**
 * Синхронизация: получить список категорий
 */
class api_sync_InCategoryTest extends api_sync_in
{
    /**
     * Вернуть название модели
     *
     * @return string
     */
    protected function getModelName()
    {
        return 'Category';
    }


    /**
     * Получить список полей полей объекта, которые принимаются из XML
     */
    protected function getFields()
    {
        return array(
            'system_id',
            'parent_id',
            'name',
            'type',
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
            'id'         => null,
            'user_id'    => $this->_user->getId(),
            'system_id'  => 1,
            'parent_id'  => 0,
            'name'       => 'Категория',
            'type'       => 1,
            'custom'     => 1,
            'created_at' => $this->_makeDate(-1000),
            'updated_at' => $this->_makeDate(0),
            'deleted_at' => null,
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
            ->postAndCheckXML("sync", "syncInCategory", $xml, "sync_in_category", $code);
    }


    /**
     * Принять новую категорию
     */
    public function testNewCategory()
    {
        $expectedData = array(
            'user_id'    => $this->_user->getId(),
            'name'       => 'Какая-то категория',
            'created_at' => $this->_makeDate(-10000),
            'updated_at' => $this->_makeDate(-300),
            'cid'        => 5,
        );

        $xml = $this->getXMLHelper()->make($expectedData);

        $this
            ->myXMLPost($xml, 200)
            ->with('response')->begin()
                ->checkElement('resultset', 1)
                ->checkElement('resultset record', 1)
                ->checkElement('resultset[type="Category"] record[id][success="true"]', 'OK')
                ->checkElement(sprintf('resultset record[cid="%d"]', $expectedData['cid']), 'OK')
            ->end();

        unset($expectedData['cid']); // у записи нет такого поля
        $this->browser
            ->with('model')->check('Category', $expectedData, 1);
    }


    /**
     * Отвергать чужие записи
     */
    public function testPostCategoryForeignUserRecord()
    {
        $user2 = $this->helper->makeUser();
        $category = $this->helper->makeCategory($user2);

        $expectedData = array(
            'id'  => $category->getId(),
            'cid' => 8,
        );

        $xml = $this->getXMLHelper()->make($expectedData);

        $this
            ->myXMLPost($xml, 200)
            ->with('response')->begin()
                ->checkElement('resultset[type="Category"] record[id][success="false"][cid]', 1)
                ->checkElement(sprintf('record[id="%d"]', $expectedData['id']))
            ->end();

        $this->checkRecordError($expectedData['cid'], '[Invalid.] Foreign category');
    }


    /**
     * Отвергать: Несуществующий тип (id) системной категории
     */
    public function testPostCategorySystemForeignKeyFail()
    {
        $id = Doctrine::getTable('SystemCategory')->createQuery('t')
            ->select("MAX(t.id)")
            ->execute(array(), Doctrine::HYDRATE_SINGLE_SCALAR) + 1;

        $xml = $this->getXMLHelper()->make(array('system_id' => $id, 'cid' => 4,));

        $this
            ->myXMLPost($xml, 200)
            ->with('response')->begin()
                ->checkElement('resultset[type="Category"] record[id][success="false"][cid]', 1)
            ->end();

        $this->checkRecordError(4, '[Invalid.] No such root (system) category');
    }


    /**
     * Отвергать категорию без родительской (parent_id)
     */
    public function testPostCategoryParentFail()
    {
        $xml = $this->getXMLHelper()->make(array('parent_id' => 1, 'cid' => 6,));

        $this
            ->myXMLPost($xml, 200)
            ->with('response')->begin()
                ->checkElement('resultset[type="Category"] record[id][success="false"][cid]', 1)
            ->end();

        $this->checkRecordError(6, '[Invalid.] No such parent category');
    }


    /**
     * Отвергать: Родительская категория принадлежит другому пользователю
     */
    public function testCategoryForeignParent()
    {
        $cat = $this->helper->makeCategory();
        $xml = $this->getXMLHelper()->make(array('parent_id' => $cat->getId(), 'cid' => 6,));

        $this
            ->myXMLPost($xml, 200)
            ->with('response')->begin()
                ->checkElement('resultset[type="Category"] record[id][success="false"][cid]', 1)
            ->end();

        $this->checkRecordError(6, '[Invalid.] No such parent category');
    }

}
