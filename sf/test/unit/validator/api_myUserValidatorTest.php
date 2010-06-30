<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';


/**
 * Валидатор пользователя (логина/пароля)
 */
class validator_myUserValidatorTest extends myUnitTestCase
{
    protected $app = 'api';
    protected $validator;


    /**
     * Before execute
     */
    protected function _start()
    {
        $this->validator = new myUserValidator;
    }


    /**
     * Пользователь не найден
     */
    public function testValidatorNotFound()
    {
        $input = array('login' => 'somelogin', 'password' => 'somepassword');
        try {
            $this->validator->clean($input);
            $this->fail("Expected validation error.");
        } catch (sfValidatorError $e) {
            $this->assertEquals('login [invalid]', $e->getCode());
            $this->assertEquals('login [The username and/or password is invalid.]', $e->getMessage());
        }
    }


    /**
     * Пользователь найден
     */
    public function testValidator()
    {
        $user = $this->helper->makeUser(array('user_name' => 'LoGiN', 'password' => 'PaSsWoRd'));
        $input = array('login' => 'LoGiN', 'password' => 'PaSsWoRd');

        try {
            $validatorClean = $this->validator->clean($input);
            $this->assertEquals($user, $validatorClean['user']);
        } catch (sfValidatorError $e) {
            $this->fail("Validation error ".$e->getCode());
        }
    }

}
