<?php

class _User_Model extends _Core_Abstract_Model
{
    public static function load( $userId )
    {
        if( !is_int( $userId ) )
        {
            throw new Certificate_Exception( _Core_Exception::typeErrorMessage( $userId, 'User id', 'integer' ) );
        }

        $sql = 'select * from users where id=' . $userId;

        $row = Core::getInstance()->db->selectRow( $sql );

        return new _User_Model( $row );
    }

    public static function loadByLogin( $userLogin )
    {
        $sql = 'select * from users where `user_login`=?';

        $row = Core::getInstance()->db->selectRow( $sql, $userLogin );

        if( !is_array( $row ) || !sizeof($row) )
        {
            throw new _User_Exception('User with login "' . $userLogin . '" do not exist!');
        }

        return new _User_Model( $row );
    }

    /**
     * Загружает пользователя по его почте
     *
     * @param string $userMail
     * @return _User_Model
     */
    public static function loadByEmail($userMail)
    {
        $sql = "SELECT * FROM users WHERE user_mail=?";

        $row = Core::getInstance()->db->selectRow($sql, $userMail);

        if (!is_array($row) || !count($row)) {
            throw new _User_Exception("User with email '" . $userMail ."' do not exist!");
        }

        return new _User_Model($row);
    }

    public function __get( $variable )
    {
        // Небольшой хак для удобного доступа

        $value = parent::__get($variable);

        if( null == $value && array_key_exists( 'user_' . $variable, $this->fields ) )
        {
            $value = $this->fields[ 'user_' . $variable ];
        }

        return $value;
    }

    public function save()
    {
        $sql = 'update users set user_name=?, user_pass=?, user_mail=? where id=?';

        Core::getInstance()->db->query( $sql, $this->name, $this->pass, $this->mail, $this->id );
    }

    public function delete()
    {

    }
}
