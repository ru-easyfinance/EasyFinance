<?php
/**
 * Готовит js объект res.calendar.future
 */
class futureComponent extends sfComponent
{
    /**
     * Execute
     *
     * @param sfRequest $request A request object
     */
    public function execute($request)
    {
        $user = $this->getUser()->getUserRecord();
        $futureOperations = Doctrine::getTable('Operation')->queryFindWithFutureCalendarChains($user)->fetchArray();

        $this->setVar('futureOperations', $futureOperations, $noEscape = true);
    }

}
