<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';


/**
 * Тест матричного отчёта
 */
class model_myReportMatrixTest extends myUnitTestCase
{
    public function setUp()
    {
        parent::setUp();
        $yamlPath = dirname(__FILE__) . '/fixtures/reportMatrix.yml';
        Doctrine::loadData($yamlPath);
    }

    /**
     *
     */
    public function testBuildReport()
    {
        $user = Doctrine::getTable('User')->findOneByLogin('tester');
        $dateStart = new DateTime('2010-05-01');
        $dateEnd   = new DateTime('2010-12-01');
        $currency  = $user->getCurrency();

        $report = new myReportMatrix($currency);
        $report->buildReport($user, $dateStart, $dateEnd);

        $result = array(
            'headerLeft' => $report->getHeaderLeft(),
            'headerTop'  => $report->getHeaderTop(),
            'matrix'     => $report->getMatrix(),
        );

        //print_r($result);

        //$yamlDumper = new sfYamlDumper();
        //$yaml = $yamlDumper->dump($result, 4);

        //print_r($yaml);
        //print_r(json_encode($result));

        $this->markTestIncomplete('Доделай меня');
    }
}
