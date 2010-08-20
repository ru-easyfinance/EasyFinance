<?php

/**
 * Category
 */
class Category extends BaseCategory
{
    const TYPE_PROFIT = 1;


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
