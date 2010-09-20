<?php

/**
 * Расчет тахометров
 */
class Info_Model
{
    public function __construct(oldUser $user = null)
    {
    }

    /**
     * Возвращает информацию для тахометров в виде массива с пояснениями
     * @return array mixed
     */
    public function get_data()
    {
        $this->init();
        Tahometer::init();

        //получим исходные данные для расчетов
        $this->GetBaseData();

        //рассчитаем основные тахометры
        $this->CalculateBaseTahometers();
        //рассчитаем итоговый тахометр
        $this->CalculateTotalTahometer();

        //запихнем значения тахометров в результат
        $result = array();
        $result[] = $this->totalTahometer->getResult();
        foreach ($this->tahometersByKeywords as $keyword => $tahometer)
        {
            $result[] = $tahometer->getResult();
        }

        return $result;
    }

    private function db()
    {
        return Core::getInstance()->db;
    }

    private function user()
    {
        return Core::getInstance()->user;
    }

    private $_currencyExchagerContainer;

    private function getCurrencyExchanger()
    {
        if(!isset($this->_currencyExchagerContainer))
            $this->_currencyExchagerContainer = sfConfig::get('ex');

        return $this->_currencyExchagerContainer;
    }

    /*
    * ключевые слова и названия тахометров
    * упорядочены для использования в возвращаемом массиве рассчитанных значений
    */
    private $_tahometersByKeywords;

    private function CalculateTotalTahometer()
    {
        //значение итогового тахометра - взвешенная сумма значений остальных
        $totalValue = 0;
        foreach ($this->tahometersByKeywords as $keyword => $tahometer)
        {
             $totalValue = $totalValue + $tahometer->getWeightedValue();
        }
        $this->totalTahometer->SetTotalValue($totalValue);
    }

    //исходные данные для расчета

    //доходы за месяц
    private $_oneMonthProfit;

    //расходы за текущий месяц
    private $_currentMonthDrain;

    //выплаты по долгам за месяц
    private $_oneMonthCreditPayments;

    //плановые расходы на текущий месяц
    private $_currentMonthBudget;

    //текущий остаток доступных денег
    private $_currentRealMoneyBalance;

    //доходы за 3 месяца
    private $_threeMonthProfit;

    //расходы за 3 месяца
    private $_threeMonthDrain;

    //рассчитываем основные тахометры, уже получив данные
    private function CalculateBaseTahometers()
    {
        //деньги
        $this->tahometersByKeywords[Tahometer::$MONEY_KEYWORD]->SetBaseValue(
                $this->_currentRealMoneyBalance,
                $this->_threeMonthDrain / 3);
        
        $this->tahometersByKeywords[Tahometer::$BUDGET_KEYWORD]->SetBaseValue(
                $this->_currentMonthDrain,
                $this->_currentMonthBudget);
                    
        $this->tahometersByKeywords[Tahometer::$LOANS_KEYWORD]->SetBaseValue(
                $this->_oneMonthCreditPayments,
                $this->_oneMonthProfit);
        
        
        $this->tahometersByKeywords[Tahometer::$DIFF_KEYWORD]->SetBaseValue(
                $this->_threeMonthProfit,
                $this->_threeMonthDrain);
    }

    /*
    *типы запросов
    */
    private static $PROFIT_QUERY = 'PROFIT';
    private static $DRAIN_QUERY = 'DRAIN';
    
    //выплаты тела кредита - переводы на кредит
    private static $CREDIT_BODY_QUERY = 'CREDIT_BODY';
    
    //выплаты процентов - расходы по категории "Проценты по кредитам и займам"
    private static $CREDIT_PERCENT_QUERY = 'CREDIT_PERCENT';
    
    private static $BALANCE_QUERY = 'BALANCE';


    /*
    * получение исходных данных
    */
    private function GetBaseData()
    {
        //получаем каждый из показателей; показатели, получаемые как сумма за определенный период,
        //корректируем с учетом стажа в системе: если получаем за 3 месяца, а в системе мы - 1,5 месяца, нужный показатель умножить на 2
        //данные за текущий месяц с учетом показателя не корректируются
        
        //доходы за месяц
        $this->_oneMonthProfit = $this->GetOperationsSum(
            self::$PROFIT_QUERY, 1);

        //расходы за текущий месяц
        $this->_currentMonthDrain = $this->GetOperationsSum(
            self::$DRAIN_QUERY, 0);

        //доходы за 3 месяца
        $this->_threeMonthProfit = $this->GetOperationsSum(
            self::$PROFIT_QUERY, 3);

        //расходы за 3 месяца
        //это все траты и долги, т.е. все расходы, включая категорию "Проценты по кредитам", 
        // и переводы на долговые счета
        $this->_threeMonthDrain = $this->GetOperationsSum(
            self::$DRAIN_QUERY, 3) + 
            $this->GetOperationsSum(self::$CREDIT_BODY_QUERY, 3);

        //текущий остаток доступных денег - сумма всех операций за все время, включая начальные остатки
        //по денежным счетам и кредитным картам с положительным остатком
        $this->_currentRealMoneyBalance = $this->GetOperationsSum(
            self::$BALANCE_QUERY, NULL);

        //выплаты по долгам за месяц
        //пока считаем только как переводы на долговые счета
        //затем добавятся расходы по соотв. спец. категориям
        $this->_oneMonthCreditPayments = $this->GetOperationsSum(
            self::$CREDIT_BODY_QUERY, 1) + 
            $this->GetOperationsSum(self::$CREDIT_PERCENT_QUERY, 1);

        //плановые расходы на текущий месяц
        $this->_currentMonthBudget = $this->GetDrainBudget();
    }

