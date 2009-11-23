<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля "фидбека"
 * @copyright http://easyfinance.ru/
 * @version SVN $Id: $
 */

class Feedback_Controller extends Template_Controller
{
    /**
     * Ссылка на класс модель обратной связи
     * @var Feedback_Model
     */
    private $model = null;

    /**
     * Ссылка на класс Smarty
     * @var Smarty
     */
    private $tpl = null;
	
    /**
     * Конструктор класса
     * @return void
     */
    function __construct()
    {
        $this->model = new Feedback_Model();
    }

    /**
     * Главная страница обратной связи
     * @return void
     */
    function index()
    {
        $this->tpl   = Core::getInstance()->tpl;
        $this->tpl->assign('no_menu', '1');
        $this->tpl->assign('head_val', '/feedback/');
        $this->tpl->assign('name_page', 'feedback');
        $this->model = new Feedback_Model();
    }
	
    /**
     * Функция добавления сообщения от пользователей
     */
     function add_message()
     {
         // Параметры браузера
         $param['c_height'] = $_POST['cheight'];
         $param['c_width']  = $_POST['cwidth'];
         $param['colors']   = $_POST['colors'];
         $param['height']   = $_POST['height'];
         $param['width']    = $_POST['width'];
         $param['plugins']  = $_POST['plugins'];

         // Сообщение
         $msg               = $_POST['msg'];

         // Параметры страницы
         $param['request']  = $_SERVER['REQUEST_URI'];
         $param['browser']  = $_SERVER['HTTP_USER_AGENT'];
         $param['referer']  = $_SERVER['HTTP_REFERER'];

         if ($this->model->add_message($msg,$param)) {
            die(json_encode(array('success'=>array('text'=>''))));
         } else {
            die(json_encode(array('error'=>array('text'=>'Ошибка при отправке сообщения'))));
         }
     }
}