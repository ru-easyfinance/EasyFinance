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
        $user = $this->helper->makeUser(array('id' => 1));

        $this->browser->setTester('doctrine', 'sfTesterDoctrine');

        $this->browser
            ->post(
                $this->generateUrl('sync_in_account'),
                array('body' => file_get_contents(dirname(__FILE__) . '/xml/testSyncInAccounts.xml'))
            )
            ->with('request')->begin()
                ->isParameter('module', 'sync')
                ->isParameter('action', 'syncInAccount')
            ->end()
            ->with('response')->begin()
                ->isStatusCode('200')
                ->checkContains('<resultset type="account">')
                ->checkContains('record')
                ->checkElement('record', 3)
                ->checkElement('record[id]', 3)
                ->checkElement('record[cid]', 3)
                ->checkElement('record[success]', 3)
            ->end()
            ->with('doctrine')->check('Account', null, 3);
    }

}