    /*
    * Получение данных по заданному запросу с учетом стажа в системе:
    *
    * $months - диапазон времени в месяцах, за который получаем данные: если стаж в системе меньше него, применяется коэффициент стажа
    *
    */
    private function GetOperationsSum($queryType, $months)
    {
        $query = $this->GetFactOperationsQuery($queryType, $months);
        
        //Получим суммы, сгруппированные по счетам
        $sumsByAccounts = $this->db()->select($query);

        $resultSum = 0;

        //сложим вместе все суммы по счетам, конвертируя в нужную валюту
        //todo: на симфони правильно работать не с суммами по счетам,
        //а с операциями, получая суммы из них с учетом всех тонкостей валют и отношений к счетам
        if(isset($sumsByAccounts))
        {
            $baseCurrency = $this->user()->getUserProps('user_currency_default');

            foreach($sumsByAccounts as $sumByAccount)
            {
                $money = new myMoney($sumByAccount[Tahometer::$MONEY_KEYWORD], $sumByAccount['currency_id']);
                $accountResult = $this->getCurrencyExchanger()->convert($money, $baseCurrency)->getAmount();

                $resultSum = $resultSum + $accountResult;
            }
        }

        //если получаем не баланс а относительную величину со строго определенным знаком, возьмем ее модуль
        //баланс же может быть любого знака
        if($queryType <> self::$BALANCE_QUERY)
            $resultSum = abs($resultSum);

        //Скорректируем сумму с учетом стажа в системе:
        //корректируем всегда, если задано условие на диапазон операций
        if(isset($months))
        {
            $resultSum = $this->getSumWithExperienceCoeff($resultSum, $months);
        }

        return $resultSum;
    }

    ///запрос для получения данных
    //в него подставляются значения типа операций и интервала, за который нужно взять операции
    private function GetFactOperationsQuery($queryType, $months)
    {
        //тип операций для отбора
        $operationType = null;

        //добавочные условия

        //признаки использования счетов-отправителей
        $useSenders = true;

        $additionalHaving = null;
        $additionalWhere = null;
        //доп. условия зависят от типа запроса
        switch($queryType)
        {
            case self::$PROFIT_QUERY:
                //нужны только доходные, без начальных остатков
                $operationType = '1';
                break;

            case self::$DRAIN_QUERY:
                //нужны только расходные, без начальных остатков
                //переводы на долговые счета не берем
                $operationType = '0';
                break;

            //долги - переводы на долговые счета
            case self::$CREDIT_BODY_QUERY:
                //нужны только 1)переводы 2)на 3)долговые счета - займы, кредиты и кредитки
                $operationType = '2';
                $useSenders = false;
                $additionalHaving = "type_id IN (7, 8, 9)";
                break;

            //долги - проценты
            case self::$CREDIT_PERCENT_QUERY:
                //нужны только расходы
                $operationType = '0';
                //категорий с системной категорией "Проценты по кредитам и займам" может быть много, поэтому выбираем через IN
                $additionalWhere = "op.cat_id IN (SELECT cat.cat_id FROM category cat" . 
                	" WHERE cat.system_category_id = 15 AND cat.user_id = ".(int)$this->user()->getId(). ")";
                break;

                case self::$BALANCE_QUERY:
                //для баланса "живых" денег берем все операции и начальные остатки
                //берем только денежные счета и кредитки с положительным балансом
                $operationType = '0,1,2,3';
                $additionalHaving = "type_id IN (1,2,5,15,16) OR (type_id = 8 AND money > 0)";
                break;
            }

        $query = $this->getQueryByConditions($operationType, $months, $useSenders, $additionalWhere, $additionalHaving);
      
        return $query;
    }

