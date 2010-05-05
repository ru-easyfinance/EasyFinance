<?php

require_once(HTML2PS_DIR.'box.generic.formatted.php');

class SimpleInlineBox extends GenericBox {
  function SimpleInlineBox() {
    $this->GenericBox();
  }

  function readCSS(&$state) {
    parent::readCSS($state);

    $this->_readCSS($state,
                    array(CSS_TEXT_DECORATION,
                          CSS_TEXT_TRANSFORM));
    
    // '-html2ps-link-target'
    global $g_config;
    if ($g_config["renderlinks"]) {
      $this->_readCSS($state, 
                      array(CSS_HTML2PS_LINK_TARGET));
    };
  }

  function get_extra_left() {
    return 0;
  }

  function get_extra_top() {
    return 0;
  }

  function get_extra_right() {
    return 0;
  }

  function get_extra_bottom() {
    return 0;
  }

  function show(&$driver) {
    parent::show($driver);

    $link_target = $this->getCSSProperty(CSS_HTML2PS_LINK_TARGET);

    /**
     * Add interactive hyperlinks
     */
    if (CSSPseudoLinkTarget::is_external_link($link_target)) {
      $driver->add_link($this->get_left(), 
                        $this->get_top(), 
                        $this->get_width(), 
                        $this->get_height(), 
                        $link_target);
    };

    if (CSSPseudoLinkTarget::is_local_link($link_target)) {
      if (isset($driver->anchors[substr($link_target,1)])) {
        $anchor = $driver->anchors[substr($link_target,1)];
        $driver->add_local_link($this->get_left(), 
                                $this->get_top(), 
                                $this->get_width(), 
                                $this->get_height(), 
                                $anchor);
      };
    };
  }
}
?>