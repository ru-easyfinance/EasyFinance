<?php
require_once(dirname(__FILE__).'/../lib/myBaseSyncInAction.php');

/**
 * Sync: получить набор объектов-счетов
 */
class syncInAccountAction extends myBaseSyncInAction
{
    /**
     * Execute
     */
    public function execute($request)
    {
        try {
            $this->prepareExecute($request);
        } catch (sfStopException $e) {
            return sfView::ERROR;
        }

        $xml = $this->getXML();

        $data = array();
        foreach ($this->getXML()->recordset[0] as $record) {
            $data[] = $this->prepareArray($record);
        }

        // существующие записи, владельца не проверяем! так надо!
        $recordIds = $this->filterByXPath('//record/@id', 'id');
        $accounts = Doctrine_Query::create()
            ->select("a.*")
            ->from("Account a INDEXBY a.id")
            ->whereIn("a.id", $recordIds)
            ->execute();

        $recordTypes = $this->filterByXPath('//record/type_id');
        $types = Doctrine_Query::create()
            ->select("t.account_type_id id, t.account_type_id type_id")
            ->from("AccountType t")
            ->whereIn("t.account_type_id", $recordTypes)
            ->execute(array(), 'FetchPair');

        $recordCurrencies = $this->filterByXPath('//record/currency_id');
        $currencies = Doctrine_Query::create()
            ->select("c.id, c.id")
            ->from("Currency c")
            ->whereIn("c.id", $recordCurrencies)
            ->execute(array(), 'FetchPair');


        $modelName = $this->getModelName();
        $formName  = sprintf("mySyncIn%sForm", $modelName);
        $results   = array();
        foreach ($data as $record) {
            // не добавляем в коллекцию новых объектов, поэтому так:
            if ($accounts->contains($record['id'])) {
                $myObject = $accounts[(int) $record['id']];
            } else {
                $myObject = new $modelName();
            }

            $form = new $formName($myObject);

            $errors = array();

            // тип счета?
            if (!in_array($record['type_id'], $types)) {
                $errors[] = "No such account type";
            }

            // валюта?
            if (!in_array($record['currency_id'], $currencies)) {
                $errors[] = "No such currency";
            }

            // у счета другой владелец, культурно посылаем (см.выше выбор счетов)
            if (!$myObject->isNew() && ((int) $myObject->getUserId() !== (int) $this->getUser()->getId())) {
                $errors[] = "Foreign account";
            }

            // новому счету - установить владельца
            if ($myObject->isNew()) {
                $record['user_id'] = $this->getUser()->getId();
            }

            if (!$errors && $form->bindAndSave($record)) {
                $results[] = array(
                    'id'      => $form->getObject()->getId(),
                    'cid'     => (string) $record['cid'],
                    'success' => 1,
                );
            } else {
                $results[] = array(
                    'id'      => $record['id'],
                    'cid'     => (string) $record['cid'],
                    'success' => 0,
                    'message' => $this->formatErrorMessage($form, $errors),
                );
            }
        }

        $this->setVar('results', $results, $noEscape = false);

        return sfView::SUCCESS;
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
            'type_id'     => (string) $record->type_id,
            'currency_id' => (string) $record->currency_id,
            'name'        => (string) $record->name,
            'description' => (string) $record->description,
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
        return 'Account';
    }

}
