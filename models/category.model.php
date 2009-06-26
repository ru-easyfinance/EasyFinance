<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс-модель для управления категориями пользователя
 * @author korogen
 * @author Max Kamashev (ukko) <max.kamashev@gmail.com>
 * @copyright http://home-money.ru/
 * SVN $Id$
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
     *
     * @var array mixed
     */
    public $tree_sum_categories = array();

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
        $_SESSION['tree_sum_categories'] = $this->tree_sum_categories;
    }

    /**
     * Загружаем кэш с категориями
     * @return void
     */
    private function loadCache()
    {
        //FIXME Наверняка тут надо загружать данные из базы, если сессия пуста
        $this->tree = $_SESSION['categories'];
        $this->tree_sum_categories = $_SESSION['tree_sum_categories'];
    }

    /**
     * Загружает системные категории для пользователя
     * @return void
     */
    private function loadSystemCategories()
    {
        $this->system_categories = $this->db->select("SELECT * FROM system_categories WHERE parent_id = 0");
    }

    /**
     * Получает всё дерево категорий определнного пользователя.
     */
    public function loadUserTree()
    {
        //FIXME сделать проверку переменной $_SESSION['categories_filtr'];
        $where = $_SESSION['categories_filtr'];
        $forest = $this->db->select("SELECT c.*, c.cat_id AS ARRAY_KEY, c.cat_parent AS PARENT_KEY,
            sc.system_category_name FROM category c
                LEFT JOIN system_categories sc ON sc.system_category_id = c.system_category_id
                WHERE c.user_id = ? ".$where." AND c.cat_active=1 ORDER BY cat_name", $this->user_id);
        $this->tree = $forest;
        $this->saveCache();
    }

    /**
     *
     * @param $sys_currency
     * @param $date
     * @return unknown_type
     */
    public function loadSumCategories($sys_currency, $date="")
    {
        //FIXME Проверить переменную $date, а лучше переписать этот участок
        //TODO Переписать математические расчёты в функции, на использование BCMATH
        $sql = "SELECT SUM( m.money ) AS `sum`, m.cat_id, b.bill_currency FROM money m
            LEFT JOIN bill b ON b.bill_id = m.bill_id AND b.user_id = m.user_id
            WHERE m.user_id = ? ".$date." GROUP BY m.cat_id, b.bill_currency";
        $rows = $this->db->select($sql, $this->user_id);
        $cnt = count($row);
        for ($i=0; $i<$cnt; $i++)
        {
            $id = $rows[$i]['cat_id'];
            for ($j=0; $j<$cnt; $j++) {
                if ($id == $rows[$j]['cat_id']) {
                    if ($rows[$j]['currency'] != 1) {
                        $sum = $rows[$j]['sum'] * $sys_currency[$rows[$j]['bill_currency']];
                    }

                    $forest[$id]['sum'] = $forest[$id]['sum'] + $sum;

                    $drain = 0;

                    if ($forest[$id]['sum'] < 0) {
                        $drain = 1;
                        $forest[$id]['sum'] = $forest[$id]['sum'] * -1;
                    }

                    $forest[$id]['drain'] = $drain;
                }
            }
        }

        // считаем общую сумму
        if (!empty($forest)) {
            foreach($forest as $key=>$value) {
                if ($value['drain'] == 0) {
                    $this->total_sum_categories['income'] = $this->total_sum_categories['income'] + $value['sum'];
                } else {
                    $this->total_sum_categories['outcome'] = $this->total_sum_categories['outcome'] + $value['sum'];
                }
            }
        }

        // высчитываем процентное отношение и форматируем сумму
        if (!empty($forest)) {
            foreach($forest as $key=>$value) {
                if ($value['sum'] > 0) {
                    if ($value['drain'] == 0) {
                        $forest[$key]['percent'] = $value['sum'] * 100 / $this->total_sum_categories['income'];
                        $forest[$key]['drain'] = 0;
                    }else{
                        $forest[$key]['percent'] = $value['sum'] * 100 / $this->total_sum_categories['outcome'];
                        $forest[$key]['drain'] = 1;
                    }
                }else{
                    $forest[$key]['percent'] = 0;
                }
                $forest[$key]['sum'] = number_format($value['sum'], 2, '.', ' ');
                $forest[$key]['percent'] = number_format($forest[$key]['percent'], 0, '.', ' ');
            }
        }

        // вставляем дополнительные параметры в основной массив категорий
        foreach ($this->tree as $key=>$value)
        {
            $this->tree[$key]['sum'] = 0;
            $this->tree[$key]['percent'] = 0;
            $this->tree[$key]['drain'] = 1;

            if (!empty($forest))
            {
                foreach ($forest as $f_key => $f_value)
                {
                    if ($key == $f_key)
                    {
                        $this->tree[$key]['sum'] = $f_value['sum'];
                        $this->tree[$key]['percent'] = $f_value['percent'];
                        $this->tree[$key]['drain'] = $f_value['drain'];
                    }
                }
            }
            if (!empty($value['childNodes']))
            {
                foreach ($value['childNodes'] as $c_key=>$c_value)
                {
                    $this->tree[$key]['childNodes'][$c_key]['sum'] = 0;
                    $this->tree[$key]['childNodes'][$c_key]['percent'] = 0;
                    $this->tree[$key]['childNodes'][$c_key]['drain'] = 1;

                    if (!empty($forest))
                    {
                        foreach ($forest as $f_key => $f_value)
                        {
                            if ($c_key == $f_key)
                            {
                                $this->tree[$key]['childNodes'][$c_key]['sum'] = $f_value['sum'];
                                $this->tree[$key]['childNodes'][$c_key]['percent'] = $f_value['percent'];
                                $this->tree[$key]['childNodes'][$c_key]['drain'] = $f_value['drain'];
                            }
                        }
                    }
                }
            }
        }
        $this->saveCache();
    }

    /**
     * Создаёт новую категорию
     * @return bool
     */
    public function createNewCategory($category)
    {
        //XXX Почему бы массив с ключами и значениями не передавать прямиком в эту функцию?
        $sql = "INSERT INTO category(?#) VALUES(?a)";
        if (!$this->db->query($sql, array_keys($category), array_values($category))) {
            return false;
        }
        return true;
    }

    /**
     * Возвращает выбранную категорию пользователя
     * @param $id int Ид выбранной категории
     * @return array mixed
     */
    public function selectCategoryId($id)
    {
        $sql = "SELECT * FROM category WHERE cat_id = ? and user_id = ?";
        return $this->db->selectRow($sql, $id, $this->user_id);
    }

    /**
     * Обновляет категорию
     * @param $category array mixed
     * @return bool
     */
    public function updateCategory($category)
    {
        $sql = "UPDATE category SET ?a WHERE cat_id = ?d";
        if (!$this->db->query($sql, $category, $category['cat_id'], $this->user_id)) {
            return false;
        }

        if ($category['type'] != 2) {
            $sql = "UPDATE category SET `type` = ? WHERE cat_parent = ?";
            $this->db->query($sql, $category['type'], $category['cat_id']);
        }
        return true;
    }

    /**
     * Устанавливает видимость категории
     * @param $id int Ид категории
     * @param $visible int Видимость категории (1 - видна, 0 - скрыта)
     * @return bool
     */
    public function visibleCategory($id, $visible)
    {
        $id = (int)$id;
        $visible = (int)$visible;

        $sql = "UPDATE category SET visible=? WHERE cat_id = ? and user_id = ?";
        if (!$this->db->query($visible, $id, $this->user_id)) {
            return false;
        }

        $parent_info = $this->getParentInfo($id);

        if ($parent_info[0]['cat_id'] == $id) {
            $sql = "UPDATE category SET visible=? WHERE cat_parent = ? and user_id = ?";
            $this->db->query($sql, $visible, $id, $this->user_id);
        }

        return true;
    }

    /**
     * Удаляет указанную категорию
     * @param $id int Ид категории
     * @return bool
     */
    public function deleteCategory($id)
    {
        $sql = "UPDATE category SET cat_active = '0' WHERE cat_parent = ? AND user_id = ?";
        if (!$this->db->query($sql, $id, $this->user_id)) {
            return false;
        }
        return true;
    }

    /**
     * Получить информацию из родительской категории
     * @param $id int Ид категории
     * @return array mixed
     */
    private function getParentInfo($id)
    {
        //FIXME Переписать, на получение данных с сессии, а не с SQL
        $row = $this->db->selectRow("SELECT * FROM category WHERE cat_id = ?", $id);

        if ($row['cat_parent'] != 0) {
            $row = $this->db->select("SELECT * FROM category WHERE cat_id = ?", $row['cat_parent']);
        }
        return $row;
    }
}