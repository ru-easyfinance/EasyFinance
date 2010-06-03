<?php

class _Core_TemplateEngine_Native
{
    protected $baseDir = '';
    protected $assignedVars = array();

    public function __construct( $templatesBaseDir, array $assignedVars = array() )
    {
        if( !file_exists( $templatesBaseDir ) )
        {
            throw new _Core_TemplateEngine_Exception( 'Directory "' . $templatesBaseDir . '" specified as base for templates not exist!' );
        }

        $this->baseDir = $templatesBaseDir;

        $this->assignedVars = $assignedVars;
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

    public function display( $template )
    {
        $templateFileName = $this->baseDir . $template;

        if( !file_exists( $templateFileName ) )
        {
            throw new _Core_TemplateEngine_Exception('Template "' . $templateFileName . '" not found !');
        }

        extract( $this->assignedVars );

        include( $templateFileName );
    }
}
