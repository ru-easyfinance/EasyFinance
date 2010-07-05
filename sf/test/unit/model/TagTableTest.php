<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';


/**
 * Таблица тегов
 */
class model_TagTableTest extends myUnitTestCase
{
    /**
     * Выбрать теги пользователя сгруппированные и отсортированные по весу
     */
    public function testQueryFindUniqueWithCountByUser()
    {
        $user = $this->helper->makeUser();

        $this->helper->makeTag(); // теги посчитаем
        $tag1 = $this->helper->makeTag($user, array('name' => 'test_tag_1'));
        $tag2 = $this->helper->makeTag($user, array('name' => 'test_tag_2'));
        $tag3 = $this->helper->makeTag($user, array('name' => 'test_tag_2'));

        // дергаем метод
        $results = Doctrine::getTable('Tag')->queryFindUniqueWithCountByUser($user)->execute();

        // тестируем
        $this->assertEquals(2, $results->count(), "Общее кол-во уникальных тегов");

        $this->assertEquals($user->getId(), $results->getFirst()->getUserId(), "Тег(и) принадлежат пользователю");
        $this->assertEquals($user->getId(), $results->get(1)->getUserId(), "Тег(и) принадлежат пользователю");

        $this->assertEquals(2, $results->getFirst()->getCount(), "Тегов test_tag_2 должно быть 2");
        $this->assertEquals(1, $results->get(1)->getCount(), "Тегов test_tag_1 должно быть 1");

        $this->assertEquals($tag2->getName(), $results->getFirst()->getName(), "Имя тега валидно");
        $this->assertEquals($tag3->getName(), $results->getFirst()->getName(), "Имя тега валидно");
        $this->assertEquals($tag1->getName(), $results->get(1)->getName(), "Имя тега валидно");
    }

}
