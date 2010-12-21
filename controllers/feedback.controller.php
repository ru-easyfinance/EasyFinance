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
         $paramNames = array('cheight', 'cwidth', 'colors', 'height', 'width', 'plugins');

         $param = array_intersect_key($_POST, array_combine($paramNames, $paramNames));

         // Сообщение
         if (empty($_POST['msg']) || empty($_POST['title'])) {
             $this->tpl->assign('error', array('text'=>"Не заполнены обязательные поля."));
             return false;
         }

         $title             = $_POST['title'];
         $message           = $_POST['msg'];
         $param['email']    = @$_POST['email'];

         // Параметры страницы
         $param['request']  = $_SERVER['REQUEST_URI'];
         $param['browser']  = $_SERVER['HTTP_USER_AGENT'];
         $param['referer']  = $_SERVER['HTTP_REFERER'];

         $feedback = new Feedback($message, $title, $param);

         if ( $feedback->add_message() ) {
            $this->tpl->assign( 'result', array('text'=>"Заявка успешно отправлена.") );
         } else {
            $this->tpl->assign( 'error', array('text'=> implode(" \n", $feedback->errorData) ) );
         }

     }
}
