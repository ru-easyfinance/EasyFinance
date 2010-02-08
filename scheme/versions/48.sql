UPDATE `operation` SET `type` = 1 where `drain` = 0;
UPDATE `operation` SET `type` = 0 where `drain` = 1;
UPDATE `operation` SET `type` = 2 where `transfer` > 0;