<?php
/**
 * Тахометры: Агрегатор данных из моделей
 */
class myTahometers
{
    // пользователь
    private $user = null;

    private $dispatcher = null;

    private $myCurrencyExchange = null;

    // конфигурация тахометров
    private $tahometersConfig = array();

    // опытность пользователя - дней с первой операции
    private $userExpirience = null;

    // кеш конкретных запросов
    private $oneMonthProfit,
            $currentMonthExpense,
            $threeMonthProfit,
            $threeMonthExpense,
            $oneMonthRepayLoanExpense,
            $threeMonthsRepayLoanExpense,
            $oneMonthInterestOnLoanExpense = null;
    // плановые расходы на текущий месяц (бюджет)
    private $monthExpense = null;
    // баланс
    private $balance = null;


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

        // TODO: убить упоминания контекста
        $this->dispatcher = sfContext::getInstance()->getEventDispatcher();

        $this->tahometersConfig = $tahometerConfig;
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
     * Вернуть все тахометры в виде массива данных
     *
     * @return  array
     */
    public function toArray()
    {
        $tahometers = $this->createTahometers();

        $values = array();

        foreach ($tahometers as $tahometer) {
            $values[] = $tahometer->toArray();
        }

        return $values;
    }


    /**
     * Создать тахометр
     *
     * @param   string  $name
     * @return  myTahometer
     */
    protected function createTahometer($name)
    {
        if ($name !== myTahometer::NAME_TOTAL) {
            return new myTahometer($this->getConfiguration($name));
        }
    }


    /**
     * Инициализация тахометров, заполнение данными
     */
    protected function createTahometers()
    {
        $money  = $this->createTahometer(myTahometer::NAME_MONEY);
        $budget = $this->createTahometer(myTahometer::NAME_BUDGET);
        $loans  = $this->createTahometer(myTahometer::NAME_LOANS);
        $diff   = $this->createTahometer(myTahometer::NAME_DIFF);

        $money->setParams(
            $this->getBalance(),
            ($this->getThreeMonthsExpence() + $this->getThreeMonthsRepayLoanExpence()) / 3
        );

        $budget->setParams(
            $this->getCurrentMonthExpence(),
            $this->getMonthExpense()
        );

        $loans->setParams(
            $this->getOneMonthRepayLoanExpence() + $this->getOneMonthInterestOnLoanExpence(),
            $this->getOneMonthProfit()
        );

        $diff->setParams(
            $this->getThreeMonthsProfit(),
            $this->getThreeMonthsExpence() + $this->getThreeMonthsRepayLoanExpence()
        );

        $total = new myTotalTahometer(
            $this->getTotalTahometerValueFromTahometers(array($money, $budget, $loans, $diff)),
            $this->getConfiguration(myTahometer::NAME_TOTAL)
        );

        return array(
            $total,
            $money,
            $budget,
            $loans,
            $diff,
        );
    }


    /**
     * @return  float
     */
    protected function getTotalTahometerValueFromTahometers(array $tahometers)
    {
        $totalValue = 0.00;
        foreach ($tahometers as $tahometer) {
            $totalValue += $tahometer->getWeightedValue();
        }

        return $totalValue;
    }


    /**
     * Инстанс обменника валют
     *
     * @return  myCurrencyExchange
     */
    protected function getExchanger()
    {
        if (!$this->myCurrencyExchange) {
            $this->dispatcher->notifyUntil($event = new sfEvent($this, 'app.myCurrencyExchange', array()));
            $this->myCurrencyExchange = $event->getReturnValue();
        }

        return $this->myCurrencyExchange;
    }


    /**
     * Приводит суммы по счетам к 1ой валюте и суммирует
     *
     * @return  float
     */
    protected function calculateUserOperations(array $operations = array(), $absolute = true)
    {
        $ex = $this->getExchanger();
        $defaultCurrency = $this->getUser()->getCurrencyId();

        $total = 0.00;
        //todo: на симфони правильно работать не с суммами по счетам,
        //а с операциями, получая суммы из них с учетом всех тонкостей валют и отношений к счетам
        // все тонкости валют? хорошо бы их описать.
        // TODO: отрефакторить
        foreach ($operations as $operation) {
            $money = new myMoney($operation['money'], $operation['Account']['currency_id']);
            $total += $ex->convert($money, $defaultCurrency)->getAmount();
        }

        if ($absolute) {
            $total = abs($total);
        }

        return $total;
    }


