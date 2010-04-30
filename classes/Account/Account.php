<?php
abstract class Account
{
    /**
     * Начальный баланс
     */
    const FIELD_AMOUNT = 3;

    /**
     * Текущая рыночная стоимость
     */
    const FIELD_CURRENT_MARKET_COST = 6;

    /**
     * Банк
     */
    const FIELD_BANK = 8;

    /**
     * Займополучатель
     */
    const FIELD_LOAN_RECEIVER = 9;

    /**
     * Заимодавец
     */
    const FIELD_LOAN_GIVER  = 10;

    /**
     * Процент годовых
     */
    const FIELD_YEAR_PERCENT = 11;

    /**
     * Доходность % годовых
     */
    const FIELD_INCOME_YEAR_PERCENT = 12;

    /**
     * Дата выдачи
     */
    const FIELD_DATE_GIVE = 13;

    /**
     * Дата возврата
     */
    const FIELD_DATE_RETURN = 14;

    /**
     * Дата открытия
     */
    const FIELD_DATE_OPEN = 15;

    /**
     * Дата закрытия
     */
    const FIELD_DATE_CLOSE = 16;

    /**
     * Дата получения
     */
    const FIELD_DATE_GET = 17;

    /**
     * Дата погашения
     */
    const FIELD_DATE_OFF = 18;

    /**
     * Кредитный лимит
     */
    const FIELD_CREDIT_LIMIT = 9;

    /**
     * Свободный остаток
     */
    const FIELD_REMAIN_AMOUNT = 20;

    /**
     * Грейс-период
     */
    const FIELD_GRAISE_PERIOD = 21;

    /**
     * Тип карты / платёжная система
     */
    const FIELD_PAY_SYSTEM = 22;

    /**
     * Срок действия
     */
    const FIELD_VALIDITY_PERIOD = 23;

    /**
     * тип платежа
     */
    const FIELD_TYPE_PAYMENT = 24;

    /**
     * Обеспечение
     */
    const FIELD_SUPPORT = 25;

    /**
     * Тип металла
     */
    const FIELD_TYPE_METALL = 26;

    /**
     * УК
     */
    const FIELD_UK = 27;

    /**
     * Тип имущества
     */
    const FIELD_TYPE_PROPERTY = 28;

    /**
     * Привязка к номеру счёта в банке
     */
    const FIELD_BINDING = 29;

    /**
     * Название счёта
     * @var string
     */
    protected $name = '';

    /**
     * Зарезервированная сумма
     * @var float
     */
    protected $reserve = 0;

    /**
     * Комментарий к счёту
     * @var string
     */
    protected $comment = '';

    /**
     * Модель счёта
     * @var Account_Model
     */
    protected $model = null;

    /**
     * Ид, счёта
     * @var int
     */
    protected $id = 0;

    /**
     * Возвращает id счёта, если есть. В ином случае 0
     */
    public function getId()
    {
        return $this->id;
    }

    public function setReserve($reserve)
    {
        $this->reserve = $reserve;
    }

    /**
     * Возвращает зарезервированную сумму
     * @return float
     */
    public function getReserve()
    {
        return $this->reserve;
    }

    /**
     * Возвращает имя счёта
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Возвращает комментарий
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }


    public function getTypeByID($args)
    {
        $id = $args['id'];
        $tip = new Account_Model();
        $tip = $tip->getTypeByID($id);
        $typearray = array(
            Account_Collection::ACCOUNT_TYPE_CASH               => 'Account_Cash',//
            Account_Collection::ACCOUNT_TYPE_DEBETCARD          => 'Account_DebetCard',//
            Account_Collection::ACCOUNT_TYPE_DEPOSIT            => 'Account_Deposit',//
            Account_Collection::ACCOUNT_TYPE_LOANGIVE           => 'Account_LoanGive',//
            Account_Collection::ACCOUNT_TYPE_LOANGET            => 'Account_LoanGet',//
            Account_Collection::ACCOUNT_TYPE_CREDITCARD         => 'Account_CreditCard',
            Account_Collection::ACCOUNT_TYPE_CREDIT             => 'Account_Credit',//
            Account_Collection::ACCOUNT_TYPE_METALLIC           => 'Account_Metallic',//
            Account_Collection::ACCOUNT_TYPE_SHARE              => 'Account_Share',//
            Account_Collection::ACCOUNT_TYPE_PIF                => 'Account_PIF',//
            Account_Collection::ACCOUNT_TYPE_OFBU               => 'Account_OFBU',//
            Account_Collection::ACCOUNT_TYPE_PROPERTY           => 'Account_Property',//
            Account_Collection::ACCOUNT_TYPE_ELECTPURSE         => 'Account_ElectPurse',//
            Account_Collection::ACCOUNT_TYPE_BANKACC            => 'Account_BankAcc'
        );
        $acc = new $typearray[$tip];
        return $acc;
    }
    /**
     * Фабричный метод, возвращающий наследуемый класс, например, Account_Cash
     * @param array $params
     * @return Account
     */
    public function load($params)
    {
        $typearray = array(
            Account_Collection::ACCOUNT_TYPE_CASH               => 'Account_Cash',//
            Account_Collection::ACCOUNT_TYPE_DEBETCARD          => 'Account_DebetCard',//
            Account_Collection::ACCOUNT_TYPE_DEPOSIT            => 'Account_Deposit',//
            Account_Collection::ACCOUNT_TYPE_LOANGIVE           => 'Account_LoanGive',//
            Account_Collection::ACCOUNT_TYPE_LOANGET            => 'Account_LoanGet',//
            Account_Collection::ACCOUNT_TYPE_CREDITCARD         => 'Account_CreditCard',
            Account_Collection::ACCOUNT_TYPE_CREDIT             => 'Account_Credit',//
            Account_Collection::ACCOUNT_TYPE_METALLIC           => 'Account_Metallic',//
            Account_Collection::ACCOUNT_TYPE_SHARE              => 'Account_Share',//
            Account_Collection::ACCOUNT_TYPE_PIF                => 'Account_PIF',//
            Account_Collection::ACCOUNT_TYPE_OFBU               => 'Account_OFBU',//
            Account_Collection::ACCOUNT_TYPE_PROPERTY           => 'Account_Property',//
            Account_Collection::ACCOUNT_TYPE_ELECTPURSE         => 'Account_ElectPurse',//
            Account_Collection::ACCOUNT_TYPE_BANKACC            => 'Account_BankAcc'
        );

        return new $typearray[$params['type']];
    }
    /**
     * Абстрактный метод создания счёта, переопределяется в дочерних классах
     * @param User $user
     * @param array $param
     */
    abstract function create( $user, $param );

    /**
     * Абстрактный метод редактирования счёта, переопределяется в дочерних классах
     * @param User $user
     * @param array $param
     */
    abstract function update ( $user, $param);

    /**
     * Удаляет счёт из обеих используемых табличек.
     * @param User $user
     * @param array $param
     */
    public function delete( $user, $param)
    {
        $this->model = new Account_Model();
        $fin = new Targets_Model();
        $noFinTarget = $fin->countTargetsOnAccount($param['id']);// если ноль значит удаляем
        if ($noFinTarget > 0)
            return 'cel';

            $this->model->delete($param);
            unset($this->model);
        
        return $this;
    }
}
