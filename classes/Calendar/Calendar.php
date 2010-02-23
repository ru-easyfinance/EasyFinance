<?php
/**
 * Класс для работы с календарём. Цепочками событий и регулярных операций
 *
 * @author ukko
 */
class Calendar
{
    /**
     * Объект пользователь
     * @var User
     */
    protected $user;

    /**
     * Массив событий
     * @var array mixed
     */
    protected $events = array();

    /**
     * Список ошибок
     * @var array
     */
    private $errors = array();

    /**
     * Конструктор
     * @param User $user
     * @return void
     */
    public function __construct( User $user )
    {
        $this->user = $user;
    }

    /**
     * Возвращает обьект Calendar с загруженными событиями для пользователя $user
     *
     * @param User $user
     * @param $start mysql date
     * @param $end mysql date
     * @return Calendar
     * 
     * @example $calendar = Calendar::loadAll( $user );
     */
    public function loadAll( User $user, $start = null, $end = null)
    {
        if ( is_null( $start ) ) {
            $start = date('Y-m-d'); //@TODO Поставить какую-нибудь вменяемую дату
        } else {
            $start = date('Y-m-d', $start / 1000);
        }

        if ( is_null( $end ) ) {
            $end = date('Y-m-d'); //@TODO Поставить какую-нибудь вменяемую дату
        } else {
            $end = date('Y-m-d', $end / 1000);
        }

        if ( !$user ) { $user = $this->user; }

        $calendar = new Calendar( $user );
        $eventsModels = Calendar_Model::loadAll( $user, $start, $end );

        foreach ( $eventsModels as $model )
        {
            if ($model->type === 'e') { // Обычное событие календаря
                $calendar->add( new Calendar_Event_Developments( $model, $calendar->user ) );
            } elseif ($model->type === 'p') { // Событие периодической транзакции
                $calendar->add( new Calendar_Event_Periodic( $model, $calendar->user ) );
            }
        }

        return $calendar;
    }

    /**
     * Загружает список напоминаний
     * @param User $user
     * @param date $date 
     */
    public function loadReminder (User $user, $date = null)
    {
        if ( is_null( $date ) ) {
            $date = date('Y-m-d');
        }

        $calendar = new Calendar( $user );
        $eventsModels = Calendar_Model::loadAll( $user, $date, $date, true );

        foreach ( $eventsModels as $model )
        {
            if ($model->type === 'e') { // Обычное событие календаря
                $calendar->add( new Calendar_Event_Developments( $model, $calendar->user ) );
            } elseif ($model->type === 'p') { // Событие периодической транзакции
                $calendar->add( new Calendar_Event_Periodic( $model, $calendar->user ) );
            }
        }
        return $calendar;
    }

    /**
     * Добавляем события в календарь
     */
    public function add ( Calendar_Event $event )
    {
        $this->events[ $event->getId() ] = $event;
        return true;
    }

    /**
     * Создаём новое событие
     *
     * @param string $type 'e'|'p'
     * @param string $title
     * @param string $comment
     * @param string $time
     * @param int $date
     * @param  $every int
     * @param int $repeat
     * @param string $week '0000011'
     * @param float $amount
     * @param int $cat
     * @param int $account
     * @param int $op_type
     * @param string $tags
     */
    public function createEvent ($type, $title, $comment, $time, $date, $every,
                                 $repeat, $week, $amount, $cat, $account, $op_type, $tags)
    {

        //@TODO Проверка на ошибки
        if (($type !== 'e') && ($type !== 'p')) {
            throw new Calendar_Exception('Unknown event type');
        }

        $title   = (string)$title;
        $comment = (string)$comment;
        $time    = (empty($time))? '00:00' : (string)$time;
        $date    = formatRussianDate2MysqlDate($date);
        $every   = (int)$every;
        // Опционально, по-умолчанию 1, от 1 до 365 (год) **или дата окончания**
        $last_date = '0000-00-00';
        if ( strlen($repeat) == 10 ){
            $last_date = formatRussianDate2MysqlDate($repeat);
        } else {
            $repeat  = (int)$repeat;
        }
        $week    = (empty($week))? '0000000': (string)$week;
        $amount  = (float)str_replace(' ', '', $amount);
        $cat     = (int)$cat;
        $account = (int)$account;
        $op_type = (int)$op_type;
        $tags    = (string)$tags;


        if (empty ($title)) $this->errors['title'] = 'Необходимо заполнить заголовок';
        if (empty ($date))  $this->errors['date']  = 'Необходимо указать дату';

        // Проверяем на ошибки
        if ( count($this->errors) != 0 ) {

            return array(
                'error' => array(
                    'text' => $this->errors
                )
            );
            
        }

        // Если повторять более одного раза
        if ( $every == 1 ) {
            $array = $this->_repeat( $repeat, $date, 'day',  null, $last_date );
        } elseif ($every == 7) {
            $array = $this->_repeat( $repeat, $date, 'week', $week, $last_date );
        } elseif ($every == 30) {
            $array = $this->_repeat( $repeat, $date, 'month', null, $last_date );
        } elseif ($every == 90) {
            $array = $this->_repeat( $repeat, $date, 'quartal', null, $last_date );
        } elseif ($every == 365) {
            $array = $this->_repeat( $repeat, $date, 'year', null, $last_date );
        } else {
            $array = array($date);
        }

        $model = Calendar_Model::create($this->user, $type, $title, $comment, 
            $time, $date, $last_date, $every,
            $repeat, $week,$amount, $cat, $account, $op_type, $tags, $array);
        return true;
    }

