<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля "фидбека"
 * @copyright http://home-money.ru/
 * @version SVN $Id: $
 */

class Feedback_Controller extends Template_Controller
{
    /**
     * Ссылка на класс модель
     * @var <Accounts_Model>
     */
    private $model = null;

    /**
     * Конструктор класса
     * @return void
     */
    function __construct()
    {
        $this->model = new Feedback_Model();
    }

    /**
     * Функция принятия сообщения
     */
     function add_message()
     {
         $param['c_height']=$_POST['cheight'];
         $param['c_width']=$_POST['cwidth'];
         $param['colors']=$_POST['colors'];
         $param['height']=$_POST['height'];
         $param['width']=$_POST['width'];
         $param['plugins']=$_POST['plugins'];

         $msg = $_POST['msg'];

         $param['request'] = $_SERVER['REQUEST_URI'];
         $param['browser'] = $_SERVER['HTTP_USER_AGENT'];
         $param['referer'] = $_SERVER['HTTP_REFERER'];

         $this->model->add_message($msg,$param);

         die();
     }

     function r_list()
     {
        die(json_encode($this->model->get_rlist()));
     }
}
?>
