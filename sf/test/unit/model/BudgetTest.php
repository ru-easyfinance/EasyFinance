<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';


/**
 * Бюджет за период
 */
class model_BudgetTest extends myUnitTestCase
{
    public function setUp()
    {
        parent::setUp();
        $yamlPath = dirname(__FILE__) . '/fixtures/budget.yml';
        Doctrine::loadData($yamlPath);
    }

    /**
     * Посчитать бюджет на текущий месяц
     */
    public function testLoadBudget()
    {
        $user = Doctrine::getTable('User')->findOneByLogin('tester');
        $dateStart = new DateTime('2010-11-01');

        $budgetManager = new BudgetManager();
        $data = $budgetManager->load($user, $dateStart);
        // Смотри фикстуру budget.yml
        $expectations = array(
                1 => array(
                    'mean' => 100,
                    'plan' => 500,
                    'adhoc' => 200,
                    'calendarAccepted' => 100,
                    'calendarFuture' => 50
                ),
                2 => array(
                    'mean' => 0,
                    'plan' => 1000,
                    'adhoc' => 0,
                    'calendarAccepted' => 0,
                    'calendarFuture' => 0
                )
            );
        $this->markTestIncomplete('Допиши меня');
        foreach ($data as $budgetArticle) {
            $expectation = $expectations[$budgetArticle->key];
            foreach ($expectation as $field => $value) {
                $this->assertEquals(
                    $value,
                    $budgetArticle->$field,
                    "Поле $field статьи бюджета {$budgetArticle->key}"
                );
            }
        }
    }
}
