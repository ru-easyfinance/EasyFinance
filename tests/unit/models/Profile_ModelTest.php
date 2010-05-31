<?php
require_once dirname(__FILE__) . '/../bootstrap.php';

/**
 * Profile_Model
 */
class models_ProfileTest extends UnitTestCase
{
    /**
     * @var Profile_Model
     */
    protected $profile1;

    /**
     * @var Profile_Model
     */
    protected $profile2;

    /**
     * @var User
     */
    protected $user1;

    /**
     * @var User
     */
    protected $user2;


    /**
     * SetUp
     */
    protected function _start()
    {
        // Создаём заместо удалённых новых, чистых
        $options = array(
            'user_name'             => 'user1',
            'user_login'            => 'user1',
            'user_pass'             => sha1('123123'),
            'user_mail'             => 'user1@gmail.com',
            'user_created'          => '2009-08-14',
            'user_active'           => 1,
            'user_new'              => 0,
            'getNotify'             => 1,
            'user_currency_default' => 1,
            'user_currency_list'    => 'a:7:{i:0;s:1:"5";i:1;s:1:"7";i:2;s:1:"2";i:3;s:1:"3";i:4;s:2:"13";i:5;s:1:"4";i:6;s:1:"1";}',
            'user_type'             => 0,
            'user_service_mail'     => '',
            'referrerId'            => ''
        );

        $userId1 = CreateObjectHelper::createUser($options);

        $options = array(
            'user_name'             => 'user2',
            'user_login'            => 'user2',
            'user_pass'             => sha1('123123'),
            'user_mail'             => 'user2@gmail.com',
            'user_created'          => '2009-08-14',
            'user_active'           => 1,
            'user_new'              => 0,
            'getNotify'             => 1,
            'user_currency_default' => 1,
            'user_currency_list'    => 'a:7:{i:0;s:1:"5";i:1;s:1:"7";i:2;s:1:"2";i:3;s:1:"3";i:4;s:2:"13";i:5;s:1:"4";i:6;s:1:"1";}',
            'user_type'             => 0,
            'user_service_mail'     => 'ukko2@mail.easyfinance.ru',
            'referrerId'            => ''
        );
        $userId2 = CreateObjectHelper::createUser($options);
        
        $this->user1  = new oldUser ('user1', '123123');
        $this->user2  = new oldUser ('user2', '123123');

        $this->profile1 = new Profile_Model($this->user1);
        $this->profile2 = new Profile_Model($this->user2);
    }


    /**
     * Добавляем новую служебную почту для пользователя
     */
    public function testCreateServiceMail ()
    {
        // Тру
        $this->assertTrue( $this->profile1->createServiceMail( $this->user1, 'ukko1@mail.easyfinance.ru' ) );
        // Фэлз
        $this->assertFalse( $this->profile1->createServiceMail( $this->user2, 'ukko2@mail.easyfinance.ru' ) );

        // Фэлзе
        $this->assertFalse( $this->profile1->createServiceMail( new oldUser(), 'ukko@mail.easyfinance.ru' ) );
        //$this->assertFalse( $this->profile1->createServiceMail( $this->user1, '' ) ); // кхм
    }


    /**
     * Проверка на уникальную почту
     */
    public function testServiceMailIsUnique ()
    {
        // Уникально
        $email = 'ukko1@mail.easyfinance.ru';
        $this->assertTrue($this->profile1->checkServiceEmailIsUnique( $email ));

        // Не уникально
        $email = 'ukko2@mail.easyfinance.ru';
        $this->assertFalse($this->profile2->checkServiceEmailIsUnique( $email ));
    }


    /**
     * Удаление почты пользователя
     */
    public function testDeleteServiceMail ()
    {
        // Фэлз, ибо нет почты
        $this->assertFalse( $this->profile1->deleteServiceMail ( $this->user1 ) );
        // Тру, почта удалена
        $this->assertTrue( $this->profile1->deleteServiceMail ( $this->user2 ) );
    }

}
