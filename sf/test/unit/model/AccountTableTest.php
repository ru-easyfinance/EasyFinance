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
     *
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
}
