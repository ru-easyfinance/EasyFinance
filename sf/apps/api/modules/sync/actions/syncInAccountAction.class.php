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

        $rawXml = $request->getRawPostBody();

        if (0 !== strlen($rawXml)) {
            return sfView::SUCCESS;
        }

        $this->getResponse()->setStatusCode(400);
        return sfView::ERROR;
    }

}
