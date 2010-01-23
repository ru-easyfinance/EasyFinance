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
	public static function execRouterHook(  _Core_Request $request, &$class, &$method, array &$chunks, &$templateEngine )
	{
		switch ( $request->host )
		{
			case 'pda':
				
				break;
			default:
				
				require_once SYS_DIR_LIBS . 'external/smarty/Smarty.class.php';
				require_once SYS_DIR_LIBS . 'external/smarty/Smarty_Compiler.class.php';
				require_once SYS_DIR_LIBS . 'external/smarty/Config_File.class.php';
				
				$tpl = new Smarty();

				$tpl->template_dir    =  SYS_DIR_ROOT.'/views';
				$tpl->compile_dir     =  TMP_DIR_SMARTY.'/cache';

				$tpl->plugins_dir     =  array(SYS_DIR_LIBS.'external/smarty/plugins');
				$tpl->compile_check   =  true;
				$tpl->force_compile   =  false;
				
				$tpl->_tpl_vars 	= $templateEngine->assignedVars;
				
				$templateEngine = $tpl;
				
				unset( $tpl );
		}
		
		
	}
	
	public function __get( $variable )
	{
		if( key_exists( $variable, $this->assignedVars ) )
		{
			return $this->assignedVars[ $variable ];
		}
		
		return null;
	}
}
