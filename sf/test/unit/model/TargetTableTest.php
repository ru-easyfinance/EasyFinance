<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';


/**
 * model_TargetTableTest
 */
class model_TargetTableTest extends myUnitTestCase
{
    /**
     * @var User
     */
    private $_user;

    /**
     * @var myTestObjectHelper
     */
    protected $helper;

    /**
     * (non-PHPdoc)
     * @see sfPHPUnitTestCase::setUp()
     */
    public function setUp()
    {
        parent::setUp();

        $this->_user = $this->helper->makeUser();
        $account = $this->helper->makeAccount($this->_user);

        // Создаём две финцели в категории 1
        $this->helper->makeTarget(
            $account,
            array(
               'category_id' => '1',
               'visible'     => '1',
               'done'        => '1',
           )
        );

        $this->helper->makeTarget(
            $account,
            array(
               'category_id' => '1',
               'visible'     => '1',
               'done'        => '1',
           )
        );
        // и одну финцель в категории 2
        $this->helper->makeTarget(
            $account,
            array(
               'category_id' => '2',
               'visible'     => '1',
               'done'        => '0',
           )
        );
    }


    /**
     * Проверяем список категорий финцелей
     */
    public function testGetTargetCategories()
    {
        $table = Doctrine::getTable('Target');
        $categories = $table->getTargetCategories();

        $this->assertEquals(
            2,
            count($categories),
            'Категорий должно быть две'
        );
        $this->assertEquals(
            2,
            $categories[0]['cnt'],
            'В первой категории должно быть две финцели'
        );
        $this->assertEquals(
            2,
            $categories[0]['cl'],
            'Во второй категории должно быть ноль закрытых финцелей'
        );
    }


    /**
     * Проверяем список финцелей пользователя
     */
    public function testGetUserTargets()
    {
        $table = Doctrine::getTable('Target');
        $targets = $table->getUserTargets($this->_user);
        $this->assertEquals(
            3,
            count($targets),
            'У пользователя должно быть 3 финцели'
        );
    }
}
