<?php
/**
 * Возврат на сервер Партнёра после успешной или неуспешной оплаты.
 *
 * После завершения клиентом процесса оплаты (в случае, если клиент осуществляет
 * возврат нажатием соответствующей кнопки на сайте мерчанта платёжной системы),
 * а также в части случаев некорректной передачи данных скрипту payaccept.php сервер
 * e-POS осуществляет переключение на сайт Партнёра по одному из двух заранее
 * определённых Партнёром адресов для уведомления, соответственно, об успешной
 * или неуспешной оплате.
 */
/**
 * При этом осуществляется передача следующих параметров методом POST:
 * operID
 *      код, присвоенный операции на сервере e-POS
 * errornumber
 *      код ошибки
 * errortext
 *      текстовое сообщение об ошибке
 * Данные параметры возвращаются только в случае переключения при некорректной
 * передаче данных скрипту payaccept.php
 *
 * <strong>Примечание.</strong>
 *  Адрес, по которому осуществляет переключение сервер e-POS в случае успешной
 *  или неуспешной оплаты может быть выбран Партнёром один и тот же.
 * <strong>ВНИМАНИЕ!</strong>
 *  Факт переключения на соответствующий скрипт НЕЛЬЗЯ рассматривать как
 *  подтверждение успешной или неуспешной оплаты. Для получения достоверной
 *  информации о текущем статусе счёта серверу Партнёра необходимо осуществить
 *  соответствующее обращение к серверу e-POS (см. п. III.7).
 */
//возврат из платежной системы
if(isset($_POST['operID']) && strlen($_POST['operID']) > 0) {

    include_once 'conf.php';

    $url = "http://www.e-pos.ru/RUR02/paystatus.php";
    $params = "opertype=check&operID=".$_POST['operID'];

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

    $fd=fopen("_3_6.txt", 'w');
    fwrite($fd, $sql);
    fclose($fd);

    echo "<pre>";
    print_r($_POST);
}
?>