    /**
     * Редактируем события
     *
     * @param int $id
     * @param string $type 'e'|'p'
     * @param string $title
     * @param string $comment
     * @param string $time
     * @param int $date
     * @param  $every int
     * @param int $repeat
     * @param string $week '0000011'
     * @param float $amount
     * @param int $cat
     * @param int $account
     * @param int $op_type
     * @param string $tags
     * @param string $use_mode 'single' | 'all' | 'follow'
     */
    public function editEvents ($id, $chain, $type, $title, $comment, $time, $date, $every, $repeat,
            $week,$amount, $cat, $account, $op_type, $tags, $use_mode)
    {
        $id      = (int)$id;
        $chain   = (int)$chain;
        $title   = (string)$title;
        $comment = (string)$comment;
        $time    = (empty($time))? '00:00' : (string)$time;
        $date    = formatRussianDate2MysqlDate($date);
        $every   = (int)$every;
        
        $last_date = '0000-00-00';
        if ( strlen($repeat) == 10 ){
            $last_date = formatRussianDate2MysqlDate($repeat);
        } else {
            $repeat  = (int)$repeat;
        }

        $week    = (empty($week))? '0000000': (string)$week;
        $amount  = (float)str_replace(' ', '', $amount);
        $cat     = (int)$cat;
        $account = (int)$account;
        $op_type = (int)$op_type;
        $tags    = (empty ($tags))? '' : (string)$tags;
        $use_mode= (string)$use_mode;

        switch ($use_mode) {
            case 'single':
                break;
            case 'all':
                break;
            default: // follow
                $use_mode = 'follow';
                break;
        }

        //@TODO Проверка на ошибки
        if (($type !== 'e') && ($type !== 'p')) {
            throw new Calendar_Exception('Unknown event type');
        }
        if ( $repeat > 500 ) {
            $this->errors['repeat'] = "Максимальное количество повторений = 500 раз, у вас " . $repeat;
        }

        // Проверяем на ошибки
        if ( count($this->errors) != 0 ) {
            return array('error'=>array('text' => implode("\n", $this->errors)));
        }

        $model = Calendar_Model::loadById( $this->user, $id, $chain);

        $diff = array();
        if ( $date   != $model->date )   $diff[] = 'date';
        if ( $week   != $model->week )   $diff[] = 'week';
        if ( $every  != $model->every )  $diff[] = 'every';
        if ( $repeat != $model->repeat ) $diff[] = 'repeat';

        // Обновляем событие
        if ( count($diff) == 0 ) {
            Calendar_Model::update($this->user, $id, $chain, $type, $title,
                $comment, $time, $date, $every, $repeat, $week, $amount, $cat,
                $account, $op_type, $tags, $diff);
            return array('result' => array('text'=>''));
        // Обновляем даты события
        } else {
            // Если мы перетащили событие мышкой, или установили у него другую дату
            if ( $use_mode == 'single' ) {
                Calendar_Model::updateEventSingleDate($id, $chain, $date);
                return array('result' => array('text'=>''));
            // Если нам нужно обновить все события в цепочке
            } elseif ($use_mode == 'all') {
                Calendar_Model::deleteAcceptedEvents( $this->user, $chain );

                // Если повторять более одного раза
                if ( $every == 1 ) {
                    $array = $this->_repeat( $repeat, $model->start, 'day',  null, $last_date );
                } elseif ($every == 7) {
                    $array = $this->_repeat( $repeat, $model->start, 'week', $week, $last_date );
                } elseif ($every == 30) {
                    $array = $this->_repeat( $repeat, $model->start, 'month', null, $last_date );
                } elseif ($every == 90) {
                    $array = $this->_repeat( $repeat, $model->start, 'quartal', null, $last_date );
                } elseif ($every == 365) {
                    $array = $this->_repeat( $repeat, $model->start, 'year', null, $last_date );
                } else {
                    $array = array($date);
                }
                Calendar_Model::update( $this->user, $id, $chain, $type, $title, $comment, $time, $date,
                    $every, $repeat, $week, $amount, $cat, $account, $op_type, $tags, $array);
            }
        }
    }

