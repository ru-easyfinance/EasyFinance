<?php

/**
 * Sync: получить набор объектов-счетов
 */
class syncInAccountAction extends sfAction
{
    /**
     * Execute
     */
    public function execute($request)
    {

        // Явно указать layout для всех форматов
        $this->setLayout('layout');
        // Явно указать шаблон
        $this->setTemplate('syncIn');

        if (0 !== strlen($rawXml = $request->getContent())) {
            $xml = simplexml_load_string($rawXml);

            // кол-во объектов - отсутствуют или >100
            $cnt = (int) count($xml->recordset[0]);
            $limit = sfConfig::get('app_records_sync_limit');

            if (($cnt <= 0) OR ($cnt > $limit)) {
                $this->getResponse()->setStatusCode(400);
                $this->setVar('errMessage', $cnt ?
                    "More than 'limit' ({$limit}) objects were sent" : 'No objects were sent'
                );
                return sfView::ERROR;
            }

            $cIds = array();
            foreach ($xml->xpath('//record/@id') as $tId) {
                $cIds[(string)$tId['id']] = (string)$tId['id'];
            }

            $accounts = Doctrine::getTable('Account')->createQuery('a')
                ->select("a.id")
                ->whereIn("a.id", $cIds)
                ->fetchArray();

            $accIds = array();
            foreach ($accounts as $account) {
                $accIds[] = $account['id'];
            }

            $results = array();
            foreach ($xml->recordset[0] as $record) {
                $data = array();
                if (in_array((string) $record['id'], $accIds)) {
                    $data['id'] = (string) $record['id'];
                }
                $data['user_id']     = (string) $record->user_id;
                $data['type_id']     = (string) $record->type_id;
                $data['currency_id'] = (string) $record->currency_id;
                $data['name']        = (string) $record->name;
                $data['description'] = (string) $record->description;
                $data['created_at']  = (string) $record->created_at;
                $data['updated_at']  = (string) $record->updated_at;
                $data['deleted_at']  = (string) $record->deleted_at;

                $form = new mySyncInAccountForm();
                $form->bind($data);

                if ($form->isValid()) {
                    $form->save();

                    $results[] = array(
                        'id' => $form->getObject()->getId(),
                        'cid' => $record['cid'],
                        'success' => 1,
                    );
                } else {
                    $results[] = array(
                        'id' => $record['id'],
                        'cid' => $record['cid'],
                        'success' => 0,
                    );
                }
            }

            $this->setVar('results', array_merge(array(), $results));

            return sfView::SUCCESS;
        }

        $this->getResponse()->setStatusCode(400);
        $this->setVar('errMessage', 'No data were sent');
        return sfView::ERROR;
    }

}
