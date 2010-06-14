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

        $results = array();
        foreach ($data as $record) {

            $form = new mySyncInCategoryForm();

            $errors = array();

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
        return 'category';
    }

}
