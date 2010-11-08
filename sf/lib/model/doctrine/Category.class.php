<?php

/**
 * Category
 */
class Category extends BaseCategory
{
    const TYPE_PROFIT = 1;
    const DEBT_SYSTEM_CATEGORY_ID = 25;


    /**
     * @param $user
     * @return Category или NULL
     */
    public static function getDebtCategoryInstance(User $user)
    {
        static $debtCategoryByUserId = array();

        if (!array_key_exists($user->getId(), $debtCategoryByUserId)) {
            $category = Doctrine::getTable('Category')
                ->findOneBySystemIdAndUserId(
                    Category::DEBT_SYSTEM_CATEGORY_ID,
                    $user->getId()
                );
            $debtCategoryByUserId[$user->getId()] = ($category !== false) ?
                $category : null ;
        }

        return $debtCategoryByUserId[$user->getId()];
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
