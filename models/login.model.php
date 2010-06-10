<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);

/**
 * Класс модели для логина
 * @category login
 * @copyright http://easyfinance.ru/
 */
class Login_Model
{
    /**
     * Создаёт категории по умолчанию
     * @return void
     */
    static public function defaultCategory($uid)
    {
        $date = date('Y-m-d H:i:s');

        $sql = "INSERT INTO category (`cat_parent`, `user_id`, `system_category_id`, `cat_name`, `type`, `created_at`, `updated_at`) VALUES
        (0, {$uid}, 1,  'Автомобиль', -1, '{$date}', '{$date}'),
        (0, {$uid}, 2,  'Банковское обслуживание', -1, '{$date}', '{$date}'),
        (0, {$uid}, 3,  'Дети', -1, '{$date}', '{$date}'),
        (0, {$uid}, 4,  'Домашнее хозяйство', -1, '{$date}', '{$date}'),
        (0, {$uid}, 5,  'Домашние животные', -1, '{$date}', '{$date}'),
        (0, {$uid}, 6,  'Досуг и отдых', -1, '{$date}', '{$date}'),
        (0, {$uid}, 22, 'Зарплата и персональные доходы', 1, '{$date}', '{$date}'),
        (0, {$uid}, 23, 'Инвестиционный доход', 1, '{$date}', '{$date}'),
        (0, {$uid}, 7,  'Коммунальные платежи', -1, '{$date}', '{$date}'),
        (0, {$uid}, 8,  'Медицина', -1, '{$date}', '{$date}'),
        (0, {$uid}, 9,  'Налоги, сборы и взносы', -1, '{$date}', '{$date}'),
        (0, {$uid}, 10, 'Образование', -1, '{$date}', '{$date}'),
        (0, {$uid}, 11, 'Одежда, обувь, аксессуары', -1, '{$date}', '{$date}'),
        (0, {$uid}, 12, 'Питание', -1, '{$date}', '{$date}'),
        (0, {$uid}, 13, 'Подарки, помощь родственникам, благотворительность', -1, '{$date}', '{$date}'),
        (0, {$uid}, 14, 'Проезд, транспорт', -1, '{$date}', '{$date}'),
        (0, {$uid}, 15, 'Проценты по кредитам и займам', -1, '{$date}', '{$date}'),
        (0, {$uid}, 16, 'Прочие доходы', 1, '{$date}', '{$date}'),
        (0, {$uid}, 17, 'Прочие личные расходы', -1, '{$date}', '{$date}'),
        (0, {$uid}, 18, 'Расходы по работе', -1, '{$date}', '{$date}'),
        (0, {$uid}, 19, 'Связь, ТВ и интернет', -1, '{$date}', '{$date}'),
        (0, {$uid}, 20, 'Страхование', -1, '{$date}', '{$date}'),
        (0, {$uid}, 21, 'Уход за собой', -1, '{$date}', '{$date}')";
        Core::getInstance()->db->query($sql);

        $sql = "INSERT INTO category (`cat_parent`, `user_id`, `system_category_id`, `cat_name`, `type`, `created_at`, `updated_at`) VALUES
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=1 AND cat_parent=0), {$uid}, 1, 'Стоянка', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=1 AND cat_parent=0), {$uid}, 1, 'Платные дороги, штрафы', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=1 AND cat_parent=0), {$uid}, 1, 'Техническое обслуживание, ремонт автомобиля', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=1 AND cat_parent=0), {$uid}, 1, 'Бензин', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=1 AND cat_parent=0), {$uid}, 1, 'Мойка автомобиля', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=1 AND cat_parent=0), {$uid}, 1, 'Аренда автомобиля', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=2 AND cat_parent=0), {$uid}, 2, 'Комиссия банкомата', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=2 AND cat_parent=0), {$uid}, 2, 'Оплата услуг банка', -1, '{$date}', '{$date}'),

        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=3 AND cat_parent=0), {$uid}, 3, 'Хобби, спорт, увлечения детей', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=3 AND cat_parent=0), {$uid}, 3, 'Образование детей', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=3 AND cat_parent=0), {$uid}, 3, 'Оплата няни', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=3 AND cat_parent=0), {$uid}, 3, 'Игрушки', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=3 AND cat_parent=0), {$uid}, 3, 'Медицинские расходы на детей', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=3 AND cat_parent=0), {$uid}, 3, 'Детская одежда и обувь', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=3 AND cat_parent=0), {$uid}, 3, 'Детское питание и гигиена', -1, '{$date}', '{$date}'),

        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=4 AND cat_parent=0), {$uid}, 4, 'Хозяйственные товары и бытовая химия', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=4 AND cat_parent=0), {$uid}, 4, 'Электроника и бытовая техника', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=4 AND cat_parent=0), {$uid}, 4, 'Ремонт обуви', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=4 AND cat_parent=0), {$uid}, 4, 'Уборка дома', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=4 AND cat_parent=0), {$uid}, 4, 'Прочие бытовые услуги', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=4 AND cat_parent=0), {$uid}, 4, 'Ремонт недвижимости', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=4 AND cat_parent=0), {$uid}, 4, 'Покупка мебели и предметов интерьера', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=4 AND cat_parent=0), {$uid}, 4, 'Прачечная и химчистка', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=4 AND cat_parent=0), {$uid}, 4, 'Аренда жилья', -1, '{$date}', '{$date}'),

        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=5 AND cat_parent=0), {$uid}, 5, 'Прочие расходы на животных', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=5 AND cat_parent=0), {$uid}, 5, 'Ветеринар', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=5 AND cat_parent=0), {$uid}, 5, 'Корм', -1, '{$date}', '{$date}'),

        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=6 AND cat_parent=0), {$uid}, 6, 'Спортивные товары', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=6 AND cat_parent=0), {$uid}, 6, 'Фотография', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=6 AND cat_parent=0), {$uid}, 6, 'Рестораны и клубы', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=6 AND cat_parent=0), {$uid}, 6, 'Спортивные события', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=6 AND cat_parent=0), {$uid}, 6, 'Отпуск', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=6 AND cat_parent=0), {$uid}, 6, 'Развлечения', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=6 AND cat_parent=0), {$uid}, 6, 'Компакт-диски', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=6 AND cat_parent=0), {$uid}, 6, 'Культурные события', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=6 AND cat_parent=0), {$uid}, 6, 'Кино и прокат видео', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=6 AND cat_parent=0), {$uid}, 6, 'Книги, газеты и журналы', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=6 AND cat_parent=0), {$uid}, 6, 'Игрушки и игры', -1, '{$date}', '{$date}'),

        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=22 AND cat_parent=0), {$uid}, 22, 'Сверхурочное время', 1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=22 AND cat_parent=0), {$uid}, 22, 'Пенсия', 1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=22 AND cat_parent=0), {$uid}, 22, 'Зарплата без налогов', 1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=22 AND cat_parent=0), {$uid}, 22, 'Зарплата вкл. налоги', 1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=22 AND cat_parent=0), {$uid}, 22, 'Бонусы', 1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=22 AND cat_parent=0), {$uid}, 22, 'Доход предпринимателя', 1, '{$date}', '{$date}'),

        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=23 AND cat_parent=0), {$uid}, 23, 'Проценты не облагаемые налогом', 1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=23 AND cat_parent=0), {$uid}, 23, 'Проценты полученные', 1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=23 AND cat_parent=0), {$uid}, 23, 'Доход от аренды имущества', 1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=23 AND cat_parent=0), {$uid}, 23, 'Доходы от прироста капитала', 1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=23 AND cat_parent=0), {$uid}, 23, 'Дивиденды', 1, '{$date}', '{$date}'),

        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=7 AND cat_parent=0), {$uid}, 7, 'Электричество', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=7 AND cat_parent=0), {$uid}, 7, 'Консъержи и охрана', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=7 AND cat_parent=0), {$uid}, 7, 'Отопление', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=7 AND cat_parent=0), {$uid}, 7, 'Газ', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=7 AND cat_parent=0), {$uid}, 7, 'Квартплата', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=7 AND cat_parent=0), {$uid}, 7, 'Водоснабжение', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=7 AND cat_parent=0), {$uid}, 7, 'Вывоз мусора, канализация', -1, '{$date}', '{$date}'),

        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=8 AND cat_parent=0), {$uid}, 8, 'Терапевт и другие врачи', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=8 AND cat_parent=0), {$uid}, 8, 'Лекарства', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=8 AND cat_parent=0), {$uid}, 8, 'Стоматология', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=8 AND cat_parent=0), {$uid}, 8, 'Больница', -1, '{$date}', '{$date}'),

        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=9 AND cat_parent=0), {$uid}, 9, 'Членские взносы', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=9 AND cat_parent=0), {$uid}, 9, 'Подоходный налог - прошлого года', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=9 AND cat_parent=0), {$uid}, 9, 'Почтовые расходы и оформление документов', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=9 AND cat_parent=0), {$uid}, 9, 'Налог на имущество', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=9 AND cat_parent=0), {$uid}, 9, 'Подоходный налог', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=9 AND cat_parent=0), {$uid}, 9, 'Другие налоги и сборы', -1, '{$date}', '{$date}'),

        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=10 AND cat_parent=0), {$uid}, 10, 'Обучение', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=10 AND cat_parent=0), {$uid}, 10, 'Прочие образовательные расходы', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=10 AND cat_parent=0), {$uid}, 10, 'Книги и учебники', -1, '{$date}', '{$date}'),

        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=11 AND cat_parent=0), {$uid}, 11, 'Одежда', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=11 AND cat_parent=0), {$uid}, 11, 'Аксессуары', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=11 AND cat_parent=0), {$uid}, 11, 'Обувь', -1, '{$date}', '{$date}'),

        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=12 AND cat_parent=0), {$uid}, 12, 'Обеды вне дома', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=12 AND cat_parent=0), {$uid}, 12, 'Питание дома', -1, '{$date}', '{$date}'),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=12 AND cat_parent=0), {$uid}, 12, 'Алкоголь, табачные изделия', -1, '{$date}', '{$date}')";
        Core::getInstance()->db->query($sql);
    }


    /**
     * Создаёт дефолтные счета
     *
     * @return void
     */
    static public function defaultAccounts($uid)
    {
        $sql = "INSERT INTO accounts (`account_name`,`account_type_id`,`account_description`,`account_currency_id`,`user_id`, `created_at`, `updated_at`)
            VALUES('Кошелёк', 1, 'Мои наличные деньги', 1,".$uid.", NOW(), NOW())";
        $aid = Core::getInstance()->db->query($sql);
        $sql = "INSERT INTO `operation` (`user_id`, `money`, `date`, `cat_id`, `account_id`,
                `drain`, `comment`, `created_at`, `updated_at`) VALUES (?, 0, '0000-00-00', NULL, ?, ?, ?, NOW(), NOW())";
            Core::getInstance()->db->query($sql, $uid, $aid, 0, 'Начальный остаток');
    }


    /**
     * Отправить регистрационное письмо
     */
    private static function _sendRegistrationLetter()
    {
        $body = "<html><head><title>Успешная регистрация на сайте домашней бухгалтерии EasyFinance.ru</title></head>
        <p>Здравствуйте!</p>
        <p>Поздравляем вас с успешным завершением регистрации в системе. Теперь вы можете в любое время войти в систему, введя свой логин и пароль на сайте https://easyfinance.ru.</p>
        <p>Используйте наш сервис для контроля своей домашней бухгалтерии.</p>
        <p>Мы надеемся, что с помощью EasyFinance.ru Вам будет удобно планировать и контролировать ваш личный и семейный бюджет, принимать на основе объективной информации взвешенные решения, вносить коррективы в свой финансовый план.</p>
        <p>Отслеживайте динамику своих расходов и доходов, анализируйте рациональность личных и семейных трат с сайтом для ведения домашней бухгалтерии EasyFinance.ru.</p>
        <p>Контролируйте состояние своих финансов и семейный бюджет круглосуточно из любой точки мира – с рабочего места, из кафе, даже из машины. Все, что для этого нужно, – доступ в Интернет и компьютер.</p>
        <p>Кроме личного бюджета с помощью EasyFinance.ru Вы можете контролировать состояние финансов Вашего малого бизнеса. </p>
        <p>Пожалуйста, ознакомьтесь с несколькими рекомендациями:</p>
        <ul>
            <li>Запомните ваш пароль или сохраните его в надежном месте. В случае, если вы забудете ваш пароль, письмо о его восстановлении придет на этот адрес.</li>
            <li>Если вам понадобится дополнительная информация о работе с системой, воспользуйтесь разделом меню Инструкции.</li>
            <li>Если у вас возникнут какие-то сложности в работе с системой, обратитесь за помощью в службу поддержки support@easyfinance.ru.</li>
        </ul>
        <p>C уважением,<br/>Администрация системы EasyFinance.ru</p>
        <p>Email: <a href='mailto:info@easyfinance.ru'>info@easyfinance.ru</a><br/>
        <a href='http://www.easyfinance.ru'>www.easyfinance.ru</a>";

         $message = Swift_Message::newInstance()
            // Заголовок
            ->setSubject('Успешная регистрация на сайте домашней бухгалтерии EasyFinance.ru')
            // Указываем "От кого"
            ->setFrom(array('support@easyfinance.ru' => 'EasyFinance.ru'))
            // Говорим "Кому"
            ->setTo(array($_SESSION['user']['user_mail']=>$_SESSION['user']['user_name']))
            // Устанавливаем "Тело"
            ->setBody($body, 'text/html');
        // Отсылаем письмо
        $result = Core::getInstance()->mailer->send($message);
    }


    /**
     * Устанавливает куки на авторизацию
     * @param string $login
     * @param string $password
     * @param bool $remember
     * @param oldUser $user
     * @return void
     */
    public function login($login, $password, $remember = false, oldUser $user = null)
    {
        $encpass = encrypt(array($login, $password));

        if (!$user) {
            $user = Core::getInstance()->user;
        }

        // Шифруем и сохраняем куки
        if($remember) {

            setcookie(COOKIE_NAME, $encpass, time() + COOKIE_EXPIRE, COOKIE_PATH, COOKIE_DOMEN, COOKIE_HTTPS);

        // Шифруем, но куки теперь сохраняются лишь до конца сессии
        } else {

            setcookie(COOKIE_NAME, $encpass, 0, COOKIE_PATH, COOKIE_DOMEN, COOKIE_HTTPS);

        }

        if(sizeof($user->getUserCategory()) == 0) {
            setcookie('guide', 'uyjsdhf', 0, COOKIE_PATH, COOKIE_DOMEN, false);
        }

        // У пользователя нет категорий, т.е. надо помочь ему их создать
        if (sizeof($user->getUserCategory()) == 0 && $user->getType() == 0) {
            self::defaultCategory($user->getId());
            self::defaultAccounts($user->getId());
            Core::getInstance()->user->initUserCategory();
            Core::getInstance()->user->save();
            self::_sendRegistrationLetter();
        }

        // Устанавливаем дефолтный путь
        if(!isset($_SESSION['REQUEST_URI'])) {
            $_SESSION['REQUEST_URI'] = '/info/';
        }

    }


    function authDemoUser()
    {
        $user = Core::getInstance()->user;

        $auth = $this->getGenerated();

        if ( $user->initUser($auth['login'], $auth['pass'] ) )
        {
            setcookie(COOKIE_NAME, encrypt(array($auth['login'], $auth['pass'])), 0, COOKIE_PATH, COOKIE_DOMEN, COOKIE_HTTPS);
            session_commit();

            $keys = array_keys($user->getUserAccounts());
            header("Location: " . '/operation/#account=' . $keys[0]);
            exit;
        }
    }

    function getGenerated()
    {
        $usersFile = DIR_SHARED . 'generatedUsers.php';

        if( file_exists($usersFile) )
        {
            @include( $usersFile );
        }

        if( !isset($users) || !sizeof($users) )
        {
            die('К сожалению, демонстрационные аккаунты закончились. Ожидаем поставки в ближайшее время.');
        }

        $user = array(
            'login'     => key($users),
            'pass'    => array_shift( $users )
        );

        file_put_contents(  $usersFile, '<?php $users = ' . var_export($users,true) . ';');

        return $user;
    }
        /**
         * возвращает данные по юзеру по его айди
         * @param integer $id
         * @return array
         */
        public static function getUserDataByID($id) {
            $db = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
            return $db->selectRow("SELECT user_login, user_pass, user_mail FROM users WHERE id = ?", $id);
        }

        /**
         * По присланному логину в азбуке финансов генерируем нового пользователя состоящего из
         * логина и префикса azbuka_
         * возвращаем айди сгенерированного пользователя.
         * @param string $login
         * @return integer
         */
        public static function generateUserByAzbukaLogin($login , $mail){

            $id = 0; //айди сгенерированного пользователя

            if ( empty( $login ) ) {
                die('Ошибка!!! Пустой логин');
            }

            if ( empty( $mail) ) {
                die('Ошибка!!! Нет почты');
            }

            $pass = sha1($login);

            $db = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);

            $islog = $db->selectRow("SELECT count(*) as `count` FROM users WHERE user_login=?", 'azbuka_'.$login);

            if ( $islog['count'] == 0 ) {

                $db->query("INSERT into users (user_name , user_login, user_pass, user_mail,
                    user_active, user_new, user_created) VALUES (?, ?, ?, ?, 1, 0, NOW())",
                        $login, 'azbuka_'.$login, $pass, $mail);

                $id = mysql_insert_id();

                if ( $id ){

                    self::defaultCategory($id);
                    self::defaultAccounts($id);

                     //   http://www.azbukafinansov.ru/ef/set_ef_id.php?ef_id=IDвВашейСистеме&af_login=ЛогинКоторыйЯПередал
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, "http://www.azbukafinansov.ru/ef/set_ef_id.php?ef_id=".$id."&af_login=".$login);

                    curl_exec($ch);

                    curl_close($ch);//*/
                } else{
                    die("Пользователь не добавлен");
                }
            }

            return $id;
        }
}
