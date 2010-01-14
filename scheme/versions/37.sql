ALTER TABLE `daily_currency` ADD `currency_from` INT( 11 ) DEFAULT '1' NOT NULL AFTER `currency_id` ;

UPDATE `currency` SET cur_char_code='AZN' WHERE cur_char_code='AZM';