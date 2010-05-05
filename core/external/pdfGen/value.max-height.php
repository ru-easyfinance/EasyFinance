<?php

require_once(HTML2PS_DIR.'value.generic.percentage.php');

class ValueMaxHeight extends CSSValuePercentage {
  function fromString($value) {
    return CSSValuePercentage::_fromString($value, new ValueMaxHeight);
  }

  function copy() {
    return parent::_copy(new ValueMaxHeight);
  }
}

?>