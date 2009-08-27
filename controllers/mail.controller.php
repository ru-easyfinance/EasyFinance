<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля почты
 * @copyright http://home-money.ru/
 * @version SVN $Id: $
 */

class Mail_Controller extends Template_Controller
{
    /**
     * Ссылка на класс модель
     * @var Mail_Model
     */
    private $model = null;

    /**
     * Конструктор класса
     * @return void
     */
    function __construct()
    {

        $this->model = new Mail_Model();
    }

    /**
     * обязательно при подключении класса!!!(ускоряет работу с ним)
     * @return void
     */
    function index()
    {
        $this->model->index();
    }

    /**
     * отдаёт список писем,при необходимости формирует его
     * @return json список писем
     */
    function mail_list()
    {
        die(json_encode($this->model->mail_list()));
    }

    /**
     * добавляет письмо в базу данных
     * @return json ид вставленного письма
     */
    function add_mail()
    {
        $param['text']=htmlspecialchars($_POST['text']);
        $param['category']=htmlspecialchars($_POST['category']);
        if(!$param['category'])
            die();
        $param['title']=htmlspecialchars($_POST['title']);
        $param['to']=(int)$_POST['to'];
        $param['date'] = formatRussianDate2MysqlDate($_POST['date']);
        die(json_encode($this->model->add_mail($param)));
    }

    /**
     * удаляет письмо из базы данных
     * @return 0 1 успешность операции
     */
    function del_mail()
    {
        $param['id']=$_POST['id'];
        $param['to']=$_POST['to'];
        die($this->model->del_mail($param));
    }

    /**
     * помечает письмо как прочитанное
     * @return 0 1 успешность операции
     */
    function read_mail()
    {
        $param['id']=$_POST['id'];
        die($this->model->read_mail($param));
    }

}