    //формирование строки запроса по условиям
    private function getQueryByConditions($operationType, $months, $useSenders, $additionalWhere, $additionalHaving)
    {
        //задаем все подготовленные условия: типы операций, начальный остаток, интервал, дополнительные условия
        //выбираем с привязкой к счетам
        //всегда выбираем подтвержденные операции
        //операции перевода входят в разные счета с разным знаком

        //если расход или 'перевод с' - минус
        //иначе (доход или 'перевод на') - плюс
        $query = "
            SELECT
                SUM(CASE 
                        WHEN op.account_id = acc.account_id THEN op.money
                        WHEN IFNULL(op.transfer_amount, 0) = 0 THEN ABS(op.money)
                        ELSE op.transfer_amount 
                    END
                ) AS money,
                acc.account_type_id AS type_id, acc.account_currency_id AS currency_id
            FROM operation op INNER JOIN accounts acc
            ON acc.user_id = ".(int)$this->user()->getId()." AND ( ";

        if($useSenders) {
            $query .= "op.account_id = acc.account_id OR ";
        }

        $query .= "op.transfer_account_id = acc.account_id )";

        $query .= " WHERE op.user_id = " . $this->user()->getId()
                . " AND op.accepted = 1"
                . " AND op.deleted_at IS NULL"
                . " AND acc.deleted_at IS NULL";

        if (is_numeric($operationType)) {
            $query .= " AND op.type = {$operationType}";
        } else {
            $query .= " AND op.type IN ({$operationType})";
        }

        //определим интервал, за который взять операции, если он задан
        if (isset($months)) {
            if($months > 0) { //отсчитываем заданное количество месяцев
                $subIntervalValue = $months;
                $subIntervalType = "MONTH";
            } else { //внутри текущего месяца - сбросим дни до первого
                $subIntervalValue = "DAYOFMONTH(CURDATE()) - 1";
                $subIntervalType = "DAY";
            }

            $query .= " AND op.date >= DATE_SUB(CURDATE(), INTERVAL " . $subIntervalValue . " " . $subIntervalType . ")";
        }
        
        if(isset($additionalWhere))
        {
            $query .= " AND " . $additionalWhere;
        }

        //группируем по счетам
        $query .= " GROUP BY acc.account_id";

        //учтем добавочное условие
        if(isset($additionalHaving))
            $query = $query . " HAVING " . $additionalHaving;

        return $query;
    }

    // сумма с учетом коэффициента стажа для экстраполяции значений, если реальный стаж меньше используемого периода:
    // ведем учет в системе 6 дней, т.е. данные есть всего за 6 дней
    // получаем показатель за 3 месяца
    // поэтому полученные данные об операциях за 6 дней экстраполируем на 3 месяца - умножим на ~ (31 + 30 + 31 / 6)
    private function getSumWithExperienceCoeff($resultSum, $months)
    {
        //данные за текущий месяц с учетом показателя не корректируются
        if($months == 0)
            return $resultSum;
        
        //конец интервала считаем сегодняшним числом
        $intervalEnd = new DateTime();
        $intervalStart = new DateTime();
        //сдвиг начала интервала
        //если задано количество месяцев, их используем, иначе считаем, что нужно начало месяца - просто "сбрасываем" дни у конца интервала

        $subIntervalValue = $months > 0 ? $months . ' month' : $intervalEnd->format('d') . ' day';
        $intervalStart->modify('-' . $subIntervalValue);


        //получаем интервал в днях
        // Два варианта решения:
        //  - используя mktime(0, 0, 0, $intervalEnd->format('m'), $intervalEnd->format('d'), $intervalEnd->format('Y'));
        //  - используя strtotime($intervalEnd->format('Y-m-d'));

        // @deprecated
        // $queryString = "SELECT DATEDIFF('" . $intervalEnd->format('Y-m-d'). "', '" . $intervalStart->format('Y-m-d'). "')";
        // $daysInInterval = $this->db()->selectCell($queryString);
        $daysInInterval = (int) (
            (
                // разница в секундах
                strtotime($intervalEnd->format('Y-m-d')) - strtotime($intervalStart->format('Y-m-d'))
            ) / 86400 // секунд в 1 сутках 60*60*24
        );

        //собственно коэффицент
        $experienceCoeff = $daysInInterval / $this->getExperienceDays();

        //если коэффициент < 1, значит, стаж уже достаточный, и экстраполировать не нужно
        return ($experienceCoeff > 1 ? $resultSum * $experienceCoeff : $resultSum);
    }

    //стаж в системе в днях
    private $_experienceDaysValue;
    private function getExperienceDays()
    {
        if(!isset($this->_experienceDaysValue))
        {
            //стаж определяется датой первой операции
            $daysQuery = "
                SELECT DATEDIFF(CURDATE(), IFNULL(mindate, CURDATE()))
                FROM (
                    SELECT MIN(op.date) AS mindate
                    FROM operation op
                    WHERE op.type <> ". Operation::TYPE_BALANCE ."
                        AND op.accepted = 1
                        AND op.deleted_at IS NULL
                        AND op.user_id = " . $this->user()->getId() . "
                ) AS tbl";

            //добавим единицу, чтобы учесть сегодняшний день
            //например, если вчера была первая операция, то стаж = 2 дням

            $days = $this->db()->selectCell($daysQuery) + 1;
            if ($days < 1) {
                $days = 1;
            }
            $this->_experienceDaysValue = $days;
        }

        return $this->_experienceDaysValue;
    }

    private function GetDrainBudget()
    {
        //возвращаем просто сумму запланированных категорий расходов текущего месяца
        //не паримся о валюте, т.к. бюджет пока планируем в базовой, и считаем, что тахометры получаем в базовой

        $query = "
            SELECT SUM(ABS(amount))
            FROM budget
            WHERE
                    date_start = DATE_SUB(CURDATE(), INTERVAL DAYOFMONTH(CURDATE()) - 1 DAY)
                AND drain = 1
                AND user_id = " . $this->user()->getId();

        $drainBudget = $this->db()->selectCell($query);

        if(!isset($drainBudget))
            $drainBudget = 0;

        return $drainBudget;
    }

    private $_totalTahometer;

    //Инициализация тахометров
    private function init()
    {
        $this->totalTahometer = new TotalTahometer();
        $this->tahometersByKeywords = array(
                //Деньги
                Tahometer::$MONEY_KEYWORD => new BaseTahometer(Tahometer::$MONEY_KEYWORD),
                //Бюджет
                Tahometer::$BUDGET_KEYWORD => new BaseTahometer(Tahometer::$BUDGET_KEYWORD),
                //Долги
                Tahometer::$LOANS_KEYWORD => new BaseTahometer(Tahometer::$LOANS_KEYWORD),
                //Доходы vs Расходы
                Tahometer::$DIFF_KEYWORD => new BaseTahometer(Tahometer::$DIFF_KEYWORD)
            );
    }
}

/*
*класс "Тахометр" для более удобной работы с тахометрами
*/
abstract class Tahometer
{
    public static $MONEY_KEYWORD = 'money';
    public static $BUDGET_KEYWORD = 'budget';
    public static $LOANS_KEYWORD = 'loans';
    public static $DIFF_KEYWORD = 'diff';
    public static $TOTAL_KEYWORD = 'total';

