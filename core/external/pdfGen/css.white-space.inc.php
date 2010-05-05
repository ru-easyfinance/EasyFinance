<?php
// $Header: /cvsroot/html2ps/css.white-space.inc.php,v 1.8 2006/12/24 14:42:44 Konstantin Exp $

define('WHITESPACE_NORMAL',   0);
define('WHITESPACE_PRE',      1);
define('WHITESPACE_NOWRAP',   2);
define('WHITESPACE_PRE_WRAP', 3);
define('WHITESPACE_PRE_LINE', 4);

class CSSWhiteSpace extends CSSPropertyStringSet {
  function CSSWhiteSpace() { 
    $this->CSSPropertyStringSet(true, 
                                true,
                                array('normal'   => WHITESPACE_NORMAL,
                                      'pre'      => WHITESPACE_PRE,
                                      'pre-wrap' => WHITESPACE_PRE_WRAP,
                                      'nowrap'   => WHITESPACE_NOWRAP)); 
  }

  function parse($value) {
    $result = parent::parse($value);
    return $result;
  }

  function default_value() { 
    return WHITESPACE_NORMAL; 
  }

  function getPropertyCode() {
    return CSS_WHITE_SPACE;
  }

  function getPropertyName() {
    return 'white-space';
  }
}

CSS::register_css_property(new CSSWhiteSpace);
  
?>