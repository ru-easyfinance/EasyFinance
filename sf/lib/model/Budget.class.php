<?php
/**
 * Бюджет за период
 */
class BudgetManager
{
    /**
     * Загружает список статей бюджета
     * @param User $user
     * @param string $startDate - дата начала текущего месяца
     * @param float $rate курс пользовательской валюты
     */
    public function load($user, $startDate)
    {
        //получим операции за этот месяц - для подсчета текущего бюджета,
        //и за предыдущие - для подсчета среднего показателя
        
        //средний показатель рассчитываем по трем предыдущим месяцам
        $meanRateMonthsAmount = 3;
        
        $beginDate = date_sub($startDate, new DateInterval("P" . $meanRateMonthsAmount . "M"));
        
        //дата конца - последнее число заданного месяца, т.е. "начало месяца + 1 месяц - 1 день"
        $endDate = date_sub(date_add($startDate, new DateInterval("P1M")), new DateInterval("P1D")); 

        //получим выборку операций за рассчитанный период
        $operations = new OperationCollection();
        $operations->FillForPeriod($beginDate, $endDate);
        
        //получим бюджет на месяц (коллекцию статей бюджета на заданный месяц)
        $budget = new Budget();
        
        $budget->Fill($startDate);
        
        //валюта нужна для подсчета сумм операций
        $mainCurrency = $user->getMainCurrency();        
        
        //TODO: всю логику подсчета можно вынести в отдельный калькулятор бюджета и тестить его уже модульно,
        //без привлечения базы, передавая готовые операции и статьи бюджета
        
        //по каждой операции определим ее вклад в соотв. статью бюджета или средний показатель
        foreach ($operations->getOperations() as $operation) {
            
            //получаем модуль вклада операции в бюджет:
            //получаем в основной валюте и без знака
            $signed = false;
            $operationContributionAmount = $operation->GetAmountForBudget($mainCurrency, $signed);
            
            //определяем, к какой категории относится операция, и по ней определяем статью бюджета
            //категорию берем не напрямую из связи операции, а вычисляем для корректного учета переводов
            //если категория не запланирована, создается и возвращается пустая
            $currentBudgetArticle = $budget->GetBudgetArticleByCategory(
                $operation->DefineCategory());
                
            //в зависимости от свойств операции учтем ее сумму в среднем показателе или статье бюджета:
            
            //операция - из предыдущих месяцев => только в средний показатель
            if($operation->getDate() < $budget->StartDate) 
                $currentBudgetArticle->Mean += $operationContributionAmount;

            //иначе учитываем операцию в текущем бюджете:
            //операцию не из календаря учтем как ad hoc              
            else if(!$operation->IsFromCalendar())
                $currentBudgetArticle->Adhoc += $operationContributionAmount;
                
            //подтвержденные и неподтвержденные учтем отдельно
            else if ($operation->IsAccepted())
                $currentBudgetArticle->CalendarAccepted += $operationContributionAmount;
            
            else
                $currentBudgetArticle->CalendarFuture += $operationContributionAmount;
        }
        
        //возвращаем уже заполненные категории        
        return $monthBudget;
    }
}
?>