<?php

require_once(HTML2PS_DIR.'inline.content.builder.php');

class InlineContentBuilderPre extends InlineContentBuilder {
  function InlineContentBuilderPre() {
    $this->InlineContentBuilder();
  }

  function build(&$box, $raw_content, &$pipeline) {
    // Remove the newfeed at the very beginning / end of the text block
    $raw_content = preg_replace("/^[\r\n]*/", "", $raw_content);
    $raw_content = preg_replace("/[\r\n]*$/", "", $raw_content);

    // Convert text content to series of lines
    $lines = preg_split("/[\r\n]/",$raw_content);

    $size = count($lines);
    for ($i=0; $i<$size; $i++) {
      $line = $lines[$i];
      $box->process_word($line, $pipeline);

      $break_box =& new BRBox();
      $break_box->readCSS($pipeline->getCurrentCSSState());
      $box->add_child($break_box);
    };    
  }
}

?>