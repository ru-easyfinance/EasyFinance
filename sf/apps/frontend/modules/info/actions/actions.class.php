<?php
/**
 * info actions.
 *
 * @package    EasyFinance
 * @subpackage info
 * @author     EasyFinance
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class infoActions extends sfActions
{
    /**
     * Executes index action
     *
     * @param sfRequest $request A request object
     */
    public function executeIndex(sfWebRequest $request)
    {
        return sfView::SUCCESS;
    }
}
