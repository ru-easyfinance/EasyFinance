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
        $sql = "INSERT INTO users
            (
                `id`,
                `user_name`,
                `user_login`,
                `user_pass`,
                `user_mail`,
                `user_created`,
                `user_active`,
                `user_new`,
                `getNotify`,
                `user_currency_default`,
                `user_currency_list`,
                `user_type`,
                `user_service_mail`,
                `referrerId`
            )
            VALUES
            (
                1,
                'ukko1',
                'ukko1',
                '601f1889667efaebb33b8c12572835da3f027f78',
                'max.kamashev@gmail.com',
                '2009-08-14',
                1,
                0,
                1,
                1,
                '" . 'a:7:{i:0;s:1:"5";i:1;s:1:"7";i:2;s:1:"2";i:3;s:1:"3";i:4;s:2:"13";i:5;s:1:"4";i:6;s:1:"1";}'."',
                0,
                '',
                ''
            ),
            (
                2,
                'ukko2',
                'ukko2',
                '601f1889667efaebb33b8c12572835da3f027f78',
                'max.kamashev@gmail.com',
                '2009-08-16',
                1,
                0,
                1,
                1,
                '" . 'a:7:{i:0;s:1:"5";i:1;s:1:"7";i:2;s:1:"2";i:3;s:1:"3";i:4;s:2:"13";i:5;s:1:"4";i:6;s:1:"1";}'."',
                0,
                'ukko2@mail.easyfinance.ru',
                ''
            )";


        Core::getInstance()->db->query( $sql );

        $this->user1  = new oldUser ('ukko1', '123123');
        $this->user2  = new oldUser ('ukko2', '123123');

        $this->profile1 = new Profile_Model( $this->user1 );
        $this->profile2 = new Profile_Model( $this->user2 );
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
