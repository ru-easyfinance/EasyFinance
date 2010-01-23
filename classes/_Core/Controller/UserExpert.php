<?php

abstract class _Core_Controller_UserExpert extends _Core_Controller_User
{
	/**
	 * Конструктор
	 *
	 */
	final public function __construct( $templateEngine )
	{
		parent::__construct( $templateEngine );
		
		// Проверка на тип пользователя
		if( Core::getInstance()->user->getType() !== 1 )
		{
			header( 'Location: /info/' );
			exit;
		}
	}
}