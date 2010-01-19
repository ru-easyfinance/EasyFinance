<?php

/**
 * Логин сео-оптимизатора
 */
define ('SEO_LOGIN', 'userseo');

/**
 * Пароль сео-оптимизатора
 */
define ('SEO_PASS', 'onuevpwe52ky');

// Подключение конфигурации
define ('INDEX', true);
include(dirname(dirname(dirname(__FILE__))) . '/include/config.php');

/**
 * Путь до файла с массивом сео
 */
define ('SEO_FILENAME', DIR_SHARED . 'seo.php');

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
        $this->name  = $_POST['name'];
        $this->text1 = $_POST['maintext'];
        $this->text2 = $_POST['relatedtext'];
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
            $button .= "<form name='edit' method='post' action='/admin/seo/'>
                <input name='key' type='hidden' value='" . htmlspecialchars($k, ENT_QUOTES) . "' />
                <input name='editname' type='hidden' value='" . htmlspecialchars($v[0], ENT_QUOTES)  . "' />
                <input name='edittext1' type='hidden' value='" . htmlspecialchars($v[1], ENT_QUOTES) . "' />
                <input name='edittext2' type='hidden' value='" . htmlspecialchars($v[2], ENT_QUOTES) . "' />
                <input type='submit' value='Редактировать' /></form>";

            $lists .= '<table border="1" width="100%"><tr>' . $v[0] . '    ' .$button . '</tr>';
            $lists .=  '<tr><td>' . $v[1] . '</td></tr><tr><td>' . $v[2] . '</td></tr></table><br>';
        }
        return $lists;
    }

    function notag($text){
        $allowable_tags = '<span><b><i><u><h1><h2><h3><h4><h5><h6>';
        return strip_tags($text, $allowable_tags);
    }

    /**
     * Запись массива в файл
     * @return void
     */
    function AppendToFile() {
        $arr = array($this->notag($this->name), $this->text1, $this->text2);
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
            $delkey ="";
            $all ="";
            $f = fopen(SEO_FILENAME, 'w');
            foreach ($this->array as $k=>$value){
                if ($this->array[$k][0] == $name) {
                    $delkey = $key;
                    foreach ($this->array as $k=>$v){
                        if ($k>=$delkey)
                            $this->array[$k] = $this->array[$k+1];
                    }
                    //unset ($this->array[$k]);
                }
                $allkey = $k;
            }
            unset ($this->array[$allkey]);
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

    function EditString($key, $name, $text1, $text2){
        $f = fopen(SEO_FILENAME,'r');
        if (file_exists(SEO_FILENAME)){
            include SEO_FILENAME;
            $this->array = $texts;

            $f = fopen(SEO_FILENAME, 'w');
            foreach ($this->array as $k=>$value){
                //if ($this->array[$k][0] == $name){
                if ($k == $key){
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
        /*foreach ( $this->array as $k => $v ){
            if ($v[0] == $_POST['name']) {
                $isnew = $k;
            }
        }*/
        if (isset($_POST['keyf'])){
            $isnew = $_POST['keyf'];
        }
        if (!$isnew){
            $this->AppendToFile();
        }else{
            $this->EditString($isnew, $_POST['name'],$_POST['maintext'],$_POST['relatedtext']);
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
    $hidd = $_POST['key'];
    $editname = $_POST['editname'];
    $edittext1 = $_POST['edittext1'];
    $edittext2 = $_POST['edittext2'];
} else {
    $editname = '';
    $edittext1 = '';
    $edittext2 = '';
}

$contents = str_replace('__LISTS__', $lists, $contents);
$contents = str_replace('__HIDD__', $hidd, $contents);
$contents = str_replace('__NAME__', $editname, $contents);
$contents = str_replace('__EDITOR__', $edittext1, $contents);
$contents = str_replace('__RELATEDTEXT__', $edittext2, $contents);

//die(print_r($_POST));

die($contents);
