<?php

class Currency
{
    private $id;

    /**
     * видимое имя валюты. например "руб."
     * @var string
     */
    private $abbr;

    /**
     * Чар код валюты.
     * @example RUB
     * @var string
     */
    private $charCode;

    /**
     * Полное имя валюты .
     * @example Российский рубль
     * @var string
     */
    private $name;

    /**
     * Возвращает код валюты по ОКВ
     * @example 643
     * @var int
     */
    private $okv;

    /**
     *
     * @param int $id
     * @param string $name
     * @param string $charCode
     * @param string $abbr
     * @param int $okv - общеросссийский конвертатор валют
     */
    function __construct( $id, $name, $charCode, $abbr, $okv ){
        $this->id = $id;
        $this->name = $name;
        $this->charCode = $charCode;
        $this->abbr = $abbr;
        $this->okv = $okv;
    }

    /**
     * Возвращает ID валюты в ef
     * @return id
     */
    public function getID(){
        return $this->id;
    }

    /**
     * Возвращает Трёхсимвольный буквенный код валюты
     * @return string
     */
    public function getCharCode(){
        return $this->charCode;
    }

    /**
     * Возвращает сокращённое имя валюты.
     * @return string
     */
    public function getName(){
        return $this->abbr;
    }

    /**
     * возвращает полное имя валюты
     * @return string
     */
    public function getFullName(){
        return $this->name;
    }

    /**
     * возвращает код валюты по мировому конвертатору валют.
     * @return int
     */
    public function getOKV(){
        return $this->okv;
    }

    


}