<?php
require_once dirname(__FILE__).'/../../../bootstrap/all.php';


/**
 * Счет: создать
 */
class frontend_account_AddTest extends myFunctionalTestCase
{
    protected $app = 'frontend';


    /**
     * Ошибки валидации
     */
    public function testValidationErrors()
    {
        $user = $this->helper->makeUser();
        $this->authenticateUser($user);

        $this->browser
            ->post($this->generateUrl('account_create', array('sf_format' => 'json')))
            ->with('request')->checkModuleAction('account', 'create')
            ->with('response')->isStatusCode(400)
            ->with('form')->begin()
                ->isInstanceOf('AccountWithBalanceForm')
                ->hasErrors(true)
            ->end()
            ->with('response')->checkEquals(json_encode(array('error'=>array('text' => 'Ошибка при создании счета'))));
    }


    /**
     * Создать счет с начальным балансом
     */
    public function testAddAccountWithInitBalance()
    {
        $user = $this->helper->makeUser();
        $this->authenticateUser($user);

        $this->browser
            ->post($this->generateUrl('account_create', array('sf_format' => 'json')), $data = array(
                'type_id'          => 1,
                'currency_id'      => 1,
                'name'             => 'Название "\'счета"',
                'description'      => 'Описание счета',
                'initPayment'      => '123.45',
                ))
            ->with('request')->checkModuleAction('account', 'create')
            ->with('response')->isStatusCode(200)
            ->with('form')->begin()
                ->isInstanceOf('AccountWithBalanceForm')
                ->hasErrors(false)
            ->end();

            // Сохранили в базу счет
            $accountProps = $data;
            unset($accountProps['initPayment']);
            $this->browser
                ->with('model')->check('Account', $accountProps, 1, $found);
            $account = $found->getFirst()->toArray(false);

            // Сохранили в базу операцию
            $this->browser
                ->with('model')->check('Operation', array(
                    'user_id'     => $user->getId(),
                    'account_id'  => $account['id'],
                    'amount'      => $data['initPayment'],
                ), 1);

            // Вернули JSON со свойствами созданного счета
            $expected = array(
                'id'           => (int)$account['id'],
                'type'         => (int)$account['type_id'],
                'name'         => $account['name'],
                'currency'     => (int)$account['currency_id'],
                'comment'      => $account['description'],
                'initPayment'  => (float)$data['initPayment'],
                'totalBalance' => (float)$data['initPayment'],
            );
            $this->browser
                ->with('response')->checkEquals(json_encode(array('result'=>$expected)));
    }
}
