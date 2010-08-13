<?php

class _Core_TemplateEngine_CSV extends _Core_Abstract_TemplateEngineOutput
{
    private function str_putcsv($input, $delimiter = ',', $enclosure = '"')
    {
        $fp = fopen('php://temp', 'r+');
        fputcsv($fp, $input, $delimiter, $enclosure);
        rewind($fp);
        $data = fread($fp, 1048576); // [changed]
        fclose($fp);
        return rtrim( $data, "\n" );
    }

    public function display()
    {
        $array = array_diff_key($this->assignedVars, $this->excludedVars);
        $elements = $array["elements"];
        $headers = $array["headers"];
        $filename = $array["filename"];

        $delimiter=";";
        $content = "";
        $content .= implode($delimiter, $headers);
        $content .= "\r\n";
        foreach($elements as $ar)
        {
            //$content .= implode($delimiter, $ar);
            $ar = str_replace(';',',',$ar);
            $list = Array ($ar['date'],
                    $ar['type'],
                    number_format($ar['money'], 2, '.', ''),
                    $ar['account_name'],
                    $ar['cat_name'],
                    $ar['tags'],
                    $ar['comment']);
            $content .=  $this->str_putcsv($list, ';');
            $content .= "\r\n";
        }

        header("content-type: plain/text");
        header('content-disposition: attachment; filename="'.$filename.'"');
        header("content-Transfer-Encoding: binary");
        header("Expires: 0");
        header("Expires: 0");
        header("Cache-Control: private");
        header("Pragma: cache");
        echo iconv('UTF-8', 'WINDOWS-1251', $content);
    }
}
