<?php

abstract class _Core_Abstract_TemplateEngineOutput
{
    protected $assignedVars = array();

    protected $excludedVars = array();

    public function __construct()
    {
    }

    public function assign( $variable, $value )
    {
        // Совместимость с smarty
        if( is_array($variable) ) {
            throw new _Core_Exception('Multiple variables assign not supported anymore!');
        } else {
            $this->assignedVars[ $variable ] = $value;
        }
    }

    public function excludeFromOutput( array $varsArray )
    {
        $this->excludedVars += array_flip($varsArray);
    }

    public function append( $variable, $value, $merge=false )
    {
        if( $variable == '' || !isset($value) ) {
            return false;
        }

        if( !isset($this->assignedVars[$variable]) ) {
            $this->assignedVars[$variable] = array();
        } elseif ( !is_array($this->assignedVars[$variable]) ) {
            $this->assignedVars[$variable] = array($this->assignedVars[$variable]);
        }

        $this->assignedVars[$variable][] = $value;
    }

    abstract public function display();
}