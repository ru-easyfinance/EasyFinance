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
        return array();
    }


    /**
     * Получить массив валидных данных
     */
    protected function getValidData()
    {
        return array(
            'id'          => 'ID12345',
            'email'       => $this->_getUser()->getUserServiceMail(),
            'type'        => 0,
            'account'     => $this->helper->makeText('Номер счета, который передал банк', false),
            'timestamp'   => '2005-08-15T15:52:01+000',
            'amount'      => '1234.56',
            'payment'     => '1000 USD',
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

        $op = $this->helper->makeOperation();
        $source = new SourceOperation;
        $source->setOperationId($op->getId());
        $source->setSourceUid(Operation::SOURCE_AMT);
        $source->setSourceOperationUid('12345');
        $source->save();

        return array(
            // Ничего не отправлено
            'Empty request' => new sfPHPUnitFormValidationItem(
                array(),
                array(
                    'id'          => 'required',
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

            // Операция с таким id уже существует
            'Source operation already exists' => new sfPHPUnitFormValidationItem(
                array_merge($this->getValidInput(), array('id' => $source->getSourceOperationUid())),
                array(
                    'source_uid' => 'invalid',
                )),
        );
    }


    /**
     * Сохраним операцию дохода
     */
    public function testSaveProfitOperation()
    {
        // Создать дебетовый счет (шум для привязки к счету)
        $acc = $this->helper->makeAccount($this->_getUser(), array(
            'type_id' => Account::TYPE_DEBIT_CARD,
            'Properties' => array(
                array(
                    'field_id'    => 999,
                    'field_value' => 'abcd',
                ),
            )
        ));

        $input = $this->getValidInput();
        // доход
        $input['type'] = 1; // см. вики
        $input['amount'] = '-1234.56'; // минус роли не играет, abs()
        $date = new DateTime($input['timestamp']);
        $date->setTimezone(new DateTimeZone(date_default_timezone_get()));

        $this->form->bind($input, array());
        $this->assertFormIsValid($this->form);

        $op = $this->form->save();
        $expected = array(
            'user_id'   => $this->_getUser()->getId(),
            'account_id' => null,
            'amount'    => abs((float) $input['amount']),
            'date'      => $date->format('Y-m-d'),
            'type'      => Operation::TYPE_PROFIT,
            'comment'   => sprintf("%s\n\nНомер счета: %s\nМесто совершения операции: %s\nТекущий баланс: %s\nСумма платежа: %s",
                $input['description'], $input['account'], $input['place'], $input['balance'], $input['payment']),
            'source_id' => Operation::SOURCE_AMT,
            'accepted'  => Operation::STATUS_DRAFT,
        );
        $this->assertOperation($expected, $op);
        $this->assertEquals(1, $this->queryFind('Operation', $expected)->count(), 'Expected found 1 object (Operation)');

        // Операция из внешнего источника
        $expected = array(
            'operation_id' => $op->getId(),
            'source_uid'   => Operation::SOURCE_AMT,
            'source_operation_uid' => $input['id'],
        );
        $this->assertEquals(1, $this->queryFind('SourceOperation', $expected)->count(), 'Expected found 1 object (SourceOperation)');
    }


    /**
     * Сохраним операцию расхода
     */
    public function testSaveExpenseOperation()
    {
        $input = $this->getValidInput();
        // расход
        $input['type'] = 0; // Расход, см. вики
        $input['amount'] = '1234.56';

        $this->form->bind($input, array());
        $this->assertFormIsValid($this->form);

        $op = $this->form->save();
        $expected = array(
            'user_id'   => $this->_getUser()->getId(),
            'account_id' => null,
            'amount'    => -(float) $input['amount'],
            'type'      => Operation::TYPE_EXPENSE,
        );
        $this->assertOperation($expected, $op);
        $this->assertEquals(1, $this->queryFind('Operation', $expected)->count(), 'Expected found 1 object');
    }


    /**
     * Привязка к счету
     */
    public function testBindAccount()
    {
        // Создать счет и привязать к AMT
        $account = $this->helper->makeAccount($this->_getUser(), array(
            'type_id' => Account::TYPE_DEBIT_CARD,
            'props' => array(array(AccountProperty::COLUMN_BINDING, Operation::SOURCE_AMT)),
        ));


        $this->form->bind($this->getValidInput(), array());
        $this->assertFormIsValid($this->form);

        $op = $this->form->save();
        $expected = array(
            'user_id'    => $this->_getUser()->getId(),
            'account_id' => $account->getId(),
            'source_id'  => Operation::SOURCE_AMT,
        );
        $this->assertOperation($expected, $op);
        $this->assertEquals(1, $this->queryFind('Operation', $expected)->count(), 'Expected found 1 object');
    }

}
