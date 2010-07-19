<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Расчет тахометров
 */
class Info_Model
{
    public function __construct(oldUser $user = null)
    {
        Tahometer::init();
    }

    /**
     * Возвращает информацию для тахометров в виде массива с пояснениями
     * @return array mixed
     */
    public function get_data()
    {
        $this->init();

        //получим исходные данные для расчетов
        //$this->GetBaseData();

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
        $this->totalTahometer->SetRawValue($totalValue);
    }

    //исходные данные для расчета
    //значения могут быть нужны только для отладки (при закомментированном вызове GetBaseData):

    //доходы за месяц
    private $_oneMonthProfit = 50000;

    //расходы за текущий месяц
    private $_currentMonthDrain = 15000;

    //выплаты по долгам за месяц
    private $_oneMonthCreditPayments = 25000;

    //плановые расходы на текущий месяц
    private $_currentMonthBudget = 45000;

    //текущий остаток доступных денег
    private $_currentRealMoneyBalance = 3000;

    //доходы за 3 месяца
    private $_threeMonthProfit = 135000;

    //расходы за 3 месяца
    private $_threeMonthDrain = 100000;

        //все числовые константы не имеют какого-либо "управляющего" значения - просто данные
    private static $MONEY_DEFAULT_VALUE = 5;
    private static $BUDGET_DEFAULT_VALUE = 100;
    private static $LOANS_DEFAULT_VALUE = 100;
    private static $DIFF_DEFAULT_VALUE = 20;

    //рассчитываем основные тахометры, уже получив данные
    private function CalculateBaseTahometers()
    {
        //деньги
        $this->tahometersByKeywords[Tahometer::$MONEY_KEYWORD]->SetRawValue(
            $this->calculateTahometerValue(
                $this->_currentRealMoneyBalance,
                $this->_threeMonthDrain,
                self::$MONEY_DEFAULT_VALUE)
        );

        $this->tahometersByKeywords[Tahometer::$BUDGET_KEYWORD]->SetRawValue(
            $this->calculateTahometerValue(
                $this->_currentMonthDrain,
                $this->_currentMonthBudget,
                self::$BUDGET_DEFAULT_VALUE) * 100
        );

        $this->tahometersByKeywords[Tahometer::$LOANS_KEYWORD]->SetRawValue(
            $this->calculateTahometerValue(
                $this->_oneMonthCreditPayments,
                $this->_oneMonthProfit,
                self::$LOANS_DEFAULT_VALUE) * 100
        );

        $this->tahometersByKeywords[Tahometer::$DIFF_KEYWORD]->SetRawValue(
            ($this->calculateTahometerValue(
                $this->_threeMonthProfit,
                $this->_threeMonthDrain,
                self::$DIFF_DEFAULT_VALUE)-1)*100
        );
    }

    private function calculateTahometerValue($dividend, $divisor, $defaultValue)
    {
        return $dividend == 0 ? 0 :
            ($divisor == 0 ? $defaultValue : $dividend / $divisor);
    }

    /*
    *типы запросов
    */
    private static $PROFIT_QUERY = 'PROFIT';
    private static $DRAIN_QUERY = 'DRAIN';
    private static $CREDIT_QUERY = 'CREDIT';
    private static $BALANCE_QUERY = 'BALANCE';


