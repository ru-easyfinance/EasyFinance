<?php

class CSSState {
  var $_state;
  var $_stateDefaultFlags;
  var $_handlerSet;
  var $_baseFontSize;

  function CSSState(&$handlerSet) {
    $this->_handlerSet        =& $handlerSet;
    $this->_state             =  array($this->_getDefaultState());
    $this->_stateDefaultFlags =  array($this->_getDefaultStateFlags());

    /**
     * Note that default state should contain font size in absolute units (e.g. 11pt),
     * so we may pass any value as a base font size parameter of 'toPt' method call
     */
    $this->_baseFontSize      =  array($this->_state[0][CSS_FONT]->size->toPt(0));
  }

  function _getDefaultState() {
    return $this->_handlerSet->_getDefaultState();
  }

  function _getDefaultStateFlags() {
    return $this->_handlerSet->_getDefaultStateFlags();
  }

  function replaceParsed($property_data, $property_list) {
    foreach ($property_list as $property) {
      $this->setProperty($property, $property_data->getCSSProperty($property));
    };
  }

  function popState() {
    array_shift($this->_state);
    array_shift($this->_stateDefaultFlags);
    array_shift($this->_baseFontSize);
  }

  function getStoredState(&$base_font_size, &$state, &$state_default_flags) {
    $base_font_size      = array_shift($this->_baseFontSize);
    $state               = array_shift($this->_state);
    $state_default_flags = array_shift($this->_stateDefaultFlags);
  }

  function pushStoredState($base_font_size, $state, $state_default_flags) {
    array_unshift($this->_baseFontSize,      $base_font_size);
    array_unshift($this->_state,             $state);
    array_unshift($this->_stateDefaultFlags, $state_default_flags);
  }

  function pushState() {
    $base_size = $this->getBaseFontSize();
    /**
     * Only computed font-size values are inherited; this means that 
     * base font size value should not be recalculated if font-size was not set explicitly
     */
    if ($this->getPropertyDefaultFlag(CSS_FONT_SIZE)) {
      array_unshift($this->_baseFontSize, $base_size);
    } else {
      $size = $this->getInheritedProperty(CSS_FONT_SIZE);
      array_unshift($this->_baseFontSize, $size->toPt($base_size));
    };

    array_unshift($this->_state, $this->getState());
    array_unshift($this->_stateDefaultFlags, $this->_getDefaultStateFlags());
  }

  function pushDefaultState() {
    $this->pushState();
    $this->_state[0] = $this->_getDefaultState();

    $handlers = $this->_handlerSet->getInheritableHandlers();

    foreach ($handlers as $property => $handler) {
      $handler->inherit($this->_state[1], $this->_state[0]);
    };
  }

  function pushDefaultTextState() {
    $state = $this->getState();

    $this->pushState();
    $this->_state[0] = $this->_getDefaultState();
    $new_state =& $this->getState();

    $handlers = $this->_handlerSet->getInheritableTextHandlers();
    foreach ($handlers as $property => $handler) {
      $handler->inherit_text($state, $new_state);
    }
  }

  function &getStateDefaultFlags() {
    return $this->_stateDefaultFlags[0];
  }

  function &getState() {
    return $this->_state[0];
  }

  function getInheritedProperty($code) {
    $handler =& CSS::get_handler($code);

    $size = count($this->_state);
    for ($i=0; $i<$size; $i++) {
      $value = $handler->get($this->_state[$i]);
      if ($value != CSS_PROPERTY_INHERIT) {
        return $value;
      };

      // Prevent taking  the font-size property; as,  according to CSS
      // standard,  'inherit'  should mean  calculated  value, we  use
      // '1em' instead,  forcing the script to  take parent calculated
      // value later
      if ($code == CSS_FONT_SIZE) {
        return Value::fromData(1, UNIT_EM);
      };
    };

    return null;
  }

  function getPropertyOnLevel($code, $level) {
    return $this->_state[$level][$code];
  }

  function getProperty($code) {
    $handler =& CSS::get_handler($code);
    return $handler->get($this->getState());
  }

  function getPropertyDefaultFlag($code) {
    return $this->_stateDefaultFlags[0][$code];
  }

  function setPropertyOnLevel($code, $level, $value) {
    $this->_state[$level][$code] = $value;
  }

  function setPropertyDefault($code, $value) {
    $state =& $this->getState();
    $state[$code] = $value;
  }

  function setProperty($code, $value) {
    $this->setPropertyDefault($code, $value);

    $handler =& CSS::get_handler($code);
    $handler->clearDefaultFlags($this);
  }

  function setPropertyDefaultFlag($code, $value) {
    $state_flags =& $this->getStateDefaultFlags();
    $state_flags[$code] = $value;
  }

  function getBaseFontSize() {
    return $this->_baseFontSize[0];
  }
}

?>