<?php

class Mail_Message
{
    const OUTGOING = 0;
    const INCOMING = 1;

    /**
     * Mail_MessageModel
     *
     * @var unknown_type
     */
    private $model;

    private $direction;

    /**
     * Обьект отправителя
     *
     * @var _User
     */
    public $sender;

    /**
     * Обьект получателя
     *
     * @var _User
     */
    public $receiver;

    static public function create()
    {
        if( !$trash )
        {
            //$this->send();
        }
    }

    public function __construct( Mail_MessageModel $model, oldUser $user)
    {
        $this->model = $model;

        if( $this->model->sender_id == $user->getId() )
        {
            $this->direction = self::OUTGOING;

            $this->receiver = _User::load( $this->model->receiver_id );

            $this->sender = $user;
        }
        else
        {
            $this->direction = self::INCOMING;

            $this->sender = _User::load( $this->model->sender_id );

            $this->receiver = $user;
        }
    }

    public function send()
    {
        $this->model->addReceiver( $this->receiver );
    }

    public function getId()
    {
        return (int)$this->model->id;
    }

    public function getDirection()
    {
        return $this->direction;
    }

    public function getReaded()
    {
        return (bool)$this->model->readed;
    }

    public function setReaded( $state )
    {
        if( $this->getReaded() == (bool)$state )
        {
            return false;
        }

        $this->model->readed = $state?1:0;

        return true;
    }

    public function getSubject()
    {
        return $this->model->subject;
    }

    public function getDate()
    {
        return $this->model->date;
    }

    public function getBody()
    {
        return $this->model->body;
    }

    public function setBody( $text )
    {
        $this->model->body = $text;
    }

    public function getDraft()
    {
        return (boolean)$this->model->draft;
    }

    public function getTrash()
    {
        return (boolean)$this->model->trash;
    }

    public function setTrash( $state )
    {
        $this->model->trash = $state?1:0;
    }

    public function getJsonArray()
    {
        $json = array(
            'id'         => $this->getId(),
            'date'         => Helper_Date::getFromString( $this->getDate(), true ),
            'subject'    => $this->getSubject(),
            'body'        => $this->getBody(),
            'readed'    => $this->getReaded(),
            'receiverId'    => $this->receiver->getId(),
            'receiverName' => $this->receiver->getName(),
            'senderId'    => $this->sender->getId(),
            'senderName'    => $this->sender->getName(),
            'folder'        => Mail::getFolder( $this ),
        );

        return $json;
    }

    public static function load( oldUser $user, $id )
    {
        $model = Mail_MessageModel::load( (int)$id, $user );

        $message = new Mail_Message( $model, $user );

        return $message;
    }
}
