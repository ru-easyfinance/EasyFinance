<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс модели для управления тегами
 * @category tags
 * @author Max Kamashev (ukko) <max.kamashev@gmail.com>
 * @copyright http://easyfinance.ru/
 * @version SVN $Id$
 */
class Tags_Model {
    /**
     * Ссылка на экземпляр DBSimple
     * @var DbSimple_Mysql
     */
    private $db = NULL;

    /**
     * Массив с ошибками
     * @var array
     */
    private $errors = array();


    /**
     * Конструктор
     * @return void
     */
    function  __construct() {
        $this->db = Core::getInstance()->db;
    }

    /**
     * Возвращает массив с ошибками
     * @return array
     */
    function getErrors()
    {
        return $this->errors;
    }

    /**
     * Добавляет новый тег
     * @FIXME Оптимизировать хранение тегов в БД
     * @param string $tag
     * @return array
     */
    function add ( $tag )
    {
        if ( empty( $tag ) ) {
            $this->errors[] = 'Тег не должен быть пустым';
            return false;
        }
        $sql = "INSERT INTO tags VALUES(? ,0, ?)";
        $this->db->query( $sql, Core::getInstance()->user->getId(), $tag );

        Core::getInstance()->user->initUserTags();
        Core::getInstance()->user->save();

        return Core::getInstance()->user->getUserTags();
    }

    /**
     * Редактирует тег
     * @param string $tag
     * @param string $old_tag
     * @return array
     */
    function edit ( $tag = '', $old_tag = '' )
    {
        if ( empty( $tag ) || empty( $old_tag ) ) {

            $this->errors[] = 'Теги не должны быть пустыми';
            return false;

        } else {

            //@TODO Оптимизировать запросы
            $sql = "UPDATE operation o SET tags = REPLACE(tags, ?, ?) WHERE user_id=?";
            $this->db->query( $sql, $old_tag, $tag, Core::getInstance()->user->getId() );
            $sql = "UPDATE tags SET name= ? WHERE name=? AND user_id=?";
            $this->db->query( $sql, $tag, $old_tag, Core::getInstance()->user->getId() );

            Core::getInstance()->user->initUserTags();
            Core::getInstance()->user->save();

            return Core::getInstance()->user->getUserTags();
        }
    }

    /**
     * Удаляет тег
     * @param string $tag
     * @return json
     */
    function del ( $tag = '' )
    {

        if ( empty( $tag ) ) {

            $this->errors[] = 'Тег не должен быть пустым';
            return false;

        }

        $sql = "DELETE FROM tags WHERE name=? AND user_id=?";
        $this->db->query( $sql, $tag, Core::getInstance()->user->getId() );

        $sql = "UPDATE operation o SET tags = REPLACE(tags, ?, '') WHERE user_id=?";
        $this->db->query( $sql, $tag, Core::getInstance()->user->getId() );

        Core::getInstance()->user->initUserTags();
        Core::getInstance()->user->save();

        return Core::getInstance()->user->getUserTags();

    }

    /**
     * Возвращает массив тегов с количеством их повторов (если указано)
     * @param bool $cloud Возвращать статистику для составления облака?
     * @return array
     */
    function getTags ( $cloud = true )
    {
        if ( $cloud ) {

            $sql = "SELECT name, COUNT(name) as cnt
                FROM tags
                WHERE user_id = ?
                GROUP BY name ORDER BY COUNT(name) DESC";
            return $this->db->select( $sql, Core::getInstance()->user->getId() );

        } else {

            $sql = "SELECT name
                FROM tags
                WHERE user_id = ?
                GROUP BY name ORDER BY name";
            return $this->db->selectCol( $sql, Core::getInstance()->user->getId() );

        }
    }
}