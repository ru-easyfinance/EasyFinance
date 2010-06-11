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
        // $userId = $this->getUser()->getId();
        if (null === ($userId = $request->getParameter('user_id'))) {
            $this->getResponse()->setHttpHeader('WWW_Authenticate', "Authentification required");
            return $this->raiseError("Authentification required", 0, 401);
        }

        if (0 === strlen($rawXml = $request->getContent())) {
            return $this->raiseError("Expected XML data");
        }

        $xml = simplexml_load_string($rawXml);

        $count = (int) count($xml->recordset[0]);
        $limit = sfConfig::get('app_records_sync_limit', 100);

        if ($count <= 0) {
            return $this->raiseError("Expected at least one record");
        } elseif ($count > $limit) {
            return $this->raiseError("More than 'limit' ({$limit}) objects sent, {$count}");
        }

        $data = array();
        foreach ($xml->recordset[0] as $record) {
            $data[] = $this->prepareArray($record);
        }

        // существующие записи, владельца не проверяем! так надо!
        $recordIds = $this->searchInXML($xml->xpath('//record/@id'), 'id');
        $accounts = Doctrine_Query::create()
            ->select("a.*")
            ->from("Account a INDEXBY a.id")
            ->whereIn("a.id", $recordIds)
            ->execute();

        $recordTypes = $this->searchInXML($xml->xpath('//record/type_id'));
        $types = Doctrine_Query::create()
            ->select("t.account_type_id id, t.account_type_id type_id")
            ->from("AccountType t")
            ->whereIn("t.account_type_id", $recordTypes)
            ->execute(array(), 'FetchPair');

        $recordCurrencies = $this->searchInXML($xml->xpath('//record/currency_id'));
        $currencies = Doctrine_Query::create()
            ->select("c.id, c.id")
            ->from("Currency c")
            ->whereIn("c.id", $recordCurrencies)
            ->execute(array(), 'FetchPair');

        $results = array();
        foreach ($data as $record) {
            // не добавляем в коллекцию новых объектов, поэтому так:
            if ($accounts->contains($record['id'])) {
                $account = $accounts[(int) $record['id']];
            } else {
                $account = new Account();
            }

            $form = new mySyncInAccountForm($account);

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
            if (!$account->isNew() && ($account->getUserId() !== $userId)) {
                $errors[] = "Foreign account";
            }

            // новому счету - установить владельца
            if ($account->isNew()) {
                $record['user_id'] = $userId;
            }

            if (!$errors && $form->bindAndSave($record)) {
                $results[] = array(
                    'id'      => $form->getObject()->getId(),
                    'cid'     => (string) $record['cid'],
                    'success' => 1,
                );
            } else {
                $message = (strlen($form->getErrorSchema())
                    ? $form->getErrorSchema() . " "
                    : "[Invalid.] " . implode(" [Invalid.] ", $errors)
                );
                $results[] = array(
                    'id'      => $record['id'],
                    'cid'     => (string) $record['cid'],
                    'success' => 0,
                    'message' => $message,
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
        $data = array();
        $data['id']          = (string) $record['id'];
        $data['cid']         = (string) $record['cid'];
        $data['type_id']     = (string) $record->type_id;
        $data['currency_id'] = (string) $record->currency_id;
        $data['name']        = (string) $record->name;
        $data['description'] = (string) $record->description;
        $data['created_at']  = (string) $record->created_at;
        $data['updated_at']  = (string) $record->updated_at;
        $data['deleted_at']  = (string) $record->deleted_at;
        return $data;
    }


    /**
     * @return string
     */
    protected function getModelName()
    {
        return 'account';
    }

}
