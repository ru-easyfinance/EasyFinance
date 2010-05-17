<?php
/**
 * Класс для учёта статистики
 */
class Logs
{
    /**
     * Записывает в базу
     * 
     * @param User $user
     * @param string $type 'amt' или 'book'
     * @param string $info Расширенная информация
     * @return bool
     */
    static public function write(User $user, $type, $info='')
    {
        $sql = "INSERT INTO logs(`user_id`, `type`, `dt`, `info`) VALUES(?, ?, NOW(), ?)";
        return (bool)Core::getInstance()->db->query($sql, $user->getId(), $type, $info);
    }
}
