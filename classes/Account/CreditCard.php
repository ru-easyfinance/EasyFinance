<?php
class Account_CreditCard extends Account
{
    /**
     * Начальный платёж
     * @var int
     */
    private $bank = '';

    /**
     * Процент годовых
     * @var int
     */
    private $yearPercent = 0;

    /**
     * Платёжная система
     * @var int
     */
    private $paySystem = 0;

    /**
     * Срок действия карты
     * @var string
     */
    private $validityPeriod = '';

    /**
     * Кредитный лимит
     * @var int
     */
    private $creditLimit = 0;

    /**
     * Свободный остаток
     * @var int
     */
    private $remainAmount = 0;

    /**
     * Грейс-период
     * @var int
     */
    private $graisePeriod = 0;

    /**
     * Проверяет корректность введённых данных и подготавливает массив для работы с моделью
     * @param array $params
     * @return array
     */
    public function check($params)
    {
        $valid = array();
        $args = $params;
        // Проверяем ID
        //подготавливаем отправляемый массив в модель
        $valid[1] = (string)@$params['name'];
        $valid['id'] = (int)@$params['id'];
        $valid[2] = (int)@$params['type'];
        $valid[7] = trim(@$params['comment']);
        $valid[29] = (int)@$params['currency'];
        $valid[8] = (string)@$params['bank'];
        $valid[11] = (int)@$params['yearPercent'];
        $valid[22] = (int)@$params['paySystem'];
        $valid[23] = @formatRussianDate2MysqlDate($params['validityPeriod']);
        $valid[19] = (int)@$params['creditLimit'];
        $valid[21] = (int)@$params['graisePeriod'];
        //подготовим объект
        $this->name = $valid[1];
        $this->type = $valid[2];
        //$this->bank = $valid[8];
        $this->comment = $valid[7];
        $this->currency = $valid[29];
        //$this->yearPercent = $valid[11];
        //$this->paySystem = $valid[22];
        //$this->validityPeriod = $valid[23];
        //$this->creditLimit = $valid[19];
        //$this->graisePeriod = $valid[21];

        return ($valid);
    }


    /**
     * Возвращает первую операцию
     * @return int
     */
    public function getamount()
    {
        return $this->bank;
    }
}
