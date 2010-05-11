<?php

class _Core_TemplateEngine_Json extends _Core_Abstract_TemplateEngineOutput
{
    public function display()
    {
        //echo '<pre>' . print_r($this->excludedVars,true);

        //echo '<pre>' . print_r(array_diff_key($this->assignedVars, $this->excludedVars),true);

        echo json_encode( array_diff_key($this->assignedVars, $this->excludedVars) );
    }
}