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
     * Конструктор
     * @param Calendar_Model $model
     * @param User $user
     */
    function __construct( Calendar_Model $model, User $user ) 
    {
        $this->model = $model;
    }

    /**
     * ID события или регулярной транзакции
     * @return int
     */
    public function getId ()
    {
        return $this->model->id;
    }

    /**
     * Возвращает заголовок события
     * @return str
     */
    public function getTitle ()
    {
        return $this->model->title;
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
     * Возвращает дату события + время события
     * @return unixtimestamp
     */
    public function getDate ()
    {
        return strtotime( $this->model->date . ' ' . $this->model->time );
    }

    /**
     * Дата начала события
     */
    public function getStart ()
    {
        return formatMysqlDate2UnixTimestamp($this->model->start);
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
    public function getAccept ()
    {
        return $this->model->accept;
    }

    /**
     * Возвращает частоту повторения события
     * @return int
     * Опционально, по-умолчанию 0
     * 0   - без повторения
     * 1   - каждый день
     * 7   - каждую неделю
     * 30  - месяц
     * 90  - квартал
     * 365 -год
     */
    public function getEvery ()
    {
        return $this->model->every;
    }

    /**
     * Возвращает количество повторений
     * Опционально, по-умолчанию 0 (т.е. повторять 0 раз) от 0 до 365 (год)
     * или возможность указать дату окончания,
     * или 0 - т.е. повторять бесконечно
     * @return int || date
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
     * Возвращает объект в виде массива
     */
    public function __getArray()
    {
        return array(
            // Общие данные
            'type'  => $this->getType(),
            'id'    => $this->getId(),
            'chain' => $this->getChain(),
            'date'  => $this->getDate(),
            'start' => $this->getStart(),
            'comment' => $this->getComment(),
            'accept'=> $this->getAccept(),
            'title' => $this->getTitle(),
            // Повторение
            'every' => $this->getEvery(),
            'repeat'=> $this->getRepeat(),
            'week'  => $this->getWeek(),
        );

    }
}
