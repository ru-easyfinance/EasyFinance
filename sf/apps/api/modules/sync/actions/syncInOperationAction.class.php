<?php
require_once(dirname(__FILE__).'/../lib/myBaseSyncInAction.php');

/**
 * Sync: получить набор объектов-операций
 */
class syncInOperationAction extends myBaseSyncInAction
{
    /**
     * Execute
     */
    public function execute($request)
    {
    }


    /**
     * Делает из объекта SimpleXML массив
     *
     * @see    myBaseSyncInAction
     * @param  SimpleXMLElement $record
     * @return array
     */
    protected function prepareArray(SimpleXMLElement $record)
    {
        return array(
            'id'          => (string) $record['id'],
            'cid'         => (string) $record['cid'],
            'account_id'  => (string) $record->account_id,
            'category_id' => (string) $record->category_id,
            'amount'      => (string) $record->amount,
            'date'        => (string) $record->date,
            'time'        => (string) $record->time,
            'type'        => (string) $record->type,
            'comment'     => (string) $record->comment,
            'created_at'  => (string) $record->created_at,
            'updated_at'  => (string) $record->updated_at,
            'deleted_at'  => (isset($record['deleted']) ? (string) $record->updated_at : null),
        );
    }


    /**
     * @return string
     */
    protected function getModelName()
    {
        return 'Operation';
    }

}
