<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Модель для управления периодическими транзакциями
 * @author Max Kamashev (ukko) <max.kamashev@gmail.com>
 * @category periodic
 * @copyright http://home-money.ru/
 * @version SVN $Id: periodic.model.php 299 2009-08-26 18:12:03Z ukko $
 */
class Periodic_Model
{

    /**
     * Ссылка на экземпляр DBSimple
     * @var DbSimple_Mysql
     */
    private $db = null;

    /**
     * Массив с ошибками
     * @var array mixed
     */
    public $error = null;

    /**
     * Конструктор
     * @return void
     */
    function __construct()
    {
        $this->db = Core::getInstance()->db;
        $this->user = Core::getInstance()->user;
    }

    /**
     * Возвращает массив со всеми периодическими транзакциями пользователя
     * @return array mixed
     */
    function getList()
    {
        $sql = "SELECT id, category, account, drain, title, DATE_FORMAT(date,'%d.%m.%Y') AS `date`,
                    `amount`, type_repeat AS `repeat`, count_repeat AS `counts`, `comment`,
                    `infinity` FROM periodic  WHERE user_id = ?";
        $array = $this->db->select($sql, Core::getInstance()->user->getId());
        $ret = array();
        foreach ($array as $val) {
            if ($val['drain'] == 1) {
                $type = -1;
            } else {
                $type = 0;
            }
            $ret[$val['id']] = array(
                'id'        => $val['id'],
                'category'  => $val['category'],
                'account'   => $val['account'],
                'type'      => $type,
                'title'     => $val['title'],
                'date'      => $val['date'],
                'amount'    => $val['amount'],
                'repeat'    => $val['repeat'],
                'counts'    => $val['counts'],
                'comment'   => $val['comment'],
                'infinity'  => $val['infinity']
            );
        }
        return $ret;
    }

    /**
     * Добавляет новую периодическую транзакцию
     * @return void
     */
    function add($account, $amount, $category, $comment, $counts, $date, $infinity, $repeat, $title, $drain)
    {
        $sql = "INSERT periodic(user_id, category, account, drain, title, date, amount, type_repeat,
            count_repeat, comment, dt_create, infinity) VALUES(?,?,?,?,?,?,?,?,?,?,NOW(),?)";
        $last_id = $this->db->query($sql, Core::getInstance()->user->getId(), $category, $account, $drain,
            $title,$date, $amount, $repeat, $counts, $comment, $infinity);
        $this->addEvents($last_id, $amount, $comment, $counts, $date, $infinity, $repeat, $title, $drain);
        return array();
    }

    /**
     * Удаляет периодическую транзакцию
     * @param int $id
     */
    function del($id = 0) 
    {
        $sql = "DELETE FROM periodic WHERE user_id=? AND id=?";
        $this->db->query($sql, Core::getInstance()->user->getId(), $id);
        // @FIXME Дописать удаление транзакций из календаря
        return array();
    }

    /**
     * Редактирует периодическую транзакцию
     * @return void
     */
    function edit($id, $account, $amount, $category, $comment, $counts, $date, $infinity, $repeat, $title, $drain)
    {
        $sql = "UPDATE periodic SET category = ?, account = ?, drain = ?, title = ?, date = ?, amount = ?, 
            type_repeat = ?, count_repeat = ?, comment = ?, infinity = ? WHERE id = ? AND user_id = ?";
        $this->db->query($sql, $category, $account, $drain, $title, $date, $amount, $repeat, $counts,
            $comment, $infinity, $id, Core::getInstance()->user->getId());
        //@FIXME Добавить обновление транзакций
        return array();
    }

    /**
     * Проверяет корректность данных
     * @return array mixed
     */
    function checkData()
    {
        $this->error = array();
        $array = array();

        $array['account'] = abs((int)@$_POST['account']);
        if ($array['account'] <= 0) {
            $this->error['account'] = 'Необходимо указать счёт для транзакции';
        }

        $array['amount'] = abs((int)@$_POST['amount']);
        if ($array['amount'] == 0) {
            $this->error['amount'] = 'Указанная сумма не должна быть нулём';
        }

        $array['category'] = abs((int)@$_POST['category']);
        if ($array['category'] == 0) {
            $this->error['category'] = 'Необходимо указать категорию';
        }

        $array['comment'] = htmlspecialchars(@$_POST['comment']);

        $array['counts'] = abs((int)@$_POST['counts']);

        $array['date'] = formatRussianDate2MysqlDate(@$_POST['date']);
        if (!$array['date']) {
            $this->error['date'] = 'Необходимо правильно указать дату';
        }

        if (abs((int)@$_POST['infinity']) == 0) {
            $array['infinity'] = 1;
        } else {
            $array['infinity'] = 0;
        }
        
        $array['repeat'] = abs((int)@$_POST['repeat']);

        $array['title'] = htmlspecialchars(@$_POST['title']);
        if (empty($array['title'])) {
            $this->error['title'] = 'Необходимо заполнить заголовок';
        }

        if ((int)@$_POST['type'] < 0) {
            $array['drain'] = 1;
        } else {
            $array['drain'] = 0;
        }
        return $array;
    }

    private function addEvents($id, $amount, $comment, $counts, $date, $infinity, $repeat, $title, $drain) {
        // Если у нас есть повторения события, то добавляем и их тоже
        if ($repeat == 1) {
            $period = "DAY";
        } elseif ($repeat == 7) {
            $period = "WEEK";
        } elseif ($repeat == 30) {
            $period = "MONTH";
        } elseif ($repeat == 90) {
            $period = 'QUARTER';
        } elseif ($repeat == 365) {
            $period = 'YEAR';
        } else {
            return false;
        }

        if ($drain == 1) {
            $amount = abs($amount) * -1;
        } else {
            $amount = abs($amount);
        }

        $sql = '';

        if ($infinity == 1) {
            $counts = 90;
        }
        for ($i = 0; $i <= $counts ; $i++) {
            if (!empty ($sql)) { $sql .= ','; }
            $sql .= "('".Core::getInstance()->user->getId()."','".addslashes($title)."','{$date}',
                '{$repeat}','{$counts}','".addslashes($comment)."', NOW(), ".
                "ADDDATE('{$date}', INTERVAL {$i} {$period}),'','per','{$id}','{$amount}')";

        }
        if (!empty($sql) or $period = 0) {
            $this->db->query("INSERT INTO calendar (`user_id`,`title`,`start_date`,`type_repeat`,".
                "`count_repeat`, `comment`, `dt_create`, `near_date`,`week`,`event`,`chain`,`amount`) VALUES "
                    . $sql);
        }
        return true;
    }
}