<?php
/**
 * Класс для страницы welcome
 * @copyright http://home-money.ru/
 * SVN $Id$
 */
class Welcome_Model extends Template_Model {

   /**
     * Возвращает количество активных пользователей
     * return int
     */
    function getCountusers ()
    {
        return $this->db->selectCell("SELECT count(user_id) FROM users WHERE user_active='1';");
    }

    /**
     * Возвращает количество всех денег
     * return int
     */
    function getAllTransaction ()
    {
        return $this->db->selectCell("SELECT count(money) FROM money;");
    }

    /**
     * Возвращает список статей
     * @return array mixed
     */
    function getAtricles ()
    {
        $row = $this->db->query("SELECT title, id FROM articles ORDER BY `date` DESC LIMIT 0,5");
    }

}