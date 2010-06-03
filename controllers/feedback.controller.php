<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля "обратной связи"
 * @copyright http://easyfinance.ru/
 * @version SVN $Id: $
 */
class Feedback_Controller extends _Core_Controller
{
    /**
     * Конструктор класса
     * @return void
     */
    function __init()
    {

    }

    /**
     * Главная страница обратной связи
     * @return void
     */
    function index()
    {
        $this->tpl->assign('no_menu', '1');
        $this->tpl->assign('head_val', '/feedback/');
        $this->tpl->assign('name_page', 'feedback');
    }

    /**
     * Функция добавления сообщения от пользователей
     */
     function add_message()
     {
         // Параметры браузера
         $param = array();
         $param['c_height'] = $_POST['cheight'];
         $param['c_width']  = $_POST['cwidth'];
         $param['colors']   = $_POST['colors'];
         $param['height']   = $_POST['height'];
         $param['width']    = $_POST['width'];
         $param['plugins']  = $_POST['plugins'];

         // Сообщение
         $title             = $_POST['title'];
         $message           = $_POST['msg'];
         $param['email']    = @$_POST['email'];


         // Параметры страницы
         $param['request']  = $_SERVER['REQUEST_URI'];
         $param['browser']  = $_SERVER['HTTP_USER_AGENT'];
         $param['referer']  = $_SERVER['HTTP_REFERER'];

         $feedback = new Feedback($message, $title, $param);

         if ( $feedback->add_message() ) {
            $this->tpl->assign( 'result', array('text'=>"Отзыв успешно добавлен.") );
         } else {
            $this->tpl->assign( 'error', array('text'=> implode(" \n", $feedback->errorData) ) );
         }

     }
}
