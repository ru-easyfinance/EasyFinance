<?php

/**
 * Category
 */
class Category extends BaseCategory
{
    const TYPE_PROFIT = 1;
    const DEBT_SYSTEM_CATEGORY_ID = 25;


    public function getDebtCategoryInstance(User $user)
    {
        Doctrine::getTable('Category')->setData(
            array(
                'system_id' => Category::DEBT_SYSTEM_CATEGORY_ID,
                'user_id'   => $user->getId()
            )
        );
        $category = Doctrine::getTable('Category')->getRecord();

        return $category;
    }


    /**
     * ToString
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }
}
