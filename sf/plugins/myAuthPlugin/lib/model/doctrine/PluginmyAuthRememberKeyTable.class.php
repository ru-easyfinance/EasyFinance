<?php

/**
 * Таблица: myAuthRememberKey
 */
class PluginmyAuthRememberKeyTable extends Doctrine_Table
{
    # TODO
    # Svel: таблицу пользователя и названия колонок в настройки
    /**
     * Запрос выборки по уникальному ключу
     *
     * @param  string  $key
     * @param  string  $alias
     * @return Doctrine_Query
     */
    public function findWithUserByRememberKey($key, $alias = 'k')
    {
        return $this->createQuery($alias)
            ->select("{$alias}.*, u.*")
            ->innerJoin("{$alias}.User u")
            ->where("{$alias}.remember_key = ?", $key);
    }


    /**
     * Убить все устаревшие ключи
     *
     * @param  DateTime  $expire
     * @return Doctrine_Query
     */
    public function removeOldKeys(DateTime $expire = null)
    {
        return $this->createQuery('k')
            ->delete()
            ->where('k.created_at < ?', $expire->format(DATE_ISO8601));
    }


    /**
     * Убить все ключи пользователя
     *
     * @param  int  $userId
     * @return Doctrine_Query
     */
    public function removeKeysByUserId($userId)
    {
        return $this->createQuery('k')
            ->delete()
            ->where('k.user_id = ?', $userId);
    }

}
