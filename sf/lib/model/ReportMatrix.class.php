<?php

class ReportMatrix {

    private $_root;

    /**
     * Список операций
     * @var OperationCollection
     */
    private $_operations;

    private $_currency;

    //строковые названия уровней дерева
    private $_levels;

    public function __construct(Currency $currency)
    {
        $this->_currency = $currency;
    }

    /**
     *
     * @param User $user
     * @param DateTime $startDate
     * @param DateTime $endtDate
     * @return void
     */
    public function fill(User $user, DateTime $startDate, DateTime $endDate)
    {
        $this->_getData($user, $startDate, $endDate);
        $this->_buildMatrix();
        $this->_fillMatrix();
    }

    public function getResult()
    {
        $result = array();
        $this->_renderChildren($this->_root, $result);

        return $result;
    }

    private function _renderChildren(ReportMatrixNode $node, $nodeArray)
    {
        $this->_renderNodeSelf($node, $nodeArray);


        foreach($node->children as $child)
        {
            $childArray = array();
            $this->_renderChildren($child, $childArray);
            $nodeArray["children"][] = $childArray;
        }

        $nodeArray["row"] = array();
        $this->_renderRowNode($node->row, $nodeArray["row"]);
    }

    private function _renderNodeSelf($node, $nodeArray){
        $nodeArray["value"] = $this->GetStringFromValue($node->groupValue);
        $nodeArray["level"] = $node->level;
        $nodeArray["children"] = array();
    }

    private function _renderRowNode($rowElements, $rowArray)
    {
        foreach($rowElements as $rowElement)
        {
            $rowElementArray = array();
            $this->_renderNodeSelf($rowElement, $rowElementArray);
            $rowElementArray["amount"] = $rowElement->getAmount();

            $this->_renderRowNode($rowElement->children, $rowElementArray["children"]);
        }
    }

    private function _getData(User $user, DateTime $startDate, DateTime $endDate)
    {
        $this->_operations = new OperationCollection($user);
        $this->_operations->fillForPeriod($startDate, $endDate);
    }

    private function _buildMatrix()
    {
        //пока нужен конкретный отчет =>структуру дерева захардкодим

        //строим все уровни дерева
        //проходим по порядку по уровням
        //проходим по элементам уровня
        //foreach($parentCategory) =

        for($levelIndex = 0; $levelIndex < count($this->_levels); $levelIndex++)
        {
            $currentLevel = $this->_levels[$levelIndex];
            $previousLevel = $levelIndex > 0 ? $this->_levels[$levelIndex - 1] : null;
        }
    }

    private function _fillMatrix()
    {
        foreach ($this->_operations->getOperations() as $operation) {
            $increment = new ReportMatrixIncrement($this->_currency);
            $increment->setOperation($operation);

            $this->_root->_pushIncrement($increment);
        }
    }
}


class ReportMatrixNode {

    /**
     * @var array
     */
    public $children = array();

    private $_type;

    public $row = array();

    /**
     * @var int
     */
    private $_amount;

    private $_leftTreeNode;

    /**
     * @var DoctrineRecord
     */
    public $groupValue;


    /**
     * Уровень узла
     * @var string
     */
    public $level;

    public function getAmount()
    {
        if(isset($this->_amount))
            return $this->_amount;

        $this->_amount = 0;

        $leftTreeNodeChildren =$this->_leftTreeNode->children;
        $rowChildren = $this->children;

        if(length($leftTreeNodeChildren) > 0)
        {
            foreach($leftTreeNodeChildren as $leftTreeNodeChild)
            {
                $leftTreeNodeChildRowNode = self::FindNodeInTree($leftTreeNodeChild->row, $this);
                $this->_amount += $leftTreeNodeChildRowNode->getAmount();
            }
        }
        else if(length($rowChildren) > 0)
        {
            foreach($rowChildren as $rowChild)
            {
                $this->_amount += $rowChild->getAmount();
            }
        }
    }

    /**
     *
     * Enter description here ...
     * @param $increment
     */
    public function _pushIncrement(ReportMatrixIncrement $increment)
    {
        if ($this->_fits($increment)) {

            //значения добавляем только листовым элементам -
            //для родительских вычисляем из листовых, агрегируя в двух измерениях
            if(length($this->children) > 0)
            {
                $iteratedArray = $this->children;
            }

            else if(length($this->row) > 0)
            {
                $iteratedArray = $this->row;
            }

            if(isset($iteratedArray))
            {
                foreach ($iteratedArray as $childNode)
                {
                    $childNode->_pushIncrement($increment);
                }
            }
            else
            {
                $this->_increment($increment);
            }
        }
    }

    /*
     * увеличиваем с проверкой на null
     * null-значения важны, чтобы видеть, что уже закешировали, что нет
     */
    private function _increment($increment)
    {
        if(!isset($this->amount))
        {
            $this->amount = 0;
        }
        $this->amount += $increment->amount;
    }

    private function _fits(ReportMatrixIncrement $increment)
    {
        return $this->groupValue == $increment->getLevelValue($this->level);
    }

}


class ReportMatrixIncrement {

    public $amount;

    public $currency;

    private $levelValues = array();

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
        $this->_levelValues["category"] = $operation->getCategory();

        $tags = array_map('trim', explode(',', $operation->getTags()));
        $tag  = isset($tags[0]) ? $tags[0] : null;
        $this->_levelValues["tag"] = $tag;

        $this->amount = $operation->getAmountForBudget(
            $this->currency,
            true
        );
    }
}