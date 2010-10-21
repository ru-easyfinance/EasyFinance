<?php
/**
 * Слушатель коннекта
 */
class myDoctrineConnectionListener extends Doctrine_EventListener
{
    protected $connection, $attributes;

    /**
     * @param  Doctrine_Connection $connection
     * @param  array  $attributes  Атрибуты для коннекта PDO (ключ => значение)
     */
    public function __construct(Doctrine_Connection $connection, array $attributes = array())
    {
        $this->connection = $connection;
        $this->attributes = $attributes;
    }


    /**
     * Событие: после реального соединения с БД
     */
    public function postConnect(Doctrine_Event $event)
    {
        $pdoDbh = $this->connection->getDbh();
        foreach ($this->attributes as $name => $value) {
            $constName = 'PDO::' . $name;
            $pdoDbh->setAttribute(constant($constName), $value);
        }
    }

}
