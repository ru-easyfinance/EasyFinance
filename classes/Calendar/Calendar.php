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
    protected $_user;

    /**
     * Массив событий
     * @var array mixed
     */
    protected $_events = array();

    /**
     * Список ошибок
     * @var array
     */
    private $_errors = array();

    /**
     * Максимально-разрешённое количество событий
     * @var
     */
    const MAX_EVENTS = 500;

    /**
     * Конструктор
     * @param oldUser $user
     * @return void
     */
    public function __construct( oldUser $user )
    {
        $this->_user = $user;
    }

    /**
     * Возвращает обьект Calendar с загруженными событиями для пользователя $user
     *
     * @param oldUser $user
     * @param int $start
     * @param int $end
     * @return Calendar
     *
     * @example $calendar = Calendar::loadAll( $user );
     */
    public function loadAll( oldUser $user, $start = null, $end = null)
    {
        if (is_null($start)) {
            $start = date('Y-m-d'); //@TODO Поставить какую-нибудь вменяемую дату
        } else {
            $start = date('Y-m-d', $start);
        }

        if (is_null($end)) {
            $end = date('Y-m-d'); //@TODO Поставить какую-нибудь вменяемую дату
        } else {
            $end = date('Y-m-d', $end);
        }

        if (!$user) {
            $user = $this->_user;
        }

        //$calendar = new Calendar( $user );
        $eventsModels = Calendar_Model::loadAll($user, $start, $end);

        foreach ($eventsModels as $model) {
            $this->add(new Calendar_Event($model, $this->_user));
        }

        return $this;
    }

    /**
     * Список неподтверждённых операций
     *
     * @param oldUser $user
     * @return array
     */
    public function loadOverdue(oldUser $user)
    {
        if (!$user) {
            $user = $this->_user;
        }

        //$calendar = new Calendar( $user );
        $eventsModels = Calendar_Model::loadOverdue($user);

        foreach ($eventsModels as $model) {
            $this->add(new Calendar_Event($model, $this->_user));
        }

        return $this;
    }

    /**
     * Список напоминалок на ближайшее будущее
     *
     * @param oldUser $user
     * @return array
     */
    public function loadReminder(oldUser $user)
    {
        if (!$user) {
            $user = $this->_user;
        }

        //$calendar = new Calendar( $user );
        $eventsModels = Calendar_Model::loadReminder($user);

        foreach ($eventsModels as $model) {
            $this->add(new Calendar_Event($model, $this->_user));
        }

        return $this;
    }

    /**
     * Добавляем события в календарь
     * @param Calendar_Event $event
     */
    public function add ( Calendar_Event $event )
    {
        $this->_events[ $event->getId() ] = $event;
        return true;
    }

    /**
     * Создаём событие календаря с рег. операцией
     * @param Calendar_Event $event
     */
    public function create ( Calendar_Event $event )
    {
        // Если повторять каждый день
        if ( $event->getEvery() == 1 ) {
            $arrayDays = $this->_repeat($event, 'day');

        // Неделя
        } elseif ( $event->getEvery() == 7) {
            $arrayDays = $this->_repeat($event, 'week');

        // Месяц
        } elseif ( $event->getEvery() == 30) {
            $arrayDays = $this->_repeat($event, 'month');

        // Квартал (сейчас не работает!)
        } elseif ( $event->getEvery() == 90) {
            $arrayDays = $this->_repeat($event, 'quartal');

        // Год
        } elseif ( $event->getEvery() == 365) {
            $arrayDays = $this->_repeat($event, 'year');

        // Без повторения
        } else {
            $arrayDays = array( $event->getDate() );
        }

        if (count($this->getErrors()) == 0) {
            $model = Calendar_Model::create($this->_user, $event, $arrayDays);
        } else {
            return false;
        }
        return true;
    }

    /**
     * Редактируем события
     * @param Calendar_Event $event
     * @return bool
     */
    public function edit ( Calendar_Event $event )
    {

        $eventArray = Calendar_Model::getByChain($this->_user, $event->getChain());

        if (count($eventArray) == 0) {
            $this->_errors[] = "Событие не найдено";
        }

        // Если повторять каждый день
        if ( $event->getEvery() == 1 ) {
            $arrayDays = $this->_repeat($event, 'day');

        // Неделя
        } elseif ( $event->getEvery() == 7) {
            $arrayDays = $this->_repeat($event, 'week');

        // Месяц
        } elseif ( $event->getEvery() == 30) {
            $arrayDays = $this->_repeat($event, 'month');

        // Квартал (сейчас не работает!)
        } elseif ( $event->getEvery() == 90) {
            $arrayDays = $this->_repeat($event, 'quartal');

        // Год
        } elseif ( $event->getEvery() == 365) {
            $arrayDays = $this->_repeat($event, 'year');

        // Без повторения
        } else {
            $arrayDays = array($event->getDate());
        }

        if (count($this->getErrors()) == 0) {
            // Сперва удаляем все подтверждённые, затем создаём новые :)
            if ($this->deleteEvents($event->getChain())) {

                $model = Calendar_Model::update($this->_user, $event, $arrayDays);

            }
        } else {

            return false;

        }
        return true;
    }

    /**
     * Возвращает сформированный массив дат
     * @param Calendar_Event $event
     * @param string $period
     * @return array
     */
    private function _repeat(Calendar_Event $event, $period)
    {

        $datetime = new DateTime( $event->getDate() );
        $week = $event->getWeek();

        // Массив с датами события
        $arrayDays = array();

        $lastDate = strtotime($event->getLast());

        // Если мы идём до даты окончания
        if ( $lastDate > 0 ) {

            // Устанавливаем максимальное число повторов
            $endRepeat = self::MAX_EVENTS;

        } else {

            $endRepeat = $event->getRepeat();

        }

        for ($i = 1 ; $i <= $endRepeat ; $i++) {

            if ( $lastDate > 0 &&  ( $datetime->format('U') > $lastDate ) ) {
                return $arrayDays;
            }

            if ( count($arrayDays) > self::MAX_EVENTS ) {
                $this->_errors['repeat'] = "Максимальное количество повторений = " .
                    self::MAX_EVENTS . " раз, у вас " . count($arrayDays);
                return false;
            }

            if ( $period == 'week' ) {

                if ($week[$datetime->format('N')-1] == 1) {

                    $arrayDays[] = $datetime->format('Y-m-d');

                }

                // День недели для выбранной даты, от 1 (пнд) до 7 (вск)
                $dwr =  $datetime->format('N');

                // Перебираем по циклу неделю, на один день меньше
                for ( $j = 0; $j < 6; $j++ ) {
                    $dw = $dwr + $j ;
                    if ($dw > 6) {
                        $dw = $dw - 7;
                    }

                    if ( $week[$dw] == 1 ) {
                        $arrayDays[] = date('Y-m-d', $datetime->format('U') + (($j + 1) * 86400));
                    }
                }
            } else {
                $arrayDays[] = $datetime->format('Y-m-d');
            }

            $datetime->modify("+1 " . $period);
        }

        return $arrayDays;
    }

    /**
     * Удаляет цепочку событий календаря
     * @param int $chain
     * @return bool
     */
    public function deleteEvents( $chain )
    {

        if (Calendar_Model::deleteEvents($this->_user, $chain)) {

            return true;

        } else {

            $this->_errors[] = "Не удалено ни одной операции";
            return false;

        }

    }

    /**
     * При перемещении операции
     * @param int $id
     * @param mysql $date
     * @return bool
     */
    public function editDate ( $id, $date )
    {
        if ( ! $date ) {
            $this->_errors['date'] = 'Неверный формат даты';
        }

        if ( ! $id ) {
            $this->_errors['id']   = 'Не указан id';
        }

        if (count($this->_errors) == 0 && Calendar_Model::editDate($this->_user, $id, $date)) {

            return true;

        } else {

            return false;

        }
    }

    /**
     * Подтверждает события
     * @param array int
     */
    public function acceptEvents ( $ids )
    {
        $overdue  = Core::getInstance()->user->getUserEvents('overdue');
        $future   = Core::getInstance()->user->getUserEvents('future');
        $calendar = Core::getInstance()->user->getUserEvents('calendar');
        $newIds   = array();

        foreach ( $ids as $id ) {

            if ( isset ( $overdue[$id] ) ) {
                $operation = $overdue[$id];
            } elseif ( isset ( $future[$id] ) ) {
                $operation = $future[$id];
            } elseif ( isset ( $calendar[$id] ) ) {
                $operation = $calendar[$id];
            } else {
                $this->_errors['error'] = "Не найдена операция для подтверждения";
            }

            $operation['accepted']  = 1;
            $operation['category']  = $operation['cat_id'];
            $operation['account']   = $operation['account_id'];
            $operation['toAccount'] = $operation['transfer'];
            $operation['amount']    = $operation['money'];

            $event = new Calendar_Event (
                new Calendar_Model($operation, Core::getInstance()->user), Core::getInstance()->user);

            if ( ! $event->checkData() ) {
                $this->_errors = array_merge($this->_errors, $event->getErrors());
            } else {
                $newIds[] = $id;
            }
        }

        if (count($newIds) > 0) {
            // Получаем список событий, отмечаем что они выполненные
            return Calendar_Model::acceptEvents($this->_user, $newIds);
        } else {
            return false;
        }
    }

    /**
     * Возвращает массив с ошибками
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * Возвращает массив, со всеми загруженными событиями
     */
    public function getArray()
    {
        $array = array();
        foreach ($this->_events as $k => $v) {
            $array[$k] = $v->__getArray();
        }
        return $array;
    }

}
