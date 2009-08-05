<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс модели для управления календарём
 * @category targets
 * @author Max Kamashev (ukko) <max.kamashev@gmail.com>
 * @copyright http://home-money.ru/
 * @version SVN $Id$
 */
class Targets_Model {
    
    /**
     * Ссылка на экземпляр DBSimple
     * @var <DbSimple_Mysql>
     */
    private $db = NULL;

    /**
     * Ссылка на класс Смарти
     * @var <Smarty>
     */
    private $tpl = null;

    /**
     * Массив со ссылками на ошибки. Ключ - имя поля, значение массив текста ошибки
     * @example array('date'=>array('Не указана дата'), 'time'=> array('Не указано время'));
     * @var <array>
     */
    private $errorData = array();
    
    /**
     * Количество целей, показываемых на отдельной странице
     * @var <int>
     */
    private $limitFull = 10;

    /**
     * Количество целей, показываемых на совмёщённой странице
     * @var <int>
     */
    private $limitCommon = 5;


    /**
     * Конструктор
     * @return void
     */
    function  __construct() {
        $this->db = Core::getInstance()->db;
        $this->tpl = Core::getInstance()->tpl;
    }

    /**
     * Возвращает массив из ближайших к окончанию целей
     * @param <int> $index С какой цели начать возвращать
     * @param <int> $limit Количество лимитируемых записей возвращаемых из БД
     * @return <array> mixed
     */
    public function getLastList($index = 0, $limit = 0) {
        if ((int)$limit == 0) {
            $limit = $this->limitCommon;
        }

        if ((int)$index == 0) {
            $start = 0;
        } else {
            $start = (((int)$index - 1) * $limit);
        }
        $list = $this->db->selectPage($total, "SELECT *, 'Account' as periodic_account
            FROM target WHERE user_id = ? ORDER BY date_end ASC LIMIT ?d,?d;",
            Core::getInstance()->user->getId(), $start, $limit);
		if (!is_array($list)) $list = array();
        
        $category = Core::getInstance()->user->getUserCategory();
        foreach ($list as $key => $var) {
            $list[$key]['category'] = $category[$key]['cat_name'];
        }
        $this->tpl->assign('total', $total);
        $this->tpl->assign('index', $index);
        $this->tpl->assign('index_limit', $limit);
        return $list;
    }

    /**
     * Возвращает массив из пяти популярных целей
     * @param <int> $index С какой цели возвращать
     * @return <array> mixed
     */
    public function getPopList($index = 0) {
        $limit = $start = 0;

        if ((int)$index == 0) {
            $limit = $this->limitCommon;
            $start = 0;
        } else {
            $limit = $this->limitFull;
            $start = (((int)$index - 1) * $limit);
        }
        $list = $this->db->selectPage($total, "SELECT category_id, title, COUNT(*) AS cnt
            FROM target WHERE visible=1 GROUP BY title ORDER BY cnt DESC, title ASC LIMIT ?d, ?d;"
            , $start, $limit);
        $category = Core::getInstance()->user->getUserCategory();
        $list['cat_name'] = $category[$list['category_id']]['cat_name'];
        $this->tpl->assign('total', $total);
        $this->tpl->assign('index', $index);
        $this->tpl->assign('index_limit', $limit);
        return $list;
    }

    /**
     * Форматирует и устанавливает селектбоксы формы
     * @return void
     */
    private function _setFormSelectBoxs() {
        $this->tpl->assign('type_options', array(
            "r"=>"Расход",
            "d"=>"Доход"));
        $cat_sel = $this->tpl->get_template_vars('data');
        $tpl->assign('category_options',get_tree_select($cat_sel['category_sel']));

        $accounts = array();
        foreach ($_SESSION['user_account'] as $key => $var) {
            $accounts[$var['id']]= $var['name'];
        }

        $tpl->assign('target_account_id_options', $accounts);
    }

