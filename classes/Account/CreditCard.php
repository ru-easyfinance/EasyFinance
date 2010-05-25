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
     * Создаёт новый счёт типа "Кредитная карта"
     * @param oldUser $user
     * @param array mixed $params
     * @return bool
     */
    function create( $user, $params )
    {
        $this->model = new Account_Model();
        $valid = $this->check($params);
        if (!$valid) {
            throw new Account_Exception();
        } else {
            $acc = $this->model->create($valid);
            $params['id'] = $acc;
            $this->model->new_operation($params);
            unset($this->model);
        }
        return $acc;
    }

    /**
     * Редактирует существующий счёт
     * @param oldUser $user
     * @param array $params
     * @return bool
     */
    function update( $user, $params)
    {
        $this->model = new Account_Model();
        $valid = $this->check($params);
        if (!$valid) {

        } else {
            $this->model->update($valid);
            $params['id'] = $valid['id'];
            $this->model->edit_operation($params);
            unset($this->model);
        }
        return $this;
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