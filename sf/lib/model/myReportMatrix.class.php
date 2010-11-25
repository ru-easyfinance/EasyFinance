<?php

class myReportMatrix {

    private
        $_headerLeft,
        $_headerTop,
        $_matrix,
        $_currency,
        $_categories,
        $_tags,
        $_totalCategory;

    public function __construct(Currency $currency)
    {
        $this->_currency = $currency;
    }

    public function buildReport(User $user, Account $account = null, DateTime $startDate, DateTime $endDate)
    {
        $operationCollection = new OperationCollection($user);
        $operationCollection->fillForPeriod($startDate, $endDate);
        $operations = $operationCollection->getOperations();

        $this->_tags = array();
        $this->_categories = array();

        $this->_matrix = array();

        $this->_totalCategory = new Category();
        $this->_totalCategory->setId(-1);
        $this->_totalCategory->setName("Итого:");
        $this->_totalCategory->setParentId(null);


        foreach ($operations as $operation) {
            if ($account && $operation->getAccount() != $account)
                continue;

            $category = $operation->getCategory();
            $opTags = array_map('trim', explode(',', $operation->getTags()));
            $tag = (isset($opTags[0])) ? $opTags[0] : null ;

            if ($tag && $category) {
                $this->_addTagAndCategory($tag, $category, $operation);
                $this->_addTagAndCategory($tag, $this->_totalCategory, $operation);
            }
        }

        if (isset($this->_categories[-1])) {
            $tmpCat = $this->_categories[-1];
            unset($this->_categories[-1]);
            $this->_categories[-1] = $tmpCat;
        }

        $this->_buildHeaderLeft($this->_categories);
        $this->_buildHeaderTop($this->_tags);
    }

    public function _addTagAndCategory($tag, Category $category, Operation $operation)
    {
        $flatIndexLeft = $category->getId();
        $flatIndexTop  = $tag;

        $this->_tags[$tag] = $tag;
        $this->_categories[$category->getId()] = $category;

        if (!isset($this->_matrix[$flatIndexLeft][$flatIndexTop]))
            $this->_matrix[$flatIndexLeft][$flatIndexTop] = 0;

        $this->_matrix[$flatIndexLeft][$flatIndexTop]
            += (float) $operation->getAmountForBudget($this->_currency, false);

        $parentCategory = ($category->getParentId()) ?
            Doctrine::getTable('Category')->findOneById($category->getParentId()) :
            null ;

        if ($parentCategory) {
            $this->_addTagAndCategory($tag, $parentCategory, $operation);
        }
    }

    public function getHeaderLeft()
    {
        return $this->_headerLeft;
    }

    public function getHeaderTop()
    {
        return $this->_headerTop;
    }

    public function getMatrix()
    {
        return $this->_matrix;
    }

    private function _buildHeaderLeft($categories)
    {
        $categoriesById = array();
        $elementsByCategoryId = array();
        $header = array();
        $this->_headerLeft = array();

        foreach ($categories as $category) {
            $element = new myReportMatrixHeaderElement;
            $element->label = $category->getName();
            $element->flatIndex = $category->getId();
            $element->children = array();

            $categoriesById[$category->getId()] = $category;
            $elementsByCategoryId[$category->getId()] = $element;
            $header[$category->getId()] = $element;
        }

        foreach ($categoriesById as $categoryId => $category) {
            if ($parentId = $category->getParentId()) {
                if (!isset($categoriesById[$parentId])) {
                    $categoriesById[$parentId]
                        = Doctrine::getTable('Category')->findOneById($parentId);
                }

                $elementsByCategoryId[$parentId]->children[]
                    = $elementsByCategoryId[$categoryId];
                unset($header[$categoryId]);
            }
        }

        foreach ($header as $element) {
            $this->_headerLeft[] = $element;
        }
    }

    private function _buildHeaderTop($tags)
    {
        $this->_headerTop = array();

        foreach ($tags as $tag) {
            $element = new myReportMatrixHeaderElement;
            $element->label = $tag;
            $element->flatIndex = $tag;
            $element->children = array();
            $this->_headerTop[] = $element;
        }
    }
}

class myReportMatrixHeaderElement {
    public $label = '';
    public $flatIndex = null;
    public $children = array();
}