    //ключевое слово
    protected $_keyword;

    private static $_captions;


    public static function init()
    {
        self::$_captions = array(
            Tahometer::$MONEY_KEYWORD => 'Насколько Вы обеспечены деньгами: отношение доступных денег к среднему месячному расходу за последние три месяца',
            Tahometer::$BUDGET_KEYWORD => 'Удается ли сэкономить: насколько израсходован бюджет на текущий месяц',
            Tahometer::$LOANS_KEYWORD => 'Долговая нагрузка: какая часть доходов за последний месяц пошла на выплату кредитов и займов',
            Tahometer::$DIFF_KEYWORD => 'Удается ли тратить меньше, чем получаете: превышение доходов над расходами за последние 3 месяца',
            Tahometer::$TOTAL_KEYWORD => 'Итоговая оценка финансового здоровья на базе всех других показателей'
        );

        //Итоговый тахометр здесь не используем
        self::$_calculateTypes = array(
            Tahometer::$MONEY_KEYWORD => 'direct',
            Tahometer::$BUDGET_KEYWORD => 'negative',
            Tahometer::$LOANS_KEYWORD => 'negative',
            Tahometer::$DIFF_KEYWORD => 'over',
            Tahometer::$TOTAL_KEYWORD => 'total'
        );        

        self::$_zoneBorders = array(
            Tahometer::$MONEY_KEYWORD => array(0, 2, 5, 6),
            Tahometer::$BUDGET_KEYWORD => array(0, 3, 15, 100),
            Tahometer::$LOANS_KEYWORD => array(0, 30, 60, 100),
            Tahometer::$DIFF_KEYWORD => array(0, 5, 10, 20),
            Tahometer::$TOTAL_KEYWORD => array(0,100,200,300)
        );

        self::$_advices = array(
            Tahometer::$MONEY_KEYWORD =>
                array(
                    0 => array(
                            0 => "<p>Крайне низкий запас наличных денег, у Вас нет достаточного страхового резерва</p><p>Контролируйте свой бюджет</p><p>Откладывайте 10% от дохода сразу после получения, сформируйте финансовую цель &mdash; &laquo;Резерв&raquo;</p><p>Нужно иметь остаток наличных денег на 3-6 месяцев жизни без дохода</p>",
                            1 => "<p>Очень низкий запас наличных денег, у Вас почти нет достаточного страхового резерва</p><p>Контролируйте свой бюджет</p><p>Откладывайте 10% от дохода сразу после получения, сформируйте финансовую цель &mdash; &laquo;Резерв&raquo;</p><p>Нужно иметь остаток наличных денег на 3-6 месяцев жизни без дохода</p>",
                            2 => "<p>Низкий запас наличных денег, у Вас минимальный страховой резерв до 2х месяцев</p><p>Контролируйте свой бюджет</p><p>Откладывайте 10% от дохода сразу после получения, сформируйте финансовую цель &mdash; &laquo;Резерв&raquo;</p><p>Нужно иметь остаток наличных денег на 3-6 месяцев жизни без дохода</p><p>Можно часть резерва разместить в депозит под процент</p>"
                        ),
                    1 => array(
                            0 => "<p>Вы имеете достаточный запас наличных денег, Ваш страховой резерв примерно на 2-3 месяца</p><p>Не останавливайтесь на достигнутом</p><p>Контролируйте бюджет и откладывайте 10% от дохода ежемесячно, копите на финансовую цель &mdash; &laquo;Резерв&raquo;</p><p>Нужно иметь остаток наличных денег на 3-6 месяцев жизни без дохода</p><p>Можно половину резерва разместить в депозитах под процент</p>",
                            1 => "<p>Вы имеете достаточный запас наличных денег, примерно на 3-4 месяца</p><p>Не останавливайтесь на достигнутом</p><p>Контролируйте свой бюджет</p><p>Откладывайте 10% от дохода сразу после получения, копите на финансовую цель &mdash; &laquo;Резерв&raquo;</p><p>Нужно иметь остаток наличных денег на 3-6 месяцев жизни без дохода</p><p>Лучше 3/4 резерва разместить в депозитах под процент</p>",
                            2 => "<p>Поздравляем, Вы имеете запас наличных денег примерно на 4-5 месяцев</p><p>Контролируйте свой бюджет</p><p>Откладывайте 10% от дохода ежемесячно, копите на финансовую цель &mdash; Резерв</p><p>Лучше 3/4 резерва разместить в депозитах под процент и 1/4 выделить для инвестиций</p>"
                        ),
                    2 => array(
                            0 => "<p>Поздравляем, Вы имеете очень высокий запас наличных денег, примерно на 5-6 месяцев</p><p>Продолжайте откладывать 10% от дохода сразу после получения, копите на финансовую цель &mdash; &laquo;Резерв&raquo;</p><p>Нужно иметь остаток наличных денег на 3-6 месяцев жизни без дохода</p><p>Лучше 3/4 резерва разместить в депозитах под процент и 1/4 выделить для инвестиций</p>",
                            1 => "<p>Поздравляем, Вы имеете очень высокий запас наличных денег , примерно на 5-6 месяцев</p><p>Продолжайте откладывать 10% от дохода сразу после получения, копите на финансовую цель &mdash; &laquo;Резерв&raquo;</p><p>Нужно иметь остаток наличных денег на 3-6 месяцев жизни без дохода</p><p>Можно 3/4 резерва разместить в депозитах под процент и 1/4 выделить для инвестиций</p>",
                            2 => "<p>Поздравляем, Вы имеете выше необходимого страхового запаса наличных денег, примерно на 6 месяцев и более</p><p>Думайте об инвестициях, деньги должны работать</p><p>Лучше 3/4 резерва разместить в депозитах под процент и 1/4 выделить для инвестиций</p><p>Вы можете уже участвовать частью резерва свыше 6 месяцев в рискованных инвестициях</p>"
                        )
                ),
            Tahometer::$BUDGET_KEYWORD =>
                array(
                    0 => array(
                            0 => "<p>Вы израсходовали бюджет на месяц, будьте экономны в своих тратах</p><p>Анализируйте свои затраты, часть из них можно сократить, Вам могут быть полезны советы/статьи об экономии</p>",
                            1 => "<p>Вы израсходовали бюджет на месяц, будьте экономны в своих тратах</p><p>Анализируйте свои затраты, часть из них можно сократить, Вам могут быть полезны советы/статьи об экономии</p>",
                            2 => "<p>Вы почти израсходовали бюджет на месяц, будьте экономны в своих тратах</p><p>Анализируйте свои затраты, часть из них можно сократить, Вам могут быть полезны советы/статьи об экономии</p>"
                        ),
                    1 => array(
                            0 => "<p>Вы почти израсходовали бюджет на месяц, будьте экономны в своих тратах</p><p>Анализируйте свои затраты, часть из них можно сократить, Вам могут быть полезны советы/статьи об экономии</p>",
                            1 => "<p>Вы почти израсходовали бюджет на месяц, будьте экономны в своих тратах</p><p>Анализируйте свои затраты, часть из них можно сократить, Вам могут быть полезны советы/статьи об экономии</p>",
                            2 => "<p>Вы почти израсходовали бюджет на месяц, будьте экономны в своих тратах</p><p>Анализируйте свои затраты, часть из них можно сократить, Вам могут быть полезны советы/статьи об экономии</p>"
                        ),
                    2 => array(
                            0 => "<p>Сохраняйте темпы экономии, если месяц еще не закончился</p><p>Если у Вас нет финансового резерва, начните откладывать деньги в страховой резерв</p>",
                            1 => "<p>Сохраняйте темпы экономии, если месяц еще не закончился</p><p>Если у Вас нет финансового резерва, начните откладывать деньги в страховой резерв</p>",
                            2 => "<p>Сохраняйте темпы экономии, если месяц еще не закончился</p><p>Если у Вас нет финансового резерва, начните откладывать деньги в страховой резерв</p>"
                        )
                ),
            Tahometer::$LOANS_KEYWORD =>
                array(
                    0 => array(
                            0 => "<p>У вас запредельно высокий уровень платежей по кредитам и займам</p><p>Вам нужно гасить кредиты/займы, рефинансируйте долги под более низкий процент или больший срок</p><p>Вы работаете на кредиторов, высока угроза банкротства</p>",
                            1 => "<p>У вас недопустимый уровень платежей по кредитам и займам</p><p>Вам нужно гасить кредиты/займы, рефинансируйте долги под более низкий процент или больший срок</p><p>Вы практически работаете на кредиторов, высока угроза банкротства</p>",
                            2 => "<p>У вас очень высокий уровень платежей по кредитам и займам</p><p>Вам нужно гасить кредиты/займы, рефинансируйте долги под более низкий процент или больший срок</p><p>Вы практически работаете на кредиторов, высока угроза банкротства</p>"
                        ),
                    1 => array(
                            0 => "<p> К сожалению, Вы превысили нормативный уровень платежей по кредитам и займам</p><p>Не берите больше кредиты/займы без крайней необходимости</p><p>Подумайте о снижении уровня долгов или о рефинансировании долгов под более низкий процент или больший срок</p>",
                            1 => "<p>Вы приближаетесь к опасной отметке уровня платежей по кредитам и займам</p><p>Не берите больше кредиты/займы без крайней необходимости</p><p>Вам нужно гасить долги или рефинансируйте под более низкий процент или больший срок</p>",
                            2 => "<p>У вас высокий уровень платежей по кредитам и займам</p><p>Не берите больше кредиты/займы</p><p>Вам нужно гасить долги или рефинансируйте под более низкий процент или больший срок</p>"
                        ),
                    2 => array(
                            0 => "<p>У Вас все еще достаточный уровень финансовой независимости</p><p>Но Вы приближаетесь к границе нормативного уровня</p><p>Не берите больше кредиты/займы без необходимости</p>",
                            1 => "<p>У Вас достаточный уровень финансовой независимости</p><p>Но будьте аккуратны, рекомендуем не превышать порог 30-40% от дохода на платежи по кредитам/займам</p>",
                            2 => "<p>Поздравляем, У Вас высокий уровень финансовой независимости</p><p>У вас есть возможности для новых заимствований, но будьте аккуратны, рекомендуем не превышать порог 30-40% от дохода на платежи по кредитам/займам</p>"
                        )
                ),
            Tahometer::$DIFF_KEYWORD =>
                array(
                    0 => array(
                            0 => "<p>Вам следует либо больше зарабатывать, либо меньше тратить</p><p>Вы должны иметь минимально 10% превышение доходов над расходами, поработайте с бюджетом</p><p>Вам могут быть полезны советы/статьи об экономии</p>",
                            1 => "<p>Вам следует либо больше зарабатывать, либо меньше тратить</p><p>Вы должны иметь минимально 10% превышение доходов над расходами, поработайте с бюджетом</p><p>Вам могут быть полезны советы/статьи об экономии</p>",
                            2 => "<p>Вам следует либо больше зарабатывать, либо меньше тратить</p><p>Вы должны иметь минимально 10% превышение доходов над расходами, поработайте с бюджетом</p><p>Вам могут быть полезны советы/статьи об экономии</p>"
                        ),
                    1 => array(
                            0 => "<p>Неплохо, у Вас превышение доходов над расходами более 5%, стремитесь к 10-20% разнице для создания резерва для накоплений</p>",
                            1 => "<p>Неплохо, у Вас превышение доходов над расходами более 6,5%, стремитесь к 10-20% разнице для создания резерва для накоплений</p>",
                            2 => "<p>Неплохо, у Вас превышение доходов над расходами более 7,5%, стремитесь к 10-20% разнице для создания резерва для накоплений</p>"
                        ),
                    2 => array(
                            0 => "<p>Поздравляем, Ваши доходы превышают расходы более чем на 10%</p><p>Не забывайте делать вклады в страховой резерв</p><p>Воспользуйтесь Финансовой целью &mdash; &laquo;Резерв&raquo; для удобства контроля накоплений</p>",
                            1 => "<p>Поздравляем, Ваши доходы превышают расходы более чем на 13%</p><p>Не забывайте делать вклады в страховой резерв</p><p>Воспользуйтесь Финансовой целью &mdash; &laquo;Резерв&raquo; для удобства контроля накоплений</p>",
                            2 => "<p>Поздравляем, Ваши доходы превышают расходы более чем на 16%</p><p>Не забывайте делать вклады в страховой резерв</p><p>Воспользуйтесь Финансовой целью &mdash; &laquo;Резерв&raquo; для удобства контроля накоплений</p>"
                        )
                ),
            Tahometer::$TOTAL_KEYWORD =>
                array(
                    0 => array(
                            0 => "<p>Вам может грозить банкротство</p><p>Срочно измените свой подход к финансовым вопросам</p><p>Проанализируйте основные датчики:</p><p>Деньги: важно иметь страховой запас денег минимум на 2-3 месяца без доходов</p><p>Бюджет: не тратьте больше, чем планируете</p><p>Долги: уровень выплат по кредитам не больше 40% доходов</p><p>Доходы: обеспечьте минимум разницу в 10% между доходами и расходами</p><p>Будьте настойчивы</p>",
                            1 => "<p>Вам может грозить банкротство</p><p>Срочно измените свой подход к финансовым вопросам</p><p>Проанализируйте основные датчики:</p><p>Деньги: важно иметь страховой запас денег минимум на 2-3 месяца без доходов</p><p>Бюджет: не тратьте больше, чем планируете</p><p>Долги: уровень выплат по кредитам не больше 40% доходов</p><p>Доходы: обеспечьте минимум разницу в 10% между доходами и расходами</p><p>Будьте настойчивы</p>",
                            2 => "<p>Вам может грозить банкротство</p><p>Срочно измените свой подход к финансовым вопросам</p><p>Проанализируйте основные датчики:</p><p>Деньги: важно иметь страховой запас денег минимум на 2-3 месяца без доходов</p><p>Бюджет: не тратьте больше, чем планируете</p><p>Долги: уровень выплат по кредитам не больше 40% доходов</p><p>Доходы: обеспечьте минимум разницу в 10% между доходами и расходами</p><p>Будьте настойчивы</p>"
                        ),
                    1 => array(
                            0 => "<p>Вам финансовое состояние ближе к нестабильному</p><p>Нужно работать над улучшением ситуации</p><p>Проанализируйте основные датчики:</p><p>Деньги: важно иметь страховой запас денег минимум на 3-4 месяца без доходов</p><p>Бюджет: не тратьте больше, чем планируете, лучше экономьте дополнительно</p><p>Долги: уровень выплат по кредитам не больше 40% доходов</p><p>Доходы: обеспечьте минимум разницу в 10% между доходами и расходами</p><p>Будьте настойчивы</p>",
                            1 => "<p>Вам финансовое состояние стабильное</p><p>Ищите резервы для улучшения ситуации</p><p>Проанализируйте основные датчики:</p><p>Деньги: важно иметь страховой запас денег минимум на 4-5 месяца без доходов</p><p>Бюджет: не тратьте больше, чем планируете, лучше экономьте дополнительно</p><p>Долги: уровень выплат по кредитам не больше 40% доходов</p><p>Доходы: обеспечьте минимум разницу в 15% между доходами и расходами</p><p>Будьте настойчивы</p>",
                            2 => "<p>Вам финансовое состояние стабильное</p><p>Ищите резервы для улучшения ситуации до хорошего уровня</p><p>Проанализируйте основные датчики:</p><p>Деньги: важно иметь страховой запас денег минимум на 4 месяца без доходов</p><p>Бюджет: не тратьте больше, чем планируете, лучше экономьте дополнительно 10% от первоначального плана</p><p>Долги: уровень выплат по кредитам не больше 30% доходов</p><p>Доходы: обеспечьте минимум разницу в 15% между доходами и расходами</p><p>Будьте настойчивы</p>"
                        ),
                    2 => array(
                            0 => "<p>У Вас хорошее финансовое состояние, но не останавливайтесь на достигнутом</p><p>Важно чтобы ваши деньги работали, не стоит держать наличные, вкладывайте в депозиты/другие инструменты с учетом рейтинга надежности</p><p>Помните, что страховой резерв (на 6 месяцев жизни без доходов) нужно размещать только в надежные инструменты с гарантированной доходностью и возвратом средств</p>",
                            1 => "<p>У Вас хорошее финансовое состояние, но не останавливайтесь на достигнутом</p><p>Важно чтобы ваши деньги работали, не стоит держать наличные, вкладывайте в депозиты/другие инструменты с учетом рейтинга надежности</p><p>Помните, что страховой резерв (на 6 месяцев жизни без доходов) нужно размещать только в надежные инструменты с гарантированной доходностью и возвратом средств</p>",
                            2 => "<p>У Вас хорошее финансовое состояние, но не останавливайтесь на достигнутом</p><p>Важно чтобы ваши деньги работали, не стоит держать наличные, вкладывайте в депозиты/другие инструменты с учетом рейтинга надежности</p><p>Помните, что страховой резерв (на 6 месяцев жизни без доходов) нужно размещать только в надежные инструменты с гарантированной доходностью и возвратом средств</p>"
                        )
                )
            );

        BaseTahometer::init();
    }


