<?php

abstract class _Core_Controller_UserCommon extends _Core_Controller
{
	/**
	 * Конструктор
	 *
	 */
	final public function __construct()
	{
		// Проверка на тип пользователя
		if( Core::getInstance()->user->getType() !== 0 )
		{
			// Если эксперт - редирект на дефолтную для экспертов
			if( Core::getInstance()->user->getType() == 1 )
			{
				header( 'Location: /expert/' );
			}
			// если не авторизован - на логин
			else 
			{
				header( 'Location: /login/' );
			}
			exit;
		}
		
		parent::__construct();
	}
}