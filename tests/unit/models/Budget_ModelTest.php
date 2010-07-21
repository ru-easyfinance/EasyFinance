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

        // Правильные операции, на вчера
        $options   = array(
            'user_id'    => $this->userId,
            'chain_id'   => 999,
            'date'       => date('Y-m-d'),
            'cat_id'     => $this->cat1,
            'account_id' => $this->accountId,
            'money'      => -1000,
        );
        CreateObjectHelper::makeOperation($options);

        $options   = array(
            'user_id'    => $this->userId,
            'chain_id'   => 999,
            'date'       => date('Y-m-d'),
            'cat_id'     => $this->cat1,
            'account_id' => $this->accountId,
            'money'      => -1000,
        );
        CreateObjectHelper::makeOperation($options);

        $options   = array(
            'user_id'    => $this->userId,
            'chain_id'   => 999,
            'date'       => date('Y-m-d'),
            'cat_id'     => $this->cat1,
            'account_id' => $this->accountId,
            'money'      => -1000,
        );
        CreateObjectHelper::makeOperation($options);

        // Операция не выполнена
        $options   = array(
            'user_id'    => $this->userId,
            'chain_id'   => 999,
            'date'       => date('Y-m-d'),
            'cat_id'     => $this->cat2,
            'account_id' => $this->accountId,
            'money'      => -1000,
            'accepted'   => 0,
        );
        CreateObjectHelper::makeOperation($options);


        // Дата операции установлена на завтра
        $options   = array(
            'user_id'    => $this->userId,
            'chain_id'   => 999,
            'date'       => date('Y-m-d'),
            'cat_id'     => $this->cat2,
            'account_id' => $this->accountId,
            'money'      => -1000,
            'accepted'   => 0,
        );
        CreateObjectHelper::makeOperation($options);

        // Дата операции установлена на завтра, но она отмечена выполненной
        $options   = array(
            'user_id'    => $this->userId,
            'chain_id'   => 999,
            'date'       => date('Y-m-d'),
            'cat_id'     => $this->cat3,
            'account_id' => $this->accountId,
            'money'      => 1001,
            'accepted'   => 1,
        );
        CreateObjectHelper::makeOperation($options);

        // Удалённая операция
        $options   = array(
            'user_id'    => $this->userId,
            'date'       => date('Y-m-d'),
            'cat_id'     => $this->cat3,
            'account_id' => $this->accountId,
            'money'      => 1002,
            'accepted'   => 1,
            'deleted_at' => '2010-02-02 02:02:02',
        );
        CreateObjectHelper::makeOperation($options);

        // Обычная операция, вне цепочки
        $options   = array(
            'user_id'    => $this->userId,
            'date'       => date('Y-m-d'),
            'cat_id'     => $this->cat3,
            'account_id' => $this->accountId,
            'money'      => 1003,
            'accepted'   => 1,
        );
        CreateObjectHelper::makeOperation($options);


        // Создаём записи в бюджет
        // Расходная часть для категории "cat1"
        $budget_options = array(
            'user_id'   => $this->userId,
            'category'  => $this->cat1,
            'drain'     => 0,
            'amount'    => 500,
        );
        CreateObjectHelper::createBudget($budget_options);

        // Доходная часть для категории "cat1"
        $budget_options = array(
            'user_id'   => $this->userId,
            'category'  => $this->cat1,
            'drain'     => 1,
            'amount'    => 250,
        );
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
                        'money'=> 3000,
                        'mean'   => 1000,
                    )
                ),
                'p' => array(
                    $this->cat1 => array(
                        'amount' => 500,
                        'money'=> 0,
                        'mean'   => 1000,
                    ),
                    $this->cat3 => array(
                        'money'=> 1001 + 1003,
                        'amount' => 0,
                        'mean'   => 0,
                    )
                ),
            )
        );

        $this->assertEquals($expected, $actual, 'Expected equal arrays');
    }

}
