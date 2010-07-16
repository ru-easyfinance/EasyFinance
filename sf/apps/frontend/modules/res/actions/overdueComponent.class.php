<?php
/**
 * Готовит js объект res.calendar.overdue
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
        $user = $this->getUser()->getUserRecord();
        $data = Doctrine::getTable('Operation')->queryFindWithOverdueCalendarChains($user)->fetchArray();

        $this->setVar('data', $data, $noEscape = true);
    }

}
