<?php
require_once dirname(__FILE__) . '/../../bootstrap.php';

/**
 * Account_Model
 */
class classes_Account_ModelTest  extends UnitTestCase
{
    /**
     * Получить список всех счетов пользователя
     */
    public function testLoadAll()
    {
        $accountA = CreateObjectHelper::makeAccount();
        $account1 = CreateObjectHelper::makeAccount();
        $account2 = CreateObjectHelper::makeAccount(array('deleted_at' => '2010-06-04', 'user_id' => $account1['user_id']));

        $model = new Account_Model;
        $result = $model->loadAll($account1['user_id']);

        $this->assertEquals(array(
            $account1['account_id'] => array(
                'id'       => $account1['account_id'],
                'type'     => $account1['account_type_id'],
                'currency' => $account1['account_currency_id'],
                'name'     => $account1['account_name'],
                'comment'  => $account1['account_description'],
                'state'    => Account::STATE_NORMAL,
            )), $result);
    }


    /**
     * Получить список всех счетов пользователя
     * Счетов нет
     */
    public function testLoadAllIfEmpty()
    {
        $account = CreateObjectHelper::makeAccount(array('deleted_at' => '2010-06-04'));

        $model = new Account_Model;
        $this->assertEquals(array(), $model->loadAll($account['user_id']));
    }


    /**
     * Получить список всех счетов пользователя со статистикой по балансу и пр.
     */
    public function testLoadAllWithStat()
    {
        $accountA = CreateObjectHelper::makeAccount();
        $account1 = CreateObjectHelper::makeAccount();
        $account2 = CreateObjectHelper::makeAccount(array('deleted_at' => '2010-06-04', 'user_id' => $account1['user_id']));

        // Начальный баланс
        CreateObjectHelper::makeBalanceOperation($account1, $initBalance = 123.45);
        // Обычная операция
        CreateObjectHelper::makeOperation(array(
            'user_id'    => $account1['user_id'],
            'account_id' => $account1['account_id'],
            'money'      => $amount = 5,
        ));
        // Резерв на финцель


        $model = new Account_Model;
        $result = $model->loadAllWithStat($account1['user_id']);

        $this->assertEquals(array(
            $account1['account_id'] => array(
                'id'           => $account1['account_id'],
                'type'         => $account1['account_type_id'],
                'currency'     => $account1['account_currency_id'],
                'name'         => $account1['account_name'],
                'comment'      => $account1['account_description'],
                'state'        => Account::STATE_NORMAL,
                'totalBalance' => $amount + $initBalance,
                'reserve'      => 0,
                'initBalance'  => $initBalance,
            )), $result);
    }


    /**
     * Получить список всех счетов пользователя со статистикой по балансу и пр.
     * Счетов нет
     */
    public function testLoadAllWithStatIFEmpty()
    {
        $account = CreateObjectHelper::makeAccount(array('deleted_at' => '2010-06-04'));

        $model = new Account_Model;
        $this->assertEquals(array(), $model->loadAllWithStat($account['user_id']));
    }


    /**
     * SoftDelete
     */
    public function testSoftDelete()
    {
        $account = CreateObjectHelper::makeAccount();

        Account_Model::delete($account['user_id'], $account['account_id']);

        $account = Account_Model::findById($account['account_id']);
        $this->assertTrue((bool)$account['deleted_at'],
            "Expected Account marked as deleted");
        $this->assertEquals($account['deleted_at'], $account['updated_at'],
            "Expected Account `deleted_at` equals `updated_at`");


        // Account_Model::delete - запускает свою транзацию
        // Поэтому надо подчистить таблицу
        // TODO: исправить
        $this->getConnection()->query('TRUNCATE TABLE accounts');
    }


    public function testAccountState()
    {
        $account = CreateObjectHelper::makeAccount();
        $this->assertEquals(Account::STATE_NORMAL, $account['account_state']);

        $account = CreateObjectHelper::makeAccount(array('account_state' => Account::STATE_FAVORITE));
        $this->assertEquals(Account::STATE_FAVORITE, $account['account_state']);

        $account = CreateObjectHelper::makeAccount(array('account_state' => Account::STATE_ARCHIVE));
        $this->assertEquals(Account::STATE_ARCHIVE, $account['account_state']);
    }

}
