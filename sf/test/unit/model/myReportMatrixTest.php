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
     * Проверяем что отчёт строится и в нём появляются заголовки
     * и таблица с числами
     */
    public function testBuildReport()
    {
        $user = Doctrine::getTable('User')->findOneByLogin('tester');
        $dateStart = new DateTime('2010-05-01');
        $dateEnd   = new DateTime('2010-12-01');
        $currency  = $user->getCurrency();

        $report = new myReportMatrix($currency);
        $report->buildReport($user, null, $dateStart, $dateEnd);

        $headerLeft = $report->getHeaderLeft();
        $headerTop  = $report->getHeaderTop();
        $matrix     = $report->getMatrix();

        $getCategoryByLabel = function($elements, $label) {
            foreach ($elements as $element) {
                if ($element->label == $label) {
                    return $element;
                }
            }
            return false;
        };

        $categoryParent  = $getCategoryByLabel($headerLeft, 'Parent Cat 1');
        $categoryChild   = $getCategoryByLabel(
            $categoryParent->children,
            'Child Cat 1'
        );
        $categoryAnother = $getCategoryByLabel($headerLeft, 'Another Cat');

        $this->assertEquals(
            1200,
            $matrix[$categoryParent->flatIndex]['tag_bar'],
            'Сумма в родительской категории ' . $categoryParent->label
        );

        $this->assertEquals(
            800,
            $matrix[$categoryChild->flatIndex]['tag_bar'],
            'Сумма в дочерней категории ' . $categoryChild->label
        );

        $this->assertEquals(
            50,
            $matrix[$categoryAnother->flatIndex]['tag_foo'],
            'Сумма в третьей категории ' . $categoryAnother->label
        );
    }

    /**
     * Проверим что операции фильтруются по счёту
     */
    public function testReportFiltredByAccount()
    {
        $user = Doctrine::getTable('User')->findOneByLogin('tester');
        $dateStart = new DateTime('2010-05-01');
        $dateEnd   = new DateTime('2010-12-01');
        $currency  = $user->getCurrency();

        list($account, $emptyAccount) = $user->getAccounts();

        $report = new myReportMatrix($currency);
        $report->buildReport($user, $account, $dateStart, $dateEnd);

        $this->assertNotEmpty(
            $report->getMatrix(),
            'Счёт по которому есть операции'
        );

        $report = new myReportMatrix($currency);

        $report->buildReport($user, $emptyAccount, $dateStart, $dateEnd);

        $this->assertEmpty(
            $report->getMatrix(),
            'Счёт по которому нет операций'
        );
    }

    /**
     * Проверяем отчёт по доходам
     * и таблица с числами
     */
    public function testProfitReport()
    {
        $user = Doctrine::getTable('User')->findOneByLogin('tester');
        $dateStart = new DateTime('2010-05-01');
        $dateEnd   = new DateTime('2010-12-01');
        $currency  = $user->getCurrency();

        $report = new myReportMatrix($currency);
        $report->buildReport(
            $user,
            null,
            $dateStart,
            $dateEnd,
            Operation::TYPE_PROFIT
        );

        $headerLeft = $report->getHeaderLeft();
        $matrix     = $report->getMatrix();

        $this->assertEquals(
            2,
            count($headerLeft),
            'В заголовках ожидается одна доходная категория и Итого'
        );

        $this->assertEquals(
            800,
            $matrix[$headerLeft[0]->flatIndex]['tag_bar'],
            'Сумма в доходной категории по тэгу tag_bar'
        );

        $this->assertEquals(
            400,
            $matrix[$headerLeft[0]->flatIndex]['tag_foo'],
            'Сумма в доходной категории по тэгу tag_foo'
        );

        $this->assertEquals(
            400,
            $matrix[$headerLeft[1]->flatIndex]['tag_foo'],
            'Сумма в тэге tag_foo по категориям'
        );
    }


    /**
     * Тест итогов на одной операции
     */
    public function testTotal()
    {
        $user = Doctrine::getTable('User')->findOneByLogin('tester');
        $dateStart = new DateTime('2010-08-01');
        $dateEnd   = new DateTime('2010-12-01');
        $currency  = $user->getCurrency();

        $report = new myReportMatrix($currency);
        $report->buildReport(
            $user,
            null,
            $dateStart,
            $dateEnd,
            Operation::TYPE_PROFIT
        );

        $headerLeft = $report->getHeaderLeft();
        $matrix     = $report->getMatrix();

        $this->assertEquals(
            400,
            $matrix[$headerLeft[0]->flatIndex]['tag_foo'],
            'Сумма в доходной категории по тэгу tag_foo'
        );

        $this->assertEquals(
            400,
            $matrix[$headerLeft[1]->flatIndex]['tag_foo'],
            'Сумма в тэге tag_foo по категориям'
        );
    }
}
