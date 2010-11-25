<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';


/**
 * Тест матричного отчёта
 */
class model_ReportMatrixTest extends myUnitTestCase
{
    public function setUp()
    {
        parent::setUp();
        //$yamlPath = dirname(__FILE__) . '/fixtures/budget.yml';
        //Doctrine::loadData($yamlPath);
    }

    /**
     *
     */
    public function testFill()
    {
        $user = $this->helper->makeUser();
        $dateStart = new DateTime();
        $dateEnd   = new DateTime();

        //$report = new ReportMatrix($user->getCurrency());
        //$report->fill($user, $dateStart, $dateEnd);

        $this->markTestIncomplete('Доделай меня');
    }
}
