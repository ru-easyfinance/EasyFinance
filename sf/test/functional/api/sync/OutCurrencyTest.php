<?php
require_once dirname(__FILE__).'/../../../bootstrap/all.php';


/**
 * Синхронизация: отдать валюты
 */
class api_sync_OutCurrencyTest extends myFunctionalTestCase
{
    protected $app = 'api';


    /**
     * Отдать валюты
     * см. фикстуры, там куча валют
     */
    public function testGetCurrency()
    {
        $c = Doctrine::getTable('Currency')->find(2);
        $c->setRate(2);
        $c->save();

        $user = $this->helper->makeUser();

        $this->browser
            ->getAndCheck('sync', 'syncOut', $this->generateUrl('sync_get_modified', array(
                'some_extra_field' => 1,
                'from'    => $c->getDateTimeObject('updated_at')->format(DATE_ISO8601),
                'to'      => $c->getDateTimeObject('updated_at')->format(DATE_ISO8601),
                'model'   => 'currency',
                'user_id' => $user->getId())), 200)
            ->with('response')->begin()
                ->checkContains('<recordset type="Currency">')
                ->checkElement('record')
                ->checkElement('record code')
                ->checkElement('record symbol')
                ->checkElement('record name')
                ->checkElement('record rate')
                ->checkElement('record created_at')
                ->checkElement('record updated_at')
            ->end();
    }

}