    private function getCaption()
    {
        return self::$_captions[$this->_keyword];
    }

    //количество зон
    protected static $zonesCount = 3;

    //границы зон
    private static $_zoneBorders;

    protected function getZoneBorders()
    {
        return self::$_zoneBorders[$this->_keyword];
    }

    //советы, отображаемые пользователю
    private static $_advices;

    //Вычисленное значение
    protected $_rawValue;

    //константа для 100 %
    protected static $HUNDRED_PERCENT = 100;
    
    private static $_calculateTypes;

    //тип вычисления значения тахометра    
    //вынесли в базовый класс, чтобы не перегружать getValueForOutput
    protected function getCalculateType()
    {
        return self::$_calculateTypes[$this->_keyword];
    }
    
    protected function IsNegative()
    {
        return $this->getCalculateType() == 'negative';
    }
    

    protected function getMinBorder()
    {
        $zoneBorders = $this->getZoneBorders();
        $minBorder = $zoneBorders[0];
        return $minBorder;
    }

    protected function getMaxBorder()
    {
        $zoneBorders = $this->getZoneBorders();
        $maxBorder = $zoneBorders[self::$zonesCount];
        return $maxBorder;
    }
    
    //устанавливаем значение, подгоняя его под зоны
    protected function setValue($value)
    {
        $minBorder = $this->getMinBorder();
        $maxBorder = $this->getMaxBorder();

        //гарантируем, что значение - внутри границ
        if($value > $maxBorder)
            $value = $maxBorder;
        else if($value < $minBorder)
            $value = $minBorder;
        
        $this->_rawValue = $value;
    }
    
