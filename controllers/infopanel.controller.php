<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля info_panel
 * @category info_panel
 * @copyright http://home-money.ru/
 * @version SVN $Id: $
 */
class Infopanel_Controller extends Template_Controller
{
    /**
     * Ссылка на класс модели пользователя
     * @var Infopanel_Model
     */
    private $model = null;

    /**
     * Конструктор класса
     * @return void
     */
    function __construct()
    {
        $this->model = new Infopanel_Model();
        Core::getInstance()->tpl->assign('name_page', 'info_panel/info_panel');
    }

    /**
     * неиспользуется
     * @return void
     */
    function index($args)
    {
        
    }

    /**
     * используется для подгрузки контента;only panel2
     * реализованно отдельной функцией(для перезагрузки части контента).
     * @return void
     */
    function update()
    {
        die($this->model->content((int)$_POST['panel']));
    }

    /**
     * загружает содержание страницы с подробным описанием
     * @return void
     */
    function page()
    {
        $name = $_POST['name'];
        if ($name=='')
            die('');
    	$date = formatRussianDate2MysqlDate($_POST['date']);
        die($this->model->page($date, $name));
    }

    /**
     * загружает информацию для хромометров.
     * вызывается первый раз в window.load;
     * реализованно отдельной функцией(для подгрузки контента);
     * отделён от индекса в силу более долгой загрузки.
     * @return void
     */
    function xml()
    {
        $type = $_POST['element'];
        $date = formatRussianDate2MysqlDate($_POST['date']);
        // @TODO Проверить переменные
        header('Content-type: text/xml');
        die($this->model->xml($type, $date));
    }
}