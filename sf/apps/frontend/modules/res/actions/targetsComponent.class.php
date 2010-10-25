<?php
/**
 * Готовит js объекты res.popup_targets и res.user_targets
 */
class targetsComponent extends sfComponent
{

    /**
     * Execute
     *
     * @param sfRequest $request A request object
     */
    public function execute($request)
    {
        $user = $this->getUser()->getUserRecord();

        $targetCategories = Doctrine::getTable('Target')->getTargetCategories();

        $this->setVar('targetCategories', $targetCategories, $noEscape = true);

        $userTargets = Doctrine::getTable('Target')->getUserTargets($user);

        $this->setVar('userTargets', $userTargets, $noEscape = true);
    }

}