    //значение для вывода в тахометр
    protected function getValueForOutput()
    {
        $result = $this->_rawValue;
        $minBorder = $this->getMinBorder();
        $maxBorder = $this->getMaxBorder();
        
         //нормализуем, если нужно выводить в тахометр - там шкала от 0 до 100; 100 соответствует макс. значение тахометра, 0 - минимальное
        $result = ($result - $minBorder) / ($maxBorder - $minBorder);

        //для отрицательных тахометров - обратный порядок шкалы        
        if ($this->IsNegative())
            $result = 1 - $result;

        //переведем отношение в проценты
        return $result * self::$HUNDRED_PERCENT;
    }
    
    private $_zoneIndex;

    protected function getZoneIndex()
    {
        if(!isset($this->_zoneIndex))
        {
            //вычислим, в какую зону входит значение тахометра
            $tempZoneIndex = 0;

            //перебираем все зоны, пока проходим в следующую зону

            $zoneBorders = $this->getZoneBorders();

            while($tempZoneIndex < self::$zonesCount - 1
                && $this->_rawValue > $zoneBorders[$tempZoneIndex+1])
                        $tempZoneIndex++;

            $this->_zoneIndex = $tempZoneIndex;
        }

        return $this->_zoneIndex;
    }
    
        //определяем положение внутри зоны
    protected function getPositionValueInsideZone()
    {
        $zoneIndex = $this->getZoneIndex();
        $zoneBorders = $this->getZoneBorders();
        
        $leftBorder = $zoneBorders[$zoneIndex];
        $rightBorder = $zoneBorders[$zoneIndex + 1];

        //вычисляем, где между левой и правой границей находимся
        return
            ($this->_rawValue - $leftBorder) /
                ($rightBorder - $leftBorder);        
    }

