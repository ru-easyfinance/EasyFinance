<?php

class CSSPropertyCollection {
  var $_properties;
  var $_positions;
  var $_priorities;
  var $_max_priority;

  function copy() {
    $collection = new CSSPropertyCollection();
    
    foreach ($this->_properties as $property) {
      $collection->_properties[] = $property->copy();
    };

    $collection->_positions    = $this->_positions;
    $collection->_priorities   = $this->_priorities;
    $collection->_max_priority = $this->_max_priority;

    return $collection;
  }

  function CSSPropertyCollection() {
    $this->_properties = array();
    $this->_positions  = array();
    $this->_priorities = array();
    $this->_max_priority = 0;
  }

  function addProperty($property) {
    $this->_max_priority ++;

    $code = $property->getCode();

    /**
     * Important properties shoud not be overridden with non-important ones
     */
    if ($this->isImportant($code) &&
        !$property->isImportant()) { 
      return;
    };

    if (array_key_exists($code, $this->_positions)) {
      $this->_properties[$this->_positions[$code]] = $property;
      $this->_priorities[$this->_positions[$code]] = $this->_max_priority;
    } else {
      $this->_properties[] = $property;
      $this->_priorities[] = $this->_max_priority;
      $this->_positions[$code] = count($this->_priorities)-1;
    };
  }

  function getMaxPriority() {
    return $this->_max_priority;
  }

  function getPropertiesSortedByPriority() {
    $properties = $this->_properties;
    $priorities = $this->_priorities;

    array_multisort($priorities, $properties);

    return $properties;
  }

  function getPropertiesRaw() {
    return $this->_properties;
  }

  function isImportant($code) { 
    if (!isset($this->_positions[$code])) { 
      return false; 
    };
    return $this->_properties[$this->_positions[$code]]->isImportant();
  }

  function getPropertyValue($code) {
    return $this->_properties[$this->_positions[$code]]->getValue();
  }

  function setPropertyValue($code, $value) {
    $this->_properties[$this->_positions[$code]]->setValue($value);
  }

  /**
   * Merge two sets of CSS properties, overwriting old values
   * with values from $collection
   */
  function merge($collection) {
    $properties = $collection->getPropertiesSortedByPriority();
    foreach ($properties as $property) {
      $this->addProperty($property);
    };
  }
}

?>