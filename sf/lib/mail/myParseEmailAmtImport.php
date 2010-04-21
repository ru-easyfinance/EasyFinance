<?php

/**
 * Класс для парсинга почты из строки и возврата её содержимого в виде массива
 */
class myParseEmailAmtImport
{
    /**
     * Письмо в строке
     * @var string
     */
    private $_raw;

    /**
     * Конструктор
     *
     * @param string $raw
     * @return void
     */
    public function __construct ($raw)
    {
        // Подключаем свой инклуд файлов от Zend
        // TODO: Zend явно дергает require своих модулей
        $zPath = sfConfig::get('sf_root_dir') . '/lib/vendor';
        set_include_path($zPath . PATH_SEPARATOR . get_include_path());

        $this->_raw = $raw;
    }


    /**
     * Распарсить email и получить массив данных от AMT
     *
     * @return array | int в случае ошибки
     */
    public function getAmtData()
    {
        // Парсим и подставляем значения в массив
        $simpleXml = simplexml_load_string($this->getXML());

        if (! $simpleXml) {
            throw new Exception('Failed to load XML');
        }

        $operationData = array(
            'id'          => (string) $simpleXml->id,
            'email'       => (string) $simpleXml->email,
            'type'        => (string) $simpleXml->type,
            'account'     => (string) $simpleXml->account,
            'timestamp'   => (string) $simpleXml->timestamp,
            'amount'      => (string) $simpleXml->amount,
            'description' => (string) $simpleXml->description,
            'place'       => (string) $simpleXml->place,
            'balance'     => (string) $simpleXml->balance
        );

        // Если указана сумма платежа, то добавляем её тоже
        if (isset($simpleXml->payment)) {
            $operationData['payment'] = (string) $simpleXml->payment;
        } else {
            $operationData['payment'] = '';
        }

        return $operationData;
    }


    /**
     * Возвращает строку с XML из письма
     *
     * @return string
     */
    private function getXML()
    {
        $message = new Zend_Mail_Message(array('raw' => $this->_raw));

        foreach ($message as $part) {
            // Нам интересен только XML
            if (stristr($part->contentType, "text/xml;")) {
                if ($part->getHeader('Content-Transfer-Encoding') === Zend_Mime::ENCODING_QUOTEDPRINTABLE) {
                    return quoted_printable_decode($part->getContent());
                } elseif ( $part->getHeader('Content-Transfer-Encoding') === Zend_Mime::ENCODING_BASE64 ) {
                    return base64_decode($part->getContent());
                }
            }
        }

        // Выкидываем ошибку, если STDIN оказался пустым
        throw new Exception('Data not found');
    }

}
