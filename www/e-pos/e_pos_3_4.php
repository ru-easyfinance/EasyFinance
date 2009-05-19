<?php

if(isset($_REQUEST['p']) && strlen($_REQUEST['p']) > 0) {


    $url = "http://www.e-pos.ru/RUR02/payaccept.php";

    $params ="";
    $params.="operstatus=ACCEPT";
    $params.="&operID=".$_REQUEST['p'];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $res = curl_exec($ch);
    curl_close($ch);

    $res = trim($res);


    echo $res;

    $fd=fopen("_3_4.txt", 'w');
    fwrite($fd, $res);
    fclose($fd);
}
?>