    /**
     * Проверяет данные и возвращает ассоциативный массив, если успешно. False - при ошибке
     * @return array mixed
     */
    public function checkData() {
        $data = array();
        $data['target'] = ABS((int)@$_POST['target']);

        if (@$_POST['type'] == 'd') {
            $data['type_sel'] = 'd';
        } else {
            $data['type_sel'] = 'r';
        }

        $data['category_sel'] = ABS((int)@$_POST['category']);
        if ($data['category_sel'] == 0) {
            $this->errors['category'] = "Категория цели";
        }

        $data['title'] = @$_POST['title'];
        if (empty($data['title'])) {
            $this->errors['title'] = "Наименование цели";
        }

        if (is_numeric((float)$_POST['amount'])) {
            $data['amount'] = (float)$_POST['amount'];
        } else {
            $data['amount'] = 0;
        }
        if ($data['amount'] <= 0) {
            $this->errors['amount'] = "Сумма";
        }

        $date_begin = explode('.', @$_POST['date_begin']);
        $data['date_begin'] = @$_POST['date_begin'];
        $data['date_begin_format'] = "{$date_begin['2']}-{$date_begin['1']}-{$date_begin['0']}";
        //if (strtotime($data['date_begin']) <= 0) {
        if (count($date_begin) < 3) {
            $this->errors['date_begin'] = "Дата начала";
        }
        $date_end = explode('.',@$_POST['date_end']);
        $data['date_end'] = @$_POST['date_end'];
        $data['date_end_format'] = "{$date_end['2']}-{$date_end['1']}-{$date_end['0']}";
        //if (strtotime($data['date_end']) <= 0) {
        if (count($date_end) < 3) {
            $this->errors['date_end'] = "Дата окончания";
        }

        $data['photo']     = html(@$_POST['photo']);
        $data['url']       = html(@$_POST['url']);
        $data['comment']   = html(@$_POST['comment']);

        $data['target_account_id_sel'] = ABS((int)@$_POST['target_account_id']);
        // Не обязательное поле

        if (isset($_POST['visible'])){
            $data['visible'] = 1;
        }else{
            $data['visible'] = 0;
        }
        return $data;
    }

