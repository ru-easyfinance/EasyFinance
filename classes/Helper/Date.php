<?php

class Helper_Date
{
	private static $rusMonthsGenitive = array(
		1 => 'января',
		'февраля',
		'марта',
		'апреля',
		'мая',
		'июня',
		'июля',
		'августа',
		'сентября',
		'октября',
		'ноября',
		'декабря'
	);
	
	private static $rusMonths = array(
		1 => 'январь',
		'февраль',
		'март',
		'апрель',
		'мaрт',
		'июнь',
		'июль',
		'август',
		'сентябрь',
		'октябрь',
		'ноябрь',
		'декабрь'
	);
	
	public static function getRusMonth( $monthNum, $genitive = false )
	{
		$monthNum = (int)$monthNum;
		
		if( $monthNum > 12 || $monthNum < 1 )
		{
			throw new Exception( 'Get out, evil aliens! You will never eat my brain!' );
		}
		
		if( $genitive )
		{
			return self::$rusMonthsGenitive[ $monthNum ];
		}
		else
		{
			return self::$rusMonths[ $monthNum ];
		}
	}
	
	public static function getFromString( $dateString, $rusMonth = true )
	{
		$timestamp = strtotime( $dateString );
		
		if( $rusMonth )
		{
			return date("d ", $timestamp ) . self::getRusMonth( date("m", $timestamp), true ) . date(" Y");
		}
		else
		{
			return date( "d.m.Y", $timestamp );
		}
	}
}
