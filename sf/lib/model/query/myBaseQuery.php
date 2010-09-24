<?php
/**
 * Базовый класс запросов
 *
 * @see http://www.doctrine-project.org/projects/orm/1.2/docs/manual/configuration/en#configure-query-class
 * @see http://brentertainment.com/2010/03/03/doctrine_query_extra-extending-the-doctrine-query-object/
 * @see http://hudson.su/2010/02/05/optimize-your-doctrine-with-specialized-queries/
 */
class myBaseQuery extends Doctrine_Query
{
    /**
     * @see     Doctrine_Query_Abstract::_processWhereIn
     */
    protected function _processHavingIn($expr, $params = array(), $not = false)
    {
        $params = (array) $params;

        // if there's no params, return (else we'll get a HAVING IN (), invalid SQL)
        if (count($params) == 0) {
            throw new Doctrine_Query_Exception('You must pass at least one parameter when using an IN() condition.');
        }

        $a = array();
        foreach ($params as $k => $value) {
            if ($value instanceof Doctrine_Expression) {
                $value = $value->getSql();
                unset($params[$k]);
            } else {
                $value = '?';
            }
            $a[] = $value;
        }

        $this->_params['having'] = array_merge($this->_params['having'], $params);

        return $expr . ($not === true ? ' NOT' : '') . ' IN (' . implode(', ', $a) . ')';
    }


    /**
     * Добавляет условие IN() в HAVING часть запроса
     *
     * @see     Doctrine_Query_Abstract::whereIn
     */
    public function havingIn($expr, $params = array(), $not = false)
    {
        // if there's no params, return (else we'll get a WHERE IN (), invalid SQL)
        if (isset($params) and (count($params) == 0)) {
            return $this;
        }

        return $this->_addDqlQueryPart('having', $this->_processHavingIn($expr, $params, $not), true);
    }

}
