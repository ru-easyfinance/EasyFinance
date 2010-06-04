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
            )), $result);
    }

}
