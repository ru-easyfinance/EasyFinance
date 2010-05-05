<?php
// $Header: /cvsroot/html2ps/css.html2ps.html.content.inc.php,v 1.2 2006/12/24 14:42:43 Konstantin Exp $

class CSSHTML2PSHTMLContent extends CSSPropertyHandler {
  function CSSHTML2PSHTMLContent() { $this->CSSPropertyHandler(false, false); }

  function default_value() { return ""; }

  // CSS 2.1 p 12.2: 
  // Value: [ <string> | <uri> | <counter> | attr(X) | open-quote | close-quote | no-open-quote | no-close-quote ]+ | inherit
  //
  // TODO: process values other than <string>
  //
  function parse($value) {
    return $value;
  }

  function getPropertyCode() {
    return CSS_HTML2PS_HTML_CONTENT;
  }

  function getPropertyName() {
    return '-html2ps-html-content';
  }
}

CSS::register_css_property(new CSSHTML2PSHTMLContent);

?>