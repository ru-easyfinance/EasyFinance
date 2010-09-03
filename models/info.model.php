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
        //конец интервала считаем сегодняшним числом
        $intervalEnd = new DateTime();
        $intervalStart = new DateTime();
        //сдвиг начала интервала
        //если задано количество месяцев, их используем, иначе считаем, что нужно начало месяца - просто "сбрасываем" дни у конца интервала

        $subIntervalValue = $months > 0 ? $months . ' month' : $intervalEnd->format('d') . ' day';
        $intervalStart->modify('-' . $subIntervalValue);


        //получаем интервал в днях
        //todo: убрать SQL
        //хз как быстро сделать это средствами PHP < 5.3, т.к. diff нет

        $queryString = "SELECT DATEDIFF('" . $intervalEnd->format('Y-m-d'). "', '" . $intervalStart->format('Y-m-d'). "')";

        $daysInInterval = $this->db()->selectCell($queryString);

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

        self::$_zoneBorders = array(
            Tahometer::$MONEY_KEYWORD => array(0, 2, 5, 6),
            Tahometer::$BUDGET_KEYWORD => array(0, 85, 97, 100),
            Tahometer::$LOANS_KEYWORD => array(0, 40, 70, 100),
            Tahometer::$DIFF_KEYWORD => array(0, 5, 10, 20),
            Tahometer::$TOTAL_KEYWORD => array(0,100,200,300)
        );

        self::$_advices = array(
            Tahometer::$MONEY_KEYWORD =>
                array(
                    0 => array(
                            0 => "Крайне низкий запас наличных денег,  у Вас нет достаточного страхового резерва<br/><br/>Контролируйте свой бюджет<br/><br/>Откладывайте 10% от дохода сразу после получения, сформируйте финансовую цель - Резерв<br/><br/>Нужно иметь остаток наличных денег на 3-6 месяцев жизни без дохода<br/><br/>",
                            1 => "Очень низкий запас наличных денег,  у Вас почти нет достаточного страхового резерва<br/><br/>Контролируйте свой бюджет<br/><br/>Откладывайте 10% от дохода сразу после получения, сформируйте финансовую цель - Резерв<br/><br/>Нужно иметь остаток наличных денег на 3-6 месяцев жизни без дохода<br/><br/>",
                            2 => "Низкий запас наличных денег,  у Вас минимальный страховой резерв до 2х месяцев<br/><br/>Контролируйте свой бюджет<br/><br/>Откладывайте 10% от дохода сразу после получения, сформируйте финансовую цель - Резерв<br/><br/>Нужно иметь остаток наличных денег на 3-6 месяцев жизни без дохода<br/><br/> Можно часть резерва разместить в депозит под процент<br/><br/>"
                        ),
                    1 => array(
                            0 => "Вы имеете достаточный запас наличных денег,  Ваш страховой резерв примерно на 2-3 месяца<br/><br/>Не останавливайтесь на достигнутом<br/><br/>Контролируйте бюджет и откладывайте 10% от дохода ежемесячно, копите на финансовую цель - Резерв<br/><br/>Нужно иметь остаток наличных денег на 3-6 месяцев жизни без дохода<br/><br/>Можно половину резерва разместить в депозитах под процент<br/><br/>",
                            1 => "Вы имеете достаточный запас наличных денег, примерно на 3-4 месяца<br/><br/>Не останавливайтесь на достигнутом<br/><br/>Контролируйте свой бюджет<br/><br/>Откладывайте 10% от дохода сразу после получения, копите на финансовую цель - Резерв<br/><br/>Нужно иметь остаток наличных денег на 3-6 месяцев жизни без дохода<br/><br/>Лучше 3/4 резерва разместить в депозитах под процент<br/><br/>",
                            2 => "Поздравляем, Вы имеете запас наличных денег примерно на 4-5 месяцев<br/><br/> Контролируйте свой бюджет<br/><br/>Откладывайте 10% от дохода ежемесячно, копите на финансовую цель - Резерв<br/><br/>Лучше 3/4 резерва разместить в депозитах под процент и 1/4 выделить для инвестиций<br/><br/>"
                        ),
                    2 => array(
                            0 => "Поздравляем, Вы имеете очень высокий запас наличных денег, примерно на 5-6 месяцев<br/><br/>Продолжайте откладывать 10% от дохода сразу после получения, копите на финансовую цель - Резерв<br/><br/>Нужно иметь остаток наличных денег на 3-6 месяцев жизни без дохода<br/><br/>Лучше 3/4 резерва разместить в депозитах под процент и 1/4 выделить для инвестиций<br/><br/>",
                            1 => "Поздравляем, Вы имеете очень высокий запас наличных денег , примерно на 5-6 месяцев<br/><br/>Продолжайте откладывать 10% от дохода сразу после получения, копите на финансовую цель - Резерв<br/><br/>Нужно иметь остаток наличных денег на 3-6 месяцев жизни без дохода<br/><br/>Можно 3/4 резерва разместить в депозитах под процент и 1/4 выделить для инвестиций<br/><br/>",
                            2 => "Поздравляем, Вы имеете выше необходимого  страхового запаса наличных денег, примерно на 6 месяцев и более<br/><br/>Думайте об инвестициях , деньги должны работать<br/><br/>Лучше 3/4 резерва разместить в депозитах под процент и 1/4 выделить для инвестиций<br/><br/>Вы можете уже участвовать частью резерва свыше 6 месяцев в рискованных инвестициях<br/><br/>"
                        )
                ),
            Tahometer::$BUDGET_KEYWORD => 
                array(
                    0 => array(
                            0 => "Вы израсходовали бюджет на месяц, будьте экономны в своих тратах<br/><br/>Анализируйте свои затраты, часть из них можно сократить, Вам могут быть полезны советы/статьи об экономии<br/><br/>",
                            1 => "Вы израсходовали бюджет на месяц, будьте экономны в своих тратах<br/><br/>Анализируйте свои затраты, часть из них можно сократить, Вам могут быть полезны советы/статьи об экономии<br/><br/>",
                            2 => "Вы почти израсходовали бюджет на месяц, будьте экономны в своих тратах<br/><br/>Анализируйте свои затраты, часть из них можно сократить, Вам могут быть полезны советы/статьи об экономии<br/><br/>"
                        ),
                    1 => array(
                            0 => "Вы почти израсходовали бюджет на месяц, будьте экономны в своих тратах<br/><br/>Анализируйте свои затраты, часть из них можно сократить, Вам могут быть полезны советы/статьи об экономии<br/><br/>",
                            1 => "Вы почти израсходовали бюджет на месяц, будьте экономны в своих тратах<br/><br/>Анализируйте свои затраты, часть из них можно сократить, Вам могут быть полезны советы/статьи об экономии<br/><br/>",
                            2 => "Вы почти израсходовали бюджет на месяц, будьте экономны в своих тратах<br/><br/>Анализируйте свои затраты, часть из них можно сократить, Вам могут быть полезны советы/статьи об экономии<br/><br/>"
                        ),
                    2 => array(
                            0 => "Сохраняйте темпы экономии, если еще месяц не закончился<br/><br/>Если у Вас нет финансового резерва, начните откладывать деньги в страховой резерв<br/><br/>",
                            1 => "Сохраняйте темпы экономии, если еще месяц не закончился<br/><br/>Если у Вас нет финансового резерва, начните откладывать деньги в страховой резерв<br/><br/>",
                            2 => "Сохраняйте темпы экономии, если еще месяц не закончился<br/><br/>Если у Вас нет финансового резерва, начните откладывать деньги в страховой резерв<br/><br/>"
                        )
                ),
            Tahometer::$LOANS_KEYWORD => 
                array(
                    0 => array(
                            0 => "У вас запредельно высокий уровень платежей по кредитам и займам<br/><br/> Вам нужно гасить кредиты/займы, рефинансируйте долги под более низкий процент или больший срок<br/><br/>Вы работаете на кредиторов, высока угроза банкротства<br/><br/>",
                            1 => "У вас недопустимый уровень платежей по кредитам и займам<br/><br/> Вам нужно гасить кредиты/займы, рефинансируйте долги под более низкий процент или больший срок<br/><br/>Вы практически работаете на кредиторов, высока угроза банкротства<br/><br/>",
                            2 => "У вас очень высокий уровень платежей по кредитам и займам<br/><br/> Вам нужно гасить кредиты/займы, рефинансируйте долги под более низкий процент или больший срок<br/><br/>Вы практически работаете на кредиторов, высока угроза банкротства<br/><br/>"
                        ),
                    1 => array(
                            0 => " К сожалению, Вы превысили нормативный уровень платежей по кредитам и займам<br/><br/>Не берите больше кредиты/займы без крайней необходимости<br/><br/>Подумайте о снижении уровня долгов или о рефинансировании долгов под более низкий процент или больший срок<br/><br/>",
                            1 => "Вы приближаетесь к опасной отметке уровня платежей по кредитам и займам<br/><br/>Не берите больше кредиты/займы без крайней необходимости<br/><br/>Вам нужно гасить долги или рефинансируйте под более низкий процент или больший срок<br/><br/>",
                            2 => "У вас высокий уровень платежей по кредитам и займам<br/><br/>Не берите больше кредиты/займы<br/><br/>Вам нужно гасить долги или рефинансируйте под более низкий процент или больший срок<br/><br/>"
                        ),
                    2 => array(
                            0 => "У Вас все еще достаточный уровень финансовой независимости<br/><br/>Но Вы приближаетесь к  опасному уровню<br/><br/>Не берите больше кредиты/займы без необходимости<br/><br/>",
                            1 => "У Вас достаточный уровень финансовой независимости<br/><br/>Но будьте аккуратны, рекомендуем не превышать порог 30-40%  от дохода на платежи по кредитам/займам<br/><br/>",
                            2 => "Поздравляем, У Вас высокий уровень финансовой независимости<br/><br/>У вас есть возможности для новых заимствований, но будьте аккуратны, рекомендуем не превышать порог 30-40%  от дохода на платежи по кредитам/займам<br/><br/>"
                        )
                ),
            Tahometer::$DIFF_KEYWORD => 
                array(
                    0 => array(
                            0 => "Вам следует либо больше зарабатывать, либо меньше тратить<br/><br/>Вы должны иметь минимально 10% превышение доходов над расходами, поработайте с бюджетом<br/><br/>Вам могут быть полезны советы/статьи об экономии<br/><br/>",
                            1 => "Вам следует либо больше зарабатывать, либо меньше тратить<br/><br/>Вы должны иметь минимально 10% превышение доходов над расходами, поработайте с бюджетом<br/><br/>Вам могут быть полезны советы/статьи об экономии<br/><br/>",
                            2 => "Вам следует либо больше зарабатывать, либо меньше тратить<br/><br/>Вы должны иметь минимально 10% превышение доходов над расходами, поработайте с бюджетом<br/><br/>Вам могут быть полезны советы/статьи об экономии<br/><br/>"
                        ),
                    1 => array(
                            0 => "Неплохо, у Вас превышение доходов над расходами более 5%, стремитесь к 10-20% разнице для создания резерва для  накоплений<br/><br/>",
                            1 => "Неплохо, у Вас превышение доходов над расходами более 6,5%, стремитесь к 10-20% разнице для создания резерва для  накоплений<br/><br/>",
                            2 => "Неплохо, у Вас превышение доходов над расходами более 7,5%, стремитесь к 10-20% разнице для создания резерва для  накоплений<br/><br/>"
                        ),
                    2 => array(
                            0 => "Поздравляем, Ваши доходы превышают расходы более чем на 10%<br/><br/>Не забывайте делать вклады в страховой резерв<br/><br/>Воспользуйтесь Финансовой целью - резерв для удобства контроля накоплений<br/><br/>",
                            1 => "Поздравляем, Ваши доходы превышают расходы более чем на 13%<br/><br/>Не забывайте делать вклады в страховой резерв<br/><br/>Воспользуйтесь Финансовой целью - резерв для удобства контроля накоплений<br/><br/>",
                            2 => "Поздравляем, Ваши доходы превышают расходы более чем на 16%<br/><br/>Не забывайте делать вклады в страховой резерв<br/><br/>Воспользуйтесь Финансовой целью - резерв для удобства контроля накоплений<br/><br/>"
                        )
                ),
            Tahometer::$TOTAL_KEYWORD => 
                array(
                    0 => array(
                            0 => "Вам может грозить банкротство<br/><br/>Срочно измените свой подход к финансовым вопросам<br/><br/>Проанализируйте основные  датчики:<br/><br/>Деньги: важно иметь страховой запас денег минимум на 2-3 месяца без доходов<br/><br/>Бюджет: не тратьте больше, чем планируете<br/><br/>Долги: уровень выплат по кредитам не больше 40% доходов<br/><br/>Доходы: обеспечьте минимум разницу в 10% между доходами и расходами<br/><br/>Будьте настойчивы<br/><br/>",
                            1 => "Вам может грозить банкротство<br/><br/>Срочно измените свой подход к финансовым вопросам<br/><br/>Проанализируйте основные  датчики:<br/><br/>Деньги: важно иметь страховой запас денег минимум на 2-3 месяца без доходов<br/><br/>Бюджет: не тратьте больше, чем планируете<br/><br/>Долги: уровень выплат по кредитам не больше 40% доходов<br/><br/>Доходы: обеспечьте минимум разницу в 10% между доходами и расходами<br/><br/>Будьте настойчивы<br/><br/>",
                            2 => "Вам может грозить банкротство<br/><br/>Срочно измените свой подход к финансовым вопросам<br/><br/>Проанализируйте основные  датчики:<br/><br/>Деньги: важно иметь страховой запас денег минимум на 2-3 месяца без доходов<br/><br/>Бюджет: не тратьте больше, чем планируете<br/><br/>Долги: уровень выплат по кредитам не больше 40% доходов<br/><br/>Доходы: обеспечьте минимум разницу в 10% между доходами и расходами<br/><br/>Будьте настойчивы<br/><br/>"
                        ),
                    1 => array(
                            0 => "Вам финансовое состояние ближе к нестабильному<br/><br/>Нужно работать над улучшением ситуации<br/><br/>Проанализируйте основные  датчики:<br/><br/>Деньги: важно иметь страховой запас денег минимум на 3-4 месяца без доходов<br/><br/>Бюджет: не тратьте больше, чем планируете, лучше экономьте дополнительно<br/><br/>Долги: уровень выплат по кредитам не больше 40% доходов<br/><br/>Доходы: обеспечьте минимум разницу в 10% между доходами и расходами<br/><br/>Будьте настойчивы<br/><br/>",
                            1 => "Вам финансовое состояние стабильное<br/><br/>Ищите резервы для улучшения ситуации<br/><br/>Проанализируйте основные  датчики:<br/><br/>Деньги: важно иметь страховой запас денег минимум на 4-5 месяца без доходов<br/><br/>Бюджет: не тратьте больше, чем планируете, лучше экономьте дополнительно<br/><br/>Долги: уровень выплат по кредитам не больше 40% доходов<br/><br/>Доходы: обеспечьте минимум разницу в 15% между доходами и расходами<br/><br/>Будьте настойчивы<br/><br/>",
                            2 => "Вам финансовое состояние стабильное<br/><br/>Ищите резервы для улучшения ситуации до хорошего уровня<br/><br/>Проанализируйте основные  датчики:<br/><br/>Деньги: важно иметь страховой запас денег минимум на 4 месяца без доходов<br/><br/>Бюджет: не тратьте больше, чем планируете, лучше экономьте дополнительно 10% от первоначального плана<br/><br/>Долги: уровень выплат по кредитам не больше 30% доходов<br/><br/>Доходы: обеспечьте минимум разницу в 15% между доходами и расходами<br/><br/>Будьте настойчивы<br/><br/>"
                        ),
                    2 => array(
                            0 => "У Вас хорошее финансовое состояние, но не останавливайтесь на достигнутом<br/><br/>Важно чтобы ваши деньги работали, не стоит держать наличные, вкладывайте в депозиты/др<br/><br/>инструменты с учетом рейтинга надежности<br/><br/>Помните, что страховой резерв (на 6месяцев жизни без доходов) нужно размещать только в надежные инструменты с гарантированной доходностью и возвратом средств",
                            1 => "У Вас хорошее финансовое состояние, но не останавливайтесь на достигнутом<br/><br/>Важно чтобы ваши деньги работали, не стоит держать наличные, вкладывайте в депозиты/др<br/><br/>инструменты с учетом рейтинга надежности<br/><br/>Помните, что страховой резерв (на 6 месяцев жизни без доходов) нужно размещать только в надежные инструменты с гарантированной доходностью и возвратом средств",
                            2 => "У Вас хорошее финансовое состояние, но не останавливайтесь на достигнутом<br/><br/>Важно чтобы ваши деньги работали, не стоит держать наличные, вкладывайте в депозиты/др<br/><br/>инструменты с учетом рейтинга надежности<br/><br/>Помните, что страховой резерв (на 6 месяцев жизни без доходов) нужно размещать только в надежные инструменты с гарантированной доходностью и возвратом средств"
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


    /*
    *получение значения внутри границ и при необходимости нормализованного относительно 100 - если для вывода в тахометр
    */
    protected function getValueInsideBorders($forOutput)
    {
        $zoneBorders = $this->getZoneBorders();

        $minBorder = $zoneBorders[0];
        $maxBorder = $zoneBorders[self::$zonesCount];

        //гарантируем, что значение - внутри границ
        if($this->_rawValue > $maxBorder)
            $result = $maxBorder;
        else if($this->_rawValue < $minBorder)
            $result = $minBorder;
        else
            $result = $this->_rawValue;

        //нормализуем, если нужно выводить в тахометр - там шкала до 100; 100 соответствует макс. значение тахометра, 0 - минимальное
        if($forOutput)
            $result = ($result - $minBorder) / ($maxBorder - $minBorder) * self::$HUNDRED_PERCENT;

        return $result;
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
            ($this->getValueInsideBorders(false) - $leftBorder) /
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
        return self::$_advices[$this->_keyword][$this->getZoneIndex()][$this->getSubzoneIndex()];
    }


    //массив готовых результатов - для отображения на клиенте
    public function getResult()
    {
        return array('value' =>
            round($this->getValueInsideBorders(true)),
            'description' => $this->getAdvice(), 'title' => $this->getCaption());
    }
}

//основные тахометры - входные значения рассчитываются
class BaseTahometer extends Tahometer
{
    //множители для сравнения с границами зон - чтобы проще сравнивать "негативные" тахометры,
    //для которых лучшим является меньшее значение
    private static $_calculateTypes;

    //инициализация
    public static function init()
    {
        self::$_calculateTypes = array(
            Tahometer::$MONEY_KEYWORD => 'direct',
            Tahometer::$BUDGET_KEYWORD => 'negative',
            Tahometer::$LOANS_KEYWORD => 'negative',
            Tahometer::$DIFF_KEYWORD => 'over'
        );
        self::$_weights = array(
            Tahometer::$MONEY_KEYWORD => 35,
            Tahometer::$BUDGET_KEYWORD => 20,
            Tahometer::$LOANS_KEYWORD => 15,
            Tahometer::$DIFF_KEYWORD => 30
        );
    }
    
    //перегрузим, чтобы негативные тахометры перевернуть
    protected function getValueInsideBorders($forOutput)
    {
        $result = parent::getValueInsideBorders($forOutput);
        
        //для отрицательных тахометров - обратный порядок шкалы
        //todo: как бы это убрать?
        return ($forOutput && $this->getCalculateType() == 'negative') ?
            self::$HUNDRED_PERCENT - $result : $result;
    }

    //установка значения как отношения 2-х величин, используемых в данном тахометре, в процентах
    //в итоге получаем некий процент, но для разных тахометров он может считаться по-разному
    public function SetBaseValue($dividend, $divisor)
    {
        $zoneBorders = $this->getZoneBorders();
        //если не задан числитель, ставим минимальное значение данного тахометра
        if($dividend == 0)
            $baseValue = $this->getCalculateType() == 'negative' 
            ? $zoneBorders[self::$zonesCount]
            : $zoneBorders[0];

        //если не задан знаменатель, ставим максимальное значение для данного тахометра
        else if($divisor == 0)
        {
            $baseValue = $this->getCalculateType() == 'negative' 
            ? $zoneBorders[0]
            : $zoneBorders[self::$zonesCount];
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
        
        $this->_rawValue = $baseValue;
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

    //тип вычисления значения
    private function getCalculateType()
    {
        return self::$_calculateTypes[$this->_keyword];
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
        $this->_rawValue = $value;
    }
}
