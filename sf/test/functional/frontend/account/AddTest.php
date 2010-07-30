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
            ->with('response')->checkJsonContains('error', array('text' => 'Ошибка при создании счета'));
    }


    /**
     * Ошибки валидации для PDA версии
     */
    public function testPdaValidationErrors()
    {
        $user = $this->helper->makeUser();
        $this->authenticateUser($user);

        $this->browser
            ->post($this->generateUrl('pda_account_create'))
            ->with('request')->checkModuleAction('account', 'createForPda')
            ->with('response')->checkRedirect(302, '/accounts/add', true);
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
                'initBalance'      => '123.45',
                'state'            => Account::STATE_FAVORITE,
                ))
            ->with('request')->checkModuleAction('account', 'create')
            ->with('response')->isStatusCode(200)
            ->with('form')->begin()
                ->isInstanceOf('AccountWithBalanceForm')
                ->hasErrors(false)
            ->end();

            // Сохранили в базу счет
            $accountProps = $data;
            unset($accountProps['initBalance']);
            $this->browser
                ->with('model')->check('Account', $accountProps, 1, $found);
            $account = $found->getFirst()->toArray(false);

            // Сохранили в базу операцию
            $this->browser
                ->with('model')->check('Operation', array(
                    'user_id'     => $user->getId(),
                    'account_id'  => $account['id'],
                    'amount'      => $data['initBalance'],
                ), 1);

            // Вернули JSON со свойствами созданного счета
            $expected = array(
                'id'           => (int)$account['id'],
                'type'         => (int)$account['type_id'],
                'name'         => $account['name'],
                'currency'     => (int)$account['currency_id'],
                'comment'      => $account['description'],
                'initBalance'  => (float)$data['initBalance'],
                'totalBalance' => (float)$data['initBalance'],
                'state'        => (int)$data['state'],
            );
            $this->browser
                ->with('response')->checkJsonContains('result', array(
                    'account' => $expected,
                    'text'    => 'Счёт успешно добавлен',
                ))
                ->with('response')->isHeader('Content-Type', 'application/json; charset=utf-8');
    }


    /**
     * Создание счета для PDA версии
     */
    public function testPdaAddAccount()
    {
        $user = $this->helper->makeUser();
        $this->authenticateUser($user);

        $this->browser
            ->post($this->generateUrl('pda_account_create'), $data = array(
                'type_id'          => 1,
                'currency_id'      => 1,
                'name'             => 'Название "\'счета"',
                'description'      => 'Описание счета',
                'initBalance'      => '123.45',
                ))
            ->with('request')->checkModuleAction('account', 'createForPda')
            ->with('response')->checkRedirect(302, '/info', true)
            ->with('model')->check('Account', null, 1);
    }

}
