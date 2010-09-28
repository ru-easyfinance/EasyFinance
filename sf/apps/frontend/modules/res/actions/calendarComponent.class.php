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
        // Если интервал не задан,
        // будем выводить операции с начала месяца до сегодня
        // TODO: уточнить эти умолчания
        if (empty($this->dateStart)) {
            $this->dateStart = date('Y-m-', time()) . '01';
        }
        if (empty($this->dateEnd)) {
            $this->dateEnd = date('Y-m-d', time());
        }

        list($y, $m, $d) = explode('-', $this->dateStart);
        $dateStart = new DateTime();
        $dateStart->setDate($y, $m, $d);

        list($y, $m, $d) = explode('-', $this->dateEnd);
        $dateEnd = new DateTime();
        $dateEnd->setDate($y, $m, $d);

        $calendarOperations = Doctrine::getTable('Operation')
            ->queryFindWithCalendarChainsForPeriod(
                $user, $dateStart, $dateEnd
            )->fetchArray();

        $this->setVar(
            'calendarOperations', $calendarOperations, $noEscape = true
        );
    }

}
