<?php

require_once(HTML2PS_DIR.'css.constants.inc.php');

class CSSAtRulePage {
  var $selector;
  var $margin_boxes;

  function CSSAtRulePage($selector) {
    $this->selector = $selector;
    $this->margin_boxes = array();
  }

  function getSelector() {
    return $this->selector;
  }

  function getAtRuleMarginBoxes() {
    return $this->margin_boxes;
  }

  /**
   * Note that only one margin box rule could be added; subsequent adds 
   * will overwrite existing data
   */
  function addAtRuleMarginBox($rule) {
    $this->margin_boxes[$rule->getSelector()] = $rule;
  }
}

class CSSAtRuleMarginBox {
  var $selector;
  var $css;

  function getSelector() {
    return $this->selector;
  }

  /**
   * TODO: CSS_TEXT_ALIGN should get  top/bottom values by default for
   * left-top, left-bottom, right-top and right-bottom boxes
   */
  function CSSAtRuleMarginBox($selector, &$pipeline) {
    $this->selector = $selector;

    $css = "-html2ps-html-content: ''; content: ''; width: auto; height: auto; margin: 0; border: none; padding: 0; font: auto;";
    $css = $css . $this->_getCSSDefaults($selector);

    $this->css = new CSSRule(array(
                                   array(SELECTOR_ANY),
                                   parse_css_properties($css, $null),
                                   '',
                                   null),
                             $pipeline);
  }

  function _getCSSDefaults($selector) {
    $text_align_handler =& CSS::get_handler(CSS_TEXT_ALIGN);
    $vertical_align_handler =& CSS::get_handler(CSS_VERTICAL_ALIGN);
    
    switch ($selector) {
    case CSS_MARGIN_BOX_SELECTOR_TOP:
      return 'text-align: left; vertical-align: middle';
    case CSS_MARGIN_BOX_SELECTOR_TOP_LEFT_CORNER:
      return 'text-align: right; vertical-align: middle';
    case CSS_MARGIN_BOX_SELECTOR_TOP_LEFT:
      return 'text-align: left; vertical-align: middle';
    case CSS_MARGIN_BOX_SELECTOR_TOP_CENTER:
      return 'text-align: center; vertical-align: middle';
    case CSS_MARGIN_BOX_SELECTOR_TOP_RIGHT:
      return 'text-align: right; vertical-align: middle';
    case CSS_MARGIN_BOX_SELECTOR_TOP_RIGHT_CORNER:
      return 'text-align: left; vertical-align: middle';
    case CSS_MARGIN_BOX_SELECTOR_BOTTOM:
      return 'text-align: left; vertical-align: middle';
    case CSS_MARGIN_BOX_SELECTOR_BOTTOM_LEFT_CORNER:
      return 'text-align: right; vertical-align: middle';
    case CSS_MARGIN_BOX_SELECTOR_BOTTOM_LEFT:
      return 'text-align: left; vertical-align: middle';
    case CSS_MARGIN_BOX_SELECTOR_BOTTOM_CENTER:
      return 'text-align: center; vertical-align: middle';
    case CSS_MARGIN_BOX_SELECTOR_BOTTOM_RIGHT:
      return 'text-align: right; vertical-align: middle';
    case CSS_MARGIN_BOX_SELECTOR_BOTTOM_RIGHT_CORNER:
      return 'text-align: left; vertical-align: middle';
    case CSS_MARGIN_BOX_SELECTOR_LEFT_TOP:
      return 'text-align: center; vertical-align: top';
    case CSS_MARGIN_BOX_SELECTOR_LEFT_MIDDLE:
      return 'text-align: center; vertical-align: middle';
    case CSS_MARGIN_BOX_SELECTOR_LEFT_BOTTOM:
      return 'text-align: center; vertical-align: bottom';
    case CSS_MARGIN_BOX_SELECTOR_RIGHT_TOP:
      return 'text-align: center; vertical-align: top';
    case CSS_MARGIN_BOX_SELECTOR_RIGHT_MIDDLE:
      return 'text-align: center; vertical-align: middle';
    case CSS_MARGIN_BOX_SELECTOR_RIGHT_BOTTOM:
      return 'text-align: center; vertical-align: bottom';
    };
  }

