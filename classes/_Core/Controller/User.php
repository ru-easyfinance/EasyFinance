<?php

abstract class _Core_Controller_User extends _Core_Controller
{
    public function __construct( $templateEngine, _Core_Request $request)
    {
        //Проверяем авторизован ли пользователь. Если нет - редиректим на логин
        if( !Core::getInstance()->CurrentUserIsAuthorized())
        {
            header( 'Location: /login/' );
        }

        parent::__construct( $templateEngine, $request );
    }
}