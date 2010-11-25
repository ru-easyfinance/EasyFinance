<?php
class ReportMatrixIncrement {

    public $amount;

    public $currency;

    private $_levelValues = array();

    public function __construct(Currency $currency)
    {
        $this->_currency = $currency;
    }

    public function getLevelValue($level)
    {
        return $this->_levelValues[$level];
    }

    public function setOperation(Operation $operation)
    {
        $this->_levelValues["parentCategory"] = $operation->getCategory()->getParentCategory();
        $this->_levelValues["childCategory"] = $operation->getCategory();

        $tags = array_map('trim', explode(',', $operation->getTags()));
        $tag  = isset($tags[0]) ? $tags[0] : null;
        $this->_levelValues["tag"] = $tag;

        $this->amount = $operation->getAmountForBudget(
            $this->currency,
            true
        );
    }
}