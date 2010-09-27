<?php
/**
 * Тахометры: Агрегатор данных из моделей
 */
class myTahometers
{
    // пользователь
    private $user = null;

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
     * Выбрать данные
     */
    protected function initialize()
    {
        //
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

}
