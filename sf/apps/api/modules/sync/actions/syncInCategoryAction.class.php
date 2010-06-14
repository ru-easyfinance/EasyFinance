<?php
require_once(dirname(__FILE__).'/../lib/myBaseSyncInAction.php');

/**
 * Sync: получить набор объектов-категорий
 */
class syncInCategoryAction extends myBaseSyncInAction
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
        $categories = Doctrine_Query::create()
            ->select("c.*")
            ->from("Category c INDEXBY c.id")
            ->whereIn("c.id", $recordIds)
            ->execute();

        $recordSystemCategories = $this->searchInXML($xml->xpath('//record/system_id'));
        $systemCategories = Doctrine_Query::create()
            ->select("s.id, s.id as system_id")
            ->from("SystemCategory s")
            ->whereIn("s.id", $recordSystemCategories)
            ->execute(array(), 'FetchPair');

        $parentIds = $this->searchInXML($xml->xpath('//record/parent_id'));
        $parents = Doctrine_Query::create()
            ->select("c.id, c.id parent_id")
            ->from("Category c")
            ->whereIn("c.id", $parentIds)
            ->execute(array(), 'FetchPair');


        $modelName = $this->getModelName();
        $formName  = sprintf("mySyncIn%sForm", $modelName);
        $results   = array();
        foreach ($data as $record) {
            // не добавляем в коллекцию новых объектов, поэтому так:
            if ($categories->contains($record['id'])) {
                $myObject = $categories[(int) $record['id']];
            } else {
                $myObject = new $modelName();
            }

            $form = new $formName($myObject);

            $errors = array();

            // системная категория
            if (!in_array($record['system_id'], $systemCategories)) {
                $errors[] = "No such root (system) category";
            }

            // родительская категория
            if ($record['parent_id'] != 0 && !in_array($record['parent_id'], $parents)) {
                $errors[] = "No such parent category";
            }

            // другой владелец, культурно посылаем (см.выше выбор счетов)
            if (!$myObject->isNew() && ($myObject->getUserId() !== $userId)) {
                $errors[] = "Foreign account";
            }

            // новому - установить владельца
            if ($myObject->isNew()) {
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
        return array(
            'id'         => (string) $record['id'],
            'cid'        => (string) $record['cid'],
            'system_id'  => (string) $record->system_id,
            'parent_id'  => (string) $record->parent_id,
            'name'       => (string) $record->name,
            'type'       => (string) $record->type,
            'custom'     => (string) $record->custom,
            'created_at' => (string) $record->created_at,
            'updated_at' => (string) $record->updated_at,
            'deleted_at' => null,
            //'deleted_at' => ((string) $record['deleted']) ? (string) $record->updated_at : null,
        );
    }


    /**
     * @return string
     */
    protected function getModelName()
    {
        return 'Category';
    }

}
