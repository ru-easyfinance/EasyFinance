<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';



/**
 * Список производителей
 */
class api_dataImportAmtTest extends myFunctionalTestCase
{
    protected $app = 'api';


    /**
     * Только POST запрос
     */
    public function testPostRequestOnly()
    {
        $this->browser
            ->get($this->generateUrl('data_import_amt'))
            ->with('response')->isStatusCode(404);
    }


    /**
     * Успешный запрос
     */
    public function testOk()
    {
        $this->browser
            ->post($this->generateUrl('data_import_amt'))
            ->with('response')->isStatusCode(200)
            ->with('request')->checkModuleAction('dataImportAmt', 'import')
            ->with('response')->begin()
                ->checkElement('status:contains("OK")')
                ->checkElement('error', false)
            ->end();
    }

}