    /**
     * Добавляем новую цель
     * @return 
     */
    function add() {
        $data = array();

        // Присоединение к чужой цели
        foreach ($_SESSION['user_category'] as $key => $var) {
            if (trim($var['cat_name']) == trim(@$_GET['category'])) {
                $data['category_sel'] = $var['cat_id'];
                break;
            }
        }

        $title = @$_GET['title'];
        if (!empty($title)) {
            $data['title'] = $title;
        }

        $tpl->assign("data",$data);

        $this->_setFormSelectBoxs();

        if (isset($_POST['title'])) {
            $data = $this->checkData();

            $this->tpl->assign("data",$data);
            // Если есть ошибки, или не все обязательные поля заполнены
            if (count($this->errorData) > 0) {
                $this->tpl->assign("errors", $this->errors);
                return false;
            // Добавляем цель в БД
            } else {
                $this->db->query("INSERT INTO target(`user_id`, `category_id`, `title`,
                    `type`, `amount`,`date_begin`, `date_end`, `target_account_id`, `percent_done`,
                    `forecast_done`, `visible`,`photo`,`url`, `comment`)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
                    Core::getInstance()->user->getId(), $data['category'], $data['title'], $data['type'],
                    $data['amount'], $data['start'] , $data['end'],
                    $data['account'], 0, 0, $data['visible'], $data['photo'],
                    $data['url'], $data['comment']);
                    //$target = mysql_insert_id();
                return true;
            }
        }
    }

    /**
     * Редактируем событие
     * @return <json> Массив в формате json. Если пустой, значит успешно отредактировано, если со значениями - значит ошибка. И в них содержится информация о том, что введено не верно.
     */
    function edit() {
        global $tpl, $sql_db;
        if ($target_id > 0) { //Значит мы только вызвали форму редактирования. Берём данные из БД
            $edit = $this->db->select("SELECT `id` as target_id, `user_id`, `category_id` AS category_sel,
                `title`, `type`, `amount`, DATE_FORMAT(date_begin,'%d.%m.%Y') AS `date_begin`,
                DATE_FORMAT(date_end, '%d.%m.%Y') AS `date_end`, `target_account_id` AS `target_account_id_sel`,
                `percent_done`, `forecast_done`, `visible`, `photo`, `url`, `comment`
                FROM target WHERE user_id=? AND id=?",
                $_SESSION['user']['user_id'], $target_id);
            if (count($edit) > 0) {
                $edit[0]['target_id'] = $target_id;
                $tpl->assign("data", $edit[0]);
            } else {
                header("Location: /");
                exit();
            }
            $this->_setFormSelectBoxs();
        } else {
            $data = $this->checkPostData();
            $this->_setFormSelectBoxs();
            // Если у нас есть ошибки при заполнении формы
            if (count($this->errors) > 0) {
                $tpl->assign("errors", $this->errors);
                $tpl->assign("data", $data);
            } else {
                $this->db->query("UPDATE target SET
                    `category_id`=?, `title`=?, `type`=?, `amount`=?, `date_begin`=?, `date_end`=?,
                    `visible`=?, `photo`=?, `url`=?, `comment`=? WHERE user_id=? AND id=?;",
                    $data['category_sel'], $data['title'], $data['type_sel'], $data['amount'],
                    $data['date_begin_format'], $data['date_end_format'], $data['visible'],
                    $data['photo'], $data['url'], $data['comment'], $_SESSION['user']['user_id'], $data['target_id']);

                return true;
            }
        }
        return false;
    }

    /**
     * Удаляет указанное событие
     * @return <json> Массив в формате json. Если пустой, значит успешно удалено, если со значениями - значит ошибка. И в них содержится ошибка.
     */
    function del() {
    /**
     * Ид события, которое требуется удалить
     * @var <int>
     */
        $id = (int)@$_POST['id'];

        /**
         * Ид цепочки событий. Если указано, то удаляется вся цепочка событий следующая после id, если false - то только указанное в id
         * @var <bool>
         */
        $chain = (int)@$_POST['chain'];

        // Если сказали удалить всю цепочку, вместе с указанным событием
        if ($chain > 0) {
            $this->db->query("DELETE FROM calendar WHERE (id=?d OR (chain=?d AND id > ?d)) AND user_id=?", $id,
                $chain, $id, Core::getInstance()->user->getId());
            //@TODO Добавить апдейт цепочки событий вверх, до события с указанным Id
            return '[]';
        // Если сказали удалить только выбранное событие
        } else {
            $this->db->query("DELETE FROM calendar WHERE id=?d AND user_id=?", $id, Core::getInstance()->user->getId());
            return '[]';
        }
    }
    /**
     * Обновляет статистику для указанной финансовой цели, или указанного или текущего пользователя
     * @param $target_id int
     * @param $user_id string
     * @return bool
     */
    function staticTargetUpdate ($target_id = 0, $user_id = 0) {
        if ((int)$target_id == 0) {
            trigger_error("Для обновления статистики указана не существующая цель", E_USER_WARNING);
            return false;
        }

        if ((int)$user_id == 0) {
            $user_id = $_SESSION['user']['user_id'];
        }
        $this->db->query("UPDATE target SET
            amount_done   = (SELECT SUM(money) FROM target_bill WHERE target_id = target.id LIMIT 1)
            , percent_done  = ROUND ( amount_done / (amount / 100), 2)
            , forecast_done = ROUND((DATEDIFF(ADDDATE(date_begin,(amount / (amount_done / DATEDIFF(CURRENT_DATE(), date_begin)))), date_begin) / DATEDIFF(date_end, date_begin)) * 100, 2)
            WHERE id=? AND user_id=?", $target_id, $user_id);
        return true;
    }

    /**
     * Пополняет финансовую цель, переводя в резерв субсчёта из основного счёта
     * @param $bill_id int Ид счёта
     * @param $target_id int Ид фин.цели
     * @param $user_id string Ид пользователя
     * @param $money float
     * @param $dt date
     * @return bool
     */
    public function addTargetOperation($bill_id, $target_id, $money, $comment, $date, $close) {
        if ((int)$bill_id == 0){
            trigger_error("Указанный счёт '{$bill_id}' не существует. ", E_USER_ERROR);
            return false;
        }
        if ((int)$target_id == 0) {
            trigger_error("Указанная финансовая цель '{$target_id}' не существует. ", E_USER_ERROR);
            return false;
        }
        if (!is_float($money) && (int)$money == 0 ) {
            trigger_error("Указано не число", E_USER_WARNING);
            return false;
        }
        $comment = strip_tags($comment);
        $date = explode('.', $date);
        $date = "{$date['2']}-{$date['1']}-{$date['0']}";
        $this->db->query("INSERT INTO target_bill (`bill_id`, `target_id`, `user_id`, `money`, `dt`, `comment`, `date`)
            VALUES(?,?,?,?,NOW(),?,?);",$bill_id, $target_id, $_SESSION['user']['user_id'], $money, $comment, $date);
        if (!empty($close)) {
            $this->db->query("UPDATE target SET close=1 WHERE user_id=? AND id=?", $_SESSION['user']['user_id'], $target_id);
        }
        $this->staticTargetUpdate($target_id);
        return true;
    }

    /**
     * Редактирует финансовую операцию
     * @param $target_bill_id int Ид операции фин.цели
     * @param $bill_id int Ид счёта
     * @param $target_id int ИД фин.цели
     * @param $money decimal(10,2) Деньги
     * @param $comment string Комментарий
     * @return bool
     */
    public function editTargetOperation($target_bill_id, $bill_id, $target_id, $money, $comment, $date, $close) {
        if ((int)$target_bill_id == 0) {
            trigger_error("Не верно указан ид операции финансовой цели. ". $target_bill_id, E_USER_ERROR);
            return false;
        }

        if ((int)$bill_id == 0){
            trigger_error("Указанный счёт '{$bill_id}' не существует. ", E_USER_ERROR);
            return false;
        }
        if ((int)$target_id == 0) {
            trigger_error("Указанная финансовая цель '{$target_id}' не существует. ", E_USER_ERROR);
            return false;
        }
        if (!is_float($money) && (int)$money == 0 ) {
            trigger_error("Указано не число", E_USER_WARNING);
            return false;
        }
        $comment = strip_tags($comment);
        $date = explode('.', $date);
        $date = "{$date['2']}-{$date['1']}-{$date['0']}";
        $this->db->query("UPDATE target_bill SET bill_id=?, money=?, date=?, comment=?,
            WHERE id=? AND user_id=? LIMIT 1;", $bill_id, $money, $date, $comment, $target_bill_id, $_SESSION['user']['user_id']);
        if (!empty($close)) {
            $this->db->query("UPDATE target SET close=1 WHERE user_id=? AND id=?", $_SESSION['user']['user_id'], $target_id);
        }
        return true;
    }

    /**
     * Удаляет цель пользователя и все привязанные к ней операции
     * @param $target_id int
     * @return bool
     */
    public function delTarget($target_id = 0) {
        $this->db->query("DELETE FROM target WHERE user_id=? AND id=?",
            $_SESSION['user']['user_id'], $target_id);
        $this->db->query("DELETE FROM target_bill WHERE user_id=? AND target_id=?;",
            $_SESSION['user']['user_id'], $target_id);
        return true;
    }

    /**
     * Удаляет операцию финансовой цели
     * @param $tar_oper_id int Ид операции
     * @param $tr_id int Ид финансовой цели
     * @return bool
     */
    public function delTargetOperation($tar_oper_id = 0, $tr_id = 0) {
        $this->db->query("DELETE FROM target_bill WHERE user_id=? AND id=?;", $_SESSION['user']['user_id'], $tar_oper_id);
        return $this->staticTargetUpdate($tr_id);
    }

    /**
     * Возвращает операцию по финансовой цели из базы данных
     * @param $target_id int
     * @return array mixed
     */
    public function getTargetOperation($target_id) {
        return $this->db->selectRow("SELECT tb.id, tb.user_id, tb.money, t.category_id as cat_id, '' as transfer,
            DATE_FORMAT(tb.date,'%d.%m.%Y') as date, t.id as tr_id, tb.bill_id, '' as drain, tb.comment as comment,
            t.title as title, t.close
        FROM `target_bill` tb
        LEFT JOIN target t on tb.target_id = t.id
        WHERE tb.id = ? AND tb.`user_id` = ?", $target_id, $_SESSION['user']['user_id']);
    }
}