<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс модели для внутренней почты
 * @copyright http://home-money.ru/
 * @version SVN $Id: $
 */

class Mail_Model extends Template_Controller
{

    /**
     * ID пользователя
     * @var int
     */
    private $user_id = null;

    /**
     * ссылка на экземпляр класса для работы с бд
     * @var DBSimple
     */
    private $db = null;

    /**
     * письма пользователя
     * @var array
     */
    private $mails = null;

    /**
     * Конструктор класса
     * @return void
     */
    function __construct()
    {
        $this->db = Core::getInstance()->db;
        $this->user_id = Core::getInstance()->user->getId();
    }

    /**
     * обязательно при подключении класса!!!
     * @return void
     */
    function index()
    {
        $sql = "SELECT 
                    *
                FROM
                    mail
                WHERE
                    `from`=? OR `to`=?";
        $this->mails = $this->db->select($sql,$this->user_id,$this->user_id);
    }
        /**
     * отдаёт список писем,при необходимости формирует его
     * @return json список писем
     */
    function mail_list()
    {
        if (!$this->mails)
            $this->index();

        return $this->mails;
    }
    /**
     * добавляет письмо в базу данных
     * @return json ид вставленного письма
     */
    function add_mail($param)
    {
        if (!$this->mails)
            $this->index();
            
        $text = $param['text'];
        $category = $param['category'];
        $title = $param['title'];
        $to = $param['to'];
        $from = $this->user_id;
        $sql = "INSERT INTO
                    mail
                    (`to`, `from`, `text`, `category`, `title`, a_vis, t_vis, is_new)
                VALUES
                    (?,?,?,?,?,'1','1','1')";
        $this->db->query($sql, $to, $from, $text, $category, $title);
        $id = mysql_insert_id();

        $this->mails[$id]['to'] = $to;
        $this->mails[$id]['from'] = $from;
        $this->mails[$id]['text'] = $text;
        $this->mails[$id]['category'] = $category;
        $this->mails[$id]['title'] = $title;

        return  $id;
    }
    /**
     * удаляет письмо из базы данных
     * @return 1 успешность операции
     */
    function del_mail($param)
    {
        if (!$this->mails)
            $this->index();
            
        $id = $param['id'];

        unset($this->mails[$id]);

        $author = $param['to'];
        if ($author == $this->user_id)
            $field = 'a_vis';//от кого
        else
            $field = 't_vis';//к кому
            
        if ($id)
        {
            $sql = "UPDATE mail SET $field='0' WHERE `id`=?;";
            return $this->db->query($sql, $id);
        }
        else
        {
        return 1;
        } 
    }

    /**
     * помечает письмо как прочитанное
     * @return 1 успешность операции
     */
    function read_mail()
    {
        if (!$this->mails)
            $this->index();

        $id = $param['id'];
        $this->mails[$id]['is_new']=0;

        if ($id)
        {
            $sql = "UPDATE mail SET is_new='0' WHERE `id`=?;";
            $this->db->query($sql, $id);
        }
        return 1;
    }
}