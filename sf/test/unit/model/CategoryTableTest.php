<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';


/**
 * Таблица тегов
 */
class model_CategoryTableTest extends myUnitTestCase
{
    /**
     * Выбрать теги пользователя сгруппированные и отсортированные по весу
     */
    public function testQueryFindWithUseCount()
    {
        $user = $this->helper->makeUser();

        $account = $this->helper->makeAccount($user);

        $c1 = $this->helper->makeCategory($user);
        $c2 = $this->helper->makeCategory($user);
        $c3 = $this->helper->makeCategory($user);

        // не должны найти чужую категорию
        $this->helper->makeCategory();

        $op1 = $this->helper->makeOperation($account, array('category_id' => $c1->getId()));
        $op2 = $this->helper->makeOperation($account, array('category_id' => $c1->getId()));
        $op3 = $this->helper->makeOperation($account, array('category_id' => $c2->getId()));

        // не должны найти чужих операций
        $this->helper->makeOperation();

        // дергаем метод
        $results = Doctrine::getTable('Category')->queryFindWithUseCount($user)->execute();

        // тестируем
        $this->assertEquals(3, $results->count(), "Найдено 3 категории");

        foreach ($results as $index => $result) {
            $this->assertEquals($user->getId(), $results->get($index)->getUserId(), "Категория принадлежит пользователю");
        }

        $this->assertEquals(2, $results->get(0)->getCount(), "Операций по категории1 найдено 2");
        $this->assertEquals(1, $results->get(1)->getCount(), "Операций по категории2 найдено 1");
        $this->assertEquals(0, $results->get(2)->getCount(), "Операций по категории3 нет");
    }

}
