<?php

class Helper_Date
{
	/**
	 * Русские названия месяцев в родительном падеже
	 *
	 * @var array
	 */
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
	
	/**
	 * Русские названия месяцев в именительном падеже
	 *
	 * @var array
	 */
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
	
	/**
	 * Возвращает дату, переформатированную в строку типа "21 января 2012"
	 *
	 * @param string $dateString исходная строка с датой
	 * @param boolean $rusMonth Указатель - аодставлять русское название месяца или оставить числовое
	 * @return string Дата
	 */
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
	
	/**
	 * Форматирует Unix timestamp в mysql date
	 *
	 * @param integer $timestamp - Unix timestamp
	 * @return string Дата в формате mysql ("1983-05-22 12:43:03")
	 */
	public static function getMysql( $timestamp )
	{
		if( !is_numeric( $timestamp ) || !(int)$timestamp )
		{
			throw new Exception( 'Given timestamp do not look as real Unix timestamp!' );
		}
		
		return date( "Y-m-d H:i:s", $timestamp );
	}
	
	/**
	 * Форматирует строку с датой в mysql date
	 *
	 * @param string $dateString
	 * @return string Дата в формате mysql ("1983-05-22 12:43:03")
	 */
	public static function getMysqlFromString( $dateString )
	{
		return self::getMysql( strtotime($dateString) );
	}


    /**
    * Форматирует русское представление даты, например: <code>20.02.2009</code> в формат даты mysql <code>2009-02-20</code>
    * @param string $date Дата, в формате дд.мм.гггг
    * @param string $time Время в формате чч:мм
    * @return string в случае ошибки false
    */
   public static function RusDate2Mysql ( $date, $time = '00:00' )
   {
       /**
        * Собирает в себе отформатированную дату
        * @var string
        */
       $retval = '';
       if ( empty ( $date ) ) {
           return false;
       }

       $date = explode ( '.', $date );

       if ( count ( $date ) == 3 ) {

           // Добавляем год
           $retval = ( int ) $date[2] . '-';

           // Добавляем месяц
           if ( ( int ) $date[1] < 10 ) {
               $retval .= '0' . ( int ) $date[1] . '-';
           } else {
               $retval .= ( int ) $date[1] . '-';
           }

           // Добавляем день
           if ( ( int ) $date[0] < 10 ) {
               $retval .= '0' . ( int ) $date[0];
           } else {
               $retval .= ( int ) $date[0];
           }

           //  Добавляем время (если есть)
           if ( empty ( $time ) ) {
               return $retval;
           } elseif ( preg_match ( "/^([0-9]{2}):([0-9]{2})$/", $time ) ) {
               return $retval . ' ' . $time . ':00';
           }

       } else {
           return false;
       }
   }


}
