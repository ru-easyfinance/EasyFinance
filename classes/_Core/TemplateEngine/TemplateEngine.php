<?php

/**
 * Базовый класс шаблонизатора
 *
 */
class _Core_TemplateEngine implements _Core_Router_iHook
{
	protected $assignedVars = array();
	
	public function assign( $variable, $value )
	{
		// Совместимость с smarty
		if( is_array($variable) )
		{
			throw new _Core_Exception('Multiple variables assign not supported anymore!');
		}
		else
		{
			$this->assignedVars[ $variable ] = $value;
		}
	}
	
	public function append( $variable, $value, $merge=false )
	{
		if( $variable == '' || !isset($value) )
		{
			return false;
		}
		
		if( !is_array($this->assignedVars[$value]) )
		{
			$this->assignedVars[$value] = isset($this->assignedVars[$value])?array($this->assignedVars[$value]):array();
		}
		
		$this->assignedVars[$value][] = $value;
	}
	
	/**
	 * Хук в роутер для выбора шаблонизатора 
	 * в зависимости от политической ситуации =)
	 *
	 */
	public static function execRouterHook(  _Core_Request $request, &$class, &$method, array &$chunks, _Core_TemplateEngine $templateEngine )
	{		
		switch ( $request->domain )
		{
			case 'pda':
				
				break;
			default:
				
				break;
		}
		
		
	}
	
	public function __call( $variable )
	{
		if( key_exists( $variable, $this->assignedVars ) )
		{
			
		}
	}
}