  function setCSSProperty($property) {
    $this->css->addProperty($property);
  }

  function getCSSProperty($code) {
    return $this->css->getProperty($code);
  }
}

/**
 * Handle  @page rules  in  current CSS  media  As parse_css_media  is
 * called for  selected media  only, we can  store data to  global CSS
 * state - no data should be ignored
 *
 * at-page rules will be removed after parsing
 *
 * @param $css String contains raw CSS data to be processed
 * @return String CSS text without at-page rules 
 */
function parse_css_atpage_rules($css, &$css_ruleset) {
  while (preg_match('/^(.*?)@page(.*)/is', $css, $matches)) {
    $data = $matches[2];
    $css = $matches[1].parse_css_atpage_rule(trim($data), $css_ruleset);
  };

  return $css;
}

function parse_css_atpage_rule($css, &$css_ruleset) {
  /**
   * Extract selector and left bracket
   */
  if (!preg_match('/^(.*?){(.*)$/is', $css, $matches)) { 
    error_log('No selector and/or open bracket found in @page rule');
    return $css; 
  };
  $raw_selector = trim($matches[1]);
  $css          = trim($matches[2]);

  $selector = parse_css_atpage_selector($raw_selector);
  $at_rule = new CSSAtRulePage($selector);

  /**
   * The body of @page rule may contain declaraction (detected by ';'), 
   * margin box at-rule (detected by @top and similar tokens) or } indicating termination of 
   * @page rule
   */
  while (preg_match('/^(.*?)(;|@|})(.*)$/is', $css, $matches)) {
    $raw_prefix = trim($matches[1]);
    $raw_token  = trim($matches[2]);
    $raw_suffix = trim($matches[3]);

    switch ($raw_token) {
    case ';':
      /**
       * Normal declaration (text contained in $raw_prefix
       */
      parse_css_atpage_declaration($raw_prefix, $at_rule);
      $css = $raw_suffix;
      break;

    case '@':
      /**
       * Margin box at-rule
       */
      $css = parse_css_atpage_margin_box($raw_suffix, $at_rule, $css_ruleset);
      break;

    case '}':
      /**
       * End-of-rule
       */
      $css_ruleset->addAtRulePage($at_rule);
      return $raw_suffix;
    };
  };

  /**
   * Note that we should normally exit via '}' token handler above
   */
  error_log('No close bracket found in @page rule');
  $css_ruleset->addAtRulePage($at_rule);
  return $css; 
}

/**
 * Parses CSS at-page rule selector; syntax of this selector can be seen in 
 * CSS 3 specification at http://www.w3.org/TR/css3-page/#syntax-page-selector
 *
 * 
 */
function parse_css_atpage_selector($selector) {
  switch ($selector) {
  case '':
    return CSS_PAGE_SELECTOR_ALL;
  case ':first':
    return CSS_PAGE_SELECTOR_FIRST;
  case ':left':
    return CSS_PAGE_SELECTOR_LEFT;
  case ':right':
    return CSS_PAGE_SELECTOR_RIGHT;
  default:
    error_log(sprintf('Unknown page selector in @page rule: \'%s\'', $selector));
    return CSS_PAGE_SELECTOR_ALL;
  };
}

