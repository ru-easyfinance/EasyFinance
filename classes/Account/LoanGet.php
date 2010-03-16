<?php
class Account_LoanGet extends Account
{
    /**
     * Банк
     * @var string
     */
    private $bank = '';

    /**
     * Процент годовых
     * @var int
     */
    private $yearPercent = 0;

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
     * Займодавец
     * @var int
     */
    private $loanGiver = 0;


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
        $valid[8] = (int)@$params['bank'];
        $valid[11] = (int)@$params['yearPercent'];
        $valid[17] = @formatRussianDate2MysqlDate($params['dateGet']);
        $valid[18] = @formatRussianDate2MysqlDate($params['dateOff']);
        $valid[10] = (int)@$params['loanGiver'];
        //подготовим объект
        $this->name = $valid[1];
        $this->type = $valid[2];
        $this->comment = $valid[7];
        $this->currency = $valid[29];
        //$this->bank = $valid[8];
        //$this->yearPercent = $valid[11];
        //$this->dateGet = $valid[17];
        //$this->dateOff = $valid[18];
        //$this->loanGiver = $valid[10];

        return ($valid);
    }

    /**
     * Создаёт новый счёт типа "Займ полученный"
     * @param User $user
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
     * @param User $user
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