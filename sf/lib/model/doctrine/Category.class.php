<?php

/**
 * Category
 */
class Category extends BaseCategory
{
    const TYPE_PROFIT = 1;
    const DEBT_SYSTEM_CATEGORY_ID = 25;


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
