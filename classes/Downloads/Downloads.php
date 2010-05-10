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
     * @param string $info Расширенная информация
     * @return void
     */
    static public function write(User $user, $type, $info='')
    {
        $sql = "INSERT INTO downloads(`user_id`, `type`, `dt`, `info`) VALUES(?, ?, NOW(), ?)";
        return Core::getInstance()->db->query($sql, $user->getId(), $type, $info);
    }
}