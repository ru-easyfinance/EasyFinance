<?php
/**
 * Уведомление сервера Партнёра после успешной оплаты счёта.
 *
 * В случае успешной оплаты клиентом выставленного счёта сервер e-POS осуществляет
 * вызов заранее определённого скрипта на сервере Партнёра для уведомления о
 * произведённой оплате.
 *
 * При этом осуществляется передача следующих параметров по методу POST:
 * operID
 *      код, присвоенный операции на сервере e-POS
 * paystatus
 *      фиксированное значение – PAID
 *
 * <strong>ВНИМАНИЕ!</strong>
 *  Данный вызов носит исключительно информационный характер, и НЕ может использоваться
 *  сервером Партнёра как подтверждение о произведённой оплате. Цель данного
 *  вызова – уведомление сервера Партнёра об изменении статуса счёта, которое
 *  необходимо только в случае, если клиент не возвращается на сервер Партнёра
 *  после оплаты (например, сразу после оплаты закрывает браузер).
 *  Для получения достоверной информации об изменении статуса счёта серверу
 *  Партнёра необходимо осуществить обращение к серверу e-POS для проверки состояния
 *  счёта (см. п. III.7).
 */

include_once 'conf.php';


$params = ""; 
foreach($_POST as $key => $val) {
    //if($key !== 'action')
    $params .= $key."=".$val."&";
}

$url = "http://www.e-pos.ru/RUR02/paystatus.php";

$params .= "login=$e_pos_login&pwdmd5=$e_pos_pass&opertype=new&currency=RUR";


$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$res = curl_exec($ch);
curl_close($ch);

$res = trim($res);

$p = xml_parser_create();
xml_parse_into_struct($p, $res, $vals, $index);
xml_parser_free($p);

$operID    = $vals[$index["OPERID"][0]]['value'];
$paystatus =  $vals[$index["PAYSTATUS"][0]]['value'];

$sql = "UPDATE `$db_tbl` SET paystatus = '$paystatus' WHERE `operID` = '$operID'";

@mysql_query($sql);

header("Content-type: text/xml; charset=windows-1251");

$fd = fopen("_3_5.txt", 'w');
fwrite($fd, "@$sql@");
fclose($fd);


print $res;    

?>