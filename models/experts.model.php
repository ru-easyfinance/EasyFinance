<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс модели для управления экспертами
 * @copyright http://easyfinance.ru/
 * SVN $Id: accounts.model.php 258 2009-08-24 16:40:55Z rewle $
 */
class Experts_Model
{
    /**
     * Ссылка на экземпляр DBSimple
     * @var DbSimple_Mysql
     */
    private $db = NULL;

    /**
     * проверка пользователя на эксперта
     * @var int
     */
    private $is_expert = 0;
    /**
     * Ид текущего пользователя
     * @var int
     */
    private $user_id = NULL;

    /**
     * Конструктор
     * @return void
     */
    public function __construct()
    {
        $this->db = Core::getInstance()->db;
        $this->user_id = Core::getInstance()->user->getId();
    }

    /////////////////////////////////system funct//////////////////////////////////
    /**
     * Функция которая состовляет список используемых скриптов.
     * производит дробление на пользователя и эксперта.
     * @return array
     */
    public function js_list()
    {
        $js_list=array('main');
        if ($this->is_expert)
        {
            $js_list[]='expert';
        }
        else
        {
            $js_list[]='user';
        }
        return $js_list;
    }
    /**
     * Возвращает нужный "рабочий стол".
     * Использует сессии.
     * Используется только как переменная смарти
     * @return 'expert' or 'user'
     */
    public function get_desktop()
    {
        $ret = ($_SESSION['user']['user_type'] == 1)?'expert':'user';
        return $ret;
    }

    public function index()
    {
        $this->is_expert = $_SESSION['user']['user_type'] == 1;
        return true;
    }

    ////////////////////////////////experts///////////////////////////////////

    public function get_desktop_field($field_id)//update info into panel//only experts
    {
        switch($field_id)
        {
            case '0'://expert info
                $sql = "SELECT * FROM experts, experts_service, services WHERE id=?";
                return $row = $this->db->selectRow($sql, $this->user_id);
                break;
            default:
                die(0);
                break;
        }
    }
    /*
     * редактирует таблицу экспертов
     */
    public function update_expert($param)
    {
        $upd_arr = array();
        $set = '';

        foreach ($param as $key=>$val)
        {
            if (!is_array($val))
                $set .= " `$key`='$val',";
            else
                $upd_arr[] = $val;
        }

        $set = substr($set, 0, -1);
        $sql = "UPDATE experts SET $set WHERE id=?;";

        $sql ="DELETE FROM experts_servic WHERE id=?;";

        $val_arr = array();
        foreach ($upd_arr as $k=>$v)
        {
            foreach ($upd_arr[$k] as $key=>$val)
            {
                $val_arr[$k].="$val,";
            }
            $val_arr[$k]= substr($val_arr[$k], 0, -1);
        }

        $keys=implode(',',array_keys($val_arr));
        $vals=implode('),(',array_values($val_arr));

        $sql = "INSERT INTO experts ($keys)VALUES($vals) WHERE id=?;";

    }

    //////////////////////////////////////////////////////////////////////////

    public function get_experts_list($order)//expert light info//only user
    {
        $sql = "SELECT experts.id, min_desc, user_name  FROM
                    experts
                LEFT JOIN
                    users
                ON
                    experts.id = users.id
                ORDER BY
                    ?";
        return $rows = $this->db->select($sql, $order);
    }
    public function get_expert($expert_id)
    {
        return 'stop';
    }
}