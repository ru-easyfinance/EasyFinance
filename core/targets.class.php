<?php
/**
 * Класс для работы с Финансовыми целями
 * @package      home-money.ru
 * @author       Max Kamashev <max.kamashev@floscoeli.com>
 * @version      SVN: $Id$
 *
 * vim: set ts=4 sw=4 tw=0:
 */
class TargetsClass {

    /**
     * Ссылка на экземпляр БД
     * @var DbSimple_Generic_Database
     */
    private $db = NULL;

    /**
     * Количество целей, показываемых на отдельной странице
     * @var int
     */
    private $limitFull = 10;

    /**
     * Количество целей, показываемых на совмёщённой странице
     * @var int
     */
    private $limitCommon = 5;

    /**
     * Хранит массив всех ошибок, при создании цели или её редактировании
     * @var array string
     */
    private $errors = array();
    /**
     * Конструктор
     * @param DbSimple_Generic_Database $db
     * @return void
     */
    public function __construct(DbSimple_Generic_Database $db = null) {
        $this->db = $db;
    }

    /**
     * Возвращает массив из ближайших к окончанию целей
     * @param $index int С какой цели начать возвращать
     * @param $limit int Количество лимитируемых записей возвращаемых из БД
     * @return array mixed
     */
    public function getLastList($index = 0, $limit = 0) {
        global $tpl;

        if ($limit == 0) {
            $limit = $this->limitCommon;
        }

        if ($index == 0) {
            $start = 0;
        } else {
            $start = (($index - 1) * $limit);
        }
        $list = $this->db->selectPage($total, "SELECT *, 'Account' as periodic_account
            FROM target WHERE user_id = ? ORDER BY date_end ASC LIMIT ?d,?d;",
            $_SESSION['user']['user_id'], $start, $limit);
        foreach ($list as $key => $var) {
            $list[$key]['category'] = $_SESSION['user_category'][$var['category_id']]['cat_name'];
        }
        $tpl->assign('total', $total);
        $tpl->assign('index', $index);
        $tpl->assign('index_limit', $limit);
        return $list;
    }

    /**
     * Возвращает массив из пяти популярных целей
     * @param $index int С какой цели возвращать
     * @return array mixed
     */
    public function getPopList($index = 0) {
        global $tpl;

        if ($index == 0) {
            $limit = $this->limitCommon;
            $start = 0;
        } else {
            $limit = $this->limitFull;
            $start = (($index - 1) * $limit);
        }
        $list = $this->db->selectPage($total, "SELECT category_id, (SELECT cat_name FROM category WHERE cat_id=category_id) AS category, title, COUNT(*) AS cnt
            FROM target WHERE visible=1 GROUP BY title ORDER BY cnt DESC, title ASC LIMIT ?d, ?d;"
            , $start, $limit);
        $tpl->assign('total', $total);
        $tpl->assign('index', $index);
        $tpl->assign('index_limit', $limit);
        return $list;
    }

    /**
     * Форматирует и устанавливает селектбоксы формы
     * @return void
     */
    private function _setFormSelectBoxs() {
        global $tpl;
        $tpl->assign('type_options', array(
            "r"=>"Расход",
            "d"=>"Доход"));
        $cat_sel = $tpl->get_template_vars('data');
        $tpl->assign('category_options', get_three_select($_SESSION['user_category'], 0, $cat_sel['category_sel']));

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
    public function checkPostData() {
        $data = array();
        $data['target_id'] = ABS((int)@$_POST['target_id']);

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
     * Добавить новую цель
     * @return bool
     */
    public function addTarget() {
        global $tpl, $sql_db;
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
            $data = $this->checkPostData();

            $tpl->assign("data",$data);
            // Если есть ошибки, или не все обязательные поля заполнены
            if (count($this->errors) > 0) {
                $tpl->assign("errors", $this->errors);
                return false;
            // Добавляем цель в БД
            } else {
                $this->db->query("INSERT INTO target(`user_id`, `category_id`, `title`,
                    `type`, `amount`,`date_begin`, `date_end`, `target_account_id`, `percent_done`,
                    `forecast_done`, `visible`,`photo`,`url`, `comment`)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
                    $_SESSION['user']['user_id'], $data['category_sel'], $data['title'], $data['type_sel'],
                    $data['amount'],$data['date_begin_format'] , $data['date_end_format'],
                    $data['target_account_id_sel'], 0, 0, $data['visible'], $data['photo'],
                    $data['url'], $data['comment']);
                $target_id = mysql_insert_id();

                return true;
            }
        }
    }

    /**
     * Редактирует финансовую цель
     * @param $target_id int
     * @return bool
     */
    public function editTarget($target_id = 0) {
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

        if ($user_id == 0) {
            $user_id = $_SESSION['user']['user_id'];
        }
        //FIXME Дописать обновление прогноза
        $this->db->query("UPDATE target SET
            amount_done   = (SELECT SUM(money) FROM target_bill WHERE target_id = target.id LIMIT 1)
            , percent_done  = ROUND ( amount_done / (amount / 100), 2)
            , forecast_done = 0
            WHERE id=? AND user_id=?;", $target_id, $user_id);
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
    public function addTargetOperation($bill_id, $target_id, $money, $comment, $date) {
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
    public function editTargetOperation($target_bill_id, $bill_id, $target_id, $money, $comment) {
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
    }

    /**
     * Удаляет цель пользователя
     * @param $target_id int
     * @return bool
     */
    public function delTarget($target_id = 0) {
        $this->db->query("DELETE FROM target WHERE user_id=? AND id=?", $_SESSION['user']['user_id'], $target_id);
        $this->db->query("DELETE FROM target_bill WHERE AND target_id=?;", $target_id);
        return true;
    }
}