<?php

/**
 * Загрузить данные для ситибанка
 */
class Migration031_EmailParser_LoadData_Citibank extends myBaseMigration
{
    /**
     * Up
     */
    public function up()
    {
        $this->rawQuery("SET NAMES utf8");

        $fixtures = array();
        $fixtures[] = "INSERT INTO `email_sources` (`id`, `name`, `email_list`) VALUES (1, 'Citibank', 'citialerts.russia@citibank.com');";
        $fixtures[] = 'INSERT INTO `email_parsers` (`id`, `email_source_id`, `name`, `subject_regexp`, `account_regexp`, `total_regexp`, `description_regexp`, `type`) VALUES
            (1, 1, \'Списания по кредитной карте (РУС)\', \'Citibank Alerting Service: Списания по кредитной карте\', \'номер которой заканчивается на (\\\\d+),\', \'произошло списание на сумму\\\\s+(\\\\d*\\\\.\\\\d*)\', \'Описание операции: ([\\\\w\\\\s]+)\', 1),
            (2, 1, \'Операции по дополнительной карте\', \'Citibank Alerting Service: Операции по дополнительной карте\', \'Номер карты заканчивается на: (\\\\d+)\\\\.\', \'Сумма списания:\\\\s+([\\\\d\\\\.]+)\', \'Название магазина: ([\\\\w\\\\s]+)\\\\.\', 1),
            (3, 1, \'Поступление средств на карту (ENG)\', \'Citibank Alerting Service: Payment to Card Account\', \'Card number ending:\\\\s+(\\\\d+)\', \'Payment amount:\\\\s+([\\\\d\\\\.,]+)\', \' \', 0),
            (4, 1, \'Платеж с карты (ENG)\', \'Citibank Alerting Service: Debit on Basic \\/ Supplementary card\', \'Card number with ending:\\\\s+(\\\\d+)\', \'Amount debited:\\\\s+([\\\\d\\\\.,]+)\', \'Merchant: (.+)\', 1);';

        foreach ($fixtures as $query) {
            $this->rawQuery($query);
        }
    }


    /**
     * Down
     */
    public function down()
    {
        $this->rawQuery("TRUNCATE TABLE email_sources;
                         TRUNCATE TABLE email_parsers;");
    }

}
