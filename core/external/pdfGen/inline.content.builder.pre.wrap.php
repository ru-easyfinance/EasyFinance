<?php

require_once(HTML2PS_DIR.'inline.content.builder.php');

class InlineContentBuilderPreWrap extends InlineContentBuilder {
  function InlineContentBuilderPreWrap() {
    $this->InlineContentBuilder();
  }

  function build(&$box, $raw_content, &$pipeline) {
    // Remove the newfeed at the very beginning / end of the text block
    $raw_content = preg_replace("/^[\r\n]/", "", $raw_content);

    // Convert text content to series of lines
    $lines = preg_split("/[\r\n]/",$raw_content);

    $size = count($lines);
    for ($i=0; $i<$size; $i++) {
      $line = $lines[$i];

      $words = preg_split("/ /", $line);
      foreach ($words as $word) {
        $box->process_word($word, $pipeline);

        $whitespace =& WhitespaceBox::create($pipeline);
        $box->add_child($whitespace);
      };

      $break_box =& new BRBox();
      $break_box->readCSS($pipeline->getCurrentCSSState());
      $box->add_child($break_box);
    };    
  }
}

?>