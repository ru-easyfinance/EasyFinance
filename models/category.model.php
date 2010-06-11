<?php if (!defined('INDEX')) trigger_error("Index required!", E_USER_WARNING);

/**
 * Класс-модель для управления категориями пользователя
 * @author Max Kamashev (ukko) <max.kamashev@gmail.com>
 * @category category
 * @copyright http://easyfinance.ru/
 * @version SVN $Id$
 */
class Category_Model {

    /**
     * Ссылка на экземпляр DBSimple
     * @var DbSimple_Mysql
     */
    private $db = NULL;

    /**
     * Ид текущего пользователя
     * @var int
     */
    private $user_id = NULL;

    /**
     * Системные категории
     * @var array mixed
     */
    public $system_categories = array();

    /**
     * Дерево категорий
     * @var array mixed
     */
    public $tree = array();

    /**
     * Конструктор
     * @return void
     */
    public function __construct()
    {
        $this->db = Core::getInstance()->db;
        $this->user_id = Core::getInstance()->user->getId();
        $this->loadSystemCategories();
        $this->loadCache();
        if (!count($this->tree)) {
            $this->loadUserTree();
        }
    }

    /**
     * Сохраняем кэш c категориями
     * @return void
     */
    private function saveCache()
    {
        $_SESSION['categories'] = $this->tree;
    }

    /**
     * Загружаем кэш с категориями
     * @return void
     */
    private function loadCache()
    {
        //@FIXME Наверняка тут надо загружать данные из базы, если сессия пуста
        $this->tree = @$_SESSION['categories'];
    }

    /**
     * Загружает все системные категории
     * @return void
     */
    private function loadSystemCategories()
    {
        //@TODO Добавить кэширование
        $this->system_categories = array();
        $array = $this->db->select("SELECT * FROM system_categories ORDER BY name");
        foreach ($array as $val) {
            $this->system_categories[$val['id']] = array(
                'id'   => $val['id'],
                'name' => $val['name']
            );
        }
    }

    /**
     * Получает всё дерево категорий определённого пользователя.
     */
    public function loadUserTree()
    {
        $where = array_key_exists('categories_filtr', $_SESSION)?$_SESSION['categories_filtr']:null;

        $forest = $this->db->select("SELECT c.*, c.cat_id AS ARRAY_KEY, c.cat_parent AS PARENT_KEY,
            sc.name FROM category c
                LEFT JOIN system_categories sc ON sc.id = c.system_category_id
                WHERE c.user_id = ? " . $where . " AND c.deleted_at IS NULL ORDER BY cat_name", Core::getInstance()->user->getId());
        $this->tree = $forest;
        $this->saveCache();
    }

    /**
     * Добавляет новую категорию
     * @param string $name
     * @param int $parent
     * @param int $system
     * @param int $type
     * @return bool
     */
    function add($name, $parent, $system, $type)
    {
        $sql = "INSERT INTO category(user_id, cat_parent, system_category_id, cat_name, type, custom,
            created_at, updated_at) VALUES(?, ?, ?, ?, ?, 1, NOW(), NOW())";
        $newID = $this->db->query($sql, Core::getInstance()->user->getId(), $parent, $system, $name, $type);
        Core::getInstance()->user->initUserCategory();
        Core::getInstance()->user->save();

        return $newID;
    }

    /**
     * Редактирует категорию
     * @param int       $id
     * @param string    $name
     * @param int       $parent
     * @param int       $system
     * @param int       $type
     * @return array mixed
     */
    function edit($id, $name, $parent, $system, $type)
    {
        $cat = Core::getInstance()->user->getUserCategory();

        // Проверяем тип родительской и дочерней категории
        if ($parent > 0) {
            if ($cat[$parent]['type'] == - 1 && $type == 1) {
                return array('error' => array(
                        'text' => 'Ошибка! Нельзя кардинально менять типы дочерних категорий'
            ));
            } elseif ($cat[$parent]['type'] == - 1 && $type == 0) {
                return array('error' => array(
                        'text' => 'Ошибка! Нельзя кардинально менять типы дочерних категорий'
            ));
            } elseif ($cat[$parent]['type'] == 1 && $type == - 1) {
                return array('error' => array(
                        'text' => 'Ошибка! Нельзя кардинально менять типы дочерних категорий'
            ));
            } elseif ($cat[$parent]['type'] == 1 && $type == 0) {
                return array('error' => array(
                        'text' => 'Ошибка! Нельзя кардинально менять типы дочерних категорий'
            ));
            }
        }

        // Если это пользовательская категория
        if ($cat[$id]['custom'] == 1) {
            $sql = "UPDATE category SET cat_parent = ?, system_category_id = ? , cat_name = ?, type =?, updated_at = NOW()
                WHERE user_id = ? AND cat_id = ?";
            $result = $this->db->query($sql, $parent, $system, $name, $type,
                            Core::getInstance()->user->getId(), $id);
            // Системная категория, можно изменить только имя
        } else {
            $catname = $this->db->query('SELECT cat_name FROM category WHERE user_id=? AND
                cat_id=?', Core::getInstance()->user->getId(), $id);
            if ($catname[0]['cat_name'] == $name)
                return array('error' => array(
                        'text' => 'Категория не была изменена'
            ));
            $sql = "UPDATE category SET cat_name = ?, updated_at = NOW() WHERE user_id = ? AND cat_id = ?";
            $result = $this->db->query($sql, $name, Core::getInstance()->user->getId(), $id);
        }

        if ($result) {
            Core::getInstance()->user->initUserCategory();
            Core::getInstance()->user->save();
            return array('result' => array('text' => ''));
        } else {
            return array('error' => array(
                    'text' => 'Ошибка при редактировании категории'
        ));
        }
    }

    /**
     * Удаляет выбранную категорию (и все подкатегории, если это родительская категория)
     * @param int $id
     * @return array
     */
    function del($id = 0)
    {
        // Удаляет (скрывает) категорию, заодно и дочерние (если есть)
        $sql = "
            UPDATE category
                SET updated_at=NOW(), deleted_at=NOW()
            WHERE
                    user_id=?
                AND (cat_id=? OR cat_parent=?)
        ";
        $this->db->query($sql, Core::getInstance()->user->getId(), $id, $id);

        //@FIXME Починить удаление операций по категории
        Core::getInstance()->user->initUserCategory();
        Core::getInstance()->user->save();
        return array('result' => array('text' => 'Категория успешно удалена'));
    }

    /**
     * Возвращает список категорий пользователя и системные в виде массива
     * @return array
     */
    function getCategory()
    {
        // Массив для пользовательских категорий
        $users = array();

        // Массив для систменых категорий
        $systems = $this->system_categories;
        $systems[0] = array('id'=>'0', 'name'=>'Не установлена');

        // Сортировка для хрома #886
        //$key = 0;

        foreach (Core::getInstance()->user->getUserCategory() as $category) {

            //$key++;
            //$users[ $key ] = array

            $users[ $category['cat_id'] ] = array (
                'id'      => $category['cat_id'],
                'parent'  => $category['cat_parent'],
                'system'  => $category['system_category_id'],
                'name'    => $category['cat_name'],
                'type'    => $category['type'],
                'visible' => (int)((bool)$category['deleted_at']),
                'custom'  => $category['custom']
            );
        }

        return array(
            'user'   => $users,
            'system' => $systems
        );
    }
}