    /*
    * получение исходных данных
    */
    private function GetBaseData()
    {
        //получаем каждый из показателей; показатели, получаемые как сумма за определенный период,
        //корректируем с учетом стажа в системе: если получаем за 3 месяца, а в системе мы - 1,5 месяца, нужный показатель умножить на 2


        //доходы за месяц
        $this->oneMonthProfit = $this->GetOperationsSum(
            self::$PROFIT_QUERY, 1);

        //расходы за текущий месяц
        $this->currentMonthDrain = $this->GetOperationsSum(
            self::$DRAIN_QUERY, 0);

        //доходы за 3 месяца
        $this->threeMonthProfit = $this->GetOperationsSum(
            self::$PROFIT_QUERY, 3);

        //расходы за 3 месяца
        $this->threeMonthDrain = $this->GetOperationsSum(
            self::$DRAIN_QUERY, 3);

        //текущий остаток доступных денег - сумма всех операций за все время, включая начальные остатки
        //по денежным счетам и кредитным картам с положительным остатком
        $this->currentRealMoneyBalance = $this->GetOperationsSum(
            self::$BALANCE_QUERY, NULL);

        //выплаты по долгам за месяц
        //пока считаем только как переводы на долговые счета
        //затем добавятся расходы по соотв. спец. категориям
        $this->oneMonthCreditPayments = $this->GetOperationsSum(
            self::$CREDIT_QUERY, 1);

        //плановые расходы на текущий месяц
        $this->currentMonthBudget = $this->GetDrainBudget();
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
                $money = new efMoney($sumByAccount[Tahometer::$MONEY_KEYWORD], $sumByAccount['currency_id']);
                $accountResult = $this->getCurrencyExchanger()->convert($money, $baseCurrency)->getAmount();

                $resultSum = $resultSum + $accountResult;
            }
        }

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

        //признак отбора начальных остатков
        $needSelectStartBalances = false;

        //добавочные условия

        //признаки использования счетов-отправителей
        $useSenders = true;

        $additionalCondition = null;

        //доп. условия зависят от типа запроса
        switch($queryType)
        {
            case self::$PROFIT_QUERY:
                //нужны только доходные, без начальных остатков
                $operationType = '1';
                break;

            case self::$DRAIN_QUERY:
                //нужны только расходные, без начальных остатков
                $operationType = '0';
                break;

            //долгами пока считаем только переводы на долговые счета
            case self::$CREDIT_QUERY:
                //нужны только 1)переводы 2)на 3)долговые счета - займы, кредиты и кредитки
                $operationType = '2';
                $useSenders = false;
                $additionalCondition = "type_id IN (7, 8, 9)";
                break;

            case self::$BALANCE_QUERY:
                //для баланса "живых" денег берем все операции, кроме переводов, и начальные остатки
                //берем только денежные счета и кредитки с положительным балансом
                $operationType = '0,1';
                $needSelectStartBalances = true;
                $additionalCondition = "type_id IN (1,2,5,15,16) OR (type_id = 8 AND money > 0)";
                break;
            }

        $query = $this->getQueryByConditions($operationType, $needSelectStartBalances, $months, $useSenders, $additionalCondition);

        return $query;
    }

    //формирование строки запроса по условиям
    private function getQueryByConditions($operationType, $needSelectStartBalances, $months, $useSenders, $additionalCondition)
    {
        //задаем все подготовленные условия: типы операций, начальный остаток, интервал, дополнительные условия
        //выбираем с привязкой к счетам
        //всегда выбираем подтвержденные операции
        //операции перевода входят в разные счета с разным знаком
        $query = "
            SELECT
                SUM(CASE
                        #если расход или 'перевод с' - минус
                        WHEN op.type = 0 OR op.type = 2 AND op.account_id = acc.account_id THEN -ABS(op.money)

                        #иначе (доход или 'перевод на') - плюс
                        ELSE ABS(op.money)
                    END) AS money,
                acc.account_type_id AS type_id, acc.account_currency_id AS currency_id
            FROM
                operation op
            INNER JOIN
                accounts acc
            ON (";

        if($useSenders)
            $query = $query . "op.account_id = acc.account_id OR ";

        $query = $query . "op.transfer_account_id = acc.account_id)" .
            "WHERE op.user_id = ". $this->user()->getId() ." AND op.accepted = 1 ";

        $query = $query . " AND op.type IN (" . $operationType . ")";

        //условия на начальный остаток
        if(!$needSelectStartBalances)
        {
            $query = $query . " AND op.comment NOT LIKE 'Начальный остаток'";
        }

        //определим интервал, за который взять операции, если он задан
        if(isset($months))
        {
            if($months > 0) //отсчитываем заданное количество месяцев
            {
                $subIntervalValue = $months;
                $subIntervalType = "MONTH";
            }
            else //внутри текущего месяца - сбросим дни до первого
            {
                $subIntervalValue = "DAYOFMONTH(CURDATE()) - 1";
                $subIntervalType = "DAY";
            }

            $query = $query . " AND op.date >= DATE_SUB(CURDATE(), INTERVAL " . $subIntervalValue . " " . $subIntervalType . ")";
        }

        //группируем по счетам
        $query = $query . " GROUP BY acc.account_id";

        //учтем добавочное условие
        if(isset($additionalCondition))
            $query = $query . " HAVING " . $additionalCondition;

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

        $daysInInterval = $this->db()->selectCell(
            "SELECT DATEDIFF('" . $intervalEnd->format('Y-m-d'). "', '" . $intervalStart->format('Y-m-d'). "')");

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
            $daysQuery = "SELECT DATEDIFF(CURDATE(), IFNULL(mindate, CURDATE())) FROM (SELECT MIN(op.date) AS mindate FROM operation op WHERE op.comment NOT LIKE 'Начальный остаток' AND op.user_id = " . $this->user()->getId() . ") AS tbl";

            //добавим единицу, чтобы учесть сегодняшний день
            //например, если вчера была первая операция, то стаж = 2 дням
            $this->_experienceDaysValue = $this->db()->selectCell($daysQuery) + 1;
        }

        return $this->_experienceDaysValue;
    }

    private function GetDrainBudget()
    {
        //возвращаем просто сумму запланированных категорий расходов текущего месяца
        //не паримся о валюте, т.к. бюджет пока планируем в базовой, и считаем, что тахометры получаем в базовой

        $query = "
            SELECT SUM(amount)
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
        $this->totalTahometer = new Tahometer(Tahometer::$TOTAL_KEYWORD);
        $this->tahometersByKeywords = array(
                //Деньги
                Tahometer::$MONEY_KEYWORD => new Tahometer(Tahometer::$MONEY_KEYWORD),
                //Бюджет
                Tahometer::$BUDGET_KEYWORD => new Tahometer(Tahometer::$BUDGET_KEYWORD),
                //Долги
                Tahometer::$LOANS_KEYWORD => new Tahometer(Tahometer::$LOANS_KEYWORD),
                //Доходы vs Расходы
                Tahometer::$DIFF_KEYWORD => new Tahometer(Tahometer::$DIFF_KEYWORD)
            );
    }
}

