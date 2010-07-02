<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';


/**
 * Таблица счетов
 */
class model_AccountTableTest extends myUnitTestCase
{
    /**
     * Создать счет для фикстуры
     */
    private function _makeAccount($num, User $user, $type = null, $bind = null, $bindValue = null)
    {
        if (!$type) {
            $type = Account::TYPE_DEBIT_CARD;
        }

        if (!$bind) {
            $bind = AccountProperty::COLUMN_BINDING;
        }

        if (!$bindValue) {
            $bindValue = Operation::SOURCE_AMT;
        }

        return $this->helper->makeAccount($user, array(
            'name' => 'Account'.$num,
            'type_id' => $type,
            'props' => array(array($bind, $bindValue)),
        ));
    }


    /**
     * Получить счет привязанный к AMT
     */
    public function testFindBindedToAmt()
    {
        $user  = $this->helper->makeUser();
        $userA = $this->helper->makeUser();

        /**
         * Фикстуры
         * 1. не амт
         * 2. другая колонка
         * 3. Другой тип счета
         * 4. Чужой пользователь
         * 5. Удален
         * 6. Ok
         */
        $this->_makeAccount(6, $user, null, null, $value = 'not amt');
        $this->_makeAccount(5, $user, null, $column = 9999);
        $this->_makeAccount(4, $user, $type = Account::TYPE_CASH);
        $this->_makeAccount(3, $userA);
        $this->_makeAccount(2, $user)->delete();
        $a6 = $this->_makeAccount(1, $user);

        $result = Doctrine::getTable('Account')->queryFindLinkedWithAmt($user->getId())->execute();
        $this->assertEquals(1, $result->count());
        $this->assertModels($a6, $result->getFirst());
    }


    /**
     * Тест метода findLinkedWithSource
     */
    public function testFindLinkedWithSource()
    {
        $user  = $this->helper->makeUser();
        $userA = $this->helper->makeUser();

        $source = "citi1234";

        $account = $this->_makeAccount(5, $user, null, null, $source ); // OK
        $this->_makeAccount(4, $user );                                 // не тот источник
        $this->_makeAccount(3, $user, null, $column = 9999, $source );  // другая колонка
        $this->_makeAccount(2, $userA, null, null, $source );           // другой пользователь
        $this->_makeAccount(1, $user)->delete();                        // пользователь удален

        $result = Doctrine::getTable('Account')->findLinkedWithSource($user->getId(), $source);
        $this->assertEquals($account->getId(), $result);
    }


    /**
     * Получить счета пользователя с балансом и изначальными деньгами
     */
    public function testQueryFindWithBalanceAndBalanceOperation()
    {
        $user = $this->helper->makeUser();

        $account1 = $this->helper->makeAccount($user);
        $account2 = $this->helper->makeAccount($user);
        $account3 = $this->helper->makeAccount($user);

        // балансовые операции
        $op1 = $this->helper->makeBalanceOperation($account1, 100);
        $op2 = $this->helper->makeBalanceOperation($account2, 500);
        $op3 = $this->helper->makeBalanceOperation($account3, -20);

        // а это у другого пользователя
        $this->helper->makeBalanceOperation(null, 10000);

        // набор операций для рассчета баланса счета
        $coll1 = $this->helper->makeOperationCollection(5, $account1);
        $coll2 = $this->helper->makeOperationCollection(5, $account2);
        $coll3 = $this->helper->makeOperationCollection(5, $account3);

        $balance1 = $op1->getAmount();
        foreach ($coll1 as $collOperation) {
            $balance1 += (float) $collOperation->getAmount();
        }
        $balance2 = $op2->getAmount();
        foreach ($coll2 as $collOperation) {
            $balance2 += (float) $collOperation->getAmount();
        }
        $balance3 = $op3->getAmount();
        foreach ($coll3 as $collOperation) {
            $balance3 += (float) $collOperation->getAmount();
        }

        $result = Doctrine::getTable('Account')->queryFindWithBalanceAndBalanceOperation($user)->execute();

        $this->assertEquals(3, $result->count(), "Accounts count");
        $this->assertModels($account1, $result->get(0), "Accounts equals");
        $this->assertModels($account2, $result->get(1), "Accounts equals");
        $this->assertModels($account3, $result->get(2), "Accounts equals");

        $this->assertEquals(1, $result->get(0)->getOperations()->count(), "Account has only one Balance Operation");
        $this->assertEquals(1, $result->get(1)->getOperations()->count(), "Account has only one Balance Operation");
        $this->assertEquals(1, $result->get(2)->getOperations()->count(), "Account has only one Balance Operation");
        $this->assertEquals($op1->getAmount(), $result->get(0)->getOperations()->get(0)->getAmount(), "Account Balance equals");
        $this->assertEquals($op2->getAmount(), $result->get(1)->getOperations()->get(0)->getAmount(), "Account Balance equals");
        $this->assertEquals($op3->getAmount(), $result->get(2)->getOperations()->get(0)->getAmount(), "Account Balance equals");

        $this->assertEquals($balance1, $result->get(0)->getBalance(), '', 0.01);
        $this->assertEquals($balance2, $result->get(1)->getBalance(), '', 0.01);
        $this->assertEquals($balance3, $result->get(2)->getBalance(), '', 0.01);
    }

}
