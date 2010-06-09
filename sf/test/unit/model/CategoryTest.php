<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';


/**
 * Категории
 */
class model_CategoryTest extends myUnitTestCase
{
    /**
     * Отношения
     */
    public function testRelations()
    {
        $cat = new Category;

        // Пользователь
        $this->assertType('User', $cat->User);
    }


    /**
     * Создание объекта, алиасы
     */
    public function testMakeRecord()
    {
        $data = array(
            'user_id'     => $this->helper->makeUser()->getId(),
            'parent_id'   => 0,
            'system_id'   => 0,
            'name'        => 'Название категории',
            'type'        => Category::TYPE_PROFIT,
        );

        $this->checkModelDeclaration('Category', $data, $isTimestampable = true);
    }

}
