<?php

abstract class _Core_Controller_UserExpert extends _Core_Controller
{
	/**
	 * Конструктор
	 *
	 */
	final public function __construct()
	{
		// Проверка на тип пользователя
		if( Core::getInstance()->user->getType() !== 1 )
		{
			// Если авторизован - редирект на дефолтную для пользователя
			if( Core::getInstance()->user->getType() === 0 )
			{
				header( 'Location: /info/' );
			}
			else // если не авторизован - на логин
			{
				header( 'Location: /login/' );
			}
			exit;
		}
		
		parent::__construct();
	}
}