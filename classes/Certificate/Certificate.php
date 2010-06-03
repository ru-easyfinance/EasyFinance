<?php
/**
 * Сертификат эксперта
 * @copyright http://easyfinance.ru/
 * @author Andrew Tereshko aka mamonth
 */

class Certificate
{
    /**
     * Константы статусов сертификата
     *
     */
    const STATUS_PROCESSING     = 0;
    const STATUS_ACCEPTED     = 1;
    const STATUS_REJECTED    = 2;

    /**
     * Экземпляр модели
     *
     * @var Certificate_Model
     */
    protected $model;

    /**
     * Экземпляр владельца ( эксперта )
     *
     * @var unknown_type
     */
    protected $user = null;

    /**
     * Тексты статусов сертификата
     *
     * @var array
     */
    protected $textStatus = array(
        0 =>     'В обработке',
        1 =>     'Одобрен',
        2 =>     'Не допущен',
    );

    /**
     * Конструктор
     *
     * @param Certificate_Model $model
     */
    public function __construct( Certificate_Model $model )
    {
        $this->model = $model;
    }

    /**
     * Создание нового сертификата
     *
     * @param string $comment Комментарий
     * @param string $imageSrc Адрес изображения сертификата
     */
    public static function create( oldUser $user, $comment, $imageSrc )
    {
        $comment    = $comment;

        $imgSrc    = self::getImagePath($user->getId());
        $imgThumbSrc = self::getImagePath($user->getId(), true);

        if( !file_exists( SYS_DIR_ROOT . '/www' . dirname($imgSrc) ) )
        {
            mkdir( SYS_DIR_ROOT . '/www' . dirname($imgSrc) );
        }

        $image = new External_SimpleImage();

        if ( !$image->load( $imageSrc ) )
        {
            throw new Certificate_Exception( 'Unable to load image!' );
        }

        $image->save( SYS_DIR_ROOT . '/www' . $imgSrc );

        $image->resizeToWidth(200);

        $image->save( SYS_DIR_ROOT . '/www' . $imgThumbSrc );

            return new self(
                Certificate_Model::create( $user->getId(), $comment, $imgSrc, $imgThumbSrc )
        );
    }

    public static function delete( oldUser $user, $certId )
    {
        @unlink( SYS_DIR_ROOT . '/www' . self::getImagePath($user->getId())  );
        @unlink( SYS_DIR_ROOT . '/www' . self::getImagePath($user->getId(), true) );

        Certificate_Model::delete( (int)$certId );
    }

    public static function getImagePath( $userId, $thumb = false )
    {
        $uploadDir     =  '/upload/certs';

        $imgSrc     = $uploadDir . '/' . $userId . '_' . time();

        if( $thumb )
        {
            $imgThumbSrc     .= '_thumb';
        }

        $imgSrc    .= '.jpg';

        return $imgSrc;
    }

    public function getId()
    {
        return $this->model->cert_id;
    }

    public function getImage()
    {
        return $this->model->cert_img;
    }

    public function getThumb()
    {
        return $this->model->cert_img_thumb;
    }

    public function getDetails()
    {
        return $this->model->cert_details;
    }

    public function getStatusCode()
    {
        return $this->model->cert_status;
    }

    public function getStatusMessage()
    {
        return $this->textStatus[ $this->getStatusCode() ];
    }
}