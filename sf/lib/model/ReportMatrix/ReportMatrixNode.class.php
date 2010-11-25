<?php
class ReportMatrixNode {

    /**
     * @var array
     */
    public $children = array();

    public $label;

    public $index;

    /**
     * @var DoctrineRecord
     */
    public $groupValue;

    /**
     * Уровень узла
     * @var string
     */
    public $level;

    private $_parent;

    public function __construct(ReportMatrixNode $parent,
                                String $label, $groupValue, String $level,
    String $indexId) {

        //TODO: label, groupValue, indexId занести в единый объект nodeValue
        $this->label = $label;
        $this->groupValue = $groupValue;
        $this->level = $level;

        $this->_parent = $parent;
        $this->_setIndex($indexId);

        $parent->children[] = $this;
    }

    private function _setIndex(String $indexId) {
        $parentIndex = is_null($this->_parent) ? "" : $this->_parent->index;
        $this->index = $parentIndex . $this->level . "[" . $indexId . "]";
    }

    public function incrementFits(ReportMatrixIncrement $increment)
    {
        return $this->groupValue == $increment->getLevelValue($this->level);
    }
}