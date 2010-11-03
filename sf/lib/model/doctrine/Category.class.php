<?php

/**
 * Category
 */
class Category extends BaseCategory
{
    const TYPE_PROFIT = 1;
    const DEBT_SYSTEM_CATEGORY_ID = 25;


    public static function getDebtCategoryInstance(User $user)
    {
        static $systemCategory = array();

        if (!isset($systemCategory[$user->getId()])) {
            $systemCategory[$user->getId()] = Doctrine::getTable('Category')
                ->findOneBySystemIdAndUserId(
                    Category::DEBT_SYSTEM_CATEGORY_ID,
                    $user->getId()
                );
        }

        return $systemCategory[$user->getId()];
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
