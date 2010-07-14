<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <?php include_http_metas() ?>
    <?php include_metas() ?>
    <?php include_title() ?>
    <link rel="shortcut icon" href="/favicon.ico" />
    <?php include_stylesheets() ?>
    <?php include_javascripts() ?>
  </head>
  <body>
<?php
    // TODO: добавить авторизацию и убить эту гадость
    if (!$sf_user->isAuthenticated()) {
        $sf_user->setAuthenticated(true);
    }
?>
    <?php include_component('sfAdminDash', 'header'); ?>
    <?php echo $sf_content ?>
    <?php include_partial('sfAdminDash/footer'); ?>
    <script type="text/javascript">
    $( document ).ready( function(){
        $( "<a class='testRegexp' href='#'>test</a>" ).insertAfter("input[id$=regexp]");
        $( ".testRegexp").click( function(){

            $("#regexp_test").val( $(this).prev().val() );
            var regexp = $(this).prev().val();
            var source = $("#email_parser_sample").val();

            $.post( "/emailparsers/1/regexp",
                    {
                        module: "emailparsers",
                        action: "regexp",
                        regexp: regexp,
                        source: source,
                        "email_parser[_csrf_token]":$("#email_parser__csrf_token").val()
                    },
                    function( data ){
                        alert( data );
                    }
            );
        });
    });
    </script>
  </body>
</html>
