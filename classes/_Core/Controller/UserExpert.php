<?php

abstract class _Core_Controller_UserExpert extends _Core_Controller_User
{
	/**
	 * Конструктор
	 *
	 */
	final public function __construct()
	{
		parent::__construct();
		
		// Проверка на тип пользователя
		if( Core::getInstance()->user->getType() !== 1 )
		{
			header( 'Location: /info/' );
			exit;
		}
	}
}