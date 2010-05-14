<?php

class Helper_Mail
{
    /**
    * Проверяет корректность почтового ящика
    *
    * @param $email string
    * @return bool
    */
    public static function validateEmail( $email )
    {
        if ( ! empty($email) ) {

            if (preg_match( '/^[a-z0-9_\-\.\+]+@[a-z0-9_\-\.]+\.[a-z]{2,4}$/i', $email) ) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
