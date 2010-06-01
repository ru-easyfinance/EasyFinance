<?php

/**
 * Гидратор: возвращает массив, где ключ - это значение первой колонки, а
 * значение - это значение второй колонки
 */
class Doctrine_Hydrator_FetchPair extends Doctrine_Hydrator_Abstract
{
    public function hydrateResultSet($stmt)
    {
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }
}
