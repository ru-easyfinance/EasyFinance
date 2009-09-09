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
INSERT INTO currency VALUES('1','643','руб.','RUB','Российский рубль ','Россия; Абхазия; Южная Осетия','1'),('5','036','AUD','AUD','Австралийский доллар ','Австралия; Кирибати; Кокосовые острова; Науру; Норфолк; Остров Рождества; Тувалу; Херд и Макдональд','1'),('6','974','BYR','BYR','Белорусский рубль ','Беларусь','1'),('7','208','DKK','DKK','Датская крона ','Дания; Гренландия; Фарерские острова','1'),('2','840','$','USD','Доллар США ','США; Американское Самоа; Британская территория в Индийском океане; Виргинские острова (Британские); Виргинские острова (США); Восточный Тимор; Гаити; Гуам; Малые Тихоокеанские Отдаленные острова США; Маршалловы острова; Микронезия; Палау; Панама; Пуэрто-Рико','1'),('3','978','€','EUR','Евро ','Австрия; Андорра; Бельгия; Ватикан; Гваделупа; Германия; Греция; Ирландия; Испания; Италия; Кипр, Люксембург; Мальта, Мартиника; Монако; Нидерланды; Португалия; Реюньон; Сан-Марино; Сен-Пьер и Микелон; Финляндия; Франция; Французская Гвиана; Французские Южные Территории; Черногория','1'),('8','352','ISK','ISK','Исландская крона ','Исландия','1'),('9','398','KZT','KZT','Тенге ','Казахстан','1'),('10','124','CAD','CAD','Канадский доллар ','Канада','1'),('11','156','CNY','CNY','Юань жэньминьби ','Китай','1'),('12','578','NOK','NOK','Норвежская крона ','Норвегия; Остров Буве; Свальбард; Ян-Майен','1'),('13','960','XDR','XDR','СДР (Специальные права заимствования) ','Международный валютный фонд','1'),('14','702','SGD','SGD','Сингапурский доллар ','Сингапур','1'),('15','949','TRY','TRY','Турецкая лира ','Турция','1'),('4','980','грн.','UAH','Гривна ','Украина','1'),('16','826','GBP','GBP','Фунт стерлингов ','Великобритания','1'),('17','752','SEK','SEK','Шведская крона ','Швеция','1'),('18','756','CHF','CHF','Швейцарский франк ','Лихтенштейн; Швейцария','1'),('19','392','JPY','JPY','Иена ','Япония','1'),('20','004','AFA ','AFA ','Афгани ','Афганистан','0'),('21','008','ALL ','ALL ','Лек ','Албания','0'),('22','012','DZD ','DZD ','Алжирский динар ','Алжир','0'),('23','020','ADP ','ADP ','Андорская песета ','Андорра','0'),('24','031','AZM ','AZM ','Азербайджанский манат ','Азербайджан','0'),('25','032','ARS ','ARS ','Аргентинское песо ','Аргентина','0'),('26','036','AUD ','AUD ','Австралийский доллар ','Австралия; Кирибати; Кокосовые (Килинг) острова; Науру; Норфолк, остров; Остров Рождества; Тувалу; Херд и Макдональд, острова','0'),('27','044','BSD ','BSD ','Багамский доллар ','Багамские острова','0'),('28','048','BHD ','BHD ','Бахрейнский динар ','Бахрейн','0'),('29','050','BDT ','BDT ','Така ','Бангладеш','0'),('30','051','AMD ','AMD ','Армянский драм ','Армения','0'),('31','052','BBD ','BBD ','Барбадосский доллар ','Барбадос','0'),('32','060','BMD ','BMD ','Бермудский доллар ','Бермудские острова','0'),('33','064','BTN ','BTN ','Нгултрум ','Бутан','0'),('34','068','BOB ','BOB ','Боливиано ','Боливия','0'),('35','072','BWP ','BWP ','Пула ','Ботсвана','0'),('36','084','BZD ','BZD ','Белизский доллар ','Белиз','0'),('37','090','SBD ','SBD ','Доллар Соломоновых островов ','Соломоновы острова','0'),('38','096','BND ','BND ','Брунейский доллар ','Бруней Даруссалам','0'),('39','100','BGL','BGL','Лев ','Болгария','0'),('40','104','MMK','MMK','Кьят ','Мьянма','0'),('41','108','BIF','BIF','Бурундийский франк ','Бурунди','0'),('42','116','KHR','KHR','Риель ','Камбоджа','0'),('43','132','CVE','CVE','Эскудо Кабо-Верде ','Кабо-Верде','0'),('44','136','KYD','KYD','Доллар Каймановых островов ','Каймановы острова','0'),('45','144','LKR','LKR','Шри-Ланкийская рупия ','Шри-Ланка','0'),('46','152','CLP','CLP','Чилийское песо ','Чили','0'),('47','170','COP','COP','Колумбийское песо ','Колумбия','0'),('48','174','KMF','KMF','Франк Коморских островов ','Коморские острова','0'),('49','188','CRC','CRC','Костариканский колон ','Коста-Рика','0'),('50','191','HRK','HRK','Куна ','Хорватия','0'),('51','192','CUP','CUP','Кубинское песо ','Куба','0'),('52','196','CYP','CYP','Кипрский фунт ','Кипр','0'),('53','203','CZK','CZK','Чешская крона ','Чешская Республика','0'),('54','214','DOP','DOP','Доминиканское песо ','Доминиканская Республика','0'),('55','222','SVC','SVC','Сальвадорский колон ','Сальвадор','0'),('56','230','ЕТВ','ЕТВ','Эфиопский быр ','Эфиопия','0'),('57','232','ERN','ERN','Накфа ','Эритрея','0'),('58','233','ЕЕК','ЕЕК','Крона ','Эстония','0'),('59','238','FKP','FKP','Фунт Фолклендских островов ','Фолклендские (Мальвинские) острова','0'),('60','242','FJD','FJD','Доллар Фиджи ','Фиджи','0'),('61','262','DJF','DJF','Франк Джибути ','Джибути','0'),('62','270','GMD','GMD','Даласи ','Гамбия','0'),('63','288','GHC','GHC','Седи ','Гана','0'),('64','292','GIP','GIP','Гибралтарский фунт ','Гибралтар','0'),('65','320','GTQ','GTQ','Кетсаль ','Гватемала','0'),('66','324','GNF','GNF','Гвинейский франк ','Гвинея','0'),('67','328','GYD','GYD','Гайанский доллар ','Гайана','0'),('68','332','HTG','HTG','Гурд ','Гаити','0'),('69','340','HNL','HNL','Лемпира ','Гондурас','0'),('70','344','HKD','HKD','Гонконгский доллар ','Гонконг','0'),('71','348','HUF','HUF','Форинт ','Венгрия','0'),('72','356','INR','INR','Индийская рупия ','Бутан; Индия','0'),('73','360','IDR','IDR','Рупия ','Восточный Тимор; Индонезия','0'),('74','364','IRR','IRR','Иранский риал ','Иран (Исламская Республика)','0'),('75','368','IQD','IQD','Иракский динар ','Ирак','0'),('76','376','ILS','ILS','Новый израильский шекель ','Израиль','0'),('77','388','JMD','JMD','Ямайский доллар ','Ямайка','0'),('78','400','JOD','JOD','Иорданский динар ','Иордания','0'),('79','404','KES','KES','Кенийский шиллинг ','Кения','0'),('80','408','KPW','KPW','Северо-корейская вона ','Корея, демократическая народная республика','0'),('81','410','KRW','KRW','Вона ','Корея, республика','0'),('82','414','KWD','KWD','Кувейтский динар ','Кувейт','0'),('83','417','KGS','KGS','Сом ','Киргизия','0'),('84','418','LAK','LAK','Кип ','Лаос, народная демократическая республика','0'),('85','422','LBP','LBP','Ливанский фунт ','Ливан','0'),('86','426','LSL','LSL','Лоти ','Лесото','0'),('87','428','LVL','LVL','Латвийский лат ','Латвия','0'),('88','430','LRD','LRD','Либерийский доллар ','Либерия','0'),('89','434','LYD','LYD','Ливийский динар ','Ливийская Арабская Джамахирия','0'),('90','440','LTL','LTL','Литовский лит ','Литва','0'),('91','446','MOP','MOP','Патака ','Макао','0'),('92','450','MGF','MGF','Малагасийский франк ','Мадагаскар','0'),('93','454','MWK','MWK','Квача ','Малави','0'),('94','458','MYR','MYR','Малайзийский рингтит ','Малайзия','0'),('95','462','MVR','MVR','Руфия ','Мальдивы','0'),('96','470','MTL','MTL','Мальтийская лира ','Мальта','0'),('97','478','MRO','MRO','Угия ','Мавритания','0'),('98','480','MUR','MUR','Маврикийская рупия ','Маврикий','0'),('99','484','MXN','MXN','Мексиканское песо ','Мексика','0'),('100','496','MNT','MNT','Тугрик ','Монголия','0'),('101','498','MDL','MDL','Молдавский лей ','Молдова','0'),('102','504','MAD','MAD','Марокканский дирхам ','Западная Сахара; Марокко','0'),('103','508','MZM','MZM','Метикал ','Мозамбик','0'),('104','512','OMR','OMR','Оманский риал ','Оман','0'),('105','516','NAD','NAD','Доллар Намибии ','Намибия','0'),('106','524','NPR','NPR','Непальская рупия ','Непал','0'),('107','532','ANG','ANG','Нидерландский антильский гульден ','Нидерландские Антильские острова','0'),('108','533','AWG','AWG','Арубанский гульден ','Аруба','0'),('109','548','VUV','VUV','Вату ','Вануату','0'),('110','554','NZD','NZD','Новозеландский доллар ','Ниуэ; Новая Зеландия; Острова Кука; Питкерн; Токелау','0'),('111','558','NIO','NIO','Золотая кордоба ','Никарагуа','0'),('112','566','NGN','NGN','Найра ','Нигерия','0'),('113','586','PKR','PKR','Пакистанская рупия ','Пакистан','0'),('114','590','PAB','PAB','Бальбоа ','Панама','0'),('115','598','PGK','PGK','Кина ','Папуа-Новая Гвинея','0'),('116','600','PYG','PYG','Гуарани ','Парагвай','0'),('117','604','PEN','PEN','Новый соль ','Перу','0'),('118','608','PHP','PHP','Филиппинское песо ','Филиппины','0'),('119','624','GWP','GWP','Песо Гвинеи-Бисау ','Гвинея-Бисау','0'),('120','626','TPE','TPE','Тиморское эскудо ','Восточный Тимор','0'),('121','634','QAR','QAR','Катарский риал ','Катар','0'),('122','642','ROL','ROL','Лей ','Румыния','0'),('123','646','RWF','RWF','Франк Руанды ','Руанда','0'),('124','654','SHP','SHP','Фунт Острова Святой Елены ','Остров Святой Елены','0'),('125','678','STD','STD','Добра ','Сан-Томе и Принсипи','0'),('126','682','SAR','SAR','Саудовский риял ','Саудовская Аравия','0'),('127','690','SCR','SCR','Сейшельская рупия ','Сейшельские Острова','0'),('128','694','SLL','SLL','Леоне ','Сьерра-Леоне','0'),('129','703','SKK','SKK','Словацкая крона ','Словакия','0'),('130','704','VND','VND','Донг ','Вьетнам','0'),('131','705','SIT','SIT','Толар ','Словения','0'),('132','706','SOS','SOS','Сомалийский шиллинг ','Сомали','0'),('133','710','ZAR','ZAR','Рэнд ','Лесото; Намибия; Южная Африка','0'),('134','716','ZWD','ZWD','Доллар Зимбабве ','Зимбабве','0'),('135','736','SDD','SDD','Суданский динар ','Судан','0'),('136','740','SRG','SRG','Суринамский гульден ','Суринам','0'),('137','748','SZL','SZL','Лилангени ','Свазиленд','0'),('138','760','SYP','SYP','Сирийский фунт ','Сирийская Арабская Республика','0'),('139','764','THB','THB','Бат ','Таиланд','0'),('140','776','TOP','TOP','Паанга ','Тонга','0'),('141','780','TTD','TTD','Доллар Тринидада и Тобаго ','Тринидад и Тобаго','0'),('142','784','AED','AED','Дирхам (ОАЭ) ','Объединенные Арабские Эмираты (ОАЭ)','0'),('143','788','TND','TND','Тунисский динар ','Тунис','0'),('144','792','TRL','TRL','Турецкая лира ','Турция','0'),('145','795','TMM','TMM','Манат ','Туркмения','0'),('146','800','UGX','UGX','Угандийский шиллинг ','Уганда','0'),('147','807','MKD','MKD','Динар ','Македония','0'),('149','818','EGP','EGP','Египетский фунт ','Египет','0'),('150','834','TZS','TZS','Танзанийский шиллинг ','Танзания, единая республика','0'),('151','858','UYU','UYU','Уругвайское песо ','Уругвай','0'),('152','860','UZS','UZS','Узбекский сум ','Узбекистан','0'),('153','862','VEB','VEB','Боливар ','Венесуэла','0'),('154','882','WST','WST','Тала ','Самоа','0'),('155','886','YER','YER','Йеменский риал ','Йемен','0'),('156','891','YUM','YUM','Югославский динар ','Югославия','0'),('157','894','ZMK','ZMK','Квача (замбийская) ','Замбия','0'),('158','901','TWD','TWD','Новый тайваньский доллар ','Тайвань, провинция Китая','0'),('159','950','XAF','XAF','Франк КФА ВЕАС (денежная единица Банка государств Центральной Африки) ','Габон; Камерун; Конго; Центрально-африканская Республика; Чад; Экваториальная Гвинея','0'),('160','951','XCD','XCD','Восточно-карибский доллар ','Ангилья; Антигуа и Барбуда; Гренада; Доминика; Монсеррат; Сент-Винсент и Гренадины; Сент-Китс и Невис; Сент-Люсия','0'),('161','952','XOF','XOF','Франк КФА ВСЕАО (денежная единица Центрального Банка государств Западной Африки) ','Бенин; Буркина-Фасо; Гвинея-Бисау; Кот д\'Ивуар; Мали; Нигер; Сенегал; Того','0'),('162','953','XPF','XPF','Франк КФП ','Французская Полинезия; Новая Каледония; Уоллис и Футуна, острова','0'),('163','972','TJS','TJS','Сомони ','Таджикистан','0'),('164','973','AOA','AOA','Кванза ','Ангола','0'),('165','975','BGN','BGN','Болгарский лев ','Болгария','0'),('166','976','CDF','CDF','Конголезский франк ','Конго, демократическая республика','0'),('167','977','ВАМ','ВАМ','Конвертируемая марка ','Босния и Герцеговина','0'),('168','981','OEL','OEL','Лари ','Грузия','0'),('169','985','PLN','PLN','Злотый ','Польша','0'),('170','986','BRL','BRL','Бразильский реал ','Бразилия ','0')

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
INSERT INTO `system_categories` (`id`, `name`) VALUES
(1, 'Автомобиль'),
(2, 'Банковское обслуживание'),
(3, 'Дети'),
(4, 'Домашнее хозяйство'),
(5, 'Домашние животные'),
(6, 'Досуг и отдых'),
(7, 'Коммунальные платежи'),
(21, 'Уход за собой'),
(20, 'Страхование'),
(19, 'Связь, ТВ и интернет'),
(18, 'Расходы по работе'),
(17, 'Прочие личные расходы'),
(16, 'Прочие доходы'),
(15, 'Проценты по кредитам и займам'),
(14, 'Проезд, транспорт'),
(13, 'Подарки, помощь родственникам, благотворительность'),
(12, 'Питание'),
(11, 'Одежда, обувь, аксессуары'),
(10, 'Образование'),
(9, 'Налоги, сборы и взносы'),
(8, 'Медицина'),
(23, 'Инвестиционный доход'),
(22, 'Зарплата и персональные доходы');
