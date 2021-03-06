<?php

/**
 * reports actions.
 *
 * @package    EasyFinance
 * @subpackage reports
 * @author     Anton Minin <anton.a.minin@gmail.com>
 */
class reportsActions extends myBaseFrontendJsonActions
{
    /**
     * Executes matrix action
     *
     * @param sfRequest $request A request object
     */
    public function executeMatrix(sfWebRequest $request)
    {
        $dateFrom = $request->getGetParameter('dateFrom', date('Y-m-01'));
        $dateTo   = $request->getGetParameter('dateTo',   date('Y-m-01'));
        $currency = $request->getGetParameter('currency', 1);
        $account  = $request->getGetParameter('account',  null);
        $type     = $request->getGetParameter('type',     null);

        $dateFrom = new DateTime(preg_replace("/(\d{2}).(\d{2}).(\d{4})/", "$3-$2-$1", $dateFrom));
        $dateTo   = new DateTime(preg_replace("/(\d{2}).(\d{2}).(\d{4})/", "$3-$2-$1", $dateTo));
        $currency = Doctrine::getTable('Currency')->findOneById($currency);
        $user     = $this->getUser()->getUserRecord();

        $account  = ($account) ?
            Doctrine::getTable('Account')->findOneById($account) :
            null ;

        $report = new myReportMatrix($currency);
        $report->buildReport($user, $account, $dateFrom, $dateTo, $type);

        $result = array(
            'headerLeft' => $report->getHeaderLeft(),
            'headerTop'  => $report->getHeaderTop(),
            'matrix'     => $report->getMatrix(),
        );

        return $this->renderJson($result);
    }
}