    /**
     * Получение баланса
     *
     * @return  float
     */
    public function getBalance()
    {
        if (!$this->balance) {
            $result = $this->getOperationTable()
                ->getBalance($this->getUser());

            $this->balance = $this->calculateUserOperations($result, $absolute = false);
        }

        return $this->balance;
    }

    /**
     * Получение доходов
     */
    protected function getProfit($months = null)
    {
        $result = $this->getOperationTable()
            ->getProfit($this->getUser(), $months);

        $total = $this->calculateUserOperations($result);
        $total = $this->makeMoneyAccuracyByExpirience($total, $months);

        return $total;
    }


    /**
     * Получение расходов
     */
    protected function getExpence($months = null)
    {
        $result = $this->getOperationTable()
            ->getExpence($this->getUser(), $months);

        $total = $this->calculateUserOperations($result);
        $total = $this->makeMoneyAccuracyByExpirience($total, $months);

        return $total;
    }


    /**
     * Получение выплат за кредиты
     */
    protected function getRepayLoanExpence($months = null)
    {
        $result = $this->getOperationTable()
            ->getRepayLoanExpence($this->getUser(), $months);

        $total = $this->calculateUserOperations($result);
        $total = $this->makeMoneyAccuracyByExpirience($total, $months);

        return $total;
    }


    /**
     * Получение выплат-процентов по кредитам и займам
     */
    protected function getInterestOnLoanExpence($months = null)
    {
        $result = $this->getOperationTable()
            ->getInterestOnLoanExpence($this->getUser(), $months);

        $total = $this->calculateUserOperations($result);
        $total = $this->makeMoneyAccuracyByExpirience($total, $months);

        return $total;
    }


    /**
     * Получить доходы за 1 месяц
     */
    public function getOneMonthProfit()
    {
        if (!$this->oneMonthProfit) {
            $this->oneMonthProfit = $this->getProfit(1);
        }

        return $this->oneMonthProfit;
    }


    /**
     * Получить доходы за 3 месяц
     */
    public function getThreeMonthsProfit()
    {
        if (!$this->threeMonthProfit) {
            $this->threeMonthProfit = $this->getProfit(3);
        }

        return $this->threeMonthProfit;
    }


    /**
     * Расходы за текущий месяц
     */
    public function getCurrentMonthExpence()
    {
        if (!$this->currentMonthExpense) {
            $this->currentMonthExpense = $this->getExpence(0);
        }

        return $this->currentMonthExpense;
    }


    /**
     * Расходы за 3 месяца
     */
    public function getThreeMonthsExpence()
    {
        if (!$this->threeMonthExpense) {
            $this->threeMonthExpense = $this->getExpence(3);
        }

        return $this->threeMonthExpense;
    }


    /**
     * Выплаты по кредитам за месяц
     */
    public function getOneMonthRepayLoanExpence()
    {
        if (!$this->oneMonthRepayLoanExpense) {
            $this->oneMonthRepayLoanExpense = $this->getRepayLoanExpence(1);
        }
        return $this->oneMonthRepayLoanExpense;
    }


    /**
     * Выплаты по кредитам за 3 месяца
     */
    public function getThreeMonthsRepayLoanExpence()
    {
        if (!$this->threeMonthsRepayLoanExpense) {
            $this->threeMonthsRepayLoanExpense = $this->getRepayLoanExpence(3);
        }
        return $this->threeMonthsRepayLoanExpense;
    }


    /**
     * Выплаты процентов по кредитам за месяц
     */
    public function getOneMonthInterestOnLoanExpence()
    {
        if (!$this->oneMonthInterestOnLoanExpense) {
            $this->oneMonthInterestOnLoanExpense = $this->getInterestOnLoanExpence(1);
        }
        return $this->oneMonthInterestOnLoanExpense;
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
     * @param   string  $name
     * @return  array
     */
    protected function getConfiguration($name = null)
    {
        if ($name && array_key_exists($name, $this->tahometersConfig)) {
            return $this->tahometersConfig[$name];
        }

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
