<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для календаря
 * @category calendar
 * @copyright http://easyfinance.ru/
 * @version SVN $Id$
 */
class Calendar_Controller extends _Core_Controller_UserCommon
{
    /**
     * Конструктор класса
     * @return void
     */
    protected function __init()
    {
        $this->tpl->assign('name_page', 'calendar/calendar');
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
     * Добавляет новое событие
     * @param $args array mixed Какие-нибудь аргументы
     * @return void
     */
    function add( $args )
    {
        $calendar = new Calendar( Core::getInstance()->user );
        die ( json_encode ( $calendar->createEvent(
            @$_POST['type'], @$_POST['title'], @$_POST['comment'],
            @$_POST['time'] , @$_POST['date'], @$_POST['every'], @$_POST['repeat'], 
            @$_POST['week'],@$_POST['amount'], @$_POST['cat'], @$_POST['account'],
            @$_POST['op_type'], @$_POST['tags']
        ) ) );
    }

    /**
     * Редактирует событие
     * @param $args array mixed Какие-нибудь аргументы
     * @return void
     */
    function edit( $args )
    {
        $calendar = new Calendar( Core::getInstance()->user );
        die ( json_encode ( $calendar->editEvents(
            @$_POST['id'], @$_POST['chain'], @$_POST['type'], @$_POST['title'],
            @$_POST['comment'], @$_POST['time'] , @$_POST['date'], @$_POST['every'],
            @$_POST['repeat'], @$_POST['week'],@$_POST['amount'], @$_POST['cat'],
            @$_POST['account'], @$_POST['op_type'], @$_POST['tags'], @$_POST['use_mode']
        ) ) );
    }
    
    /**
     * Удаляет выбранное событие
     * @param $args array mixed Какие-нибудь аргументы
     * @return void
     */
    function del( $args )
    {
        $calendar = new Calendar( Core::getInstance()->user );
        die( json_encode(
            $calendar->deleteEvents((int)@$_POST['id'], (int)@$_POST['chain'], @$_POST['use_mode']
        )));
    }



    /**
     * Возвращает список событий, в формате JSON
     * @return void
     */
    function events($args) {
        $calendar = Calendar::loadAll( Core::getInstance()->user , $_GET['start'], $_GET['end'] );
        return die ( json_encode( $calendar->getArray() ) );
    }

    /**
     * Подтверждение событий
     */
    function reminderAccept()
    {
        $ids = explode(',', $_POST['ids']);
        $calendar = new Calendar ( Core::getInstance()->user );
        $calendar->acceptEvents($ids);

        $calendar = Calendar::loadReminder( Core::getInstance()->user );
        die ( json_encode( $calendar->getArray() ) );
    }

    /**
     * Удаляем события из календаря
     */
    function reminderDel ( )
    {
        $ids = explode(',', $_POST['ids']);
        $calendar = new Calendar ( Core::getInstance()->user );
        $calendar->deleteEvents($ids);
        
        $calendar = Calendar::loadReminder( Core::getInstance()->user );
        die ( json_encode( $calendar->getArray() ) );
    }
}