/*
*класс "Тахометр" для более удобной работы с тахометрами
*/
class Tahometer
{
    public static $MONEY_KEYWORD = 'money';
    public static $BUDGET_KEYWORD = 'budget';
    public static $LOANS_KEYWORD = 'loans';
    public static $DIFF_KEYWORD = 'diff';
    public static $TOTAL_KEYWORD = 'total';

    private $_keyword;

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
        self::$_weights = array(
            Tahometer::$MONEY_KEYWORD => 35,
            Tahometer::$BUDGET_KEYWORD => 20,
            Tahometer::$LOANS_KEYWORD => 15,
            Tahometer::$DIFF_KEYWORD => 30
        );

        self::$_benefitCoefficients = array(
            Tahometer::$MONEY_KEYWORD => 1,
            Tahometer::$BUDGET_KEYWORD => -1,
            Tahometer::$LOANS_KEYWORD => -1,
            Tahometer::$DIFF_KEYWORD => 1,
            Tahometer::$TOTAL_KEYWORD => 1
        );

        self::$_zoneBorders = array(
            Tahometer::$MONEY_KEYWORD => array(0, 2, 5, 6),
            Tahometer::$BUDGET_KEYWORD => array(100, 97, 85, 0),
            Tahometer::$LOANS_KEYWORD => array(100, 70, 40, 0),
            Tahometer::$DIFF_KEYWORD => array(0, 5, 10, 20),
            Tahometer::$TOTAL_KEYWORD => array(0,100,150,300)
        );

