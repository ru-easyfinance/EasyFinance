<?php

abstract class _Core_Controller_User extends _Core_Controller
{
	public function __construct( $templateEngine )
	{
		//Проверяем авторизован ли пользователь. Если нет - редиректим на логин
		if( !Core::getInstance()->user->getId() )
		{
			header( 'Location: /login/' );
		}
		
		parent::__construct( $templateEngine );
	}
}