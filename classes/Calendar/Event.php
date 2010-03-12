<?php
/**
 * Событие календаря
 *
 * @author ukko
 */
class Calendar_Event {

    /**
     * Модель календаря
     * @var Calendar_Model
     */
    protected $model = null;

    /**
     * Массив с ошибками
     * @var array
     */
    private $errors = array();

    /**
     * Конструктор
     * @param Calendar_Model $model
     * @param User $user
     */
    public function __construct( Calendar_Model $model, User $user )
    {
        $this->model = $model;
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
        return $this->model->id;
    }

    /**
     * ID цепочки
     * @return int
     */
    public function getChain ()
    {
        return $this->model->chain;
    }

    /**
     * Возвращает дату события
     * @return unixtimestamp date
     */
    public function getDate ()
    {
        return $this->model->date;
    }

    /**
     * Дата начала события
     */
    public function getStart ()
    {
        return $this->model->start;
    }

    /**
     * Комментарий
     */
    public function getComment ()
    {
        return $this->model->comment;
    }


    /**
     * Подтверждено ли событие
     * @return bool
     */
    public function getAccepted ()
    {
        return $this->model->accepted;
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
        return $this->model->every;
    }

    /**
     * Возвращает количество повторений
     * Опционально, по-умолчанию 1 (т.е. повторять 1 раз) от 1 до 365 (год)
     * @return int
     */
    public function getRepeat ()
    {
        return $this->model->repeat;
    }

    /**
     * Возвращает двоичную маску повторений по дням недели
     * Двоичная маска (0000011 - выходные, 1111100 - будни)
     * Опционально, первое число - понедельник
     * @return str
     */
    public function getWeek ()
    {
        return $this->model->week;
    }

    /**
     * Возвращает последнюю дату (если есть)
     * @return int unixtimestamp
     */
    public function getLast ()
    {
        return $this->model->last;
    }
    
    /**
     * 
     */
    public function getConvert ()
    {
       return $this->model->convert;
    }

    /**
     *
     */
    public function getClose ()
    {
       return $this->model->close;
    }

    /**
     *
     * @return <type>
     */
    public function getCurrency ()
    {
        return $this->model->currency;
    }

    /**
     *
     * @return <type>
     */
    public function getToAccount ()
    {
        return $this->model->toAccount;
    }

    /**
     *
     * @return <type> 
     */
    public function getTarget ()
    {
        return $this->model->target;
    }

    public function getTime ()
    {
        return $this->model->time;
    }

    /**
     * Возвращает сумму операции
     * @return float
     */
    public function getAmount ()
    {
        return $this->model->amount;
    }

    /**
     * Возвращает ид категории
     * @return int
     */
    public function getCategory ()
    {
        return $this->model->category;
    }

    /**
     * Возвращает ид счёта
     * @return int
     */
    public function getAccount ()
    {
        return $this->model->account;
    }

    /**
     * Возвращает теги
     *
     * @return array
     */
    public function getTags ()
    {
        return $this->model->tags;
    }

    /**
     * Возвращает тип рег. операции (расход, доход, перевод со счёта, перевод на финцель)
     * @return int
     */
    public function getType()
    {
        return $this->model->type;
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
                strtotime( $this->getDate() . $this->getTime() ) :
                strtotime( $this->getDate() ),

            // Повторение
            'every'      => $this->getEvery(),
            'repeat'     => $this->getRepeat(),
            'week'       => $this->getWeek(),

            // Операция
            'money'      => $this->getAmount(),
            'cat_id'     => $this->getCategory(),
            'account_id' => $this->getAccount(),
            'tags'       => $this->getTags(),
            'tr_id'      => $this->model->tr_id,
            'transfer'   => $this->model->transfer,
        );

    }

    /**
     * Возвращает массив с ошибками (если есть)
     * @return array
     */
    public function getErrors ()
    {
        return $this->errors;
    }

    /**
     * Устанавливает начало
     * @param MYSQL DATE $date
     */
    public function setStart ( $date ) {
        $this->model->start = $date;
    }

    /**
     * Устанавливает начало
     * @param MYSQL DATETIME $date
     */
    public function setDate ( $date ) {
        $this->model->date = $date;
    }

    /**
     * Проверяем модель на ошибки
     * Если есть ошибки, то их можно получить так $event->getErrors();
     * @return bool
     */
    public function checkData ()
    {
        // Преобразовываем данные к нужному формату

        if ( ! in_array( $this->model->every, array( 0, 1, 7, 30, 90, 365) ) ) {
            $this->model->every = 0;
        }

        if ( $this->model->repeat > Calendar::MAX_EVENTS ) {
            $this->model->repeat = Calendar::MAX_EVENTS;
        }


        if ( $this->getLast() != 0 && 
           (strtotime( $this->getLast() ) < strtotime( $this->getDate() ) ) )
        {
            $this->errors['date']  = 'Конечная дата не может быть меньше даты начала';
        }


        if ( ( int ) $this->model->date === 0 ) {
            $this->errors['date']  = 'Необходимо указать дату';
        }

        if ( ( int ) $this->model->accepted === 1 && ( int ) $this->model->account === 0 ) {
            $this->errors['account']  = 'Необходимо указать счёт';
        }

        if ( ( int ) $this->model->accepted === 1 && ( int ) $this->model->money == 0 ) {
            $this->errors['account']  = 'Необходимо указать сумму';
        }

        // Перевод со счёта на счёт
        if ( $this->model->type === 2 ) {

            if ( ( int ) $this->model->accepted === 1 && ( int ) $this->model->toAccount === 0 ) {
                $this->errors['toAccount']  = 'Необходимо указать счёт куда нужно перевести';
            }

        // Перевод на фин.цель
        } elseif ( $this->model->type === 4) {

            if ( ( int ) $this->model->accepted === 1 && ( int ) $this->model->target === 0 ) {
                $this->errors['target']  = 'Необходимо указать счёт финансовой цели';
            }

        // Расход или доход
        } else {

            if ( ( int ) $this->model->accepted === 1 && ( int ) $this->model->category === 0 ) {
                 $this->errors['category']  = 'Необходимо указать категорию';
            }

        }

        // Проверяем на ошибки
        if ( count($this->errors) != 0 ) {
            
            return false;

        } else {

            return true;

        }
    }
}
