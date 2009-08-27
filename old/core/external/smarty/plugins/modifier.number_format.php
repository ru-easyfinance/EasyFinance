<?php
/**
 * Smarty plugin
 */


/**
 * Smarty number_format modifier plugin
 *
 * Type:     modifier<br>
 * Name:     number_format<br>
 * Purpose:  format number via number_format
 * @link http://www.php.net/manual/ru/function.number-format.php
 *          number_format (PHP online manual)
 * @author   Roman Korostov <pint at narod dot ru>
 * @param number
 * @return number
 */
function smarty_modifier_number_format($number)
{
	if ($number > 0)
	{
    	return number_format($number, 2, '.', ' ');
	}
	else
	{
		if (!empty($number))
		{
			$result = substr($number, 1);
			$result = "-".number_format($result, 2, '.', ' ');
			return $result;
		}
		return $number;
	}
}

/* vim: set expandtab: */

?>
