<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';


/**
 * Форма для импорта операций из AMT
 */
class form_OperationImportAmtFormTest extends sfPHPUnitFormTestCase
{
    /**
     * Отключим автоматизированное тестирование сохранения формы.
     * Сделаем это ручками здесь.
     */
    protected $saveForm = false;

    /**
     * User
     */
    private $_user;


    /**
     * Создать пользователя
     *
     * @return User
     */
    private function _getUser()
    {
        if (!$this->_user) {
            $this->_user = $this->helper->makeUser();
        }
        return $this->_user;
    }


    /**
     * Сравнить Operation с ожидаемым массивом значений
     */
    private function assertOperation(array $expected, Operation $op)
    {
        ksort($expected);

        $actual = array_intersect_key($op->toArray(false), $expected);
        ksort($actual);

        $this->assertEquals($expected, $actual);
    }


    /**
     * Создать форму
     */
    protected function makeForm()
    {
        return new OperationImportAmtForm;
    }


    /**
     * Получить массив доступных полей формы
     */
    protected function getFields()
    {
        return array('email', 'type', 'account', 'timestamp', 'amount', 'description', 'place', 'balance');
    }


    /**
     * Получить массив валидных данных
     */
    protected function getValidData()
    {
        return array(
            'email'       => $this->_getUser()->getUserServiceMail(),
            'type'        => 0,
            'account'     => $this->helper->makeText('Номер счета', false),
            'timestamp'   => '2005-08-15T15:52:01+000',
            'amount'      => '1234.56',
            'description' => $this->helper->makeText(' Комментарий', false),
            'place'       => $this->helper->makeText('Место совершения операции', false),
            'balance'     => '23456.04',
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
                    'email'       => 'required',
                    'type'        => 'required',
                    'timestamp'   => 'required',
                    'amount'      => 'required',
                    'description' => 'required',
                )),

            // Email не найден
            'Email not found' => new sfPHPUnitFormValidationItem(
                array_merge($this->getValidInput(), array('email' => 'unknown@example.org')),
                array(
                    'email' => 'invalid',
                )),

            // Неизвестный тип операции
            'Invalid operation type' => new sfPHPUnitFormValidationItem(
                array_merge($this->getValidInput(), array('type' => 2)),
                array(
                    'type' => 'invalid',
                )),
        );
    }


    /**
     * Сохраним операцию дохода
     */
    public function testSaveProfitOperation()
    {
        $input = $this->getValidInput();
        // доход
        $input['type'] = Operation::TYPE_PROFIT;
        $input['amount'] = '-1234.56';
        $date = new DateTime($input['timestamp']);
        $date->setTimezone(new DateTimeZone(date_default_timezone_get()));

        $this->form->bind($input, array());
        $this->assertFormIsValid($this->form);

        $op = $this->form->save();
        $expected = array(
            'user_id'   => $this->_getUser()->getId(),
            'money'     => abs((float) $input['amount']),
            'date'      => $date->format('Y-m-d'),
            'time'      => $date->format('H:i:s'),
            'drain'     => Operation::TYPE_PROFIT,
            'type'      => Operation::TYPE_PROFIT,
            'comment'   => sprintf("%s\n\nНомер счета: %s\nМесто совершения операции: %s\nТекущий баланс: %s",
                $input['description'], $input['account'], $input['place'], $input['balance']),
            'source_id' => Operation::SOURCE_AMT,
            'accepted'  => Operation::STATUS_DRAFT,
        );
        $this->assertOperation($expected, $op);
        $this->assertEquals(1, $this->queryFind('Operation', $expected)->count(), 'Expected found 1 object');
    }


    /**
     * Сохраним операцию расхода
     */
    public function testSaveExpenseOperation()
    {
        $input = $this->getValidInput();
        // расход
        $input['type'] = Operation::TYPE_EXPENSE;
        $input['amount'] = '1234.56';

        $this->form->bind($input, array());
        $this->assertFormIsValid($this->form);

        $op = $this->form->save();
        $expected = array(
            'user_id'   => $this->_getUser()->getId(),
            'money'     => -(float) $input['amount'],
            'drain'     => Operation::TYPE_EXPENSE,
            'type'      => Operation::TYPE_EXPENSE,
        );
        $this->assertOperation($expected, $op);
        $this->assertEquals(1, $this->queryFind('Operation', $expected)->count(), 'Expected found 1 object');
    }

}
