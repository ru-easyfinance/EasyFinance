<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс модели для управления тегами
 * @category tags
 * @author Max Kamashev (ukko) <max.kamashev@gmail.com>
 * @copyright http://home-money.ru/
 * @version SVN $Id$
 */
class Tags_Model {
    /**
     * Ссылка на экземпляр DBSimple
     * @var DbSimple_Mysql
     */
    private $db = NULL;

    /**
     * Конструктор
     * @return void
     */
    function  __construct() {
        $this->db = Core::getInstance()->db;
    }

    /**
     * @return json 
     */
    function add() {

    }

    /**
     * @return json 
     */
    function edit() {

    }

    /**
     * @return json 
     */
    function del() {

    }

    /**
     * Возвращает массив тегов с количеством их повторов (если указано)
     * @param bool $cloud Возвращать статистику для составления облака?
     * @return JSON
     */
    function getTags($cloud = true) {
        if ($cloud) {
            $sql = "SELECT name, COUNT(name) FROM tags WHERE user_id = ? GROUP BY name ORDER BY COUNT(name) DESC";
            return json_encode($this->db->select($sql, Core::getInstance()->user->getId()));
        } else {
            $sql = "SELECT name FROM tags WHERE user_id = ? GROUP BY name ORDER BY name";
            return json_encode($this->db->selectCol($sql, Core::getInstance()->user->getId()));
        }
    }
}