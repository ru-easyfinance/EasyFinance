<?php

class ReportMatrix {

    /**
     * Список операций
     * @var OperationCollection
     */
    private $_operations;

    private $_currency;

    //строковые названия уровней дерева
    private $_leftRoot;
    private $_topRoot;
    private $_valuesTable = array();

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
        //TODO: 
        $this->_getData($user, $startDate, $endDate);
        $this->_buildMatrix();
        $this->_fillMatrix();
    }

    public function getResult()
    {
        $result = array();

        $result["values"] = array();
        $this->_renderValues($result["values"], $this->_leftRoot, $this->_topRoot);

        $result["leftTree"] = array();
        $this->_renderTree($result["leftTree"], $this->_leftRoot);
        $result["topTree"] = array();
        $this->_renderTree($result["topTree"], $this->_topRoot);
    }

    private function _getData(User $user, DateTime $startDate, DateTime $endDate)
    {
        $this->_operations = new OperationCollection($user);
        $this->_operations->fillForPeriod($startDate, $endDate);
    }

    private function _buildMatrix()
    {
        //создаем корневые узлы
        $this->_leftRoot = new ReportMatrixNode(null, "Всего", null, "leftRoot", "root");
        $this->_topRoot = new ReportMatrixNode(null, "Всего", null, "topRoot", "root");

        //пока нужен конкретный отчет =>структуру дерева захардкодим

        $parentCategories = GetParentCategories;

        //TODO: использовать ReportMatrixNode->addChildNode
        foreach($parentCategories as $parentCategory)
        {
            $parentCategoryNode = new ReportMatrixNode($this->_leftRoot,
                $parentCategory->getName(), $parentCategory, "parentCategory",
                $parentCategory->id);

            foreach($parentCategory->getChildCategories() as $childCategory) {
                $childCategoryNode = new ReportMatrixNode($parentCategoryNode,
                    $parentCategory->getName(), $parentCategory, "childCategory",
                    $parentCategory->id);
            }
        }

        $tags = GetTags();
        foreach($tags as $tag)
        {
            $tagNode = new ReportMatrixNode($this->_topRoot,
                $tag, $tag, "tag",
                $tag->id);
        }
    }

    private function _fillMatrix()
    {
        foreach ($this->_operations->getOperations() as $operation) {
            $increment = new ReportMatrixIncrement($this->_currency);
            $increment->setOperation($operation);

            $this->_pushIncrement($increment, $this->_leftRoot, $this->_topRoot);
        }
    }

    private function _renderValues(Array $values, ReportMatrixNode $leftNode,
                                   ReportMatrixNode $topNode) {
        $leftIndex = $leftNode->index;
        $topIndex = $topNode->index;
        $joinedIndex = $leftIndex . $topIndex;
        $values[$joinedIndex] = $this->_getValue($leftNode, $topNode);

        $iterateNext = null;
        if(count($leftNode->children) > 0)
        {
            $iterateNext = "left";
            $nestedLevel = $leftNode->children;
        }
        else if(count($topNode->children) > 0) {
            $iterateNext = "top";
            $nestedLevel = $topNode->children;
        }

        if (!is_null($iterateNext)) {
            foreach($nestedLevel as $nestedNode) {
                $nextLeftNode = $iterateNext == "left" ? $nestedNode : $leftNode;
                $nextTopNode = $iterateNext == "top" ? $nestedNode : $topNode;
                $this->_renderValues($values, $nextLeftNode, $nextTopNode);
            }
        }
    }

    private function _getValue($leftNode, $topNode) {
        $joinedIndex = $leftNode->index . $topNode->index;
        if(!array_key_exists($this->_valuesTable, $joinedIndex))
        {
            $this->_valuesTable[$joinedIndex] = 0;
            $iterateNext = null;
            if(count($leftNode->children) > 0)
            {
                $iterateNext = "left";
                $nestedLevel = $leftNode->children;
            }
            else if(count($topNode->children) > 0) {
                $iterateNext = "top";
                $nestedLevel = $topNode->children;
            }

            if (!is_null($iterateNext)) {
                foreach($nestedLevel as $nestedNode) {
                    $nextLeftNode = $iterateNext == "left" ? $nestedNode : $leftNode;
                    $nextTopNode = $iterateNext == "top" ? $nestedNode : $topNode;
                    $this->_valuesTable[$joinedIndex] +=
                            $this->_getValue($nextLeftNode, $nextTopNode);
                }
            }
        }
        $values[$joinedIndex] = $this->_valuesTable[$joinedIndex];
    }

    private function _pushIncrement(ReportMatrixIncrement $increment,
        ReportMatrixNode $leftNode, ReportMatrixNode $topNode) {

        $iterateNext = null;
        if(count($leftNode->children) > 0)
        {
            $iterateNext = "left";
            $nestedLevel = $leftNode->children;
        }
        else if(count($topNode->children) > 0) {
            $iterateNext = "top";
            $nestedLevel = $topNode->children;
        }

        if (!is_null($iterateNext)) {
            foreach($nestedLevel as $nestedNode) {
                $nextLeftNode = $iterateNext == "left" ? $nestedNode : $leftNode;
                $nextTopNode = $iterateNext == "top" ? $nestedNode : $topNode;
                if ($nextLeftNode->incrementFits($increment) &&
                    $nextTopNode->incrementFits($increment)) {
                    $this->_pushIncrement($increment, $nextLeftNode, $nextTopNode);
                }
            }
        }
        else {
            $this->_incrementValue($increment, $leftNode, $topNode);
        }
    }

    private function _incrementValue(ReportMatrixIncrement $increment, ReportMatrixNode $leftNode,
                                     ReportMatrixNode $topNode) {
        $joinedIndex = $leftNode->index . $topNode->index;
        if(!array_key_exists($this->_valuesTable, $joinedIndex)) {
            $this->_valuesTable[$joinedIndex] = 0;
        }
        $this->_valuesTable[$joinedIndex] += $increment->amount;
    }

    private function _renderTree(Array $nodeArray,
                                 ReportMatrixNode $node) {
        $nodeArray["label"] = $node->label;
        $nodeArray["index"] = $node->index;
        $nodeArray["children"] = array();
        foreach($node->children as $childNode) {
            $childArray = array();
            $this->_renderTree($childArray, $childNode);
            $nodeArray["children"][] = $childArray;
        }
    }
}