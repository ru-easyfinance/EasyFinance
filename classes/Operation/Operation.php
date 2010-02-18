<?php

/**
 * Операция
 * @copyright http://easyfinance.ru/
 * @author Andrew Tereshko aka mamonth
 *
 */
class Operation
{
	const TYPE_WASTE 	= 0;
	const TYPE_PROFIT 	= 1;
	const TYPE_TRANSFER 	= 2;
	const TYPE_TARGET 	= 4;
	
	private static $types = array(
		self::TYPE_WASTE 	=> 'waste',
		self::TYPE_PROFIT 	=> 'profit',
		self::TYPE_TRANSFER 	=> 'transfer',
		self::TYPE_TARGET 	=> 'target',
	);
	
	/**
	 * Возвращает массив типов операций
	 *
	 */
	final public static function getTypesArray()
	{
		return self::$types;
	}
}
