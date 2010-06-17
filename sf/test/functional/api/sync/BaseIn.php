<?php
require_once dirname(__FILE__).'/../../../bootstrap/all.php';

/**
 * Sync: базовые тесты для всех входящих синхронизаций
 */
abstract class api_sync_in extends mySyncInFunctionalTestCase
{
    protected $app = 'api';


    /**
     * Ошибка при отправке без авторизации
     * (пока без ID пользователя в query string)
     */
    public function testPostAuthError()
    {
        $this->_user->setId(null);

        $this->checkSyncInError(null, 401, 'Authentification required');
    }


    /**
     * Ошибка при отправке пустого POST
     * нет данных на входе
     */
    public function testEmptyPostError()
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

}
