<?php
require_once dirname(__FILE__).'/../../../bootstrap/all.php';


/**
 * Синхронизация: получить список объектов
 */
class api_sync_InTest extends myFunctionalTestCase
{
    protected $app = 'api';


    public function testPostAccount()
    {
        $this->browser
            ->post(
                $this->generateUrl('sync_in_account'),
                array('body' => file_get_contents(dirname(__FILE__) . '/xml/testSyncInAccounts.xml'))
            )
            ->with('request')->begin()
                ->isParameter('module', 'sync')
                ->isParameter('action', 'syncInAccount')
            ->end()
            ->with('response')->isStatusCode('200');

    }
}
