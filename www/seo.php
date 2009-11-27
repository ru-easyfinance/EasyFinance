<html>
<style type="text/css">
#col1 {
 width: 75%;
}

#col2 {
 width: 25%;
}

#col1, #col2 {
 vertical-align: top; /* Выравнивание по верхнему краю */
 padding: 5px; /* Поля вокруг содержимого ячеек */
}
</style>
<table width="100%" border="1">
<tr>
    <td id="col1">
<?php

class SeoText{
    private $name;
    private $text1;
    private $text2;
    private $array = array();

    function SeoText(){
        $this->name = $_POST['name'];
        $this->text1 = $_POST['maintext'];
        $this->text2 = $_POST['relatedtext'];
    }
    
    function GetArray() {
        $f = fopen('admin/seo.txt','r');
        if (filesize('admin/seo.txt')){
            $content = fread($f,filesize('admin/seo.txt'));
            fclose($f);
            $ArrString = explode('\n',$content);
            foreach ($ArrString as $key=>$v){
                $str = explode('\t',$v);
                $this->array[$key][0] = $str[0];
                $this->array[$key][1] = $str[1];
                $this->array[$key][2] = $str[2];             
            };
        }
    }
    
    function ShowAll(){
        foreach ($this->array as $k=>$v){
            if ($v[0]!=''){
                $button = '<form name="del" method="post" action="/seo.php"><input name="delname" type="hidden" value="'.$v[0].'"><input type="submit" value="Удалить"></form>';
                $button .= '<form name="edit" method="post" action="/seo.php"><input name="editname" type="hidden" value="'.$v[0].'"><input name="edittext1" type="hidden" value="'.$v[1].'">
                    <input name="edittext2" type="hidden" value="'.$v[2].'"><input type="submit" value="Редактировать"></form>';
                echo($v[0].'     '.$button);
                echo(''.$v[1].'');
                echo(''.$v[2].''.'<br>');
            }
        }
    }

    function PlusToFile() {
        $arr = array($this->name, $this->text1, $this->text2);
        //print_r($this->array);
        $this->array[] = $arr;
        $dump = $this->name.'\t'.$this->text1.'\t'.$this->text2.'\n';
        $f = fopen('admin/seo.txt', 'a');
        fwrite($f, $dump);
        fclose($f);
    }

    function DeleteRecord($name){
        $f = fopen('admin/seo.txt','r');
        if (filesize('admin/seo.txt')){
            $content = fread($f,filesize('admin/seo.txt'));
            fclose($f);
            $ArrString = explode('\n',$content);
            $f = fopen('admin/seo.txt', 'w');
            $dump='';
            foreach ($ArrString as $key=>$v){
                $str = explode('\t',$v);
                if ($str[0]!=$name) {
                    $dump .= $str[0].'\t'.$str[1].'\t'.$str[2].'\n';
                }
            };
            fwrite($f, $dump);
            fclose($f);
        } 
    }

    function EditString($name, $text1, $text2){
        $f = fopen('admin/seo.txt','r');
        if (filesize('admin/seo.txt')){
            $content = fread($f,filesize('admin/seo.txt'));
            fclose($f);
            $ArrString = explode('\n',$content);
            $f = fopen('admin/seo.txt', 'w');
            $dump='';
            foreach ($ArrString as $key=>$v){
                $str = explode('\t',$v);
                if ($str[0]!=$name) {
                    $dump .= $str[0].'\t'.$str[1].'\t'.$str[2].'\n';
                }else{
                    $dump .= $name.'\t'.$text1.'\t'.$text2.'\n';
                }

            };
            fwrite($f, $dump);
            fclose($f);
        }
    }
}


if (!(isset($_SERVER['PHP_AUTH_USER']) &&
    isset($_SERVER['PHP_AUTH_PW']) &&
    $_SERVER['PHP_AUTH_USER'] == 'megaseo' &&
    $_SERVER['PHP_AUTH_PW'] == 'qwerty')) {
  header('WWW-Authenticate: Basic realm="Secured
    area"');
  header('Status: 401 Unauthorized');
} else {

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
            $seo->PlusToFile();
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
        <td id="col2">
    <form name="test" method="post" action="/seo.php">
    <p>Заголовок<Br>
    <textarea name="name" cols="40" rows="3"></textarea></p>
    <p>Основной текст<Br>
    <textarea name="maintext" cols="40" rows="6"></textarea></p>
    <p>Расширенный текст<Br>
    <textarea name="relatedtext" cols="40" rows="6"></textarea></p>

    <p><input name="add" type="submit" value="Добавить">
    </form>

<?php
    }
    else{
        ?>
            </td>
        <td id="col2">
    <form name="test" method="post" action="/seo.php">
    <p>Заголовок<Br>
    <textarea name="edname" cols="40" rows="3"><?echo($_POST['editname'])?></textarea></p>
    <p>Основной текст<Br>
    <textarea name="edmaintext" cols="40" rows="6"><?echo($_POST['edittext1'])?></textarea></p>
    <p>Расширенный текст<Br>
    <textarea name="edrelatedtext" cols="40" rows="6"><?echo($_POST['edittext2'])?></textarea></p>

    <p><input name="edit" type="submit" value="Редактировать">
    </form>

        <?
    }
}
?>
        </td>
</tr>
</table>
</html>