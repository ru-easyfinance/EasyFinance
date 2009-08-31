<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля инфопанелей
 * @copyright http://home-money.ru/
 * @version SVN $Id: rewle $
 */
class Info_Controller extends Template_Controller
{
    /**
     * Модель класса инфо панелей
     * @var Info_Model
     */
    private $info_model = null;
    
    /**
     * Модель класса фин целей
     * @var Targets_Model
     */
    private $targets_model = null;

    /**
     * Ссылка на класс Смарти
     * @var Smarty
     */
    private $tpl = null;


    /**
     * Количество отображаемых фин целей по умолчанию
     * @var int
     */
    private $count = 5;
    /**
     * Конструктор класса
     * @return void
     */

    function __construct()
    {
        $this->tpl = Core::getInstance()->tpl;
        $this->tpl->assign('name_page', 'info_panel/info_panel');
        $this->targets_model = new Targets_Model();
        $this->info_model = new Info_Model();
    }

    /**
     * Индексная страница
     * @return void
     */
    function index()
    {
        $this->tpl->append('js','jquery/jquery.js');
        $this->tpl->append('js','jquery/jquery.cookie.js');
        $this->tpl->append('js','jquery/ui.core.js');
        $this->tpl->append('js','jquery/ui.sortable.js');
        $this->tpl->append('js','jquery/ui.draggable.js');
        $this->tpl->append('js','jquery/ui.dialog.js');
        $this->tpl->append('js','anychart/AnyChart.js');
        $this->tpl->append('js','info/functions.js');
        $this->tpl->append('js','info/view.js');
    }
////////////targets/////////////////////////////////////////
    function targets_list()
    {
        $count = (int)$_POST['count'];
        if(!$count)
            $count = $this->count;
     // Список ближайших целей пользователя
        die(json_encode($this->targets_model->getLastList(1,$count)));
    }
/////////////chronometr //////////////////////////////////////
    function get_data()
    {

        //$date_start = formatRussianDate2MysqlDate($_POST['start']);
        //$date_end = formatRussianDate2MysqlDate($_POST['end']);
        die(json_encode($this->info_model->tohometrs()));
    }

}
?>
