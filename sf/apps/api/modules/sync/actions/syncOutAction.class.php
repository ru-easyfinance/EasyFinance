<?php

/**
 * Sync: отдать список объектов
 */
class syncOutAction extends sfAction
{
    /**
     * Execute
     */
    public function execute($request)
    {
        $modelName = $this->_getModelName($request->getParameter('model'));
        $this->forward404Unless($modelName);

        // Явно указать layout для всех форматов
        $this->setLayout('layout');

        $this->getContext()->getConfiguration()->loadHelpers('Sync', $this->getContext()->getModuleName());
        sfConfig::set('sf_escaping_method', 'ESC_XML');


        $this->form = new mySyncOutForm;
        $this->form->bind($request->getGetParameters());

        if ($this->form->isValid()) {

            $userId = $this->getUser()->getId();
            $query = $this->_getQuery($modelName, $this->form->getDatetimeRange(), $userId);

            // Vars
            $this->setVar('list',    $query->execute());
            $this->setVar('model',   $modelName, $noEscape = true);
            $this->setVar('columns', $this->_getColunmsToReturn($modelName), $noEscape = true);
            return sfView::SUCCESS;
        }

        $this->getResponse()->setStatusCode(400);
        return sfView::ERROR;
    }


    /**
     * Получить название модели доступной для синхронизации
     *
     * @param  string $syncModel
     * @return string
     */
    private function _getModelName($syncModel)
    {
        $models = array(
            'currency'  => 'Currency',
            'category'  => 'Category',
            'account'   => 'Account',
            'operation' => 'Operation',
        );

        if (isset($models[$syncModel])) {
            return $models[$syncModel];
        }
    }


    /**
     * Получить инициализированный запрос для выборки объектов для синхронизации
     *
     * @param  string          $modelName
     * @param  myDatetimeRange $range
     * @param  int             $userId
     * @return Doctrine_Query
     */
    private function _getQuery($modelName, myDatetimeRange $range, $userId)
    {
        $models = array(
            'Currency'  => 'mySyncOutCurrencyQuery',
            'Category'  => 'mySyncOutCategoryQuery',
            'Account'   => 'mySyncOutAccountQuery',
            'Operation' => 'mySyncOutOperationQuery',
        );

        if (isset($models[$modelName])) {
            $queryClass = $models[$modelName];
        } else {
            throw new InvalidArgumentException(__CLASS__.": Expected valid model name, got `{$modelName}`");
        }

        $q = new $queryClass($range, $userId);
        return $q->getQuery();
    }


    /**
     * Получить список свойств объекта, которые надо вернуть
     *
     * @param  string $modelName
     * @return array
     */
    public function _getColunmsToReturn($modelName)
    {
        $models = array(
            'Currency'  => array(
                'code',
                'symbol',
                'name',
                'rate'
            ),
            'Category'  => array(
                'parent_id',
                'system_id',
                'name',
                'type'
            ),
            'Account'   => array(
                'name',
                'description',
                'currency_id',
                'type_id'
            ),
            'Operation' => array(
                'account_id',
                'category_id',
                'amount',
                'type',
                'date',
                'accepted',
                'comment',
                'transfer_account_id',
                'transfer_amount',
            ),
        );

        return $models[$modelName];
    }

}
