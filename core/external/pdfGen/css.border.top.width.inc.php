<?php
// $Header: /cvsroot/html2ps/css.border.top.width.inc.php,v 1.1 2006/09/07 18:38:13 Konstantin Exp $

class CSSBorderTopWidth extends CSSSubProperty {
  function CSSBorderTopWidth(&$owner) {
    $this->CSSSubProperty($owner);
  }

  function setValue(&$owner_value, &$value) {
    if ($value != CSS_PROPERTY_INHERIT) {
      $owner_value->top->width = $value->copy();
    } else {
      $owner_value->top->width = $value;
    };
  }

  function getValue(&$owner_value) {
    return $owner_value->top->width;
  }

  function getPropertyCode() {
    return CSS_BORDER_TOP_WIDTH;
  }

  function getPropertyName() {
    return 'border-top-width';
  }

  function parse($value) {
    if ($value == 'inherit') {
      return CSS_PROPERTY_INHERIT;
    }

    return Value::fromString($value);
  }
}

?>