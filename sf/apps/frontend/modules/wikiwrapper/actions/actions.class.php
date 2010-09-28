<?php

/**
 * WikiWrapper actions.
 *
 * @package    EasyFinance
 * @subpackage WikiWrapper
 * @author     EasyFinance
 */
class wikiwrapperActions extends sfActions
{
    /**
     * Показываем вики в обвязке EasyFinance
     *
     * @param sfRequest $request A request object
     */
    public function executeIndex(sfWebRequest $request)
    {
        $wikiURL = $request->getParameter('wikiURL');
        $querySrting = http_build_query($request->getGetParameters());

        $this->setVar('wikiURL', "{$wikiURL}?{$querySrting}");
        $this->setLayout('layoutClear');
        return sfView::SUCCESS;
    }
}