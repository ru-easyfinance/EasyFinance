<?php
require_once dirname(__FILE__).'/../../../bootstrap/all.php';
require_once sfConfig::get('sf_root_dir') . '/apps/frontend/lib/form/AccountWithBalanceForm.php';


/**
 * Форма для создания/редактирования счета с начальным балансом
 */
class form_frontend_AccountWithBalanceFormTest extends myFormTestCase
{
    protected $app = 'frontend';

    /**
     * Отключим автоматизированное тестирование сохранения формы.
     * Сделаем это ручками здесь.
     */
    protected $saveForm = false;


    /**
     * Создать форму
     */
    protected function makeForm(User $user = null)
    {
        $account = new Account;
        if (!$user) {
            $user = $this->helper->makeUser();
        }
        $account->setUser($user);

        return new AccountWithBalanceForm($account);
    }


    /**
     * Создать массив свойств балансовой операции
     */
    public function _makeBlanceOpeationArray(User $user, Account $account, $balance = 0)
    {
        return array(
            'user_id'     => $user->getId(),
            'account_id'  => $account->getId(),
            'category_id' => null,
            'amount'      => $balance,
            'date'        => '0000-00-00',
            'type'        => Operation::TYPE_BALANCE,
            'comment'     => 'Начальный остаток',
            'accepted'    => 1,
        );
    }


    /**
     * Получить массив доступных полей формы
     */
    protected function getFields()
    {
        return array(
            'type_id'     => array(
                'required' => true,
            ),
            'currency_id' => array(
                'required' => true,
            ),
            'name'        => array(
                'required' => true,
            ),
            'description' => array(),
            'initBalance' => array(),
            'state'       => array(),
        );
    }


    /**
     * Получить массив валидных данных
     */
    protected function getValidData()
    {
        return array(
            'type_id'     => Account::TYPE_CASH,
            'currency_id' => 1,
            'name'        => 'Название счета',
            'description' => 'Описание счета',
            'initBalance' => 234.56,
            'state'       => 0,
        );
    }


    /**
     * План тестирования ошибок валидации
     */
    protected function getValidationTestingPlan()
    {
        $validInput = $this->getValidInput();

        return array(
            // Ничего не отправлено
            'Empty request' => new sfPHPUnitFormValidationItem(
                array(),
                array(
                    'type_id'      => 'required',
                    'currency_id'  => 'required',
                    'name'         => 'required',
                )),

            // Неверный тип счета
            'Invalid type' => new sfPHPUnitFormValidationItem(
                array_merge($validInput, array('type_id' => 99999)),
                array(
                    'type_id'  => 'invalid',
                )),

            // Неверный тип валюты
            'Invalid сurrency' => new sfPHPUnitFormValidationItem(
                array_merge($validInput, array('currency_id' => 99999)),
                array(
                    'currency_id'  => 'invalid',
                )),

            // Превышена длина названия
            'Name max length' => new sfPHPUnitFormValidationItem(
                array_merge($this->getValidInput(), array('name'  => str_repeat('я', 256))),
                array(
                    'name'  => 'max_length',
                )),

            // Превышена длина описания
            'Description max length' => new sfPHPUnitFormValidationItem(
                array_merge($this->getValidInput(), array('description'  => str_repeat('я', 256))),
                array(
                    'description'  => 'max_length',
                )),
        );
    }


    /**
     * Сохраним счет с нулевым балансом
     */
    public function testSaveWithZeroBalance()
    {
        $input = $this->getValidInput();
        $input['initBalance'] = 0;

        $user = $this->helper->makeUser();
        $form = $this->makeForm($user);
        $form->bind($input);
        $this->assertFormIsValid($form);

        $account = $form->save();
        $expected = $input;
        unset($expected['initBalance']);

        $this->assertEquals(1, $this->queryFind('Account', $expected)->count(), 'Expected found 1 object (Account)');

        // Операция с нулевым балансом
        $expectedOperation = $this->_makeBlanceOpeationArray($user, $account, $balance = 0);
        $this->assertEquals(1, $this->queryFind('Operation', $expectedOperation)->count(), 'Expected found 1 object (Operation)');
    }


