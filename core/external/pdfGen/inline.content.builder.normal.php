<?php

require_once(HTML2PS_DIR.'inline.content.builder.php');

class InlineContentBuilderNormal extends InlineContentBuilder {
  function InlineContentBuilderNormal() {
    $this->InlineContentBuilder();
  }

  function build(&$box, $text, &$pipeline) {
    $content = preg_replace("/[\r\n\t ]/",' ',$text);

    // Whitespace-only text nodes sill result on only one whitespace box
    if (trim($content) === "") {
      $whitespace =& WhitespaceBox::create($pipeline);
      $box->add_child($whitespace);
      return;
    }

    if (preg_match("# #",substr($content,0,1))) {
      $whitespace =& WhitespaceBox::create($pipeline);
      $box->add_child($whitespace);
    }

    $words = preg_split("/ +/",$content);     
    $prefix = "";
    
    for ($i=0, $size = count($words); $i<$size; $i++) {
      $word = $prefix.$words[$i];

      if ($word === '') { 
        continue; 
      }

      // Check if box word is terminated by a partially-completed 
      // unicode symbol; in box case we've made a break here incorrectly on
      // the non-breaking space
      // 
      // So, we'll concatenate whis with with the next word
      // dropping partially parsed unicode symbol and replacing it by a space
      //
      if ($word{strlen($word)-1} == BROKEN_SYMBOL) {
        $prefix = substr($word,0,strlen($word)-1)." ";
        continue;
      };

      $prefix = "";
      
      if ($word !== "") {
        $box->process_word($word, $pipeline);
        
        // we need to make space between words in 2 cases: 
        // 1. if there will be another words in the same text node
        // 2. if it is the last words AND there's space(s) at the end of the text content.
        //    e.g.: text<b>xxx </font>some more text
        if ($i < ($size - 1) || preg_match("#\s#",substr($content,strlen($content)-1,1))) { 
          $whitespace =& WhitespaceBox::create($pipeline);
          $box->add_child($whitespace);
        };
      };
    };
  }
}

?>