    //индекс - в какой из подзон внутри зоны находится значение тахометра
    private function getSubzoneIndex()
    {
        //считаем зоны равными по ширине:
        $subzonesCount = 3;
        $subzoneWidth = 1.0 / $subzonesCount;
        
        $pozitionInsideZone = $this->getPositionValueInsideZone();
        
        
        //перебираем зоны, пока значение не станет меньше следующей зоны
        $nextSubzoneIndex = 1;
        while($nextSubzoneIndex < $subzonesCount && ($nextSubzoneIndex) * $subzoneWidth <= $pozitionInsideZone)
        {
            $nextSubzoneIndex++;
        }
        
        return $nextSubzoneIndex - 1;
    }

    private function getAdvice()
    {
        $colorize = '<p style="color:#%s;font-weight:bold;">';
        $colors = array(
            'bf0000',
            'd6ab00',
            '106601',
        );

        $advice = self::$_advices[$this->_keyword][$this->getZoneIndex()][$this->getSubzoneIndex()];

        return sprintf($colorize, $colors[$this->getZoneIndex()]) . substr($advice, 3, strlen($advice));
    }


    //массив готовых результатов - для отображения на клиенте
    public function getResult()
    {
        return array('value' =>
            round($this->getValueForOutput()),
            'description' => $this->getAdvice(), 'title' => $this->getCaption());
    }
}

//основные тахометры - входные значения рассчитываются
class BaseTahometer extends Tahometer
{

