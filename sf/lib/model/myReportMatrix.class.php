<?php

class myReportMatrix {

    private
        $_headerLeft,
        $_headerTop,
        $_matrix,
        $_currency,
        $_categories,
        $_tags,
        $_totalCategory,
        $_totalTag;

    const TAG_FOR_OPERATIONS_WITHOUT_TAGS = "<Без метки>";

    public function __construct(Currency $currency)
    {
        $this->_currency = $currency;
    }

    /**
     * Строит матричный отчёт
     * @param User $user
     * @param Account $account
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @param int $operationType
     */
    public function buildReport(
        User $user,
        Account $account = null,
        DateTime $startDate,
        DateTime $endDate,
        $operationType = null
    )
    {
        $totalLabel = "ИТОГО:";

        $operationCollection = new OperationCollection($user);
        $operationCollection->setPeriodStartDate($startDate)
                ->setPeriodEndDate($endDate)
                ->setAcceptedOnly(true)
                ->fill();
        $operations = $operationCollection->getOperations();

        $this->_tags = array();
        $this->_categories = array();

        $this->_matrix = array();

        $this->_totalCategory = new Category();
        $this->_totalCategory->setId(-1);
        $this->_totalCategory->setName($totalLabel);
        $this->_totalCategory->setParentId(null);
        $this->_totalTag = $totalLabel;


        foreach ($operations as $operation) {
            if (
                $operationType !== null &&
                $operationType != $operation->getType()
            ) {
                continue;
            }

            if ($account && $operation->getAccount() != $account) {
                continue;
            }

            $category = $operation->getCategory();
            $opTags = array_map('trim', explode(',', $operation->getTags()));

            //возьмем первый тэг операции (считаем, что тэг - один)
            //если тэгов у операции нет, присвоим специальный тэг
            $tag = (isset($opTags[0]) && strlen($opTags[0]) > 0)
                    ? $opTags[0]
                    : self::TAG_FOR_OPERATIONS_WITHOUT_TAGS;

            if ($tag && $category) {
                $this->_addTagAndCategory($tag, $category, $operation);
            }
        }

        //сортируем по алфавиту, но итоговые значения ставим самыми последними
        $sortLabelsWithTotal = function($firstLabel, $secondLabel) use ($totalLabel) {
            if($firstLabel == $totalLabel)
                return 1;
            else if($secondLabel == $totalLabel)
                return -1;
            else return $firstLabel < $secondLabel;
        };

        usort($this->_categories, $sortLabelsWithTotal);
        usort($this->_tags, $sortLabelsWithTotal);

        $this->_buildHeaderLeft($this->_categories);
        $this->_buildHeaderTop($this->_tags);
    }

    public function _addTagAndCategory(
        $tag,
        Category $category,
        Operation $operation
    )
    {
        //добавим сначала прямо в соответствующие тэг и категорию
        $this->_directAddTagAndCategory($tag, $category, $operation);

        $parentCategory = ($category->getParentId()) ?
            Doctrine::getTable('Category')
                ->findOneById($category->getParentId()) :
            null ;

        //добавим непосредственно в родительскую
        if ($parentCategory) {
            $this->_directAddTagAndCategory($tag, $parentCategory, $operation);
        }

        //добавим непосредственно итог по всем категориям
        $this->_directAddTagAndCategory($tag, $this->_totalCategory, $operation);

        //полностью повторим добавление для итога по тегам, если добавляем в обычный тэг
        if($tag != $this->_totalTag)
            $this->_addTagAndCategory($this->_totalTag, $category, $operation);
    }

    private function _directAddTagAndCategory($tag, $category, $operation)
    {
        $flatIndexLeft = $category->getId();
        $flatIndexTop  = $tag;

        $this->_tags[$tag] = $tag;
        $this->_categories[$category->getId()] = $category;

        if (!isset($this->_matrix[$flatIndexLeft][$flatIndexTop]))
            $this->_matrix[$flatIndexLeft][$flatIndexTop] = 0;

        $this->_matrix[$flatIndexLeft][$flatIndexTop]
            += (float) $operation->getAmountForBudget($this->_currency, false);
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
                        = Doctrine::getTable('Category')
                            ->findOneById($parentId);
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
