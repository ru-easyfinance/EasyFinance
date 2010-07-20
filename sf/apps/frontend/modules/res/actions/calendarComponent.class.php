<?php
/**
 * Готовит js объект res.calendar.calendar
 */
class calendarComponent extends sfComponent
{
    /**
     * Execute
     *
     * @param sfRequest $request A request object
     */
    public function execute($request)
    {
        $user = $this->getUser()->getUserRecord();
        // Если интервал не задан, будем выводить операции с начала месяца до сегодня
        // TODO: уточнить эти умолчания
        if(empty($this->dateStart)) {
            $this->dateStart = date('Y-m-', time()) . '01';
        }
        if(empty($this->dateEnd)) {
            $this->dateEnd = date('Y-m-d', time());
        }
        $data = Doctrine::getTable('Operation')->queryFindWithCalendarChainsForPeriod(
              $user
            , DateTime::createFromFormat('Y-m-d', $this->dateStart)
            , DateTime::createFromFormat('Y-m-d', $this->dateEnd)
            )->fetchArray();

        $this->setVar('data', $data, $noEscape = true);
    }

}
