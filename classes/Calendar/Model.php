<?php
/**
 * Модель событий в календаре
 *
 * @author ukko <max.kamashev@easyfinance.ru>
 */
class Calendar_Model extends _Core_Abstract_Model {

    /**
     * Храним ид овнера для всяческих операций.
     *
     * @var integer
     */

    protected function __construct( array $row, User $owner )
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
    public static function loadAll( User $user, $start, $end, $delay )
    {
        $modelsArray = array();
               
//        $cache = _Core_Cache::getInstance();
//        $cacheId = 'calendarUser' . $user->getId();

        // Проверка наличия в кеше идентификаторов сообщений пользователя
        //$messageIds = $cache->get( $cacheId );
        // Если есть - запрашиваем их все из кеша
        //if ( $messageIds && is_array($messageIds) )
        //{
        //	$modelsArray = $cache->getMulti( $messageIds );
        //}

        // Запрос данных для полного календаря
        if ( ! $delay ) {
            $sql = 'SELECT c.id AS chain, c.title, c.type, c.start, c.last, c.time, c.every,
                c.repeat, c.comment, c.amount, c.cat_id, c.account_id, c.op_type, c.tags, c.week,
                e.id, e.`date`, e.accept
                FROM calend c
                RIGHT JOIN calendar_events e ON c.id = e.cal_id
                WHERE c.user_id = ? AND e.`date` BETWEEN ? AND ?';
            $rows = Core::getInstance()->db->select($sql, $user->getId(), $start, $end );
            
        // Запрос напоминалок
        } else {
            $sql = 'SELECT c.id AS chain, c.title, c.type, c.start, c.last, c.time, c.every,
            c.repeat, c.comment, c.amount, c.cat_id, c.account_id, c.op_type, c.tags, c.week,
            e.id, e.`date`, e.accept
            FROM calend c
            RIGHT JOIN calendar_events e ON c.id = e.cal_id
            WHERE c.user_id = ? AND e.`date` <= ? AND e.accept = 0';
            $rows = Core::getInstance()->db->select($sql, $user->getId(), $start );
        }

        foreach ( $rows as $row )
        {
            $model = new Calendar_Model( $row, $user );

            $modelsArray[] = $model;
        }

        // Cохранение моделей в кеш
        //$cache->set( $cacheId, $modelsArray );

        return $modelsArray;
    }

    /**
     * Создаём событие, без списка дат и повторений
     * @param User $user
     * @param <type> $type
     * @param <type> $title
     * @param <type> $comment
     * @param <type> $time
     * @param <type> $date
     * @param <type> $amount
     * @param <type> $cat
     * @param <type> $account
     * @param <type> $op_type
     * @param <type> $tags
     */
    private static function createCalendarEvent ( User $user, $type, $title, $comment, $time,
            $date, $every, $repeat, $week, $amount, $cat, $account, $op_type, $tags)
    {
        // Создаём само событие
        $sql = 'INSERT INTO calend (`user_id`, `type`, `title`,
            `start`, `last`, `time`, `every`, `repeat`, `week`, `comment`,
            `amount`, `cat_id`, `account_id`, `op_type`, `tags`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        Core::getInstance()->db->query($sql, $user->getId(), $type, $title,
            $date, $date, $time, $every, $repeat, $week, $comment,
            $amount, $cat, $account, $op_type, $tags);
        return mysql_insert_id();
    }

    /**
     * Создаёт список событий
     * @param User $user
     * @param string $type
     * @param string $title
     * @param string $comment
     * @param string $time
     * @param string $date
     * @param int $every
     * @param int $repeat
     * @param string $week
     * @param float $amount
     * @param int $cat
     * @param int $account
     * @param int $op_type
     * @param string $tags
     * @param array $array mixed
     * @return bool
     */
    public static function create ( User $user, $type, $title, $comment, $time,
            $date, $every, $repeat, $week, $amount, $cat, $account, $op_type, $tags, $array )
    {
        $cal_id = self::createCalendarEvent($user, $type, $title, $comment, $time,
                $date, $every, $repeat, $week, $amount, $cat, $account, $op_type, $tags);

        // Создаём повторы события
        $sql = '';
        foreach ($array as $value) {
            if ( !empty ($sql) ) $sql .= ',';
            $sql .= "('{$cal_id}','{$value}')";
        }
        $sql = 'INSERT INTO calendar_events (`cal_id`,`date`) VALUES '.$sql;
        Core::getInstance()->db->query($sql);
        return true;
    }
    
    /**
     * Обновляем дату для определённого одного события
     * @param int $id
     * @param int $chain
     * @param string $date
     * @return bool
     */
    public static function updateEventSingleDate ( $id, $chain, $date )
    {
        $sql = "UPDATE calendar_events c SET `date`=? WHERE id=? AND cal_id=?";
        return Core::getInstance()->db->query( $sql, $date, $id, $chain );
    }

