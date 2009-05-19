<?php
/**
 * ������� �� ������ ������� ����� �������� ��� ���������� ������.
 *
 * ����� ���������� �������� �������� ������ (� ������, ���� ������ ������������
 * ������� �������� ��������������� ������ �� ����� �������� �������� �������),
 * � ����� � ����� ������� ������������ �������� ������ ������� payaccept.php ������
 * e-POS ������������ ������������ �� ���� ������� �� ������ �� ���� �������
 * ����������� �������� ������� ��� �����������, ��������������, �� ��������
 * ��� ���������� ������.
 */
/**
 * ��� ���� �������������� �������� ��������� ���������� ������� POST:
 * operID
 *      ���, ����������� �������� �� ������� e-POS
 * errornumber
 *      ��� ������
 * errortext
 *      ��������� ��������� �� ������
 * ������ ��������� ������������ ������ � ������ ������������ ��� ������������
 * �������� ������ ������� payaccept.php
 *
 * <strong>����������.</strong>
 *  �����, �� �������� ������������ ������������ ������ e-POS � ������ ��������
 *  ��� ���������� ������ ����� ���� ������ �������� ���� � ��� ��.
 * <strong>��������!</strong>
 *  ���� ������������ �� ��������������� ������ ������ ������������� ���
 *  ������������� �������� ��� ���������� ������. ��� ��������� �����������
 *  ���������� � ������� ������� ����� ������� ������� ���������� �����������
 *  ��������������� ��������� � ������� e-POS (��. �. III.7).
 */
//������� �� ��������� �������
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
