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
    protected function executeLogic(sfRequest $request)
    {
        $xml = $this->getXML();

        $data = array();
        foreach ($xml->recordset[0] as $record) {
            $data[] = $this->prepareArray($record);
        }

        // существующие записи, владельца не проверяем! так надо!
        $recordIds = $this->filterByXPath('//record/@id', 'id');
        $categories = Doctrine_Query::create()
            ->select("c.*")
            ->from("Category c INDEXBY c.id")
            ->whereIn("c.id", $recordIds)
            ->execute();

        $recordSystemCategories = $this->filterByXPath('//record/system_id');
        $systemCategories = Doctrine_Query::create()
            ->select("s.id, s.id as system_id")
            ->from("SystemCategory s")
            ->whereIn("s.id", $recordSystemCategories)
            ->execute(array(), 'FetchPair');

        $parentIds = $this->filterByXPath('//record/parent_id');
        $parents = Doctrine_Query::create()
            ->select("c.id, c.id parent_id")
            ->from("Category c")
            ->whereIn("c.id", $parentIds)
            ->andWhere("c.user_id = ?", $this->getUser()->getId())
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
            if (!$myObject->isNew() && ((int) $myObject->getUserId() !== (int) $this->getUser()->getId())) {
                $errors[] = "Foreign category";
            }

            // новому - установить владельца
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
            'id'         => (string) $record['id'],
            'cid'        => (string) $record['cid'],
            'system_id'  => (string) $record->system_id,
            'parent_id'  => (string) $record->parent_id,
            'name'       => (string) $record->name,
            'type'       => (string) $record->type,
            'custom'     => (string) $record->custom,
            'created_at' => (string) $record->created_at,
            'updated_at' => (string) $record->updated_at,
            'deleted_at' => (isset($record['deleted']) ? (string) $record->updated_at : null),
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
