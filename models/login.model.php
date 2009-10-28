<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс модели для логина
 * @category login
 * @copyright http://home-money.ru/
 * @version SVN $Id$
 */
class Login_Model
{

    /**
     * Создаёт категории по умолчанию
     * @return void
     */
    function defaultCategory() {
        $uid = Core::getInstance()->user->getId();
        $sql = "INSERT INTO category (`cat_parent`, `user_id`, `system_category_id`, `cat_name`, `type`, `dt_create`) VALUES
        (0, {$uid}, 1,  'Автомобиль', -1, NOW()),
        (0, {$uid}, 2,  'Банковское обслуживание', -1, NOW()),
        (0, {$uid}, 3,  'Дети', -1, NOW()),
        (0, {$uid}, 4,  'Домашнее хозяйство', -1, NOW()),
        (0, {$uid}, 5,  'Домашние животные', -1, NOW()),
        (0, {$uid}, 6,  'Досуг и отдых', -1, NOW()),
        (0, {$uid}, 22, 'Зарплата и персональные доходы', 1, NOW()),
        (0, {$uid}, 23, 'Инвестиционный доход', 1, NOW()),
        (0, {$uid}, 7,  'Коммунальные платежи', -1, NOW()),
        (0, {$uid}, 8,  'Медицина', -1, NOW()),
        (0, {$uid}, 9,  'Налоги, сборы и взносы', -1, NOW()),
        (0, {$uid}, 10, 'Образование', -1, NOW()),
        (0, {$uid}, 11, 'Одежда, обувь, аксессуары', -1, NOW()),
        (0, {$uid}, 12, 'Питание', -1, NOW()),
        (0, {$uid}, 13, 'Подарки, помощь родственникам, благотворительность', -1, NOW()),
        (0, {$uid}, 14, 'Проезд, транспорт', -1, NOW()),
        (0, {$uid}, 15, 'Проценты по кредитам и займам', -1, NOW()),
        (0, {$uid}, 16, 'Прочие доходы', 1, NOW()),
        (0, {$uid}, 17, 'Прочие личные расходы', -1, NOW()),
        (0, {$uid}, 18, 'Расходы по работе', -1, NOW()),
        (0, {$uid}, 19, 'Связь, ТВ и интернет', -1, NOW()),
        (0, {$uid}, 20, 'Страхование', -1, NOW()),
        (0, {$uid}, 21, 'Уход за собой', -1, NOW())";
        Core::getInstance()->db->query($sql);

        $sql = "INSERT INTO category (`cat_parent`, `user_id`, `system_category_id`, `cat_name`, `type`, `dt_create`) VALUES
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=1 AND cat_parent=0), {$uid}, 1, 'Стоянка', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=1 AND cat_parent=0), {$uid}, 1, 'Платные дороги, штрафы', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=1 AND cat_parent=0), {$uid}, 1, 'Техническое обслуживание, ремонт автомобиля', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=1 AND cat_parent=0), {$uid}, 1, 'Бензин', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=1 AND cat_parent=0), {$uid}, 1, 'Мойка автомобиля', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=1 AND cat_parent=0), {$uid}, 1, 'Аренда автомобиля', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=2 AND cat_parent=0), {$uid}, 2, 'Комиссия банкомата', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=2 AND cat_parent=0), {$uid}, 2, 'Оплата услуг банка', -1, NOW()),

        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=3 AND cat_parent=0), {$uid}, 3, 'Хобби, спорт, увлечения детей', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=3 AND cat_parent=0), {$uid}, 3, 'Образование детей', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=3 AND cat_parent=0), {$uid}, 3, 'Оплата няни', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=3 AND cat_parent=0), {$uid}, 3, 'Игрушки', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=3 AND cat_parent=0), {$uid}, 3, 'Медицинские расходы на детей', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=3 AND cat_parent=0), {$uid}, 3, 'Детская одежда и обувь', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=3 AND cat_parent=0), {$uid}, 3, 'Детское питание и гигиена', -1, NOW()),

        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=4 AND cat_parent=0), {$uid}, 4, 'Хозяйственные товары и бытовая химия', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=4 AND cat_parent=0), {$uid}, 4, 'Электроника и бытовая техника', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=4 AND cat_parent=0), {$uid}, 4, 'Ремонт обуви', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=4 AND cat_parent=0), {$uid}, 4, 'Уборка дома', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=4 AND cat_parent=0), {$uid}, 4, 'Прочие бытовые услуги', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=4 AND cat_parent=0), {$uid}, 4, 'Ремонт недвижимости', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=4 AND cat_parent=0), {$uid}, 4, 'Покупка мебели и предметов интерьера', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=4 AND cat_parent=0), {$uid}, 4, 'Прачечная и химчистка', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=4 AND cat_parent=0), {$uid}, 4, 'Аренда жилья', -1, NOW()),

        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=5 AND cat_parent=0), {$uid}, 5, 'Прочие расходы на животных', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=5 AND cat_parent=0), {$uid}, 5, 'Ветеринар', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=5 AND cat_parent=0), {$uid}, 5, 'Корм', -1, NOW()),

        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=6 AND cat_parent=0), {$uid}, 6, 'Спортивные товары', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=6 AND cat_parent=0), {$uid}, 6, 'Фотография', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=6 AND cat_parent=0), {$uid}, 6, 'Рестораны и клубы', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=6 AND cat_parent=0), {$uid}, 6, 'Спортивные события', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=6 AND cat_parent=0), {$uid}, 6, 'Отпуск', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=6 AND cat_parent=0), {$uid}, 6, 'Развлечения', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=6 AND cat_parent=0), {$uid}, 6, 'Компакт-диски', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=6 AND cat_parent=0), {$uid}, 6, 'Культурные события', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=6 AND cat_parent=0), {$uid}, 6, 'Кино и прокат видео', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=6 AND cat_parent=0), {$uid}, 6, 'Книги, газеты и журналы', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=6 AND cat_parent=0), {$uid}, 6, 'Игрушки и игры', -1, NOW()),

        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=22 AND cat_parent=0), {$uid}, 22, 'Сверхурочное время', 1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=22 AND cat_parent=0), {$uid}, 22, 'Пенсия', 1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=22 AND cat_parent=0), {$uid}, 22, 'Зарплата без налогов', 1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=22 AND cat_parent=0), {$uid}, 22, 'Зарплата вкл. налоги', 1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=22 AND cat_parent=0), {$uid}, 22, 'Бонусы', 1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=22 AND cat_parent=0), {$uid}, 22, 'Доход предпринимателя', 1, NOW()),

        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=23 AND cat_parent=0), {$uid}, 23, 'Проценты не облагаемые налогом', 1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=23 AND cat_parent=0), {$uid}, 23, 'Проценты полученные', 1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=23 AND cat_parent=0), {$uid}, 23, 'Доход от аренды имущества', 1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=23 AND cat_parent=0), {$uid}, 23, 'Доходы от прироста капитала', 1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=23 AND cat_parent=0), {$uid}, 23, 'Дивиденды', 1, NOW()),

        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=7 AND cat_parent=0), {$uid}, 7, 'Электричество', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=7 AND cat_parent=0), {$uid}, 7, 'Консъержи и охрана', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=7 AND cat_parent=0), {$uid}, 7, 'Отопление', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=7 AND cat_parent=0), {$uid}, 7, 'Газ', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=7 AND cat_parent=0), {$uid}, 7, 'Квартплата', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=7 AND cat_parent=0), {$uid}, 7, 'Водоснабжение', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=7 AND cat_parent=0), {$uid}, 7, 'Вывоз мусора, канализация', -1, NOW()),

        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=8 AND cat_parent=0), {$uid}, 8, 'Терапевт и другие врачи', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=8 AND cat_parent=0), {$uid}, 8, 'Лекарства', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=8 AND cat_parent=0), {$uid}, 8, 'Стоматология', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=8 AND cat_parent=0), {$uid}, 8, 'Больница', -1, NOW()),

        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=9 AND cat_parent=0), {$uid}, 9, 'Членские взносы', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=9 AND cat_parent=0), {$uid}, 9, 'Подоходный налог - прошлого года', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=9 AND cat_parent=0), {$uid}, 9, 'Почтовые расходы и оформление документов', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=9 AND cat_parent=0), {$uid}, 9, 'Налог на имущество', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=9 AND cat_parent=0), {$uid}, 9, 'Подоходный налог', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=9 AND cat_parent=0), {$uid}, 9, 'Другие налоги и сборы', -1, NOW()),

        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=10 AND cat_parent=0), {$uid}, 10, 'Обучение', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=10 AND cat_parent=0), {$uid}, 10, 'Прочие образовательные расходы', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=10 AND cat_parent=0), {$uid}, 10, 'Книги и учебники', -1, NOW()),

        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=11 AND cat_parent=0), {$uid}, 11, 'Одежда', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=11 AND cat_parent=0), {$uid}, 11, 'Аксессуары', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=11 AND cat_parent=0), {$uid}, 11, 'Обувь', -1, NOW()),

        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=12 AND cat_parent=0), {$uid}, 12, 'Обеды вне дома', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=12 AND cat_parent=0), {$uid}, 12, 'Питание дома', -1, NOW()),
        ((SELECT c.cat_id FROM category c WHERE c.user_id={$uid} AND c.system_category_id=12 AND cat_parent=0), {$uid}, 12, 'Алкоголь, табачные изделия', -1, NOW())";
        Core::getInstance()->db->query($sql);
    }

    /**
     * Создаёт дефолтные счета
     * @return void
     */
    function defaultAccounts() {
        // Добавляем счёт по умолчанию
        $sql = "INSERT INTO accounts (`account_name`,`account_type_id`,`account_description`,`account_currency_id`,`user_id`)
            VALUES('Кошелёк', 1, 'Мои наличные деньги', 1,{$uid})";
        $aid = Core::getInstance()->db->query($sql);
        $sql = "INSERT INTO account_field_values (`field_value_id`, `account_fieldsaccount_field_id`, `string_value`, `accountsaccount_id`) VALUES
            (NULL,'{$aid}','Кошелёк','67'), (NULL,'{$aid}','Мои наличные деньги','68'), (NULL,'{$aid}','','69');";
        Core::getInstance()->db->query($sql);
    }

    /**
     * Активирует нового пользователя
     * @return void
     */
    function activate_user() {
        $user = Core::getInstance()->user;
        // Создаём категории по умолчанию, и высылаем письмо пользователю при отсутствии категорий
        if (count($user->getUserCategory()) == 0) {
            $this->defaultCategory();
            $this->defaultAccounts();
            Core::getInstance()->user->initUserCategory();
            Core::getInstance()->user->save();
            $message = "<html><head><title>Успешная регистрация на сайте домашней бухгалтерии EasyFinance.ru</title></head>
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

            $headers = "Content-type: text/html; charset=utf-8\n";
            $headers .= "From: info@easyfinance.ru\n";
            $subject = "Успешная регистрация на сайте домашней бухгалтерии EasyFinance.ru";
            mail($_SESSION['user']['user_mail'], $subject, $message, $headers);
            header("Location: /info/");
            exit;
        } else {

        }
    }
	
	/**
	 * Пользователь авторизируется через диалог ввода логина и пароля
	 */
	function auth_user()
	{
		$user = Core::getInstance()->user;
		
		if (!empty($_POST['login']) && !empty($_POST['pass']) )
		{
			$login = htmlspecialchars($_POST['login']);
			$pass = sha1($_POST['pass']);
			
            		if ($user->initUser($login,$pass))
            		{
				// Шифруем и сохраняем куки
				if (isset($_POST['autoLogin']))
				{
					setcookie(COOKIE_NAME, encrypt(array($login,$pass)), time() + COOKIE_EXPIRE, COOKIE_PATH, COOKIE_DOMEN, COOKIE_HTTPS);
					// Шифруем, но куки теперь сохраняются лишь до конца сессии
				}
				else
				{
					setcookie(COOKIE_NAME, encrypt(array($login,$pass)), 0, COOKIE_PATH, COOKIE_DOMEN, COOKIE_HTTPS);
				}
				
				$sql = "SELECT count(*) AS cou FROM `accounts` WHERE user_id = ?";
				
				$this->db = Core::getInstance()->db;
				$a = $this->db->query($sql , Core::getInstance()->user->getId());
				
				if ($a[0]['cou'] == 0)
				{
					setcookie('guide', 'uyjsdhf', 0, COOKIE_PATH, COOKIE_DOMEN, false);
                			}
                			
				// У пользователя нет категорий, т.е. надо помочь ему их создать
				if (count($user->getUserCategory()) == 0)
				{
					$model = new Login_Model();
					$model->activate_user();
				}
				else
				{
					if (isset($_SESSION['REQUEST_URI']))
					{
						header("Location: ".$_SESSION['REQUEST_URI']);
						unset($_SESSION['REQUEST_URI']);
						exit;
					}
					else
					{
						header("Location: /info/");
						exit;
					}
				}
			}
		}

                if (IS_DEMO)
                    setCookie("guide", "uyjsdhf",0,COOKIE_PATH, COOKIE_DOMEN, false);

		if( IS_DEMO && !Core::getInstance()->user->getId() )
		{
                    $this->authDemoUser();
		}
	}
	
	private function authDemoUser()
	{
		$user = Core::getInstance()->user;
		
		$auth = $this->getGenerated();
		
		if ( $user->initUser($auth['login'], $auth['pass'] ) )
		{
			setcookie(COOKIE_NAME, encrypt(array($auth['login'], $auth['pass'])), 0, COOKIE_PATH, COOKIE_DOMEN, COOKIE_HTTPS);
			session_commit();
			header("Location: /info");
			exit;
		}
	}
	
	private function getGenerated()
	{
		$usersFile = SYS_DIR_INC . 'generatedUsers.php';
		
		if( file_exists($usersFile) )
		{
			@include( $usersFile );
		}
		
		if( !isset($users) || !sizeof($users) )
		{
			die('К сожалению, демонстрационные аккаунты закончились. Ожидаем поставки в ближайшее время.');
		}
		
		$user = array(
			'login' 	=> key($users),
			'pass'	=> array_shift( $users )
		);
		
		file_put_contents(  $usersFile, '<?php $users = ' . var_export($users,true) . ';');
		
		return $user;
	}
}