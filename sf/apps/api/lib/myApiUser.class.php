<?php

class myApiUser extends sfBasicSecurityUser
{
    protected $_id;


    /**
     * Вернуть идентификатор пользователя
     *
     * @return integer
     */
    public function getId()
    {
        return $this->_id;
    }


    /**
     * Установить идентификатор пользователя
     *
     * @param  integer $id Идентификатор пользователя
     * @return void
     */
    public function setId($id)
    {
        $this->_id = (int) $id;
    }

}