    /**
     * Сохраним счет с начальным балансом
     */
    public function testSaveWithInitBalance()
    {
        $input = $this->getValidInput();
        $input['initBalance'] = 123.45;

        $user = $this->helper->makeUser();
        $form = $this->makeForm($user);
        $form->bind($input);
        $this->assertFormIsValid($form);

        $account = $form->save();
        $expected = $input;
        unset($expected['initBalance']);

        $this->assertEquals(1, $this->queryFind('Account', $expected)->count(), 'Expected found 1 object (Account)');

        // Операция с нулевым балансом
        $expectedOperation = $this->_makeBlanceOpeationArray($user, $account, $balance = $input['initBalance']);
        $this->assertEquals(1, $this->queryFind('Operation', $expectedOperation)->count(), 'Expected found 1 object (Operation)');
    }


    /**
     * Сохраним счет с кривым начальным балансом
     */
    public function testSaveWithFailInitBalance()
    {
        $input = $this->getValidInput();
        $input['initBalance'] = 'NaN'; // js-хрень, см. parseFloat()

        $user = $this->helper->makeUser();
        $form = $this->makeForm($user);
        $form->bind($input);
        $this->assertFormIsValid($form);

        $account = $form->save();
        $expected = $input;
        unset($expected['initBalance']);

        $this->assertEquals(1, $this->queryFind('Account', $expected)->count(), 'Expected found 1 object (Account)');

        // Операция с начальным балансом
        $expectedOperation = $this->_makeBlanceOpeationArray($user, $account, $balance = 0);
        $this->assertEquals(1, $this->queryFind('Operation', $expectedOperation)->count(), 'Expected found 1 object (Operation)');
    }


    /**
     * Редактируем счет с начальным балансом
     */
    public function testEditWithInitBalance()
    {
        $input = $this->getValidInput();
        $input['initBalance'] = 123.45;

        $account = $this->helper->makeAccount();
        // Балансовая операция
        $this->helper->makeOperation($account, $this->_makeBlanceOpeationArray($account->getUser(), $account, 0));
        $form = new AccountWithBalanceForm($account);

        $form->bind($input);
        $this->assertFormIsValid($form);

        $account = $form->save();
        $expected = $input;
        unset($expected['initBalance']);

        $this->assertEquals(1, $this->queryFind('Account', $expected)->count(), 'Expected found 1 object (Account)');

        // Операция с нулевым балансом
        $expectedOperation = $this->_makeBlanceOpeationArray($account->getUser(), $account, $balance = $input['initBalance']);
        $this->assertEquals(1, $this->queryFind('Operation', $expectedOperation)->count(), 'Expected found 1 object (Operation)');
    }


    /**
     * Редактируем счет с начальным балансом
     * Балансовой операции нет
     */
    public function testEditWithNoBalanceOpeartion()
    {
        $input = $this->getValidInput();
        $input['initBalance'] = 123.45;

        $account = $this->helper->makeAccount();
        // Балансовой операции нет
        $this->helper->makeOperation($account);
        $form = new AccountWithBalanceForm($account);

        $form->bind($input);
        $this->assertFormIsValid($form);

        $account = $form->save();
        $expected = $input;
        unset($expected['initBalance']);

        $this->assertEquals(1, $this->queryFind('Account', $expected)->count(), 'Expected found 1 object (Account)');

        // Операция с нулевым балансом
        $expectedOperation = $this->_makeBlanceOpeationArray($account->getUser(), $account, $balance = $input['initBalance']);
        $this->assertEquals(1, $this->queryFind('Operation', $expectedOperation)->count(), 'Expected found 1 object (Operation)');
    }

}
