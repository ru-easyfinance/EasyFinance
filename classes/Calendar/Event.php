<?php
/**
 * Событие календаря
 *
 * @author ukko
 */
class Calendar_Event
{

    /**
     * Модель календаря
     * @var Calendar_Model
     */
    protected $_model = null;

    /**
     * Массив с ошибками
     * @var array
     */
    private $_errors = array();

    /**
     * Конструктор
     * @param Calendar_Model $model
     * @param User $user
     */
    public function __construct(Calendar_Model $model, User $user)
    {
        $this->_model = $model;
    }

    /**
     * Магический метод
     * @param string $name
     * @param array $arguments
     */
//    public function  __call ( $name, $arguments )
//    {
//        if ( substr( $name, 0, 3 ) === 'get' ) {
//            return $this->substr( $name, 2 );
//        } elseif ( substr( $name, 0, 3 ) === 'set' ) {
//            return $this->substr( $name, 2 ) = $arguments[0];
//        }
//    }

    /**
     * ID события или регулярной транзакции
     * @return int
     */
    public function getId ()
    {
        return $this->_model->id;
    }

    /**
     * ID цепочки
     * @return int
     */
    public function getChain ()
    {
        return $this->_model->chain;
    }

    /**
     * Возвращает дату события
     * @return unixtimestamp date
     */
    public function getDate ()
    {
        return $this->_model->date;
    }

    /**
     * Дата начала события
     */
    public function getStart ()
    {
        return $this->_model->start;
    }

    /**
     * Комментарий
     */
    public function getComment ()
    {
        return $this->_model->comment;
    }


    /**
     * Подтверждено ли событие
     * @return bool
     */
    public function getAccepted ()
    {
        return $this->_model->accepted;
    }

    /**
     * Возвращает частоту повторения события
     * Опционально, по-умолчанию 0
     * 0   - без повторения
     * 1   - каждый день
     * 7   - каждую неделю
     * 30  - месяц
     * 90  - квартал
     * 365 -год
     * @return int
     */
    public function getEvery ()
    {
        return $this->_model->every;
    }

    /**
     * Возвращает количество повторений
     * Опционально, по-умолчанию 1 (т.е. повторять 1 раз) от 1 до 365 (год)
     * @return int
     */
    public function getRepeat ()
    {
        return $this->_model->repeat;
    }

    /**
     * Возвращает двоичную маску повторений по дням недели
     * Двоичная маска (0000011 - выходные, 1111100 - будни)
     * Опционально, первое число - понедельник
     * @return str
     */
    public function getWeek ()
    {
        return $this->_model->week;
    }

    /**
     * Возвращает последнюю дату (если есть)
     * @return int unixtimestamp
     */
    public function getLast ()
    {
        return $this->_model->last;
    }
    
    /**
     * 
     */
    public function getConvert ()
    {
       return $this->_model->convert;
    }

    /**
     *
     */
    public function getClose ()
    {
       return $this->_model->close;
    }

    /**
     *
     * @return <type>
     */
    public function getCurrency ()
    {
        return $this->_model->currency;
    }

    /**
     *
     * @return <type>
     */
    public function getToAccount ()
    {
        return $this->_model->toAccount;
    }

    /**
     *
     * @return <type> 
     */
    public function getTarget ()
    {
        return $this->_model->target;
    }

    public function getTime ()
    {
        return $this->_model->time;
    }

    /**
     * Возвращает сумму операции
     * @return float
     */
    public function getAmount ()
    {
        return $this->_model->amount;
    }

    /**
     * Возвращает ид категории
     * @return int
     */
    public function getCategory ()
    {
        return $this->_model->category;
    }

    /**
     * Возвращает ид счёта
     * @return int
     */
    public function getAccount ()
    {
        return $this->_model->account;
    }

    /**
     * Возвращает теги
     *
     * @return array
     */
    public function getTags ()
    {
        return $this->_model->tags;
    }

