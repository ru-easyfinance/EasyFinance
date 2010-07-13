<?php


class EmailSourceTable extends Doctrine_Table
{

    public static function getInstance()
    {
        return Doctrine_Core::getTable('EmailSource');
    }

    /**
     * Вернуть (?)EmailSource по адресу отправителя
     * FIX: сейчас в $from должен попадать адрес без спец-символов, имен и т.п.
     *      т.е. <citialerts.russia@citibank.com> не канает,
     *      а citialerts.russia@citibank.com канает
     *
     * @param  string $from
     * @return EmailSource
     */
    public function getByEmail($from)
    {
        $from = trim($from);

        return $this->findByDql("email_list LIKE ? LIMIT 1", array( "%{$from}%"))->getFirst();
    }

}
