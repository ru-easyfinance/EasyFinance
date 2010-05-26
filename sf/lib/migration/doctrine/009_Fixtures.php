<?php

/**
 * Загрузить фикстуры
 */
class Migration009_Fixtures extends myBaseMigration
{
    /**
     * Up
     */
    public function up()
    {
        $this->rawQuery("SET NAMES utf8");

        $fixture = array();
        $fixture[] = "
            REPLACE INTO `account_types` VALUES
              (1,'Наличные'),
              (2,'Дебетовая карта'),
              (5,'Депозит'),
              (6,'Займ выданный'),
              (7,'Займ полученый'),
              (8,'Кредитная карта'),
              (9,'Кредит'),
              (10,'Металлический счет'),
              (11,'Акции'),
              (12,'ПИФ'),
              (13,'ОФБУ'),
              (14,'Имущество'),
              (15,'Электронный кошелек');
        ";

        $fixture[] = "
            REPLACE INTO `Acc_Fields` VALUES
              (3,'amount','Начальный баланс'),
              (8,'bank','Банк'),
              (26,'typeMetal','Тип металла'),
              (6,'currentMarketCost','Текущая рыночная стоимость'),
              (9,'loanReceiver','Займополучатель'),
              (10,'loanGiver','Заимодавец'),
              (11,'yearPercent','Процент годовых'),
              (12,'incomeYearPercent','Доходность % годовых'),
              (13,'dateGive','Дата выдачи'),
              (14,'dateReturn','дата возврата'),
              (15,'dateOpen','Дата открытия'),
              (16,'dateClose','Дата закрытия'),
              (17,'dateGet','Дата получения'),
              (18,'dateOff','Дата погашения'),
              (19,'creditLimit','Кредитный лимит'),
              (20,'remainAmount','Свободный остаток'),
              (21,'graisePeriod','Грейс-период'),
              (22,'paySystem','тип карты / платёжная система'),
              (23,'validityPeriod','Срок действия'),
              (24,'typePayment','тип платежа'),
              (25,'support','Обеспечение'),
              (27,'UK','УК'),
              (28,'typeProperty','Тип имущества'),
              (29,'binding','Привязка к номеру счёта в банке');
        ";

        $fixture[] = "
            REPLACE INTO `Acc_ConnectionTypes` VALUES
              (3,2),
              (3,1),
              (3,5),
              (3,8),
              (3,9),
              (3,6),
              (3,7),
              (8,2),
              (8,5),
              (8,8),
              (8,9),
              (8,10),
              (9,6),
              (10,7),
              (11,2),
              (11,5),
              (11,6),
              (11,7),
              (11,8),
              (11,9),
              (12,5),
              (12,6),
              (12,10),
              (12,11),
              (12,12),
              (12,13),
              (12,14),
              (13,6),
              (14,6),
              (15,5),
              (15,10),
              (15,11),
              (15,12),
              (15,13),
              (15,14),
              (16,5),
              (17,7),
              (17,9),
              (18,7),
              (18,9),
              (19,8),
              (21,8),
              (22,8),
              (22,2),
              (22,15),
              (23,2),
              (23,8),
              (24,9),
              (25,9),
              (26,10),
              (27,12),
              (27,13),
              (28,14),
              (3,16),
              (8,16),
              (15,16),
              (16,16),
              (29,2);
        ";

        $fixture[] = "
            REPLACE INTO `system_categories` VALUES
              (1,'Автомобиль'),
              (2,'Банковское обслуживание'),
              (3,'Дети'),
              (4,'Домашнее хозяйство'),
              (5,'Домашние животные'),
              (6,'Досуг и отдых'),
              (22,'Зарплата и персональные доходы'),
              (23,'Инвестиционный доход'),
              (7,'Коммунальные платежи'),
              (8,'Медицина'),
              (9,'Налоги, сборы и взносы'),
              (10,'Образование'),
              (11,'Одежда, обувь, аксессуары'),
              (12,'Питание'),
              (13,'Подарки, помощь родственникам, благотворительность'),
              (14,'Проезд, транспорт'),
              (15,'Проценты по кредитам и займам'),
              (16,'Прочие доходы'),
              (17,'Прочие личные расходы'),
              (18,'Расходы по работе'),
              (19,'Связь, ТВ и интернет'),
              (20,'Страхование'),
              (21,'Уход за собой');
        ";

        $fixture[] = "
            REPLACE INTO `currency` VALUES
              ('1','руб','RUB','Российский рубль ','643','Россия; Абхазия; Южная Осетия','1','0.000000','0000-00-00 00:00:00','0000-00-00 00:00:00'),
              ('2','$','USD','Доллар США ','840','США; Американское Самоа; Британская территория в Индийском океане; Виргинские острова (Британские); Виргинские острова (США); Восточный Тимор; Гаити; Гуам; Малые Тихоокеанские Отдаленные острова США; Маршалловы острова; Микронезия; Палау; Панама; Пуэрто-Р','1','30.752300','0000-00-00 00:00:00','2010-05-21 09:49:50'),
              ('3','€','EUR','Евро ','978','Австрия; Андорра; Бельгия; Ватикан; Гваделупа; Германия; Греция; Ирландия; Испания; Италия; Кипр, Люксембург; Мальта, Мартиника; Монако; Нидерланды; Португалия; Реюньон; Сан-Марино; Сен-Пьер и Микелон; Финляндия; Франция; Французская Гвиана; Французские Ю','1','38.040600','0000-00-00 00:00:00','2010-05-21 09:49:50'),
              ('4','грн','UAH','Гривна ','980','Украина','1','3.881150','0000-00-00 00:00:00','2010-05-21 09:49:51'),
              ('5','AUD','AUD','Австралийский доллар ','036','Австралия; Кирибати; Кокосовые острова; Науру; Норфолк; Остров Рождества; Тувалу; Херд и Макдональд','1','25.558200','0000-00-00 00:00:00','2010-05-21 09:49:49'),
              ('6','BYR','BYR','Белорусский рубль ','974','Беларусь','1','0.010247','0000-00-00 00:00:00','2010-05-21 09:49:50'),
              ('7','DKK','DKK','Датская крона ','208','Дания; Гренландия; Фарерские острова','1','5.125900','0000-00-00 00:00:00','2010-05-21 09:49:50'),
              ('9','KZT','KZT','Тенге ','398','Казахстан','1','0.209727','0000-00-00 00:00:00','2010-05-21 09:49:50'),
              ('10','CAD','CAD','Канадский доллар ','124','Канада','1','29.229400','0000-00-00 00:00:00','2010-05-21 09:49:50'),
              ('11','CNY','CNY','Юань жэньминьби ','156','Китай','1','4.504120','0000-00-00 00:00:00','2010-05-21 09:49:50'),
              ('12','NOK','NOK','Норвежская крона ','578','Норвегия; Остров Буве; Свальбард; Ян-Майен','1','4.781290','0000-00-00 00:00:00','2010-05-21 09:49:50'),
              ('13','XDR','XDR','СДР (Специальные права заимствования) ','960','Международный валютный фонд','1','44.959200','0000-00-00 00:00:00','2010-05-21 09:49:51'),
              ('14','SGD','SGD','Сингапурский доллар ','702','Сингапур','1','21.853500','0000-00-00 00:00:00','2010-05-21 09:49:51'),
              ('15','TRY','TRY','Турецкая лира ','949','Турция','1','19.563800','0000-00-00 00:00:00','2010-05-21 09:49:51'),
              ('16','GBP','GBP','Фунт стерлингов ','826','Великобритания','1','44.123400','0000-00-00 00:00:00','2010-05-21 09:49:49'),
              ('17','SEK','SEK','Шведская крона ','752','Швеция','1','3.938010','0000-00-00 00:00:00','2010-05-21 09:49:51'),
              ('18','CHF','CHF','Швейцарский франк ','756','Лихтенштейн; Швейцария','1','26.713300','0000-00-00 00:00:00','2010-05-21 09:49:51'),
              ('19','JPY','JPY','Иена ','392','Япония','1','0.336808','0000-00-00 00:00:00','2010-05-21 09:49:51'),
              ('24','AZM ','AZN','Азербайджанский манат ','031','Азербайджан','1','38.272900','0000-00-00 00:00:00','2010-05-21 09:49:49'),
              ('30','AMD ','AMD ','Армянский драм ','051','Армения','1','0.079618','0000-00-00 00:00:00','2010-05-21 09:49:50'),
              ('53','CZK','CZK','Чешская крона ','203','Чешская Республика','1','1.479970','0000-00-00 00:00:00','2010-05-21 09:49:51'),
              ('71','HUF','HUF','Форинт ','348','Венгрия','1','0.135940','0000-00-00 00:00:00','2010-05-21 09:49:50'),
              ('72','INR','INR','Индийская рупия ','356','Бутан; Индия','1','0.659072','0000-00-00 00:00:00','2010-05-21 09:49:50'),
              ('81','KRW','KRW','Вона ','410','Корея, республика','1','0.025747','0000-00-00 00:00:00','2010-05-21 09:49:51'),
              ('83','KGS','KGS','Сом ','417','Киргизия','1','0.679684','0000-00-00 00:00:00','2010-05-21 09:49:50'),
              ('87','LVL','LVL','Латвийский лат ','428','Латвия','1','53.885200','0000-00-00 00:00:00','2010-05-21 09:49:50'),
              ('90','LTL','LTL','Литовский лит ','440','Литва','1','11.042500','0000-00-00 00:00:00','2010-05-21 09:49:50'),
              ('101','MDL','MDL','Молдавский лей ','498','Молдова','1','2.396530','0000-00-00 00:00:00','2010-05-21 09:49:50'),
              ('133','ZAR','ZAR','Рэнд ','710','Лесото; Намибия; Южная Африка','1','3.930110','0000-00-00 00:00:00','2010-05-21 09:49:51'),
              ('152','UZS','UZS','Узбекский сум ','860','Узбекистан','1','0.019518','0000-00-00 00:00:00','2010-05-21 09:49:51'),
              ('163','TJS','TJS','Сомони ','972','Таджикистан','1','7.039070','0000-00-00 00:00:00','2010-05-21 09:49:51'),
              ('165','BGN','BGN','Болгарский лев ','975','Болгария','1','19.499300','0000-00-00 00:00:00','2010-05-21 09:49:50'),
              ('169','PLN','PLN','Злотый ','985','Польша','1','9.266930','0000-00-00 00:00:00','2010-05-21 09:49:50'),
              ('170','BRL','BRL','Бразильский реал ','986','Бразилия ','1','16.830300','0000-00-00 00:00:00','2010-05-21 09:49:50'),
              ('171','RON','RON','Новый румынский лей','946','Румыния','1','9.078170','0000-00-00 00:00:00','2010-05-21 09:49:51'),
              ('172','EEK','EEK','Эстонский крон','233','Эстония','1','2.437430','0000-00-00 00:00:00','2010-05-21 09:49:51'),
              ('173','TMT','TMT','Новый туркменский манат','934','Туркмения','1','10.777000','0000-00-00 00:00:00','2010-05-21 09:49:51');
        ";

        $fixture[] = "
            REPLACE INTO `daily_currency` (`currency_id`, `currency_from`, `currency_date`, `currency_sum`) VALUES
              (5  , 1, '2010-05-20', 26.3550),
              (24 , 1, '2010-05-20', 38.1972),
              (16 , 1, '2010-05-20', 43.9741),
              (30 , 1, '2010-05-20', 0.0796),
              (6  , 1, '2010-05-20', 0.0102),
              (165, 1, '2010-05-20', 19.1439),
              (170, 1, '2010-05-20', 16.8600),
              (71 , 1, '2010-05-20', 0.1342),
              (7  , 1, '2010-05-20', 5.0333),
              (2  , 1, '2010-05-20', 30.6953),
              (3  , 1, '2010-05-20', 37.4206),
              (72 , 1, '2010-05-20', 0.6668),
              (9  , 1, '2010-05-20', 0.2093),
              (10 , 1, '2010-05-20', 29.4581),
              (83 , 1, '2010-05-20', 0.6784),
              (11 , 1, '2010-05-20', 4.4958),
              (87 , 1, '2010-05-20', 52.9412),
              (90 , 1, '2010-05-20', 10.8468),
              (101, 1, '2010-05-20', 2.3934),
              (12 , 1, '2010-05-20', 4.8354),
              (169, 1, '2010-05-20', 9.2590),
              (171, 1, '2010-05-20', 8.9109),
              (13 , 1, '2010-05-20', 45.0994),
              (14 , 1, '2010-05-20', 21.9566),
              (163, 1, '2010-05-20', 7.0259),
              (15 , 1, '2010-05-20', 19.6689),
              (173, 1, '2010-05-20', 10.7571),
              (152, 1, '2010-05-20', 0.0195),
              (4  , 1, '2010-05-20', 3.8742),
              (53 , 1, '2010-05-20', 1.4678),
              (17 , 1, '2010-05-20', 3.9077),
              (18 , 1, '2010-05-20', 26.7357),
              (172, 1, '2010-05-20', 2.3936),
              (133, 1, '2010-05-20', 4.0075),
              (81 , 1, '2010-05-20', 0.0264),
              (19 , 1, '2010-05-20', 0.3332);
        ";

        foreach ($fixture as $query) {
            $this->rawQuery($query);
        }

    }


    /**
     * Down
     */
    public function down()
    {
        $this->rawQuery("
            TRUNCATE TABLE account_types;
            TRUNCATE TABLE Acc_Fields;
            TRUNCATE TABLE Acc_ConnectionTypes;
            TRUNCATE TABLE system_categories;
            TRUNCATE TABLE currency;
            TRUNCATE TABLE daily_currency;
        ");
    }

}
