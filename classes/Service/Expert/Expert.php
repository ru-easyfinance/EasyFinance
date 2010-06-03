<?php
/**
 * Услуга эксперта
 *
 * @copyright http://easyfinance.ru/
 * @author Andrew Tereshko aka mamonth
 */

class Service_Expert extends Service
{
    /**
     * Экземпляр владельца ( эксперта )
     *
     * @var User
     */
    protected $user = null;

    public function getPrice()
    {
        return (int)$this->model->service_price;
    }

    public function getTerm()
    {
        return (int)$this->model->service_term;
    }
}