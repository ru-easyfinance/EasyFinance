<?php
class Account_Metallic extends Account
{
    /**
     * Банк
     * @var int
     */
    private $bank = '';

    /**
     * Тип металла
     * @var string
     */
    private $metall = '';

    /**
     * Доходность % годовых
     * @var int
     */
    private $incomeYearPercent = 0;

    /**
     * Текущая рыночная стоимость
     * @var int
     */
    private $currentMarketCost = 0;

    /**
     * Дата открытия счёта в формате mysql
     * @var string
     */
    private $dateOpen = '';

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
        $valid[26] = (string)@$params['typeMetal'];
        $valid[8] = (string)@$params['bank'];
        //$valid[12] = (float)@$params['incomeYearPercent'];
        $valid[6] = (float)@$params['currentmarketCost'];
        $valid[15] = @formatRussianDate2MysqlDate($params['dateOpen']);
        //подготовим объект
        $this->name = $valid[1];
        $this->type = $valid[2];
        $this->comment = $valid[7];
        $this->currency = $valid[29];
        //$this->bank = $valid[8];
        //$this->metall = $valid[26];
        //$this->currentMarketCost = $valid[6];
        //$this->incomeYearPercent = $valid[12];
        //$this->dateOpen = $valid[15];


        /*if ( in_array('amount', $params) ) {
            $valid['4'] = (int)@$params['amount'];
        }*/
        //die (print_r($valid));
        return ($valid);
    }

    /**
     * Создаёт новый счёт типа "Металлический счёт"
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
            $this->model->create($valid);
            unset($this->model);
        }
        return $this;
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
            throw new Account_Exception();
        } else {
            $this->model->update($valid);
            unset($this->model);
        }
        return $this;
    }


}