<?php

class _Core_TemplateEngine_Json
{
	protected $assignedVars = array();
	
	protected $excludedVars = array();
	
	public function __construct()
	{
	}
	
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
	
	public function excludeFromOutput( array $varsArray )
	{
		$this->excludedVars += array_flip($varsArray);
	}
	
	public function append( $variable, $value, $merge=false )
	{
		if( $variable == '' || !isset($value) )
		{
			return false;
		}
		
		if( !isset($this->assignedVars[$variable]) )
		{
			$this->assignedVars[$variable] = array();
		}
		elseif ( !is_array($this->assignedVars[$variable]) )
		{
			$this->assignedVars[$variable] = array($this->assignedVars[$variable]);
		}
		
		$this->assignedVars[$variable][] = $value;
	}
	
	public function display()
	{
		//echo '<pre>' . print_r($this->excludedVars,true);
		
		//echo '<pre>' . print_r(array_diff_key($this->assignedVars, $this->excludedVars),true);
		
		echo json_encode( array_diff_key($this->assignedVars, $this->excludedVars) );
	}
}