    //инициализация
    public static function init()
    {
        self::$_weights = array(
            Tahometer::$MONEY_KEYWORD => 35,
            Tahometer::$BUDGET_KEYWORD => 20,
            Tahometer::$LOANS_KEYWORD => 15,
            Tahometer::$DIFF_KEYWORD => 30
        );
    }
    
    //установка значения как отношения 2-х величин, используемых в данном тахометре, в процентах
    //в итоге получаем некий процент, но для разных тахометров он может считаться по-разному
    public function SetBaseValue($dividend, $divisor)
    {
        //если не задан числитель, ставим минимальное значение данного тахометра
        if($dividend == 0)
            $baseValue = $this->IsNegative() 
            ? $this->getMaxBorder()
            : $this->getMinBorder();

        //если не задан знаменатель, ставим максимальное значение для данного тахометра
        else if($divisor == 0)
        {
            $baseValue = $this->IsNegative() 
            ? $this->getMinBorder()
            : $this->getMaxBorder();
        }
        
        else
        {

            //значение считаем в зависимости от типа подсчета значения в тахометре
            switch($this->getCalculateType())
            {
                //прямое отношение величин
                case 'direct':
                    $baseValue = $dividend / $divisor;
                    break;

                //1 - отношение (считаем оставшуюся величину в процентах)
                case 'negative':
                    $baseValue = (1 - $dividend / $divisor) * self::$HUNDRED_PERCENT;
                    break;

                //перекрытие (считаем превышение в процентах)
                case 'over':
                    $baseValue = ($dividend / $divisor - 1) * self::$HUNDRED_PERCENT;
                    break;
            }
        }
        
        $this->setValue($baseValue);
    }

    //получение взвешенного значения
    public function getWeightedValue()
    {
        //точное значение тахометра - индекс зоны + положение внутри зоны
        $exactValue = $this->getZoneIndex() +
            $this->getPositionValueInsideZone();

        //наконец, установим взвешенное значение
        return $exactValue * $this->getWeight();
    }
    
    public function __construct($keyword)
    {
        $this->_keyword = $keyword;
    }

    //веса тахометров
    private static $_weights;

    private function getWeight()
    {
        return self::$_weights[$this->_keyword];
    }
}

//итоговый тахометр - входное значение устанавливается напрямую
class TotalTahometer extends Tahometer
{
    //конструируем без параметров - и так все знаем
    public function __construct()
    {
        $this->_keyword = self::$TOTAL_KEYWORD;
    }

    //установка значения напрямую
    public function SetTotalValue($value)
    {
        $this->setValue($value);
    }
}
