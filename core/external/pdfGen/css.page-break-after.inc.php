<?php
// $Header: /cvsroot/html2ps/css.page-break-after.inc.php,v 1.2 2006/07/09 09:07:45 Konstantin Exp $

class CSSPageBreakAfter extends CSSPageBreak {
  function getPropertyCode() {
    return CSS_PAGE_BREAK_AFTER;
  }

  function getPropertyName() {
    return 'page-break-after';
  }
}

CSS::register_css_property( new CSSPageBreakAfter);

?>