        self::$_advices = array(
            Tahometer::$MONEY_KEYWORD => array(
                'Запас денег на нуле. Будьте осторожны в своих расходах.',
                'Достаточная финансовая обеспеченность и есть пути для повышения.',
                'У Вас солидный запас денег. Вам стоит задуматься об инвестициях.'),
            Tahometer::$BUDGET_KEYWORD => array(
                'Деньги исчезают в неизвестном направлении. Планируйте расходы.',
                'Вы неплохо управляете расходами. Но постарайтесь быть экономней.',
                'Вы умеете планировать и экономить. Так держать!'),
            Tahometer::$LOANS_KEYWORD => array(
                'Вы утратили финансовую независимость. Сократите Ваши кредиты.',
                'Ваша долговая нагрузка в норме. Не злоупотребляйте кредитованием.',
                'У Вас высокий уровень финансовой независимости.'),
            Tahometer::$DIFF_KEYWORD => array(
                'Вам следует либо больше зарабатывать, либо меньше тратить!',
                'Хорошее соотношение, но есть резерв для улучшений!',
                'Вы близки к достижению финансовой свободы!'),
            Tahometer::$TOTAL_KEYWORD => array(
                'Вам грозит банкротство. Срочно измените свой подход к финансовым вопросам!',
                'Неплохо. но если изменить  подход к ведению дел, финансовое состояние можно существенно улучшить.',
                'У вас все хорошо, но не останавливайтесь на достигнутом. Ваше финансовое состояние можно существенно улучшить!'),
        );
    }


    private function getCaption()
    {
        return self::$_captions[$this->_keyword];
    }

    private function getWeight()
    {
        return self::$_weights[$this->_keyword];
    }

    private function getBenefitCoefficient()
    {
        return self::$_benefitCoefficients[$this->_keyword];
    }

    //веса тахометров
    private static $_weights;

    //множители для сравнения с границами зон - чтобы проще сравнивать "негативные" тахометры,
    //для которых лучшим является меньшее значение
    private static $_benefitCoefficients;

    //количество зон
    private static $zonesCount = 3;

    //границы зон
    private static $_zoneBorders;

    private function getZoneBorders()
    {
        return self::$_zoneBorders[$this->_keyword];
    }

    //советы, отображаемые пользователю
    private static $_advices;

    //Вычисленное значение
    private $_rawValue;

    public function SetRawValue($value)
    {
        $this->_rawValue = $value;
    }

    /*
    *получение значения внутри границ и при необходимости нормализованного относительно 100
    */
    private function getValueInsideBorders($normalize)
    {
        $zoneBorders = $this->getZoneBorders();

        $minBorder = $zoneBorders[0];
        $maxBorder = $zoneBorders[self::$zonesCount];

        //гарантируем, что значение - внутри границ
        if($this->compareWithBorder($maxBorder) > 0)
            $result = $maxBorder;
        else if($this->compareWithBorder($minBorder) < 0)
            $result = $minBorder;
        else
            $result = $this->_rawValue;

        //нормализуем, если нужно
        if($normalize)
            $result = ($result - $minBorder) / ($maxBorder - $minBorder) * 100;

        return $result;
    }

    //сравнение значения с границей с учетом коэффициента "пользы" тахометра
    //для вредных тахометров "зоны" уменьшаются, поэтому их сравниваем с коэффициентом -1
    private function compareWithBorder($border)
    {
        $valueToCompare = $this->_rawValue * $this->getBenefitCoefficient();
        $borderToCompare = $border * $this->getBenefitCoefficient();

        if($valueToCompare == $borderToCompare)
            return 0;
        else return $valueToCompare > $borderToCompare ? 1 : -1;
    }

    public function __construct($keyword)
    {
        $this->_keyword = $keyword;
    }

    private $_zoneIndex;

    private function getZoneIndex()
    {
        if(!isset($this->_zoneIndex))
        {
            //вычислим, в какую зону входит значение тахометра
            $tempZoneIndex = 0;

            //перебираем все зоны, пока проходим в следующую зону

            $zoneBorders = $this->getZoneBorders();

            while($tempZoneIndex < self::$zonesCount - 1
                && $this->compareWithBorder($zoneBorders[$tempZoneIndex+1]) > 0)
                        $tempZoneIndex++;

            $this->_zoneIndex = $tempZoneIndex;
        }

        return $this->_zoneIndex;
    }

    private function getAdvice()
    {
        return self::$_advices[$this->_keyword][$this->getZoneIndex()];
    }

    public function getWeightedValue()
    {
        $zoneBorders = $this->getZoneBorders();

        //точное значение - индекс зоны + положение внутри зоны:
        //при этом берем не нормализованное значение, а взвешенное по зонам
        $exactValue = $this->getZoneIndex() +
            ($this->getValueInsideBorders(false) - $zoneBorders[$this->getZoneIndex()]) /
                ($zoneBorders[$this->getZoneIndex() + 1] - $zoneBorders[$this->getZoneIndex()]);

        //наконец, установим взвешенное значение
        return $exactValue * $this->getWeight();
    }

    //массив готовых результатов - для отображения на клиенте
    public function getResult()
    {

        return array('value' =>
            //берем нормализованное исходное значение, чтобы вывести исходное, но внутри границ счетчика (0-100)
            round($this->getValueInsideBorders(true)),
            'description' => $this->getAdvice(), 'title' => $this->getCaption());
    }
}