    /**
     * Возвращает сформированный массив дат
     * @param int $repeat
     * @param int $date
     * @param string $period
     * @param string $week
     * @return array
     */
    private function _repeat( $repeat, $date, $period, $week = '0000000', $last_date = null ) {
        
        $datetime = new DateTime( $date . ' 00:00:00' );
        // Массив с датами события
        $date_events = array();

        // Определённое количество раз
        if ( $repeat > 0 ) {
            // Идём в цикле по указанному количеству раз повторений
            for ( $i = 1 ; $i <= $repeat ; $i++ ) {

                // Если указано что повторять каждую неделю
                if ( $period == 'week' ) {
                    if ($week[$datetime->format('N')-1] == 1) {
                        $date_events[] = $datetime->format('Y-m-d');
                    }

                    // День недели для выбранной даты, от 1 (пнд) до 7 (вск)
                    $dwr =  $datetime->format('N');

                    // Перебираем по циклу неделю
                    for ( $j = 0; $j < 6; $j++ ) {
                        $dw = $dwr + $j ;

                        // Если вышли за рамки недели, то возвращаем
                        if ( $dw > 6 )  { $dw = $dw - 7; }

                        if ( $week[$dw] == 1 ) {
                            $date_events[] = date('Y-m-d', $datetime->format('U') + (($j + 1) * 86400));
                        }
                    }
                // Если нужно повторять обычным способом
                } else {
                    $date_events[] = $datetime->format('Y-m-d');
                }

                $datetime->modify( "+1 " . $period );
            }

        // Повторять до даты
        } elseif( $last_date ) {
            $last_date = new DateTime( $last_date . ' 00:00:00' );
            if ( $datetime->format('U') > $last_date ) {
                throw new Calendar_Exception('Start date more end date');
            }
            for ($i = 1 ; $i <= 501 ; $i++) {
                if ( $datetime->format('U') > $last_date ) {
                    return $date_events;
                }
                if ( count($date_events) > 500 ) {
                    $this->errors['repeat'] = "Максимальное количество повторений = 500 раз, у вас "
                        . count($date_events);
                }
                $date_events[] = $datetime->format('Y-m-d');

                 if ( $period == 'week' ) {
                    // День недели для выбранной даты, от 1 (пнд) до 7 (вск)
                    $dwr =  $datetime->format('N');

                    // Перебираем по циклу неделю, на один день меньше
                    for ( $j = 0; $j < 6; $j++ ) {
                        $dw = $dwr + $j ;
                        if ( $dw > 6 )  { $dw = $dw - 7; }

                        if ( $week[$dw] == 1 ) {
                            $date_events[] = date('Y-m-d', $datetime->format('U') + (($j + 1) * 86400));
                        }
                    }

                }

                $datetime->modify( "+1 " . $period );
            }
        }
        return $date_events;
    }

    /**
     * Удаляет событие / цепочку событий календаря
     * @param int $id
     * @param int $chain
     * @param string $use_mode 'single' | 'all' | 'follow'
     * @return bool
     */
    public function deleteEvents($id, $chain = null, $use_mode = 'single')
    {
        $use_mode = (string)$use_mode;

        switch ($use_mode) {
            case 'single':
                break;
            case 'all':
                break;
            case 'follow':
                break;
            default: // follow
                $use_mode = 'single';
                break;
        }
        if ( Calendar_Model::deleteEvents( $this->user, $id, $chain, $use_mode ) ) {
            return array('result' => array('text'=>''));
        } else {
            return array('error' => array('text'=>'Не удалось удалить события.'));
        }
    }

    /**
     * Подтверждает события
     * @param int | array $id
     */
    public function acceptEvents ($id)
    {
        // Получаем список событий, отмечаем что они выполненные
        $events = Calendar_Model::acceptEvents( $this->user, $id);
        if ( $events ) {
            // создаём операции по периодическим транзакциям
            if ( count($events) ) {
                $oper = new Operation_Model();
                foreach ( $events as $k => $v ) {
                    $drain = ( $v['op_type'] > 0 )? 0 : 1;
                    $oper->add($v['amount'], $v['date'], $v['cat_id'], $drain,
                        $v['comment'],$v['account_id'], array('регулярная операция'));
                }
            }
            return array('result' => array('text'=>''));
        } else {
            return array('error' => array('text'=>''));
        }
    }

    /**
     * Возвращает массив, со всеми загруженными событиями
     */
    public function getArray()
    {
        $array = array();
        foreach ($this->events as $k => $v) {
            $array[$k] = $v->__getArray();
        }
        return $array;
    }

}