    /**
     *
     * @param User $user
     * @param <type> $id
     * @param <type> $chain
     * @param <type> $type
     * @param <type> $title
     * @param <type> $comment
     * @param <type> $time
     * @param <type> $date
     * @param <type> $every
     * @param <type> $repeat
     * @param <type> $week
     * @param <type> $amount
     * @param <type> $cat
     * @param <type> $account
     * @param <type> $op_type
     * @param <type> $tags
     * @param <type> $array
     */
    public static function update ( User $user, $id, $chain, $type, $title, $comment, $time,
            $date, $every, $repeat, $week, $amount, $cat, $account, $op_type, $tags, $array )
    {
        $sql = 'UPDATE calend SET `title`=?, `comment`=?, `time`=?, `amount`=?,
            cat_id=?, account_id=?, op_type=?, tags=? WHERE id=? AND user_id=?';
        return Core::getInstance()->db->query($sql, $title, $comment, $time,
            $amount, $cat, $account, $op_type, $tags, $chain, $user->getId());
    }

    /**
     * Загрузить по ID и по цепочке
     * @param int $id
     * @param int $chain
     * @return Calendar_Model
     */
    public static function loadById ( User $user, $id, $chain )
    {
        $sql = 'SELECT c.id AS chain, c.title, c.type, c.start, c.last, c.time, c.every,
                c.repeat, c.comment, c.amount, c.cat_id, c.account_id, c.op_type, c.tags, c.week,
                e.id, e.`date`, e.accept
            FROM calendar_events e
            LEFT JOIN calend c ON c.id = e.cal_id 
            WHERE c.user_id = ? AND c.id = ? AND e.id= ?';
       $row = Core::getInstance()->db->selectRow($sql, $user->getId(), $chain, $id );
       return new Calendar_Model( $row, $user );
    }

    /**
     * Удаляет события
     * @param User $user
     * @param int  $id
     * @param int  $chain
     * @param string $use_mode 'single' | 'all' | 'follow'
     */
    public static function deleteEvents ( User $user, $id, $chain, $use_mode )
    {
        if ( $use_mode == 'single' ) {
            if ( is_array($id) ) {
                $ids = '';
                foreach ($id as $v) {
                    if ( !empty ($ids) ) $ids .= ',';
                    $ids .= $v;
                }
                $sql = 'DELETE calendar_events FROM calendar_events
                    RIGHT JOIN calend ON user_id = ' . $user->getId() .
                    ' AND calend.id = calendar_events.cal_id
                    WHERE calendar_events.id IN (' . $ids . ')';
            } else {
                $sql = 'DELETE calendar_events FROM calendar_events
                    RIGHT JOIN calend ON user_id = ' . $user->getId() .
                    ' AND calend.id = calendar_events.cal_id
                    WHERE calendar_events.id = ' . $id;
            }
        } elseif ( $id > 0 || is_array( $id ) ) {
            $sql = 'DELETE calendar_events FROM calendar_events
                LEFT JOIN calend ON user_id = ' . $user->getId() .
                ' WHERE cal_id = ' . $chain;
            if ($use_mode == 'follow') {
                $sql .= ' AND calendar_events.id >= ' .$id;
            }
        }

        Core::getInstance()->db->query($sql);

        if ( $use_mode == 'all' ) {
            $sql = 'DELETE FROM calend WHERE user_id = ? AND id = ?';
            Core::getInstance()->db->query($sql, $user->getId(), $chain);
        }

        return true;
    }

    /**
     * Удаляет события
     * @param User $user
     * @param int  $id
     */
    public static function acceptEvents ( User $user, $id )
    {
        if ( is_array($id) ) {
            $ids = '';
            foreach ($id as $v) {
                if ( !empty ($ids) ) $ids .= ',';
                $ids .= $v;
            }
            $sql = 'UPDATE calendar_events c SET accept = 1 WHERE id IN (' . $ids . ')';
        } else {
            $sql = 'UPDATE calendar_events c SET accept = 1 WHERE id = ' . $ids;
        }

        Core::getInstance()->db->query($sql);
        $sql = 'SELECT e.id, c.amount, c.cat_id, c.account_id, c.op_type, c.tags, 
            c.comment, e.`date`
            FROM calendar_events e
            LEFT JOIN calend c ON c.id = e.cal_id 
            WHERE c.type = "p" AND c.user_id = ' . $user->getId() .  ' AND e.id IN ( ' . $ids . ' ) ';

        return Core::getInstance()->db->query($sql);
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

