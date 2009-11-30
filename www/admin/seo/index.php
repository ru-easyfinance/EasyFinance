<?php

/**
 * Логин сео-оптимизатора
 */
define ('SEO_LOGIN', 'seo');

/**
 * Пароль сео-оптимизатора
 */
define ('SEO_PASS', 'qwerty');

/**
 * Путь до файла с массивом сео
 */
define ('SEO_FILENAME', '../seo.php');

class SeoText{

    /**
     * Заголовок редактируемой / добавляемой записи
     * @var string
     */
    private $name;

    /**
     * Основной - видимый текст добавляемой / редактируемой записи
     * @var string
     */
    private $text1;

    /**
     * Расширенный текст добавляемой / редактируемой записи
     * @var string
     */
    private $text2;

    /**
     * Массив со считываемыми из файла данными
     * @var array mixed
     */
    private $array = array();



    /**
     * Заполняет переменные класса редактируемой / добавляемой записи
     * @return void
     */
    function SeoText(){
        $this->name  = addslashes($_POST['name']);
        $this->text1 = addslashes($_POST['maintext']);
        $this->text2 = addslashes($_POST['relatedtext']);
    }

    /**
     * Считывание данных из файла в массив
     * @return void
     */
    function GetArray() {
        if (file_exists(SEO_FILENAME)) {
            include SEO_FILENAME;
            $this->array = $texts;
        }
    }

    /**
     * Вывод значений из массива списком для редактирования
     * @return string
     */
    function ShowAll() {
        $lists = '';
        foreach ($this->array as $k=>$v){
            $button = '<form name="del" method="post" action="/admin/seo/">
                <input name="delname" type="hidden" value="' . $v[0] . '" />
                <input type="submit" value="Удалить" /></form>';
            $button .= '<form name="edit" method="post" action="/admin/seo/">
                <input name="editname" type="hidden" value="' . $v[0]  . '" />
                <input name="edittext1" type="hidden" value="' . $v[1] . '" />
                <input name="edittext2" type="hidden" value="' . $v[2] . '" />
                <input type="submit" value="Редактировать" /></form>';

            $lists .= '<table border="1" width="100%"><tr><th>' . $v[0] . $button . '</th></tr>';
            $lists .=  '<tr><td>' . $v[1] . '</td></tr><tr><td>' . $v[2] . '</td></tr></table>';
        }
        return $lists;
    }

    /**
     * Запись массива в файл
     * @return void
     */
    function AppendToFile() {
        $arr = array($this->name, $this->text1, $this->text2);
        $this->array[] = $arr;
        $f = fopen(SEO_FILENAME, 'w');
        $dump = '<?php $texts = ' . var_export( $this->array , true ) . ' ?>';
        fwrite($f, $dump);
        fclose($f);
    }

    /**
     * Удаляет запись из массива
     * @param string $name 
     */
    function DeleteRecord($name) {

        if ( file_exists(SEO_FILENAME) ) {
            include SEO_FILENAME;
            $this->array = $texts;

            $f = fopen(SEO_FILENAME, 'w');
            foreach ($this->array as $k=>$value){
                if ($this->array[$k][0] == $name) {
                    unset ($this->array[$k]);
                }
            }
            $dump = '<?php $texts = ' . var_export( $this->array , true ) . ' ?>';//*/
            fwrite($f, $dump);
            fclose($f);
        } 
    }

    /**
     * Редактирует запись в массиве и записывает изменения в файл
     * @param string $name
     * @param string $text1
     * @param string $text2
     */
    function EditString($name, $text1, $text2){
        $f = fopen(SEO_FILENAME,'r');
        if (file_exists(SEO_FILENAME)){
            include SEO_FILENAME;
            $this->array = $texts;

            $f = fopen(SEO_FILENAME, 'w');
            foreach ($this->array as $k=>$value){
                if ($this->array[$k][0] == $name){
                    $arr = array($name, $text1, $text2);
                    $this->array[$k] = $arr;
                }
            }
            $dump = '<?php $texts = ' . var_export( $this->array , true ) . ' ?>';
            fwrite($f, $dump);
            fclose($f);
        }
    }
    function AddEdit(){
        $isnew = 0;
        foreach ( $this->array as $k => $v ){
            if ($v[0] == $_POST['name']) {
                $isnew = $k;
            }
        }
        if (!$isnew){
            $this->AppendToFile();
        }else{
            $this->EditString($_POST['name'],$_POST['maintext'],$_POST['relatedtext']);
        }
    }
}

// Авторизация пользователя, если пользователь ввёл не верный логин или пароль!
if (!(isset($_SERVER['PHP_AUTH_USER']) &&
    isset($_SERVER['PHP_AUTH_PW']) && 
    $_SERVER['PHP_AUTH_USER'] == SEO_LOGIN && 
    $_SERVER['PHP_AUTH_PW'] == SEO_PASS)) {
    header('WWW-Authenticate: Basic realm="Secured area"');
    header('Status: 401 Unauthorized');
    exit;
}

// Редактирование
/*if (isset($_POST['edname']) && isset($_POST['edmaintext']) && isset($_POST['edrelatedtext'])) {
    $seo = new SeoText();
    $seo->EditString($_POST['edname'],$_POST['edmaintext'],$_POST['edrelatedtext']);
}*/

// Удаление
if (isset($_POST['delname'])) {
    $seo = new SeoText();
    $seo->DeleteRecord($_POST['delname']);
}

// Добавление
if (isset($_POST['name']) && isset($_POST['maintext']) && isset($_POST['relatedtext'])){
    $seo = new SeoText();
    $seo->GetArray();
    $seo->AddEdit();
    /*$isnew = 0;
    foreach ( $this->array as $k => $v ){
        if ($v[0] == $_POST['name']) {
            $isnew = $k;
        }
    }
    if (!$isnew){
        $seo->AppendToFile();
    }else{
        $seo->EditString($_POST['name'],$_POST['maintext'],$_POST['relatedtext']);
    }*/
// Вывод информации при первом запуске
} else if (!isset($_POST['edname'])) if (!isset($_POST['delname'])) {
    $seo = new SeoText();
}

$seo->GetArray();
$lists = $seo->ShowAll();

$filename = dirname(__FILE__) . '/index.html';
$handle   = fopen($filename, "r");
$contents = fread($handle, filesize($filename));
fclose($handle);

if (isset($_POST['editname'])) {
    $editname = $_POST['editname'];
    $edittext1 = $_POST['edittext1'];
    $edittext2 = $_POST['edittext2'];
} else {
    $editname = '';
    $edittext1 = '';
    $edittext2 = '';
}
$contents = str_replace('__LISTS__', $lists, $contents);
$contents = str_replace('__NAME__', $editname, $contents);
$contents = str_replace('__EDITOR__', $edittext1, $contents);
$contents = str_replace('__RELATEDTEXT__', $edittext2, $contents);

die($contents);