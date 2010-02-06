<?php

abstract class _Core_Controller_UserExpert extends _Core_Controller_User
{
	/**
	 * Конструктор
	 *
	 */
	final public function __construct( $templateEngine, _Core_Request $request )
	{
		parent::__construct( $templateEngine, $request );
		
		// Проверка на тип пользователя
		if( Core::getInstance()->user->getType() !== 1 )
		{
			header( 'Location: /info/' );
			exit;
		}
	}
}