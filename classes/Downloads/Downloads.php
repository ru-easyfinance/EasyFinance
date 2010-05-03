<?php
/**
 * Класс для учёта статистики загрузок
 */
class Downloads
{
    /**
     * Записывает в базу
     * 
     * @param User $user
     * @param string $type 'amt' или 'book'
     * @param
     */
    static public function write(User $user, $type)
    {
        $sql = "INSERT INTO downloads(`user_id`, `type`) VALUES(?, ?)";
        return Core::getInstance()->db->query($sql, $user->getId(), $type);
    }
}