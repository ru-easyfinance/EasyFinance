<?php
class Account_Cash extends Account
{
    /**
     * Начальный платёж
     * @var int
     */
    private $initPament = 0;
    

    /**
     * Проверяет корректность введённых данных и подготавливает массив для работы с моделью
     * @param array $params
     * @return array
     */
    public function check($params)
    {
        $valid = array();
        $args = $params;
        //подготавливаем отправляемый массив в модель
        $valid[1] = (string)@$params['name'];
        $valid['id'] = (int)@$params['id'];
        $valid[2] = (int)@$params['type'];
        $valid[7] = trim(@$params['comment']);
        $valid[29] = (int)@$params['currency'];
        $valid[3] = (int)@$params['initPayment'];
        //подготовим объект
        $this->name = $valid[1];
        $this->type = $valid[2];
        $this->comment = $valid[7];
        $this->currency = $valid[29];
        $this->initPament = $valid[3];
        
        /*if ( in_array('amount', $params) ) {
            $valid['4'] = (int)@$params['amount'];
        }*/
        //die (print_r($valid));
        return ($valid);
    }

    /**
     * Создаёт новый счёт типа "Наличные"
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

    /**
     * Возвращает первую операцию
     * @return int
     */
    public function getAmount()
    {
        return $this->initPament;
    }
}