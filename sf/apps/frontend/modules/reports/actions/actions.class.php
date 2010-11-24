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
        return $this->renderJsonSuccess('Ok');
    }
}