    /**
     * Возвращает тип рег. операции (расход, доход, перевод со счёта, перевод на финцель)
     * @return int
     */
    public function getType()
    {
        return $this->_model->type;
    }

    /**
     * Возвращает объект в виде массива
     */
    public function __getArray()
    {
        return array(
            // Общие данные
            'type'       => $this->getType(),
            'id'         => $this->getId(),
            'chain'      => $this->getChain(),
            'comment'    => $this->getComment(),
            'accepted'   => $this->getAccepted(),

            // Дата и время
            'date'       => $this->getDate(),
            'start'      => $this->getStart(),
            'last'       => $this->getLast(),
            'time'       => $this->getTime(),
            'timestamp'  => ( strlen($this->getDate()) == 10 ) ?
                strtotime($this->getDate() . $this->getTime()) :
                strtotime($this->getDate()),

            // Повторение
            'every'      => $this->getEvery(),
            'repeat'     => $this->getRepeat(),
            'week'       => $this->getWeek(),

            // Операция
            'money'      => $this->getAmount(),
            'cat_id'     => $this->getCategory(),
            'account_id' => $this->getAccount(),
            'tags'       => $this->getTags(),
            'tr_id'      => $this->_model->tr_id,
            'transfer'   => $this->_model->transfer,
            'source'     => $this->_model->source,
        );

    }

    /**
     * Возвращает массив с ошибками (если есть)
     * @return array
     */
    public function getErrors ()
    {
        return $this->_errors;
    }

    /**
     * Устанавливает начало
     * @param MYSQL DATE $date
     */
    public function setStart($date)
    {
        $this->_model->start = $date;
    }

    /**
     * Устанавливает начало
     * @param MYSQL DATETIME $date
     */
    public function setDate($date)
    {
        $this->_model->date = $date;
    }

    /**
     * Проверяем модель на ошибки
     * Если есть ошибки, то их можно получить так $event->getErrors();
     * @return bool
     */
    public function checkData()
    {
        // Преобразовываем данные к нужному формату

        if (!in_array($this->_model->every, array(0, 1, 7, 30, 90, 365))) {
            $this->_model->every = 0;
        }

        if ($this->_model->repeat > Calendar::MAX_EVENTS) {
            $this->_model->repeat = Calendar::MAX_EVENTS;
        }

        if ($this->getLast() != 0 && (strtotime($this->getLast()) < strtotime($this->getDate()))) {
            $this->_errors['date']  = 'Конечная дата не может быть меньше даты начала';
        }

        if ((int)$this->_model->date === 0) {
            $this->_errors['date']  = 'Необходимо указать дату';
        }

        if ((int)$this->_model->accepted === 1 && (int)$this->_model->account === 0) {
            $this->_errors['account']  = 'Необходимо указать счёт';
        }

        if ((int)$this->_model->accepted === 1 && (int)$this->_model->amount == 0) {
            $this->_errors['account']  = 'Необходимо указать сумму';
        }

        // Перевод со счёта на счёт
        if ((int)$this->_model->type === 2) {

            if ((int)$this->_model->accepted === 1  && (int)$this->_model->toAccount === 0) {
                $this->_errors['toAccount']  = 'Необходимо указать счёт куда нужно перевести';
            }

        // Перевод на фин.цель
        } elseif ($this->_model->type === 4) {

            if ((int)$this->_model->accepted === 1 && (int)$this->_model->target === 0) {
                $this->_errors['target']  = 'Необходимо указать счёт финансовой цели';
            }

        // Расход или доход
        } else {

            if ((int)$this->_model->accepted === 1 && (int)$this->_model->category === 0) {
                 $this->_errors['category']  = 'Необходимо указать категорию';
            }

        }

        // Проверяем на ошибки
        if (count($this->_errors) != 0) {
            
            return false;

        } else {

            return true;

        }
    }
}
