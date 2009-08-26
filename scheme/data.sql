INSERT INTO `account_fields` (`account_field_id`, `account_typesaccount_type_id`, `field_descriptionsfield_description_id`) VALUES
(1, 1, 1),
(3, 1, 5),
(4, 2, 1),
(79, 12, 16),
(81, 14, 16),
(8, 2, 5),
(9, 1, 6),
(70, 1, 16),
(12, 2, 6),
(80, 13, 16),
(82, 15, 16),
(69, 7, 6),
(17, 8, 1),
(58, 8, 6),
(71, 2, 16),
(73, 6, 16),
(75, 8, 16),
(77, 10, 16),
(78, 11, 16),
(24, 8, 5),
(68, 7, 5),
(72, 5, 16),
(74, 7, 16),
(76, 9, 16),
(30, 9, 1),
(31, 9, 5),
(67, 7, 1),
(33, 9, 6),
(34, 10, 1),
(35, 10, 5),
(36, 10, 6),
(66, 6, 5),
(38, 11, 1),
(39, 11, 5),
(65, 6, 6),
(41, 11, 6),
(42, 12, 1),
(43, 12, 5),
(44, 12, 6),
(64, 6, 1),
(46, 13, 1),
(47, 13, 5),
(48, 13, 6),
(63, 5, 5),
(50, 14, 1),
(51, 14, 5),
(52, 14, 6),
(62, 5, 6),
(54, 15, 1),
(55, 15, 5),
(56, 15, 6),
(61, 5, 1);
INSERT INTO `account_field_descriptions` (`field_description_id`, `field_visual_name`, `field_name`, `field_type`, `field_regexp`, `field_permissions`, `field_default_value`) VALUES
(1, 'Название', 'name', 'string', '', 'input', ''),
(2, 'Доступный остаток', 'demand_balance', 'numeric', '[0-9]+', 'input', ''),
(3, 'Зарезервировано', 'reserve', 'numeric', '[0-9]+', 'input', ''),
(4, 'Годовой процент', 'annual_percentage', 'numeric', '[0-9]+', 'input', ''),
(5, 'Примечание', 'description', 'html', '', 'input', ''),
(6, 'Начальный баланс', 'starter_balance', 'numeric', '     [0-9]+ ', 'input', ''),
(16, 'Общий баланс', 'total_balance', 'string', '[0-9]+', 'hidden', ''),
(8, 'Банк', 'bank_name', 'string', '', 'input', ''),
(9, 'Тип карты', 'card_type', 'string', '', 'input', ''),
(10, 'Срок действия', 'card_evaluation_period', 'string', '', 'input', ''),
(11, 'Кредитный лимит', 'credit_limit', 'numeric', '    [0-9]+ ', 'input', ''),
(12, 'Грейс период', 'grace_period', 'string', '', 'input', ''),
(13, 'Дата выписки', 'statement_date', 'string', '', 'input', ''),
(14, 'Использованный кредит', 'used_credit', 'string', '    [0-9]+', 'input', ''),
(15, 'Доступный остаток', 'free_limit', 'string', '     [0-9]+', 'input', '');
INSERT INTO `account_types` (`account_type_id`, `account_type_name`) VALUES
(1, 'Наличные'),
(2, 'Дебетовая карта'),
(5, 'Депозит'),
(6, 'Займ выданный'),
(7, 'Займ полученый'),
(8, 'Кредитная карта'),
(9, 'Кредит'),
(10, 'Металлический счет'),
(11, 'Акции'),
(12, 'ПИФ'),
(13, 'ОФБУ'),
(14, 'Имущество'),
(15, 'Электронный кошелек');
INSERT INTO `currency` (`cur_id`, `cur_name`, `cur_char_code`, `cur_name_value`) VALUES
(1, 'руб.', 'RUB', 'Российский рубль'),
(2, '$', 'USD', 'Доллар США'),
(3, '&euro;', 'EUR', 'Евро'),
(4, 'грн.', 'UAH', 'Украинских гривен');
INSERT INTO `daily_currency` (`currency_id`, `currency_date`, `currency_sum`, `direction`) VALUES
(2, '2009-03-27', '33.4668', '+'),
(3, '2009-03-27', '45.4446', '+'),
(2, '2009-03-28', '33.4133', '+'),
(3, '2009-03-28', '45.3151', '+'),
(2, '2009-03-26', '33.7268', '+'),
(3, '2009-03-26', '45.3963', '+'),
(2, '2009-04-01', '33.9032', '+'),
(3, '2009-04-01', '44.8946', '+'),
(4, '2009-04-01', '42.1158', '+'),
(2, '2009-08-03', '44.8932', '+'),
(3, '2009-08-03', '24.8212', '-'),
(4, '2009-08-03', '14.8662', '0');
INSERT INTO `system_categories` (`system_category_id`, `system_category_name`, `system_group_id`, `parent_id`) VALUES
(1, 'Коммунальные услуги', 0, 0),
(2, 'Личные расходы', 0, 0),
(3, 'Обучение', 0, 0),
(4, 'Одежда', 0, 0),
(5, 'Налоги', 0, 0),
(6, 'Налоги2', 1, 0),
(7, 'Налоги4', 0, 0);
INSERT INTO `system_categories_group` (`system_group_id`, `system_group_name`) VALUES
(1, 'комунальные');
