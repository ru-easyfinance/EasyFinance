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

        $syncModel = $request->getParameter('model');
        $this->forward404Unless($table = $this->_getTable($syncModel));

        // Явно указать layout для всех форматов
        $this->setLayout('layout');

        $this->form = new mySyncOutForm;
        $this->form->bind($request->getGetParameters());

        if ($this->form->isValid()) {
            $userId = $request->getParameter('user_id');
            // Vars
            $this->setVar('list',    $table->queryFindModifiedForSync($this->form->getDatetimeRange(), $userId)->fetchArray());
            $this->setVar('model',   $table->getOption('name'), $noEscape = true);
            $this->setVar('columns', $this->_getColunmsToReturn($syncModel), $noEscape = true);
            return;
        }

        $this->getResponse()->setStatusCode(400);
        return sfView::ERROR;
    }


    /**
     * Получить таблицу по коду модели
     *
     * @param  string $syncModel   - код модели, см. routing.yml
     * @return null|Doctrine_Table
     */
    private function _getTable($syncModel)
    {
        $models = array(
            'account'   => 'Account',
            'operation' => 'Operation',
            'currency'  => 'Currency',
        );
        if (isset($models[$syncModel])) {
            return Doctrine::getTable($models[$syncModel]);
        }
    }


    /**
     * Получить список свойств объекта, которые надо вернуть
     *
     * @param  string $syncModel   - код модели, см. routing.yml
     * @return array
     */
    public function _getColunmsToReturn($syncModel)
    {
        $models = array(
            'currency'  => array('code', 'symbol', 'name', 'rate', 'created_at', 'updated_at'),
            'account'   => array('name', 'description', 'currency_id', 'type_id'),
            'operation' => array('account_id', 'category_id', 'amount', 'comment','dt_create', 'dt_update'),
        );
        return $models[$syncModel];
    }

}
