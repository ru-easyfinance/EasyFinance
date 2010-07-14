<?php
/**
 * Готовит js объект res.overdue
 */
class overdueComponent extends sfComponent
{
    /**
     * Execute
     *
     * @param sfRequest $request A request object
     */
    public function execute($request)
    {
        $userId = $this->getUser()->getUserRecord()->getId();
        $data = Doctrine::getTable('Operation')->queryFindWithOverdueCalendarChains($userId)->fetchArray();

        $this->setVar('data', $data, $noEscape = true);
    }

}
