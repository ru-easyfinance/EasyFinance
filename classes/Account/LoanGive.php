<?php
class Account_LoanGive extends Account
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
     * Дата открытия
     * @var string
     */
    private $dateGive = '';

    /**
     * Дата закрытия
     * @var string
     */
    private $dateReturn = '';

    /**
     * Займополучатель
     * @var int
     */
    private $loanReceiver = 0;


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
        $valid[7] = trim(htmlspecialchars(@$params['comment']));
        $valid[29] = (int)@$params['currency'];
        $valid[8] = (int)@$params['bank'];
        $valid[11] = (int)@$params['yearPercent'];
        $valid[13] = @formatRussianDate2MysqlDate($params['dateGive']);
        $valid[14] = @formatRussianDate2MysqlDate($params['dateReturn']);
        $valid[9] = (int)@$params['loanReceiver'];
        //подготовим объект
        $this->name = $valid[1];
        $this->type = $valid[2];
        $this->comment = $valid[7];
        $this->currency = $valid[29];
        //$this->bank = $valid[8];
        //$this->yearPercent = $valid[11];
        //$this->dateGive = $valid[13];
        //$this->dateReturn = $valid[14];
        //$this->loanReceiver = $valid[9];

        return ($valid);
    }

    /**
     * Создаёт новый счёт типа "Займ выданный"
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