<?php
/**
 * Модель событий в календаре
 *
 * @author ukko <max.kamashev@easyfinance.ru>
 */
class Calendar_Model extends _Core_Abstract_Model
{

    /**
     * Храним ид овнера для всяческих операций.
     *
     * @var integer
     */
    public function __construct( array $row, oldUser $owner )
    {
            $this->ownerId = $owner->getId();

            $this->fields = $row;
    }

    /**
     * Загрузка всех событий календаря для пользователя
     *
     * @param integer $userId
     * @param mysqldate $start
     * @param mysqldate $end
     * @param bool $delay Если - тру, то отдаёт напоминалки
     *
     * @return array Массив моделей событий
     */
    public static function loadAll( oldUser $user, $start, $end )
    {
        $modelsArray = array();

//        $cache = _Core_Cache::getInstance();
//        $cacheId = 'calendarUser' . $user->getId();
//
//        // Проверка наличия в кеше идентификаторов сообщений пользователя
//        $messageIds = $cache->get( $cacheId );
//        // Если есть - запрашиваем их все из кеша
//        if ( $messageIds && is_array($messageIds) )
//        {
//            $modelsArray = $cache->getMulti( $messageIds );
//        }

        // Запрос данных для полного календаря

        $sql = 'SELECT
                    o.id,
                    o.chain_id AS chain,
                    o.type,
                    o.money AS amount,
                    o.comment,
                    o.cat_id AS category,
                    o.account_id AS account,
                    o.tags,
                    DATE_FORMAT( o.date, "%d.%m.%Y" ) AS date,
                    o.time,
                    DATE_FORMAT(c.start, "%d.%m.%Y" ) AS start,
                    DATE_FORMAT(c.last, "%d.%m.%Y" ) AS last,
                    c.every,
                    c.repeat,
                    c.week,
                    o.accepted,
                    o.transfer_account_id AS transfer,
                    o.source_id AS source
                FROM
                    operation o
                LEFT JOIN
                    calendar_chains c
                ON
                    c.id=o.chain_id
                WHERE
                    o.user_id = ?
                AND
                    o.`date` BETWEEN ? AND ?
                AND
                    (o.accepted=0 OR o.chain_id > 0)
                AND o.deleted_at IS NULL';

        $rows = Core::getInstance()->db->select($sql, $user->getId(), $start, $end);

        foreach ($rows as $row) {
            // Добавляем напомнинания
            $sql = "SELECT * FROM operation_notifications WHERE operation_id=?";
            $notifications = Core::getInstance()->db->select($sql, $row['id'] );

            $row = self::_addNotificationInfo( $user, $row );

            $model = new Calendar_Model($row, $user);

            $modelsArray[$row['id']] = $model;
        }

        // Cохранение моделей в кеш
        //$cache->set( $cacheId, $modelsArray );

        return $modelsArray;
    }

    /**
     * Загрузка всех неподтверждённых событий для указанного пользователя
     *
     * @param oldUser $user
     *
     * @return array
     */
    public static function loadOverdue(oldUser $user)
    {
        $modelsArray = array();

        // Запрос данных для полного календаря

        $sql = 'SELECT
                    o.id,
                    o.chain_id AS chain,
                    o.type,
                    o.money AS amount,
                    o.comment,
                    o.cat_id AS category,
                    o.account_id AS account,
                    o.tags,
                    DATE_FORMAT( o.date, "%d.%m.%Y" ) AS date,
                    o.time,
                    DATE_FORMAT(c.start, "%d.%m.%Y" ) AS start,
                    DATE_FORMAT(c.last, "%d.%m.%Y" ) AS last,
                    c.every,
                    c.repeat,
                    c.week,
                    o.accepted,
                    o.transfer_account_id AS transfer,
                    o.source_id AS source
                FROM
                    operation o
                LEFT JOIN
                    calendar_chains c
                ON
                    c.id=o.chain_id
                WHERE
                    o.user_id = ?
                AND
                    o.`date` <= CURRENT_DATE()
                AND
                    o.accepted=0
                AND
                    o.deleted_at IS NULL';

        $rows = Core::getInstance()->db->select($sql, $user->getId());

        foreach ($rows as $row) {
            // Добавляем напомнинания
            $sql = "SELECT * FROM operation_notifications WHERE operation_id=?";
            $notifications = Core::getInstance()->db->select($sql, $row['id'] );

            $row = self::_addNotificationInfo( $user, $row );

            $model = new Calendar_Model($row, $user);

            $modelsArray[$row['id']] = $model;
        }

        return $modelsArray;
    }

