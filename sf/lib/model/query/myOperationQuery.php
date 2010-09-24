<?php
/**
 * Запросы: таблица операций
 *
 * использовать только из OperationTable,
 * которая может определять параметры execute
 * или проксировать запрос
 *
 * все public-методы возвращают запрос, т.е. $this
 */
class myOperationQuery extends myBaseQuery
{
    private $myAccountModelAlias = null;

    /**
     * Получить альяс для объединения со счетами
     * @bug поведение со множественным джоином к модели не рассматривается
     *
     * @return  string
     */
    private function getCurrentAccountAlias()
    {
        if (!$this->myAccountModelAlias) {
            $found = false;
            // инициализировать sql-компоненты, сможем найти таблицу счетов
            // дибилизм парсить DQL каждый раз, но так в ядре Доктрины и сделано
            // лучше знать что ты делаешь, а не полагаться на магию
            // PS: Доктрина2 в стадии RC1 с грехом пополам позволит такое вытворять :-/
            $this->getSqlQuery();
            $components = $this->getQueryComponents();
            foreach ($components as $alias => $component) {
                if ($component['table']->getComponentName() === 'Account') {
                    $found = true;
                    $this->myAccountModelAlias = $alias;
                }
            }

            if (!$found) {
                throw new sfException('Не можем найти компонент запроса "Account". Для начала нужно сделать JOIN =)');
            }
        }

        return $this->myAccountModelAlias;
    }


    /**
     * Выбирать операции со связанными счетами
     *
     * @param   string  $joinAlias
     * @return  myOperationQuery
     */
    public function joinAccount($joinAlias)
    {
        $rootAlias = $this->getRootAlias();
        $this->innerJoin("{$rootAlias}.Account {$joinAlias}");

        return $this;
    }


    /**
     * Добавочное условие объединения: по ID пользователя
     *
     * @param   User    $user
     * @param   string  $joinAlias
     * @return  myOperationQuery
     */
    protected function addJoinAccountByUser(User $user, $joinAlias)
    {
        $rootAlias = $this->getRootAlias();

        $this->modifyJoinAccountCondition(sprintf("{$joinAlias}.user_id = %d", $user->getId()), $joinAlias);
        $this->andWhere("{$rootAlias}.user_id = ?", $user->getId());

        return $this;
    }


    /**
     * Модифицирует (или заменяет) условия джоина к счетам
     *
     * @param   string  $addToJoinConditions
     * @param   string  $joinAlias
     * @return  myOperationQuery
     */
    protected function modifyJoinAccountCondition($addToJoinConditions, $joinAlias)
    {
        $rootAlias = $this->getRootAlias();

        foreach ($this->_dqlParts['from'] as &$dql) {
            $trimmedDql = trim($dql);
            // по простому - ищем иннер-джоин
            if (substr($trimmedDql, 0, 10) === "INNER JOIN") {
                // среди джоинов ищем только Счета
                if (preg_match("#^\s*(INNER\s+JOIN)\s+({$rootAlias}\.Account\s+{$joinAlias})\s*(?:ON\s+)?\s*(.*)$#imx", $dql, $matches)) {
                    $conditions = trim(str_replace("\n", '', $matches['3']));
                    // модифицируем: добавляем условия
                    if ($conditions) {
                        $conditions .= " AND ({$addToJoinConditions})";
                    // модифицируем: заменяем условия на свои
                    } else {
                        $conditions = "({$addToJoinConditions})";
                    }

                    // перезаписываем DQL-часть по ссылке прямо в массиве
                    $dql = sprintf("%s %s ON %s", $matches['1'], $matches['2'], $conditions);
                }
            }
        }

        return $this;
    }


    /**
     * Добавить в выборку сумму операций по счету с учетом переводов
     *
     * @param   string  $sumAlias
     * @return  myOperationQuery
     */
    protected function selectSumOperationByAccount($sumAlias)
    {
        $rootAlias = $this->getRootAlias();
        $joinAlias = $this->getCurrentAccountAlias();

        $this->addSelect("SUM(
            CASE
                WHEN {$rootAlias}.account_id = {$joinAlias}.account_id THEN {$rootAlias}.money
                WHEN IFNULL({$rootAlias}.transfer_amount, 0) = 0 THEN ABS({$rootAlias}.money)
                ELSE {$rootAlias}.transfer_amount
            END
            ) {$sumAlias}");

        return $this;
    }


    /**
     * Добавить группировку по ID счета при объединении с таблицей счетов
     *
     * @return  myOperationQuery
     */
    protected function groupByAccount()
    {
        $joinAlias = $this->getCurrentAccountAlias();
        $this->addGroupBy("{$joinAlias}.id");

        return $this;
    }


    /**
     * Выбрать только операции за период
     * периодом может быть текущий месяц или n-месяцев
     *
     * @param   mixed   $months     период в месяцах|null|true
     * @return  myOperationQuery
     */
    protected function filterByPeriod($months = null)
    {
        $rootAlias = $this->getRootAlias();

        //определим интервал, за который взять операции, если он задан
        if (isset($months)) {
            if($months > 0) { //отсчитываем заданное количество месяцев
                $value = $months;
                $type = "MONTH";
            } else { //внутри текущего месяца - сбросим дни до первого
                $value = "DAYOFMONTH(CURDATE()) - 1";
                $type = "DAY";
            }

            $this->andWhere("{$rootAlias}.date >= DATE_SUB(
                CURDATE(),
                INTERVAL {$value} {$type}
            )");
        }

        return $this;
    }


    /**
     * Выбрать суммы операций с параметрами и фильтрами
     *
     * @param   string  $joinAlias
     * @param   string  $sumAlias
     * @return  myOperationQuery
     */
    protected function makeAggregateAccountQuery(User $user, $joinAlias, $sumAlias)
    {
        $rootAlias = $this->getRootAlias();

        $this->joinAccount($joinAlias)
            ->addJoinAccountByUser($user, $joinAlias)

            ->selectSumOperationByAccount($sumAlias)
            ->addSelect("{$joinAlias}.type_id")
            ->addSelect("{$joinAlias}.currency_id")
            ->addSelect("{$rootAlias}.account_id")

            ->andWhere("{$rootAlias}.accepted = ?", Operation::STATUS_ACCEPTED)

            ->groupByAccount();

        return $this;
    }

}
