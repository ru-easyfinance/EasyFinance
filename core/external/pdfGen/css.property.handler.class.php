<?php

class CSSPropertyHandler {
  var $_inheritable;
  var $_inheritable_text;

  function css($value, &$pipeline) { 
    $css_state =& $pipeline->getCurrentCSSState();

    if ($this->applicable($css_state)) {
      $this->replace($this->parse($value, $pipeline), $css_state); 
    };
  }

  function applicable($css_state) {
    return true;
  }

  function clearDefaultFlags(&$state) {
    $state->setPropertyDefaultFlag($this->getPropertyCode(), false);
  }

  function CSSPropertyHandler($inheritable, $inheritable_text) { 
    $this->_inheritable = $inheritable;
    $this->_inheritable_text = $inheritable_text;
  }

  function get(&$state) { 
    $property_code = $this->getPropertyCode();
    if (isset($state[$property_code])) {
      return $state[$property_code];
    } else {
      return null;
    };
  }

  function inherit($old_state, &$new_state) { 
    $code = $this->getPropertyCode();
    $new_state[$code] = ($this->_inheritable ? 
                         $old_state[$code] : 
                         $this->default_value());
  }

  function isInheritableText() { 
    return $this->_inheritable_text;
  }

  function isInheritable() {
    return $this->_inheritable;
  }

  function inherit_text($old_state, &$new_state) { 
    $code = $this->getPropertyCode();

    if ($this->_inheritable_text) {
      $new_state[$code] = $old_state[$code];
    } else {
      $new_state[$code] = $this->default_value();
    };
  }

  function is_default($value) { 
    if (is_object($value)) {
      return $value->is_default();
    } else {
      return $this->default_value() === $value; 
    };
  }

  function is_subproperty() { return false; }

  function replace($value, &$state) { 
    $state->setProperty($this->getPropertyCode(), $value);
  }

  function replaceDefault($value, &$state) { 
    $state->setPropertyDefault($this->getPropertyCode(), $value);
  }

  function replace_array($value, &$state) {
    $state[$this->getPropertyCode()] = $value;
  }
}

?>