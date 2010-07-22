<?php
class Account_ElectPurse extends Account
{
    /**
     * Платёжная система
     * @var int
     */
    private $paySystem=0;


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
        $valid[22] = (int)@$params['paySystem'];
        //подготовим объект
        $this->name = $valid[1];
        $this->type = $valid[2];
        $this->comment = $valid[7];
        $this->currency = $valid[29];
        //$this->paySystem = $valid[22];

        return ($valid);
    }

    /**
     * Возвращает первую операцию
     * @return int
     */
    public function getPaySystem()
    {
        return $this->paySystem;
    }
}
