<?php
require_once dirname(__FILE__) . '/../bootstrap.php';

/**
 * Тест для старой модели бюджета
 */
class Budget_ModelTest extends UnitTestCase
{
    private $userId    = null;
    private $cat1      = null;
    private $cat2      = null;
    private $cat3      = null;
    private $accountId = null;


    /**
     * SetUp
     */
    function _start()
    {
        // Создаём пользователя
        $this->userId = CreateObjectHelper::makeUser();

        // Создаём категории
        $cat_options = array(
            'user_id' => $this->userId,
        );

        $this->cat1 = CreateObjectHelper::createCategory($cat_options);
        $this->cat2 = CreateObjectHelper::createCategory($cat_options);
        $this->cat3 = CreateObjectHelper::createCategory($cat_options);

        // Создаём счёт
        $account = CreateObjectHelper::makeAccount(array('user_id'=>$this->userId));
        $this->accountId = $account['account_id'];
    }


    /**
     * Прегенерирует операции
     */
    private function _makeOperation()
    {

        // Создаём операции
        $options   = array(
            'user_id'    => $this->userId,
            'chain_id'   => 999,
            'date'       => date('Y-m-d', time()-86400),
            'cat_id'     => $this->cat1,
            'account_id' => $this->accountId,
        );
        // Правильные операции, на вчера
        CreateObjectHelper::makeOperation($options);
        CreateObjectHelper::makeOperation($options);
        CreateObjectHelper::makeOperation($options);

        // Операция не выполнена
        $options['accepted'] = 0;
        $options['cat_id'] = $this->cat2;
        CreateObjectHelper::makeOperation($options);


        // Дата операции установлена на завтра
        $options['date'] = date('Y-m-d', time()+86400);
        CreateObjectHelper::makeOperation($options);

        // Дата операции установлена на завтра, но она отмечена выполненной
        $options['accepted'] = 1;
        $options['date'] = date('Y-m-d', time()+86400);
        $options['cat_id'] = $this->cat3;
        CreateObjectHelper::makeOperation($options);

        // Удалённая операция
        $options['deleted_at'] = '2010-02-02 02:02:02';
        CreateObjectHelper::makeOperation($options);

        // Обычная операция, вне цепочки
        unset($options['deleted_at']);
        unset($options['chain_id']);
        CreateObjectHelper::makeOperation($options);


        // Создаём записи в бюджет
        $budget_options = array(
            'user_id'   => $this->userId,
            'category'  => $this->cat1,
            'drain'     => 0,
        );
        CreateObjectHelper::createBudget($budget_options);

        $budget_options['drain'] = 1;
        $budget_options['amount'] = 250;
        CreateObjectHelper::createBudget($budget_options);
    }

    public function testLoadBudget()
    {
        $this->_makeOperation();

        $budget = new Budget_Model();
        $actual = $budget->loadBudget(null, null, $this->userId, $category = null, $currencyId = 1);

        $expected  = array(
            'list' => array(
                'd' => array(
                    $this->cat1 => array(
                        'amount' => 250,
                        'money'=> 0,
                        'mean'   => 375,
                    )
                ),
                'p' => array(
                    $this->cat1 => array(
                        'amount' => 500,
                        'money'=> 3000,
                        'mean'   => 375,
                    ),
                    $this->cat3 => array(
                        'money'=> 2000,
                        'amount' => 0,
                        'mean'   => 0,
                    )
                ),
            )
        );

        $this->assertEquals($expected, $actual, 'Expected equals array');
    }

}
