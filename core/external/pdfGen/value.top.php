<?php

require_once(HTML2PS_DIR.'value.generic.percentage.php');

class ValueTop extends CSSValuePercentage {
  function fromString($value) {
    return CSSValuePercentage::_fromString($value, new ValueTop);
  }

  function copy() {
    return parent::_copy(new ValueTop);
  }
}

?>