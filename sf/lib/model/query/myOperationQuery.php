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

}
