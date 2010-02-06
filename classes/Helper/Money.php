<?php

class Helper_Money
{
	public static function format( $sum )
	{
		$waste = ($sum < 0)?true:false;
		
		$sum = number_format( $sum, 2, ',', ' ' );
		
		return $waste?$sum:$sum;
	}
}
