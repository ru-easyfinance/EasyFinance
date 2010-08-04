<?php
/**
 * Бюджет
 */
class budgetActions extends sfActions
{
    public function executeIndex(sfWebRequest $request)
    {
        $user = $this->getUser();
        $user->getId();
        return sfView::SUCCESS;
    }
}
?>