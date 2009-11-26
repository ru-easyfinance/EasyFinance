<?php
if (!(isset($_SERVER['PHP_AUTH_USER']) &&
    isset($_SERVER['PHP_AUTH_PW']) &&
    $_SERVER['PHP_AUTH_USER'] == 'megaseo' &&
    $_SERVER['PHP_AUTH_PW'] == 'qwerty')) {
  header('WWW-Authenticate: Basic realm="Secured
    area"');
  header('Status: 401 Unauthorized');
} else {
if (isset($_POST['name'])){
    echo ($_POST['name']);
}
?>
<script type="text/javascript" src="tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
tinyMCE.init({

// General options

mode : "textareas",

theme : "advanced",

plugins : "safari,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,imagemanager,filemanager",

theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",

theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",

theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",

theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,spellchecker,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak,|,insertfile,insertimage",
theme_advanced_toolbar_location : "top",
theme_advanced_toolbar_align : "left",
theme_advanced_statusbar_location : "bottom",
theme_advanced_resizing : true,
content_css : "css/example.css",
template_external_list_url : "js/template_list.js",
external_link_list_url : "js/link_list.js",
external_image_list_url : "js/image_list.js",
media_external_list_url : "js/media_list.js"
});
</script>
<html>
    <form name="test" method="post" action="/seo.php">
    <p>Заголовок<Br>
    <textarea name="name" cols="40" rows="3"></textarea></p>
    <p>Основной текст<Br>
    <textarea name="mailtext" cols="40" rows="6"></textarea></p>
    <p>Расширенный текст<Br>
    <textarea name="relatedtext" cols="40" rows="6"></textarea></p>

    <p><input type="submit" value="Отправить">
    <input type="reset" value="Очистить"></p>
    </form>
</html>
<?php
}
?>