<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс-модель для управления категориями пользователя
 * @author korogen
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
        //@FIXME Наверняка тут надо загружать данные из базы, если сессия пуста
        $this->tree = @$_SESSION['categories'];
        $this->tree_sum_categories = @$_SESSION['tree_sum_categories'];
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
        //@FIXME сделать проверку переменной $_SESSION['categories_filtr'];
        $where = $_SESSION['categories_filtr'];

        $forest = $this->db->select("SELECT c.*, c.cat_id AS ARRAY_KEY, c.cat_parent AS PARENT_KEY,
            sc.name FROM category c
                LEFT JOIN system_categories sc ON sc.id = c.system_category_id
                WHERE c.user_id = ? ".$where." AND c.cat_active=1 ORDER BY cat_name", Core::getInstance()->user->getId());
        $this->tree = $forest;
        $this->saveCache();
    }

    /**
     *
     * @param $sys_currency
     * @param $date
     * @return unknown_type
     */
    public function loadSumCategories($sys_currency, $start, $finish)
    {
        return '';
        //TODO Переписать математические расчёты в функции, на использование BCMATH
        if (!empty ($start)) {
            $param = "AND o.date BETWEEN '{$start}' AND '{$finish}'";
        }

        $sql = "SELECT SUM( o.money ) AS `sum`, o.cat_id, a.account_currency_id AS cur
            FROM operation o
            LEFT JOIN accounts a ON a.account_id = o.account_id AND a.user_id = o.user_id
            WHERE o.user_id = ? {$param} GROUP BY o.cat_id, a.account_currency_id";
        
        $accounts = Core::getInstance()->user->getUserAccounts();
        $rows = $this->db->select($sql, $this->user_id);
        
        $array = array();
        foreach ($rows as $val) {
            // Общая сумма
            if ($val['cur'] != 1) { // Конвертируем валюту в рубли
                $array[$val['cat_id']]['sum'] = (int)@$val[$val['cat_id']]['sum'] + ($val['sum'] * Core::getInstance()->currency[$val['cur']]['value']);
            } else { // Конкретно сумма по валюте
                $array[$val['cat_id']]['sum'] = (int)@$val[$val['cat_id']]['sum'] + $val['sum'];
            }
        }

        // считаем общую сумму
        if ( !empty($forest) ) {
            foreach( $forest as $key => $value ) {
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
            dt_create) VALUES(?, ?, ?, ?, ?, 1, NOW())";
        $newID = $this->db->query($sql, Core::getInstance()->user->getId(), $parent, $system, $name, $type);
        Core::getInstance()->user->initUserCategory();
        Core::getInstance()->user->save();
        return array(
            'result' => array(
                'text' => '',
                'id'   => $newID
             )
        );
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
            if ( $cat[$parent]['type'] == -1 && $type == 1 ) {
                return array('error' => array(
                    'text' => 'Ошибка! Нельзя кардинально менять типы дочерних категорий'
                ));
            } elseif ( $cat[$parent]['type'] == -1 && $type == 0 ) {
                return array('error' => array(
                    'text' => 'Ошибка! Нельзя кардинально менять типы дочерних категорий'
                ));
            } elseif ( $cat[$parent]['type'] == 1 && $type == -1 ) {
                return array('error' => array(
                    'text' => 'Ошибка! Нельзя кардинально менять типы дочерних категорий'
                ));
            } elseif ( $cat[$parent]['type'] == 1 && $type == 0 ) {
                return array('error' => array(
                    'text' => 'Ошибка! Нельзя кардинально менять типы дочерних категорий'
                ));
            }
        }

        // Если это пользовательская категория
        if ($cat[$id]['custom'] == 1) {
            $sql = "UPDATE category SET cat_parent = ?, system_category_id = ? , cat_name = ?, type =?
                WHERE user_id = ? AND cat_id = ?";
            $result = $this->db->query($sql, $parent, $system, $name, $type,
                Core::getInstance()->user->getId(), $id);
        // Системная категория, можно изменить только имя
        } else {
            $catname = $this->db->query('SELECT cat_name FROM category WHERE user_id=? AND
                cat_id=?', Core::getInstance()->user->getId(), $id);
            if ( $catname[0]['cat_name'] == $name)
                return array('error' => array(
                    'text' => 'Категория не была изменена'
                ));
            $sql = "UPDATE category SET cat_name = ? WHERE user_id = ? AND cat_id = ?";
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
     */
    function del($id = 0)
    {
        $sql = "UPDATE category SET visible=0 WHERE user_id=? AND ( cat_id=? OR cat_parent=? ) ";
        $this->db->query($sql, Core::getInstance()->user->getId(), $id, $id);//удаляет категорию, заодно и дочерние
        
//        $sql = "UPDATE operation SET visible=0 WHERE cat_id=? AND user_id=?";
//        $this->db->query($sql, $id, Core::getInstance()->user->getId()); //удаляет все операции по удаляемой категории.
        //@FIXME Починить удаление операций по категории
        Core::getInstance()->user->initUserCategory();
        Core::getInstance()->user->save();
        return array('result' => array('text' => ''));
    }

    /**
     * возвращает html строку для селекта в зависимости от типа категории
     * @deprecated WTF ???
     */
    function cattype($type){
        return get_tree_select2(0, $type);
    }

    /**
     * Возвращает список категорий пользователя и системные
     * @param date $start
     * @param date $finish
     * @return array mixed
     */
    function getCategory($start = null, $finish = null)
    {   
        if (!$start) {
            $start = date("Y-m-d", mktime(0, 0, 0, date("m"), "01", date("Y")));
        }
        if (!$finish) {
            $finish = date("Y-m-d", mktime(0, 0, 0, date("m")+1, "01", date("Y")));
        }
    
        $sum = $this->loadSumCategories($sys_currency, $start, $finish);

        $users = array();
        
        foreach (Core::getInstance()->user->getUserCategory() as $val) {
            $users[$val['cat_id']] = array(
                'id'      => $val['cat_id'],
                'parent'  => $val['cat_parent'],
                'system'  => $val['system_category_id'],
                'name'    => $val['cat_name'],
                'type'    => $val['type'],
                'visible' => $val['visible'],
                'custom'  => $val['custom']
            );
            if ($val['cat_id'] == $sum['cat_id']) {
                $users[$val['cat_id']]['summ'] = $sum['sum'];
            }
        }
        $systems = $this->system_categories;
        $systems[0] = array('id'=>'0','name'=>'Не установлена');

        return array(
            'user'=>$users,
            'system'=>$systems
        );
        
    }
}