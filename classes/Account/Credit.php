<?php
class Account_Credit extends Account
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
     * Свободный остаток
     * @var int
     */
    private $remainAmount = 0;

    /**
     * Дата получения
     * @var string
     */
    private $dateGet = '';

    /**
     * Дата погашения
     * @var string
     */
    private $dateOff = '';

    /**
     * Тип платежа
     * @var int
     */
    private $typePayment = 0;

    /**
     * Поддержка
     * @var string
     */
    private $support = '';


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
        $valid[17] = @formatRussianDate2MysqlDate($params['dateGet']);
        $valid[18] = @formatRussianDate2MysqlDate($params['dateOff']);
        $valid[24] = (int)@$params['typePayment'];
        $valid[25] = (string)@$params['support'];
        //подготовим объект
        $this->name = $valid[1];
        $this->type = $valid[2];
        //$this->bank = $valid[8];
        $this->comment = $valid[7];
        $this->currency = $valid[29];
        //$this->yearPercent = $valid[11];
        //$this->dateGet = $valid[17];
        //$this->dateOff = $valid[18];
        //$this->typePayment = $valid[24];
        //$this->support = $valid[25];
        return ($valid);
    }

    /**
     * Создаёт новый счёт типа "Кредит"
     * @param oldUser $user
     * @param array mixed $params
     * @return bool
     */
    function create( $user, $params )
    {
        $this->model = new Account_Model();
        $valid = $this->check($params);
        if (!$valid) {

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

}