<?php
class SeoText{
    private $name;
    private $text1;
    private $text2;
    private $array = array();

    function SeoText(){
        $this->name = addslashes($_POST['name']);
        $this->text1 = addslashes($_POST['maintext']);
        $this->text2 = addslashes($_POST['relatedtext']);
    }
    
    function GetArray() {
        if (filesize('../seo.php')){
            include '../seo.php';
            $this->array = $texts;
        }
    }
    
    function ShowAll(){
        foreach ($this->array as $k=>$v){
            if ($v[0]!=''){
                $button = '<form name="del" method="post" action="/admin/seo/"><input name="delname" type="hidden" value="'.$v[0].'"><input type="submit" value="Удалить"></form>';
                $button .= '<form name="edit" method="post" action="/admin/seo/"><input name="editname" type="hidden" value="'.$v[0].'"><input name="edittext1" type="hidden" value="'.$v[1].'">
                    <input name="edittext2" type="hidden" value="'.$v[2].'"><input type="submit" value="Редактировать"></form>';
                echo('<table border="1" bordercolor="#333333">'.$v[0].'     '.$button);
                echo('<br>'.$v[1].''.'<br>');
                echo(''.$v[2].''.'<br></table>');
            }
        }
    }

    function AppendToFile() {
        $arr = array($this->name, $this->text1, $this->text2);
        $this->array[] = $arr;
        $f = fopen('../seo.php', 'w');
        $dump = '<?php $texts = ' . var_export( $this->array , true ) . ' ?>';//*/
        fwrite($f, $dump);
        fclose($f);
    }

    function DeleteRecord($name){
        $f = fopen('../seo.php','r');
        if (filesize('../seo.php')){
            include '../seo.php';
            $this->array = $texts;

            $f = fopen('../seo.php', 'w');
            foreach ($this->array as $k=>$value){
                if ($this->array[$k][0] == $name)
                    $this->array[$k] = '';
            }
            $dump = '<?php $texts = ' . var_export( $this->array , true ) . ' ?>';//*/
            fwrite($f, $dump);
            fclose($f);
        } 
    }

    function EditString($name, $text1, $text2){
        $f = fopen('../seo.php','r');
        if (filesize('../seo.php')){
            include '../seo.php';
            $this->array = $texts;

            $f = fopen('../seo.php', 'w');
            foreach ($this->array as $k=>$value){
                if ($this->array[$k][0] == $name){
                    $arr = array($name, $text1, $text2);
                    $this->array[$k] = $arr;
                }
            }
            $dump = '<?php $texts = ' . var_export( $this->array , true ) . ' ?>';//*/
            fwrite($f, $dump);
            fclose($f);
        }
    }
}


if (!(isset($_SERVER['PHP_AUTH_USER']) &&
    isset($_SERVER['PHP_AUTH_PW']) &&
    $_SERVER['PHP_AUTH_USER'] == 'seo' &&
    $_SERVER['PHP_AUTH_PW'] == 'qwerty')) {
  header('WWW-Authenticate: Basic realm="Secured
    area"');
  header('Status: 401 Unauthorized');
} else {
    ?>
                <html>
                    <head>
                        <script type="text/javascript" src="/js/jquery/jquery-1.3.2.js"></script>
                        <script type="text/javascript" src="/js/jquery/jHtmlArea-0.6.0.js"></script>
                        <script type="text/css" src="/css/jquery/jHtmlArea.css"></script>
                        <link rel="Stylesheet" type="text/css" href="/css/jquery/jHtmlArea.css" />


                    </head>
                    <body>
<table width="100%" border="1">
<tr>
<td valign="top">
    <?

    if (isset($_POST['edname']) && isset($_POST['edmaintext']) && isset($_POST['edrelatedtext'])){
        $seo = new SeoText();
        $seo->EditString($_POST['edname'],$_POST['edmaintext'],$_POST['edrelatedtext']);
        $seo->GetArray();
        $seo->ShowAll();
    }

if (isset($_POST['delname'])){
    $seo = new SeoText();
    $seo->DeleteRecord($_POST['delname']);
    $seo->GetArray();
    $seo->ShowAll();
}else{

        if (isset($_POST['name']) && isset($_POST['maintext']) && isset($_POST['relatedtext'])){
            $seo = new SeoText();
            $seo->GetArray();
            $seo->AppendToFile();
            $seo->ShowAll();
        }else if (!isset($_POST['edname'])) if (!isset($_POST['delname'])){
            $seo = new SeoText();
            $seo->GetArray();
            $seo->ShowAll();
        }



}
    if (!isset($_POST['editname'])){
?>
        </td>
        <td id="col2" width="25%">
    <form name="test" method="post" action="/admin/seo/">
    <p>Заголовок<Br>
    <textarea name="name" cols="40" rows="3"></textarea></p>
    <p>Основной текст<Br>
    <textarea class="editor" name="maintext" cols="40" rows="6"></textarea></p>
    <p>Расширенный текст<Br>
    <textarea class="editor" name="relatedtext" cols="40" rows="6"></textarea></p>

    <p><input name="add" type="submit" value="Добавить">
    </form>
<?php
    }
    else{
        ?>
            </td>
        <td id="col2" width="25%">
    <form name="test" method="post" action="/admin/seo/">
    <p>Заголовок<Br>
    <textarea name="edname" cols="40" rows="3"><?echo($_POST['editname'])?></textarea></p>
    <p>Основной текст<Br>
    <textarea class="editor" name="edmaintext" cols="40" rows="6"><?echo($_POST['edittext1'])?></textarea></p>
    <p>Расширенный текст<Br>
    <textarea class="editor" name="edrelatedtext" cols="40" rows="6"><?echo($_POST['edittext2'])?></textarea></p>

    <p><input name="edit" type="submit" value="Редактировать">
    </form>

        <?
    }
}
?>
        </td>
</tr>
</table>
    <script type="text/javascript">
        //alert('1');
        $(".editor").htmlarea({
            toolbar: [
                    ["bold", "italic", "underline", "|", "forecolor"],
                    ["h1", "h2", "h3", "h4", "h5", "h6"]
                ],

            css: "/css/jquery/jHtmlArea.css",
            loaded: function() {

                    //// 'this' is equal to the jHtmlArea object
                    //alert("jHtmlArea has loaded!");
                    //this.showHTMLView(); // show the HTML view once the editor has finished loading
                }
        });
    </script>
</body>
</html>