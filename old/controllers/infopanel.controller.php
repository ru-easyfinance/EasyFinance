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
     *
     */
    private $tpl = null;
    /**
     * коичество отображаемых фин целей
     */
    private $targets_count = 0;

    /**
     * Конструктор класса
     * @return void
     */
    function __construct()
    {
        $this->tpl = Core::getInstance()->tpl;

        $this->tpl->append('js','jquery/jquery.js');
        $this->tpl->append('js','jquery/json.js');
        $this->tpl->append('js','jquery/jquery.cookie.js');
        $this->tpl->append('js','jquery/ui.core.js');
        $this->tpl->append('js','jquery/ui.sortable.js');
        $this->tpl->append('js','jquery/ui.draggable.js');
        $this->tpl->append('js','jquery/ui.dialog.js');
        $this->tpl->append('js','anychart/AnyChart.js');
        $this->tpl->append('js','info_panel.js');


        if (!$_SESSION['targets_count'])
            $_SESSION['targets_count']=0;   //default


        $this->targets_count = (int)$_SESSION['targets_count'];
        $this->model = new Infopanel_Model();
        $this->tpl->assign('name_page', 'info_panel/info_panel');
        $str='';
        for ($i=0;$i<$this->targets_count;$i++)
        {
        $str.="<div class='object2' id='$i'><a class='advice'>Получить совет</a><div class='descr'></div><ul><li id='edit'>редактировать</li><li id='del'>удалить</li></ul></div>";
        }
        Core::getInstance()->tpl->assign('content', $str);
    }

    function get()
    {
        die (strval($this->targets_count));
    }
    
    function targets()
    {
        $s = $_POST['cnt'];
        $this->targets_count=$s;
        $_SESSION['targets_count']=$s;
        
        die (strval($this->targets_count));
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
        $type = $_POST['type'];
        if ($type=='')
            die('');
    	$date = formatRussianDate2MysqlDate($_POST['date'],$name,$date);
        die($this->model->content((int)$_POST['panel'],$type,$date));
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
        header('Content-type: text/xml');
        die($this->model->xml($type, $date));
    }
}