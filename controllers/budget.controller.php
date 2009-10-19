<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для бюджета
 * @copyright http://home-money.ru/
 * @author Max Kamashev <max.kamashev@gmail.com>
 * SVN $Id:  $
 */

class Budget_Controller extends Template_Controller
{
    /**
     * Модель бюджета
     * @var Budget_Model
     */
    private $model = null;	

    /**
     * Конструктор класса
     * @return void
     */
    function __construct()
    {
        $this->model = new Budget_Model();
    }

    /**
     * Индексная страница
     * @param $args array mixed
     * @return void
     */
    function index($args)
    {
        Core::getInstance()->tpl->assign('name_page', 'budget/budget');
    }

    /**
     * Загружает бюджет
     * @return
     */
    function load()
    {
        $start = formatRussianDate2MysqlDate(@$_POST['start']);
        $end   = null;
        if ($start) {
            die(json_encode($this->model->loadBudget($start, $end)));
        } else {
            die('[]');
        }
    }

    function json_code ($json) {

        //remove curly brackets to beware from regex errors

        $json = substr($json, strpos($json,'{')+1, strlen($json));
        $json = substr($json, 0, strrpos($json,'}'));
        $json = preg_replace('/(^|,)([\\s\\t]*)([^:]*) (([\\s\\t]*)):(([\\s\\t]*))/s', '$1"$3"$4:', trim($json));
        return json_decode('{'.$json.'}', true);
    }


    /**
     * Добавляет данные из мастера
     */
    function add()
    {
        $start = formatRussianDate2MysqlDate(@$_POST['start']);
        $end   = null;
        $json = json_decode(@$_POST['data']);
        print_r($json);
        $budget = array();
        foreach ($json['d'] as $val) {
            foreach ($val as $k => $v) {
                if ((float)$v <> 0) {
                    $budget['d'][$k] = (float)$v;
                }
            }
        }
        foreach ($json['r'] as $val) {
            foreach ($val as $k => $v) {
                if ((float)$v <> 0) {
                    $budget['d'][$k] = (float)$v;
                }
            }
        }
        return json_encode($this->model->add($budget));
    }
}