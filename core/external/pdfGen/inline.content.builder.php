<?php

require_once(HTML2PS_DIR.'error.php');

class InlineContentBuilder {
  function InlineContentBuilder() {
  }

  function build(&$box, $text, &$pipeline) {
    error_no_method('build', get_class($this));
  }
}

?>