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

        $results = array();
        // foreach

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
        $data['created_at']  = (string) $record->created_at;
        $data['updated_at']  = (string) $record->updated_at;
        $data['deleted_at']  = ((string) $record['deleted']) ? (string) $record->updated_at : null;
        return $data;
    }

}
