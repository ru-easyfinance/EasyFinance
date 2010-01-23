<?php

abstract class _Core_Controller_UserCommon extends _Core_Controller_User
{
	/**
	 * Конструктор
	 *
	 */
	final public function __construct( $templateEngine )
	{
		parent::__construct( $templateEngine );
		
		// Если эксперт - редирект на дефолтную для экспертов
		if( Core::getInstance()->user->getType() == 1 )
		{
			header( 'Location: /expert/' );
			exit;
		}
	}
}