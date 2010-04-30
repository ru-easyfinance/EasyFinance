<?php
// $Header: /cvsroot/html2ps/css.inc.php,v 1.25 2006/09/07 18:38:14 Konstantin Exp $

class CSS {
  var $_handlers;
  var $_mapping;
  var $_defaultState;
  var $_defaultStateFlags;

  function _getDefaultState() {
    if (!isset($this->_defaultState)) {
      $this->_defaultState = array();

      $handlers = $this->getHandlers();
      foreach ($handlers as $property => $handler) {
        $this->_defaultState[$property] = $handler->default_value();
      };
    };

    return $this->_defaultState;
  }

  function _getDefaultStateFlags() {
    if (!isset($this->_defaultStateFlags)) {
      $this->_defaultStateFlags = array();

      $handlers = $this->getHandlers();
      foreach ($handlers as $property => $handler) {
        $this->_defaultStateFlags[$property] = true;
      };
    };

    return $this->_defaultStateFlags;
  }
  
  function getHandlers() {
    return $this->_handlers;
  }

  function getInheritableTextHandlers() {
    if (!isset($this->_handlersInheritableText)) {
      $this->_handlersInheritabletext = array();
      foreach ($this->_handlers as $property => $handler) {
        if ($handler->isInheritableText()) {
          $this->_handlersInheritableText[$property] =& $this->_handlers[$property];
        };
      }
    }

    return $this->_handlersInheritableText;
  }

  function getInheritableHandlers() {
    if (!isset($this->_handlersInheritable)) {
      $this->_handlersInheritable = array();
      foreach ($this->_handlers as $property => $handler) {
        if ($handler->isInheritable()) {
          $this->_handlersInheritable[$property] =& $this->_handlers[$property];
        };
      }
    }

    return $this->_handlersInheritable;
  }

  function &get() {
    global $__g_css_handler_set;

    if (!isset($__g_css_handler_set)) {
      $__g_css_handler_set = new CSS();
    };

    return $__g_css_handler_set;
  }

  function CSS() {
    $this->_handlers = array();
    $this->_mapping  = array();
  }

  function getDefaultValue($property) {
    $css =& CSS::get();
    $handler =& $css->_get_handler($property);
    $value = $handler->default_value();

    if (is_object($value)) {
      return $value->copy();
    } else {
      return $value;
    };
  }

  function &get_handler($property) {
    $css =& CSS::get();
    $handler =& $css->_get_handler($property);
    return $handler;
  }

  function &_get_handler($property) {
    if (isset($this->_handlers[$property])) {
      return $this->_handlers[$property];
    } else {
      $dumb = null;
      return $dumb;
    };
  }

  function _word2code($key) {
    if (!isset($this->_mapping[$key])) { 
      return null; 
    };

    return $this->_mapping[$key];
  }

  function word2code($key) {
    $css =& CSS::get();
    return $css->_word2code($key);
  }

  function register_css_property(&$handler) {
    $property = $handler->getPropertyCode();
    $name     = $handler->getPropertyName();

    $css =& CSS::get();
    $css->_handlers[$property] =& $handler;
    $css->_mapping[$name] = $property;
  }
}

?>