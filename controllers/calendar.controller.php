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
    }

    /**
     * Индексная страница
     * @param $args array mixed
     * @return void
     */
    function index($args)
    {
        $this->tpl->assign('name_page', 'calendar/calendar');
    }

    /**
     * Возвращает дату операции
     * @return string
     */
    private function getDateOperation ()
    {
        // Определяем массив данных для обработки
        $request = _Core_Request::getCurrent();

        // Если дата передана массивом (PDA) ...
        if ( is_array ( $request->post['date'] ) ) {

            return $request->post['date']['day']
                . '.' . $request->post['date']['month']
                . '.' . $request->post['date']['year'];

            // если пустая дата - подставляем сегодняшний день
        } elseif( empty( $request->post['date'] ) ) {

            return date ( "d.m.Y" );

        } else {

            return $request->post['date'];

        }
    }

    /**
     * Добавляет новое событие
     * @return void
     */
    function add()
    {
        $user = Core::getInstance()->user;

        // Определяем массив данных для обработки
        $request = _Core_Request::getCurrent();

        $event_array = array (
            'type'       => ( int ) $request->post['type'],
            'account'    => ( int ) $request->post['account'],
            'amount'     => (float) str_replace ( ' ', '', $request->post['amount'] ),
            'category'   => ((int) $request->post['category'] <= 0) ? null : (int) $request->post['category'],
            'date'       => Helper_Date::RusDate2Mysql( $this->getDateOperation() ),
            'comment'    => ( string ) $request->post['comment'],
            'tags'       => isset( $request->post['tags'] ) ? $request->post['tags'] : null,
            'convert'    => isset( $request->post['convert'] ) ? $request->post['convert'] : 0,
            'close'      => isset( $request->post['close'] ) ? $request->post['close'] : 0,
            'currency'   => isset( $request->post['currency'] ) ? $request->post['currency'] : 0,
            'toAccount'  => isset( $request->post['toAccount'] ) ? $request->post['toAccount'] : null,
            'target'     => isset( $request->post['target'] ) ? $request->post['target'] : null,

            // Дополнения для планирования в календарь
            'last'       => isset( $request->post['last'] ) ? Helper_Date::RusDate2Mysql( $request->post['last'] ) : '0000-00-00',
            'time'       => isset( $request->post['time'] ) ? $request->post['time'] : date ( "H:i:s" ),
            'every'      => isset( $request->post['every'] ) ? ( int ) $request->post['every'] : 0,
            'repeat'     => isset( $request->post['repeat'] ) ? ( int ) $request->post['repeat'] : 1,
            'week'       => isset( $request->post['week'] ) ? $request->post['week'] : '0000000',
        );

        $event = new Calendar_Event ( new Calendar_Model( $event_array, $user ), $user );

        if ( ! $event->checkData() ) {

            $this->tpl->assign( 'error', array('text' => implode(",\n", $event->getErrors() ) ) );

        } else {

            $calendar = new Calendar( $user );
            $calendar->create( $event );
            $this->tpl->assign( 'result', array('text' => 'Операция успешно запланирована') );

            // @FIXME Перенести этот блок кода в календарь
            Core::getInstance()->user->initUserEvents();
            Core::getInstance()->user->save();

            $this->tpl->assign( 'future', Core::getInstance()->user->getUserEvents( 'reminder' ) );
            $this->tpl->assign( 'overdue', Core::getInstance()->user->getUserEvents( 'overdue' ) );
            $this->tpl->assign( 'calendar', Core::getInstance()->user->getUserEvents( 'calendar' ) );
        }
    }

    /**
     * Редактирует событие
     * @return void
     */
    function edit ()
    {

        $user = Core::getInstance()->user;

        // Определяем массив данных для обработки
        $request = _Core_Request::getCurrent();

        $event_array = array (
            'id'         => ( int ) $request->post['id'],
            'chain'      => ( int ) $request->post['chain'],
            'type'       => ( int ) $request->post['type'],
            'account'    => ( int ) $request->post['account'],
            'amount'     => (float) str_replace ( ' ', '', $request->post['amount'] ),
            'category'   => ( int ) $request->post['category'],
            'date'       => Helper_Date::RusDate2Mysql( $this->getDateOperation() ),

            'comment'    => ( string ) $request->post['comment'],
            'tags'       => isset( $request->post['tags'] ) ? $request->post['tags'] : null,
            'convert'    => isset( $request->post['convert'] ) ? $request->post['convert'] : 0,
            'close'      => isset( $request->post['close'] ) ? $request->post['close'] : 0,
            'currency'   => isset( $request->post['currency'] ) ? $request->post['currency'] : 0,
            'toAccount'  => isset( $request->post['toAccount'] ) ? $request->post['toAccount'] : null,
            'target'     => isset( $request->post['target'] ) ? $request->post['target'] : null,

            // Дополнения для планирования в календарь
            'last'       => isset( $request->post['last'] ) ? Helper_Date::RusDate2Mysql( $request->post['last'] ) : '0000-00-00',
            'time'       => isset( $request->post['time'] ) ? $request->post['time'] : date ( "H:i:s" ),
            'every'      => isset( $request->post['every'] ) ? ( int ) $request->post['every'] : 0,
            'repeat'     => isset( $request->post['repeat'] ) ? ( int ) $request->post['repeat'] : 1,
            'week'       => isset( $request->post['week'] ) ? $request->post['week'] : '0000000',
            'accepted'   => isset( $request->post['accepted'] ) ? ( int ) $request->post['accepted'] : 0,
        );

        $event = new Calendar_Event ( new Calendar_Model( $event_array, $user ), $user );

        if ( ! $event->checkData() ) {

            $this->tpl->assign( 'error', array( 'text' => implode(",\n", $event->getErrors() ) ) );

        } else {

            // Если нет цепочки, значит только одна операция
            if ( $event_array['chain'] === 0 ) {

                $operation = new Operation_Model();

                if ( $event_array['type'] <= 1 ) {

                    if ( $event_array['type'] == 0 ) {
                        $event_array['drain'] = 1;
                        $event_array['amount'] = abs($event_array['amount']) * -1;
                    } else {
                        $event_array['drain'] = 0;
                        $event_array['amount'] = abs($event_array['amount']);
                    }

                    $operation->edit(
                        $event_array['id'],
                        $event_array['amount'],
                        $event_array['date'],
                        $event_array['category'],
                        $event_array['drain'],
                        $event_array['comment'],
                        $event_array['account'],
                        $event_array['tags'],
                        $event_array['accepted']
                    );

                } elseif ( $event_array['type'] == 2 ) {

                    $operation->editTransfer(
                        $event_array['id'],
                        $event_array['amount'],
                        $event_array['convert'],
                        $event_array['date'],
                        $event_array['account'],
                        $event_array['toAccount'],
                        $event_array['comment'],
                        $event_array['tags']
                    );

                }

                $this->tpl->assign( 'result', array('text' => 'Регулярная операция изменена') );

            } else {
                $calendar = new Calendar( $user );
                $calendar->edit( $event );
                $this->tpl->assign( 'result', array('text' => 'Регулярные операции изменены') );
            }

            // @FIXME Перенести этот блок кода в календарь
            Core::getInstance()->user->initUserEvents();
            Core::getInstance()->user->save();

            $this->tpl->assign( 'future',   Core::getInstance()->user->getUserEvents( 'reminder' ) );
            $this->tpl->assign( 'overdue',  Core::getInstance()->user->getUserEvents( 'overdue' ) );
            $this->tpl->assign( 'calendar', Core::getInstance()->user->getUserEvents( 'calendar' ) );

        }
    }

    /**
     * Удаляет выбранный события
     * @return void
     */
    function del_chain( )
    {
        $chain    = ( int ) _Core_Request::getCurrent()->post['chain'];
        $calendar = new Calendar( Core::getInstance()->user );

        if ( $calendar->deleteEvents( $chain ) ) {

            $this->tpl->assign('result', array ( 'text' => 'Цепочка событий успешно удалены' ) );

            // @FIXME Перенести этот блок кода в календарь
            Core::getInstance()->user->initUserEvents();
            Core::getInstance()->user->save();

            $this->tpl->assign( 'future', Core::getInstance()->user->getUserEvents( 'reminder' ) );
            $this->tpl->assign( 'overdue', Core::getInstance()->user->getUserEvents( 'overdue' ) );
            $this->tpl->assign( 'calendar', Core::getInstance()->user->getUserEvents( 'calendar' ) );

        } else {

            $this->tpl->assign('error', array ( 'text' => implode ( ",\n", $calendar->getErrors () ) ) );

        }

    }

    /**
     * Возвращает список событий, в формате JSON
     * @return void
     */
    function events() {

        $calendar = new Calendar ( Core::getInstance()->user );
        $calendar->loadAll( Core::getInstance()->user , @$_GET['start'], @$_GET['end'] );
        $this->tpl->assign( 'calendar', $calendar->getArray() );
    }

    /**
     * Срабатывает при перетаскивании события
     * @return void
     */
    function edit_date ()
    {

        $id   = ( int ) _Core_Request::getCurrent()->post['id'];
        $date = Helper_Date::RusDate2Mysql( _Core_Request::getCurrent()->post['date'] );

        $calendar = new Calendar( Core::getInstance()->user ) ;

        if ( $calendar->editDate( $id, $date ) ) {
            $this->tpl->assign( 'result', array ( 'text' => 'Операция успешно изменена' ) );
        } else {
            $this->tpl->assign( 'error', array ( 'text' => implode( ",\n", $calendar->getErrors() ) ) );
        }

        // @FIXME Перенести этот блок кода в календарь
        Core::getInstance()->user->initUserEvents();
        Core::getInstance()->user->save();
    }

    /**
     * Подтверждает список операций
     */
    function accept_all ( ) {
        $ids = explode(',', @$_POST['ids']);

        $accepted_array  = array ();

        foreach ( $ids as $id ) {
            if ( ( int ) $id > 0 ) {
                $accepted_array[] = $id;
            }
        }

        if ( count ( $accepted_array ) > 0 ) {

            $calendar = new Calendar ( Core::getInstance()->user );

            if ( $calendar->acceptEvents( $accepted_array ) ) {

                $this->tpl->assign( 'result', array ( 'text' => 'Операции успешно подтверждены' ) );

            } else {

                $this->tpl->assign( 'error', array ( 'text' => implode( ",\n", $calendar->getErrors() ) ) );

            }

        } else {

            $this->tpl->assign( 'error', array ( 'text' => 'Не указаны операции для подтверждения!' ) );

        }
    }
}