    /**
     * Выводит список напоминалок на неделю вперёд
     *
     * @param oldUser $user
     */
    public static function loadReminder(oldUser $user)
    {
        $modelsArray = array();

        // Запрос данных для полного календаря

        $sql = 'SELECT
                    o.id,
                    o.chain_id AS chain,
                    o.type,
                    o.money AS amount,
                    o.comment,
                    o.cat_id AS category,
                    o.account_id AS account,
                    o.tags,
                    DATE_FORMAT( o.date, "%d.%m.%Y" ) AS date,
                    o.time,
                    DATE_FORMAT(c.start, "%d.%m.%Y" ) AS start,
                    DATE_FORMAT(c.last, "%d.%m.%Y" ) AS last,
                    c.every,
                    c.repeat,
                    c.week,
                    o.accepted,
                    o.transfer_account_id AS transfer,
                    o.source_id AS source
                FROM
                    operation o
                LEFT JOIN
                    calendar_chains c
                ON
                    c.id=o.chain_id
                WHERE
                    o.user_id = ?
                AND
                    o.`date` BETWEEN ADDDATE(CURRENT_DATE(), INTERVAL 1 DAY) AND ADDDATE(CURRENT_DATE(), INTERVAL 8 DAY)
                AND
                    o.accepted=0
                AND
                    o.deleted_at IS NULL';

        $rows = Core::getInstance()->db->select($sql, $user->getId());

        foreach ($rows as $row) {
            $row = self::_addNotificationInfo( $user, $row );

            $model = new Calendar_Model($row, $user);

            $modelsArray[$row['id']] = $model;
        }

        return $modelsArray;
    }


    /**
     * Добавляет в запись информацию о напоминаниях
     *
     * @param oldUser $user
     * @param array $row
     * @return array
     */
    private function _addNotificationInfo( oldUser $user, array $row )
    {
        // Добавляем напомнинания
        $sql = "SELECT * FROM operation_notifications WHERE operation_id=?";
        $notifications = Core::getInstance()->db->select($sql, $row['id'] );

        // Получаем смещение временной зоны пользователя относительно времени сервера
        $offset = ( ( $user->getUserProps('time_zone_offset') - round( ( date("O") / 100 ), 2 ) )*3600);

        // Напоминания об операциях
        if ( count( $notifications ) ) {
            foreach ( $notifications as $notrow ) {
                // SMS
                if ( $notrow['type'] == 0 ) {
                    $row['smsEnabled'] = 1;
                    $dtTimestamp = strtotime( $notrow['date_time'] ) + $offset;
                    $daysBefore = floor(abs( strtotime( $row['date']) - strtotime( date( "Y-m-d", $dtTimestamp ) ) ) / (3600*24));
                    $row['smsDaysBefore'] = $daysBefore;
                    $row['smsHour'] = date('H', $dtTimestamp);
                    $row['smsMinutes'] = date('i', $dtTimestamp);
                }

                // Email
                if ( $notrow['type'] == 1 ) {
                    $row['mailEnabled'] = 1;
                    $dtTimestamp = strtotime( $notrow['date_time'] ) + $offset;
                    $daysBefore = floor(abs( strtotime( $row['date']) - strtotime( date( "Y-m-d", $dtTimestamp ) ) ) / (3600*24));
                    $row['mailDaysBefore'] = $daysBefore;
                    $row['mailHour'] = date('H', $dtTimestamp);
                    $row['mailMinutes'] = date('i', $dtTimestamp);
                }
            }
        }

        return $row;
    }


    /**
     * Создаёт цепочку операций
     * @param oldUser $user Пользователь
     * @param Calendar_Event $event Событие
     * @param array $arrayDays Массив с днями, повторениями
     * @return bool
     */
    public static function create (oldUser $user, Calendar_Event $event, $arrayDays)
    {

        // Создаём само событие
        $sql = "INSERT INTO calendar_chains (`user_id`,`start`,`last`,`every`, `repeat`, `week`)
            VALUES (?, ?, ?, ?, ?, ?);";

        // Создаём событие в календаре
        $calId = Core::getInstance()->db->query(
            $sql,
            $user->getId(),
            $event->getDate(),
            $event->getLast(),
            $event->getEvery(),
            $event->getRepeat(),
            $event->getWeek()
        );

        return self::createOperations($user, $event, $calId, $arrayDays);
    }

    /**
     * Обновляет события
     * @param oldUser $user
     * @param Calendar_Event $event
     * @param array $array
     * @return bool
     */
    public static function update ( oldUser $user, Calendar_Event $event, $array )
    {

        // Создаём само событие
        $sql = "UPDATE calendar_chains c
            SET `last` = ?, `every` = ?, `repeat` = ?, `week` = ?
            WHERE `user_id` = ? AND id = ? ;";

        // Создаём событие в календаре
        Core::getInstance()->db->query(
            $sql,
            $event->getLast(),
            $event->getEvery(),
            $event->getRepeat(),
            $event->getWeek(),
            $user->getId(),
            $event->getChain()
        );

        // Возвращает даты подтверждённых в этой серии
        $accepted = self::loadAcceptedByChain($user, $event->getChain());

        // Создаём повторы события
        $arrayDays = array();
        foreach ($array as $value) {
            if (in_array($value, $accepted)) {
                continue;
            }
            $arrayDays[] = $value;
        }

        return self::createOperations($user, $event, $event->getChain(), $arrayDays);
    }

