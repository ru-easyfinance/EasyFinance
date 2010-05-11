<?php
class DestinationBrowser extends DestinationHTTP {
  function headers($content_type) {
    return array(
                 "Content-Disposition:inline; filename=anketa.pdf",
                 "Content-Transfer-Encoding: binary",
                 "Cache-Control: private"
                 );
  }
}
?>