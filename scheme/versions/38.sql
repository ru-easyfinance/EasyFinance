

-- --------------------------------------------------------

--
-- Структура таблицы `Acc_ConnectionTypes`
--

CREATE TABLE `Acc_ConnectionTypes` (
  `field_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='связи полей и типов счетов';

--
-- Дамп данных таблицы `Acc_ConnectionTypes`
--

INSERT INTO `Acc_ConnectionTypes` VALUES (3, 2);
INSERT INTO `Acc_ConnectionTypes` VALUES (3, 1);
INSERT INTO `Acc_ConnectionTypes` VALUES (3, 5);
INSERT INTO `Acc_ConnectionTypes` VALUES (3, 8);
INSERT INTO `Acc_ConnectionTypes` VALUES (3, 9);
INSERT INTO `Acc_ConnectionTypes` VALUES (3, 6);
INSERT INTO `Acc_ConnectionTypes` VALUES (3, 7);
INSERT INTO `Acc_ConnectionTypes` VALUES (8, 2);
INSERT INTO `Acc_ConnectionTypes` VALUES (8, 5);
INSERT INTO `Acc_ConnectionTypes` VALUES (8, 8);
INSERT INTO `Acc_ConnectionTypes` VALUES (8, 9);
INSERT INTO `Acc_ConnectionTypes` VALUES (8, 10);
INSERT INTO `Acc_ConnectionTypes` VALUES (9, 6);
INSERT INTO `Acc_ConnectionTypes` VALUES (10, 7);
INSERT INTO `Acc_ConnectionTypes` VALUES (11, 2);
INSERT INTO `Acc_ConnectionTypes` VALUES (11, 5);
INSERT INTO `Acc_ConnectionTypes` VALUES (11, 6);
INSERT INTO `Acc_ConnectionTypes` VALUES (11, 7);
INSERT INTO `Acc_ConnectionTypes` VALUES (11, 8);
INSERT INTO `Acc_ConnectionTypes` VALUES (11, 9);
INSERT INTO `Acc_ConnectionTypes` VALUES (12, 5);
INSERT INTO `Acc_ConnectionTypes` VALUES (12, 6);
INSERT INTO `Acc_ConnectionTypes` VALUES (12, 10);
INSERT INTO `Acc_ConnectionTypes` VALUES (12, 11);
INSERT INTO `Acc_ConnectionTypes` VALUES (12, 12);
INSERT INTO `Acc_ConnectionTypes` VALUES (12, 13);
INSERT INTO `Acc_ConnectionTypes` VALUES (12, 14);
INSERT INTO `Acc_ConnectionTypes` VALUES (13, 6);
INSERT INTO `Acc_ConnectionTypes` VALUES (14, 6);
INSERT INTO `Acc_ConnectionTypes` VALUES (15, 5);
INSERT INTO `Acc_ConnectionTypes` VALUES (15, 10);
INSERT INTO `Acc_ConnectionTypes` VALUES (15, 11);
INSERT INTO `Acc_ConnectionTypes` VALUES (15, 12);
INSERT INTO `Acc_ConnectionTypes` VALUES (15, 13);
INSERT INTO `Acc_ConnectionTypes` VALUES (15, 14);
INSERT INTO `Acc_ConnectionTypes` VALUES (16, 5);
INSERT INTO `Acc_ConnectionTypes` VALUES (17, 7);
INSERT INTO `Acc_ConnectionTypes` VALUES (17, 9);
INSERT INTO `Acc_ConnectionTypes` VALUES (18, 7);
INSERT INTO `Acc_ConnectionTypes` VALUES (18, 9);
INSERT INTO `Acc_ConnectionTypes` VALUES (19, 8);
INSERT INTO `Acc_ConnectionTypes` VALUES (21, 8);
INSERT INTO `Acc_ConnectionTypes` VALUES (22, 8);
INSERT INTO `Acc_ConnectionTypes` VALUES (22, 2);
INSERT INTO `Acc_ConnectionTypes` VALUES (22, 15);
INSERT INTO `Acc_ConnectionTypes` VALUES (23, 2);
INSERT INTO `Acc_ConnectionTypes` VALUES (23, 8);
INSERT INTO `Acc_ConnectionTypes` VALUES (24, 9);
INSERT INTO `Acc_ConnectionTypes` VALUES (25, 9);
INSERT INTO `Acc_ConnectionTypes` VALUES (26, 10);
INSERT INTO `Acc_ConnectionTypes` VALUES (27, 12);
INSERT INTO `Acc_ConnectionTypes` VALUES (27, 13);
INSERT INTO `Acc_ConnectionTypes` VALUES (28, 14);



-- --------------------------------------------------------

--
-- Структура таблицы `Acc_Fields`
--

CREATE TABLE `Acc_Fields` (
  `id` tinyint(1) unsigned NOT NULL COMMENT 'Ид поля',
  `name` varchar(50) NOT NULL COMMENT 'Техническое название поля',
  `description` varchar(50) NOT NULL COMMENT 'Описание поля',
  KEY `id_idx` (`id`),
  KEY `id_acct_idx` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Оределяем поля по типу счетов';

--
-- Дамп данных таблицы `Acc_Fields`
--

INSERT INTO `Acc_Fields` VALUES (3, 'amount', 'Начальный баланс');
INSERT INTO `Acc_Fields` VALUES (8, 'bank', 'Банк');
INSERT INTO `Acc_Fields` VALUES (26, 'typeMetal', 'Тип металла');
INSERT INTO `Acc_Fields` VALUES (6, 'currentMarketCost', 'Текущая рыночная стоимость');
INSERT INTO `Acc_Fields` VALUES (9, 'loanReceiver', 'Займополучатель');
INSERT INTO `Acc_Fields` VALUES (10, 'loanGiver', 'Заимодавец');
INSERT INTO `Acc_Fields` VALUES (11, 'yearPercent', 'Процент годовых');
INSERT INTO `Acc_Fields` VALUES (12, 'incomeYearPercent', 'Доходность % годовых');
INSERT INTO `Acc_Fields` VALUES (13, 'dateGive', 'Дата выдачи');
INSERT INTO `Acc_Fields` VALUES (14, 'dateReturn', 'дата возврата');
INSERT INTO `Acc_Fields` VALUES (15, 'dateOpen', 'Дата открытия');
INSERT INTO `Acc_Fields` VALUES (16, 'dateClose', 'Дата закрытия');
INSERT INTO `Acc_Fields` VALUES (17, 'dateGet', 'Дата получения');
INSERT INTO `Acc_Fields` VALUES (18, 'dateOff', 'Дата погашения');
INSERT INTO `Acc_Fields` VALUES (19, 'creditLimit', 'Кредитный лимит');
INSERT INTO `Acc_Fields` VALUES (20, 'remainAmount', 'Свободный остаток');
INSERT INTO `Acc_Fields` VALUES (21, 'graisePeriod', 'Грейс-период');
INSERT INTO `Acc_Fields` VALUES (22, 'paySystem', 'тип карты / платёжная система');
INSERT INTO `Acc_Fields` VALUES (23, 'validityPeriod', 'Срок действия');
INSERT INTO `Acc_Fields` VALUES (24, 'typePayment', 'тип платежа');
INSERT INTO `Acc_Fields` VALUES (25, 'support', 'Обеспечение');
INSERT INTO `Acc_Fields` VALUES (27, 'UK', 'УК');
INSERT INTO `Acc_Fields` VALUES (28, 'typeProperty', 'Тип имущества');



-- --------------------------------------------------------


-- --------------------------------------------------------

--
-- Структура таблицы `Acc_Values`
--

CREATE TABLE `Acc_Values` (
  `id` int(10) unsigned NOT NULL auto_increment COMMENT 'Ид',
  `field_id` int(10) unsigned NOT NULL COMMENT 'Ид поля',
  `field_value` varchar(255) default NULL COMMENT 'Значение поля',
  `account_id` int(100) unsigned NOT NULL COMMENT 'Ид счёта',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8 COMMENT='Значения для дополнительных полей счетов' AUTO_INCREMENT=27 ;

