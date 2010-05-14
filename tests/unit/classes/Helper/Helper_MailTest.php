<?php
require_once dirname(__FILE__) . '/../../bootstrap.php';

/**
 * Helper_Mail
 */
class Helper_MailTest extends UnitTestCase
{

    public function planForValidateEmail()
    {
        return array (
            array('user@mail.ru', true),
            array('u@mail.ru', true),
            array('@mail.ru', false),
            array('user+@mail.ru', true),
            array('user+4@mail.ru', true),
            array('User@mail.ru', true),
            array('USER@mail.ru', true),
            array('user-12@mail.ru', true),
            array('user.login@mail.ru', true),
            array('555@mail.ru', true),
            array('555.login@mail.ru', true),
            array('васисуалий.пупкин@mail.ru', false),
            array('вася@mail.ru', false),
            array('mail@mail', false),
            array('', false),
            array(null, false),
            array('@', false),
            array('mail.ru', false),
        );
    }

    /**
     * @dataProvider planForValidateEmail
     */
    public function testValidateEmail($mail, $bool)
    {
        $this->assertEquals(Helper_Mail::validateEmail($mail), $bool);
    }

}
