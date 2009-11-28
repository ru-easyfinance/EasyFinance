<?php

class _Core_Exception extends Exception
{
	public static function typeErrorMessage( &$var, $varDesc, $varType )
	{
		$typeFunction = false;
		
		if( !class_exists($varType, true) && !interface_exists($varType, true) )
		{
			$typeFunction = 'is_' . $varType;
			
			if( !function_exists( $typeFunction ) )
			{
				throw new _Core_Exception( 'Unknown type "' . $varType . '" given !' );
			}
		}
		
		return ucfirst( $varDesc ) . ' must be ' . ( $typeFunction?'':'instance of' ) . ' "' . $varType . '", ' . gettype( $var ) . ' given!';
	}
}