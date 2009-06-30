<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля категорий
 * @copyright http://home-money.ru/
 * SVN $Id$
 */
class Calendar_Controller extends Template_Controller
{
    private $model = null;
    private $tpl = null;

    /**
     * Конструктор класса
     * @return void
     */
    function __construct()
    {
        $this->tpl = Core::getInstance()->tpl;
        $this->tpl->assign('name_page', 'calendar');
        $this->model = new Calendar_Model();
    }

    /**
     * Индексная страница
     * @param $args array mixed
     * @return void
     */
    function index($args)
    {

    }

    /**
     * Возвращает список событий, в формате JSON
     * @return void
     */
    function events($args) {
// $_GET['start'];  $_GET['end'];
        die (json_encode(
            array(
                array(
                    id => 1,
                    key => 34345,
                    title => "Длинное событие",
                    dt => mktime(0,0,0,6,6),
                    start => mktime(14,0,0,6,6),
                    end => mktime(14,0,0,6,11),
                    className => 'yellow',
                    comment => "Описание этого длинного события на русском языке",
                    draggable => true
                ),
                array(
                    id => 2,
                    title => "Повторяющееся событие",
                    dt => mktime(0,0,0,6,2),
                    start => mktime(14,0,0,6,2),
                    comment => 'Описание',
                    draggable => true
                ),
                array(
                    id => 2,
                    title => "Повторяющееся событие",
                    dt => mktime(0,0,0,6,9),
                    start => mktime(14,0,0,6,9),
                    draggable => true
                ),
                array(
                    id => 2,
                    title => "Повторяющееся событие",
                    dt => mktime(0,0,0,6,16),
                    start => mktime(14,0,0,6,16),
                    draggable => true
                ),
                array(
                    id => 3,
                    title => "Общение",
                    dt => mktime(0,0,0,6,20),
                    start => mktime(9,0,0,6,20),
                    className => 'yellow',
                    draggable => true
                ),
                array(
                    id => 4,
                    title => "+75 000.00 руб",
                    dt => mktime(0,0,0,6,27),
                    start => mktime(0,0,0,6,27),
                    className => 'green' ,
                    showTime => false,
                ),
                array(
                    id => 5,
                    title => "-20 000.00 руб",
                    dt => mktime(0,0,0,6,27),
                    start => mktime(0,0,0,6,27),
                    className => 'red',
                    showTime => false
                ),
                array(
                    id => 6,
                    title => "30 000.00 руб",
                    dt => mktime(0,0,0,6,27),
                    start => mktime(0,0,0,6,27),
                    className => 'blue',
                    showTime => false
                ),
                array(
                    id => 7,
                    title => "Ссылка на яндекс",
                    dt => mktime(0,0,0,6,27),
                    start => mktime(16,0,0,6,27),
                    className => array('yellow','url'),
                    url => "http://yandex.ru/",
                    showTime => false,
                    draggable => true
                ),
                array(
                    id => 8,
                    title => "Напоминалка",
                    dt => mktime(0,0,0,6,27),
                    start => mktime(0,0,0,6,27),
                    className => 'yellow',
                    draggable => true,
                    comment => "sdfsdf"
                )
            )
        ));
    }
}