function parse_css_atpage_margin_box($css, &$at_rule, &$pipeline) {
  if (!preg_match("/^([-\w]*)\s*{(.*)/is",$css,$matches)) {
    error_log("Invalid margin box at-rule format");
    return $css;
  };

  $raw_margin_box_selector = trim($matches[1]);
  $css                     = trim($matches[2]);

  $margin_box_selector = parse_css_atpage_margin_box_selector($raw_margin_box_selector);
  $at_rule_margin_box = new CSSAtRuleMarginBox($margin_box_selector, $pipeline);
  
  /**
   * The body of margin box at-rule may contain declaraction (detected
   * by ';'), or } indicating termination of at-rule
   */
  while (preg_match('/^(.*?)(;|})(.*)$/is', $css, $matches)) {
    $raw_prefix = trim($matches[1]);
    $raw_token  = trim($matches[2]);
    $raw_suffix = trim($matches[3]);

    switch ($raw_token) {
    case ';':
      /**
       * Normal declaration (text contained in $raw_prefix
       */
      parse_css_atpage_margin_box_declaration($raw_prefix, $at_rule_margin_box, $pipeline);
      $css = $raw_suffix;
      break;

    case '}':
      /**
       * End-of-rule
       */
      $at_rule->addAtRuleMarginBox($at_rule_margin_box);
      return $raw_suffix;
    };
  };  
  
  /**
   * Note that we should normally exit via '}' token handler above
   */
  error_log('No close bracket found in margin box at-rule');
  $at_rule->addAtRuleMarginBox($at_rule_margin_box);
  return $css; 
}

function parse_css_atpage_margin_box_selector($css) {
  switch ($css) {
  case 'top':
    return CSS_MARGIN_BOX_SELECTOR_TOP;
  case 'top-left-corner':
    return CSS_MARGIN_BOX_SELECTOR_TOP_LEFT_CORNER;
  case 'top-left':
    return CSS_MARGIN_BOX_SELECTOR_TOP_LEFT;
  case 'top-center':
    return CSS_MARGIN_BOX_SELECTOR_TOP_CENTER;
  case 'top-right':
    return CSS_MARGIN_BOX_SELECTOR_TOP_RIGHT;
  case 'top-right-corner':
    return CSS_MARGIN_BOX_SELECTOR_TOP_RIGHT_CORNER;
  case 'bottom':
    return CSS_MARGIN_BOX_SELECTOR_BOTTOM;
  case 'bottom-left-corner':
    return CSS_MARGIN_BOX_SELECTOR_BOTTOM_LEFT_CORNER;
  case 'bottom-left':
    return CSS_MARGIN_BOX_SELECTOR_BOTTOM_LEFT;
  case 'bottom-center':
    return CSS_MARGIN_BOX_SELECTOR_BOTTOM_CENTER;
  case 'bottom-right':
    return CSS_MARGIN_BOX_SELECTOR_BOTTOM_RIGHT;
  case 'bottom-right-corner':
    return CSS_MARGIN_BOX_SELECTOR_BOTTOM_RIGHT_CORNER;
  case 'left-top':
    return CSS_MARGIN_BOX_SELECTOR_LEFT_TOP;
  case 'left-middle':
    return CSS_MARGIN_BOX_SELECTOR_LEFT_MIDDLE;
  case 'left-bottom':
    return CSS_MARGIN_BOX_SELECTOR_LEFT_BOTTOM;
  case 'right-top':
    return CSS_MARGIN_BOX_SELECTOR_RIGHT_TOP;
  case 'right-middle':
    return CSS_MARGIN_BOX_SELECTOR_RIGHT_MIDDLE;
  case 'right-bottom':
    return CSS_MARGIN_BOX_SELECTOR_RIGHT_BOTTOM;
  default:
    error_log(sprintf('Unrecognized margin box selector: \'%s\'', $css));
    return CSS_MARGIN_BOX_SELECTOR_TOP;
  }
};

/**
 * Available properties:
 * margin (and its subproperties)
 * size
 * 
 */
function parse_css_atpage_declaration($css, &$at_rule) {
}


/**
 * Available properties:
 * content
 * text-align
 * vertical-align
 * width
 * height
 * border
 */
function parse_css_atpage_margin_box_declaration($css, &$at_rule, &$pipeline) {
  /**
   * Here we pass null as a value of $pipeline parameter to parse_css_property function;
   * we can do it because there's no @import directives allowed in margin context
   */
  $parsed = parse_css_property($css, $pipeline);

  $properties = $parsed->getPropertiesSortedByPriority();
  foreach ($properties as $property) {
    $at_rule->setCSSProperty($property);
  };
}

?>