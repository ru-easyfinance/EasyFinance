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
     * Получить массив доступных полей формы
     */
    protected function getFields()
    {
        return array('type_id', 'currency_id', 'name', 'description', 'initPayment');
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
            'initPayment' => 234.56,
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
        $input['initPayment'] = '';

        $user = $this->helper->makeUser();
        $form = $this->makeForm($user);
        $form->bind($input);
        $this->assertFormIsValid($form);

        $account = $form->save();
        $expected = $input;
        unset($expected['initPayment']);

        $this->assertEquals(1, $this->queryFind('Account', $expected)->count(), 'Expected found 1 object (Account)');

        // Операция с нулевым балансом
        $expectedOperation = array(
            'user_id'     => $user->getId(),
            'account_id'  => $account->getId(),
            'category_id' => null,
            'amount'      => 0,
            'date'        => '0000-00-00',
            'drain'       => 0,
            'type'        => 1,
            'comment'     => 'Начальный остаток',
            'accepted'    => 1,
        );
        $this->assertEquals(1, $this->queryFind('Operation', $expectedOperation)->count(), 'Expected found 1 object (Operation)');
    }


    /**
     * Сохраним счет с начальным балансом
     */
    public function testSaveWithInitBalance()
    {
        $input = $this->getValidInput();
        $input['initPayment'] = 123.45;

        $user = $this->helper->makeUser();
        $form = $this->makeForm($user);
        $form->bind($input);
        $this->assertFormIsValid($form);

        $account = $form->save();
        $expected = $input;
        unset($expected['initPayment']);

        $this->assertEquals(1, $this->queryFind('Account', $expected)->count(), 'Expected found 1 object (Account)');

        // Операция с нулевым балансом
        $expectedOperation = array(
            'user_id'     => $user->getId(),
            'account_id'  => $account->getId(),
            'category_id' => null,
            'amount'      => $input['initPayment'],
            'date'        => '0000-00-00',
            'drain'       => 0,
            'type'        => 1,
            'comment'     => 'Начальный остаток',
            'accepted'    => 1,
        );
        $this->assertEquals(1, $this->queryFind('Operation', $expectedOperation)->count(), 'Expected found 1 object (Operation)');
    }

}
