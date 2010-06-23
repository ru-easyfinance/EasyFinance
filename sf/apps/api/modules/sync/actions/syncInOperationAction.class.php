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
    protected function executeLogic(sfRequest $request)
    {
        $xml = $this->getXML();

        $data = array();
        foreach ($xml->recordset[0] as $record) {
            $data[] = $this->prepareArray($record);
        }

        // существующие записи, владельца не проверяем! так надо!
        $recordIds = $this->filterByXPath('//record/@id', 'id');
        $operations = Doctrine_Query::create()
            ->select("o.*")
            ->from("Operation o INDEXBY o.id")
            ->whereIn("o.id", $recordIds)
            ->execute();

        // существующие записи переводов
        $transferts = Doctrine_Query::create()
            ->select("o.*")
            ->from("Operation o INDEXBY o.transfer_id")
            ->whereIn("o.transfer_id", $recordIds)
            ->execute();

        // FK: выбор существующих счетов
        $accountTypes = $this->filterByXPath('//record/account_id');
        $accounts = Doctrine_Query::create()
            ->select("a.id, a.id type_id")
            ->from("Account a")
            ->whereIn("a.id", $accountTypes)
            ->andWhere("a.user_id = ?", $this->getUser()->getId())
            ->execute(array(), 'FetchPair');

        // FK: выбор существующих категорий
        $categoryIds = $this->filterByXPath('//record/category_id');
        $categories = Doctrine_Query::create()
            ->select("c.id, c.id type_id")
            ->from("Category c")
            ->whereIn("c.id", $categoryIds)
            ->andWhere("c.user_id = ?", $this->getUser()->getId())
            ->execute(array(), 'FetchPair');

        $modelName = $this->getModelName();
        $formName  = sprintf("mySyncIn%sForm", $modelName);
        $results   = array();
        foreach ($data as $record) {
            // не добавляем в коллекцию новых объектов, поэтому так:
            if ($operations->contains($record['id'])) {
                $myObject = $operations[(int) $record['id']];
            } else {
                $myObject = new $modelName();
            }

            if ($record['type'] == Operation::TYPE_TRANSFER) {
                $formName = sprintf("mySyncIn%sForm", $modelName . "Transfer");


            }
            $form = new $formName($myObject);

            $errors = array();

            // FK: счет существует?
            if (!in_array($record['account_id'], $accounts)) {
                $errors[] = "No such account";
            }

            // FK: категория существует?
            if (!in_array($record['category_id'], $categories) AND !empty($record['category_id'])) {
                $errors[] = "No such category";
            }

            // другой владелец, культурно посылаем (см.выше выбор счетов)
            if (!$myObject->isNew() && ((int) $myObject->getUserId() !== (int) $this->getUser()->getId())) {
                $errors[] = "Foreign operation";
            }

            // новому - установить владельца
            if ($myObject->isNew()) {
                $record['user_id'] = $this->getUser()->getId();
            }

            if (!$errors && $form->bindAndSave($record)) {
                if ($record['type'] == Operation::TYPE_TRANSFER) {
                    if ($transferts->contains($record['id'])) {
                        $myTransfertObject = $transferts[(int) $record['id']];
                    } else {
                        $myTransfertObject = new $modelName();
                    }

                    $real = $form->getObject();
                    $completeTransfert = array_merge($record, array(
                        'account_id' => $real->getTransfer(),
                        'amount'     => $record['transfer_amount'],
                        'transfer'   => $record['account_id'],
                        'transfer_id'=> $real->getId(),
                        'transfer_amount' => abs($real->getAmount()),
                    ));

                    $transfertForm = new mySyncInOperationTransferCompleteForm($myTransfertObject);

                    $transfertForm->bindAndSave($completeTransfert);
                }

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
            'account_id'  => (string) $record->account_id,
            'category_id' => (isset($record->category_id) ? (string) $record->category_id : null),
            'amount'      => (int)    $record->amount,
            'date'        => (string) $record->date,
            'type'        => (string) $record->type,
            'comment'     => (string) $record->comment,
            'transfer'    => (string) $record->transfer,
            'transfer_amount' => isset($record->transfer_amount) ? (int) $record->transfer_amount : null,
            'accepted'    => (string) $record->accepted,
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
