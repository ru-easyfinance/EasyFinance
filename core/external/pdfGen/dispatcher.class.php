<?php

class Dispatcher {
  var $_callbacks;

  function Dispatcher() {
    $this->_callbacks = array();
  }

  /**
   * @param String $type name of the event to dispatch
   */
  function addEvent($type) {
    $this->_callbacks[$type] = array();
  }

  function addObserver($type, $callback) {
    $this->_check_event_type($type);

    $this->_callbacks[$type][] = $callback;
  }

  function fire($type, $params) {
    $this->_check_event_type($type);

    foreach ($this->_callbacks[$type] as $callback) {
      $callback->run($params);
    };
  }

  function _check_event_type($type) {
    if (!isset($this->_callbacks[$type])) {
      die(sprintf("Invalid event type: %s", $type));
    };
  }
}

?>