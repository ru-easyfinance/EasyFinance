<?php
/**
 * Тахометры: Агрегатор данных из моделей
 */
class myTahometers
{
    // пользователь
    private $user = null;

    // конфигурация тахометров
    private $tahometersConfig = array();

    // опытность пользователя - дней с первой операции
    private $userExpirience = null;
    // плановые расходы на текущий месяц
    private $monthExpense = null;


    /**
     * Конструктор
     *
     * @param   User    $user
     * @param   boolean $totalOnly   Считать только главный тахометр
     */
    public function __construct(User $user)
    {
        $this->user = $user;

        if (!sfContext::hasInstance()) {
            throw new sfException('Мы не знаем как парсить конфигурации');
        }
        include sfContext::getInstance()->getConfigCache()->checkConfig('config/tahometers.yml');

        $this->tahometersConfig = $tahometerConfig;

        $this->initialize();
    }


    /**
     * Получить пользователя, для которого считаем
     *
     * @return  User
     */
    protected function getUser()
    {
        return $this->user;
    }


    /**
     * Прокся: получить таблицу операций
     *
     * @return  OperationTable
     */
    protected function getOperationTable()
    {
        return Doctrine_Core::getTable('Operation');
    }


    /**
     * Выбрать данные
     */
    protected function initialize()
    {
        //
    }


    /**
     * Получить интервал в днях от сегодня до даты в прошлом/будущем
     *
     * @param   integer $number
     * @param   string  $type   см. strtotime модификаторы
     * @param   boolean $past   вперед или назад во времени
     * @return  integer
     */
    protected function getDaysInterval($number, $type = 'month', $past = true)
    {
        $today = strtotime(date('Y-m-d'));
        $modifier = sprintf("%s %s %d %s", date('Y-m-d'), ($past ? '-' : '+'), (int) $number, $type);

        return (int) ($today - strtotime($modifier)) / 86400;
    }


    /**
     * Отношение: дни к пользовательскому опыту
     *
     * @param   integer $days
     * @return  float
     */
    protected function getExpirienceCoefficient($days)
    {
        return $days / $this->getExpirience();
    }


    /**
     * Считаем сумму с учетом коэффициента стажа для экстраполяции значений,
     * если реальный стаж меньше используемого периода
     *
     * @param   float   $amount
     * @param   mixed   $months за сколько месяцев получаем показатель
     * @return  float
     */
    protected function makeMoneyAccuracyByExpirience($amount, $months = null)
    {
        // данные за текущий месяц с учетом показателя не корректируются
        // данные без месяцев не корректируются - это суммы на данный момент
        if (!$months) {
            return $amount;
        }

        $coefficient = $this->getExpirienceCoefficient($this->getDaysInterval($months));

        return ($coefficient > 1) ? $amount * $coefficient : $amount;
    }


    /**
     * Получить конфигурацию тахометров
     *
     * @see     myTahometerConfigHandler::execute
     * @return  array
     */
    protected function getConfiguration()
    {
        return $this->tahometersConfig;
    }


    /**
     * Получить планируемый расход пользователя на месяц
     *
     * @see     BudgetCategoryTable::countTotalExpense
     * @return  float
     */
    protected function getMonthExpense()
    {
        if (null === $this->monthExpense) {
            $this->monthExpense = Doctrine::getTable('BudgetCategory')
                                ->countTotalExpense($this->getUser()->getId());
        }

        return $this->monthExpense;
    }


    /**
     * Опытность пользователя
     *
     * @see     OperationTable::getExpirienceByUser
     * @return  integer
     */
    protected function getExpirience()
    {
        if (null === $this->userExpirience) {
            $this->userExpirience = $this->getOperationTable()
                                  ->getExpirienceByUser($this->getUser());
        }

        return $this->userExpirience;
    }

}