    /**
     * Возвращает даты подтверждённых событий в серии
     * @param oldUser $user
     * @param int $chain
     * @return array
     */
    public static function loadAcceptedByChain (oldUser $user, $chain)
    {
        $sql = 'SELECT `date` FROM operation c WHERE user_id=? AND chain_id=? AND accepted=1 AND deleted_at IS NULL';
        return Core::getInstance()->db->selectCol($sql, $user->getId(), $chain);
    }

    /**
     * Добавляет рег. операции
     * @param oldUser $user
     * @param Calendar_Event $event
     * @param int $chain
     * @param array $arrayDays
     */
    private function createOperations (oldUser $user, Calendar_Event $event, $chain, $arrayDays )
    {
        // Создаём повторы события в виде неподтверждённых операций
        $operationsArray = array();

        foreach ($arrayDays as $value) {

            // @TODO Посмотреть, как можно адаптировать $event->__getArray()
            $operationsArray[] = array (
                'type'       => $event->getType(),
                'account'    => $event->getAccount(),
                'amount'     => $event->getAmount(),
                'category'   => $event->getCategory(),
                'date'       => $value,
                'comment'    => $event->getComment(),
                'tags'       => $event->getTags(),
                'convert'    => $event->getConvert(),
                'close'      => $event->getClose(),
                'currency'   => $event->getCurrency(),
                'toAccount'  => $event->getToAccount(),
                'target'     => $event->getTarget(),

                // Дополнения для планирования в календарь
                'last'       => $event->getLast(),
                'time'       => $event->getTime(),
                'every'      => $event->getEvery(),
                'repeat'     => $event->getRepeat(),
                'week'       => $event->getWeek(),
                'accepted'   => 0,
                'chain'      => $chain,

                // Напоминания
                'mailEnabled'       => $event->getMailEnabled(),
                'mailDaysBefore'    => $event->getMailDaysBefore(),
                'mailHour'          => $event->getMailHour(),
                'mailMinutes'       => $event->getMailMinutes(),

                'smsEnabled'        => $event->getSmsEnabled(),
                'smsDaysBefore'     => $event->getSmsDaysBefore(),
                'smsHour'           => $event->getSmsHour(),
                'smsMinutes'        => $event->getSmsMinutes(),
            );
        }

        $operation = new Operation_Model();

        // Расход и доход
        if ($event->getType() <= 1) {
            return $operation->addSome($operationsArray);
        } elseif ($event->getType() == 2) {
            return $operation->addSomeTransfer($operationsArray);
        }
    }

    /**
     * Получает операцию в виде массива
     * @param oldUser $user
     * @param int $chain
     * @return array
     */
    public static function getByChain ( oldUser $user, $chain )
    {
        $sql = 'SELECT * FROM calendar_chains c WHERE user_id=? AND id=?';
        return Core::getInstance()->db->selectRow($sql, $user->getId(), $chain);
    }

    /**
     * Удаляет события
     * @param oldUser $user
     * @param int  $chain
     * @return bool
     */
    public static function deleteEvents ( oldUser $user, $chain )
    {
        $sql = "DELETE FROM operation WHERE user_id=? AND chain_id=? AND accepted=0";

        if ( Core::getInstance()->db->query($sql, $user->getId(), $chain) ) {

            return true;

        } else {

            return false;

        }
    }

    /**
     * Отмечает события как выполненные
     * @param oldUser $user
     * @param array $ids array(int, int, int, ..)
     * @return bool
     */
    public static function acceptEvents ( oldUser $user, $ids )
    {
        $operations = array();
        foreach ($ids as $value) {
            $operations[] = array(
                'accepted' => '1',
                'id'       => $value,
            );
        }
        $operation = new Operation_Model($user);
        return (bool) $operation->editMultiple($operation);
    }

    /**
     * Редактирует дату операции
     * @param oldUser $user
     * @param int $id
     * @param mysql date $date
     * @return bool
     */
    public static function editDate ( oldUser $user, $id, $date )
    {
        $sql = "UPDATE operation SET `date`= ? WHERE id =? AND user_id=?;";

        return Core::getInstance()->db->query($sql, $date, $id, $user->getId());

    }

    /**
     *
     */
    function load ()
    {

    }

    function save()
    {

    }

    public function delete ()
    {

    }
}
