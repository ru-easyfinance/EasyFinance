<?php
/**
 * Бюджет за период
 */
class BudgetManager
{
    /**
     * Загружает список статей бюджета
     * @param User $user
     * @param DateTime $startDate - дата начала текущего месяца
     * @throws myBudgetWrongStartDateException
     */
    public function load(User $user, DateTime $startDate)
    {
        if ($startDate->format('d') !== '01') {
            throw new myBudgetWrongStartDateException(
                sprintf(
                    'Дата должна быть первым числом месяца, получена %s',
                    $startDate->format('Y-m-d')
                )
            );
        }

        //получим операции за этот месяц - для подсчета текущего бюджета,
        //и за предыдущие - для подсчета среднего показателя

        //средний показатель рассчитываем по трем предыдущим месяцам
        $meanRateMonthsAmount = 3;

        $beginDate = date_sub($startDate, new DateInterval("P" . $meanRateMonthsAmount . "M"));

        //дата конца - последнее число заданного месяца, т.е. "начало месяца + 1 месяц - 1 день"
        $endDate = date_sub(date_add($startDate, new DateInterval("P1M")), new DateInterval("P1D"));

        //получим выборку операций за рассчитанный период
        $operations = new OperationCollection();
        $operations->fillForPeriod($beginDate, $endDate);

        //получим бюджет на месяц (коллекцию статей бюджета на заданный месяц)
        $budget = new Budget();

        $budget->fill($startDate);

        //валюта нужна для подсчета сумм операций
        $mainCurrency = $user->getCurrency();

        //TODO: всю логику подсчета можно вынести в отдельный калькулятор бюджета и тестить его уже модульно,
        //без привлечения базы, передавая готовые операции и статьи бюджета

        //по каждой операции определим ее вклад в соотв. статью бюджета или средний показатель
        foreach ($operations->getOperations() as $operation) {

            //получаем модуль вклада операции в бюджет:
            //получаем в основной валюте и без знака
            $signed = false;
            $operationContributionAmount = $operation->getAmountForBudget($mainCurrency, $signed);

            //определяем, к какой категории относится операция, и по ней определяем статью бюджета
            //категорию берем не напрямую из связи операции, а вычисляем для корректного учета переводов
            //если категория не запланирована, создается и возвращается пустая
            $currentBudgetArticle = $budget->getBudgetArticleByCategory(
                $operation->getCategory()
            );

            //в зависимости от свойств операции учтем ее сумму в среднем показателе или статье бюджета:

            //операция - из предыдущих месяцев => только в средний показатель
            if($operation->getDate() < $budget->getStartDate())
                $currentBudgetArticle->mean += $operationContributionAmount;

            //иначе учитываем операцию в текущем бюджете:
            //операцию не из календаря учтем как ad hoc
            else if (!$operation->isFromCalendar())
                $currentBudgetArticle->adhoc += $operationContributionAmount;

            //подтвержденные и неподтвержденные учтем отдельно
            else if ($operation->isAccepted())
                $currentBudgetArticle->calendarAccepted += $operationContributionAmount;

            else
                $currentBudgetArticle->calendarFuture += $operationContributionAmount;
        }

        //возвращаем уже заполненные категории
        return $monthBudget;
    }
}

class myBudgetWrongStartDateException extends Exception {}
?>