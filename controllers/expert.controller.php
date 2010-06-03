<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля экспертов
 * @copyright http://easyfinance.ru/
 * @version SVN $Id: accounts.controller.php 232 2009-08-21 14:34:45Z rewle $
 */

class Expert_Controller extends _Core_Controller_UserExpert
{
    /**
     * Конструктор класса
     * @return void
     */
    protected function __init()
    {
    }

    /**
     * Индексная страница
     * @return void
     */
    function index()
    {
        $this->tpl->assign('name_page', 'expert/expert');
    }

    /**
     * Получение полной информации
     *
     * @todo Вынести получение сертификатов и услуг эксперта в эксперта =)
     *
     */
    function getProfile()
    {
        $user    = Core::getInstance()->user;

        $json     = array (
            'fio'        => $user->getUserProps('user_name'),
            'shortInfo'    => $user->getUserProps('user_info_short'),
            'fullInfo'    => $user->getUserProps('user_info_full'),
            'rating'        => 4,
            'votes'        => 12,
            'photo'        => $user->getUserProps('user_img'),
            'smallPhoto'    => $user->getUserProps('user_img_thumb'),

            'certificates'     => array(),
            'services'    => array(),
        );

        // Загрузка списка сертификатов
        foreach ( Certificate_Container::loadByUser($user)->getIterator() as $cert )
        {
            $json['certificates'][] = array (
                'id'        => $cert->getId(),
                'image'        => $cert->getImage(),
                'smallImage'    => $cert->getThumb(),
                'comment'    => $cert->getDetails(),
                'status'        => $cert->getStatusCode(),
                'statusText'    => $cert->getStatusMessage(),
            );
        }

        // Загрузка услуг эксперта
        foreach ( Service_Expert_Collection::load( $user ) as $service )
        {
            $json['services'][] = array (
                'id'        => $service->getId(),
                'title'         => $service->getName(),
                'comment'    => $service->getDesc(),
                'price'        => $service->getPrice(),
                'days'        => $service->getTerm(),
                'checked'    => true,
            );
        }

        // Загрузка общего списка услуг
        foreach ( Service_Collection::load() as $service )
        {
            $json['services'][] = array (
                'id'         => $service->getId(),
                'title'        => $service->getName(),
                'comment'     => $service->getDesc(),
                'price'        => null,
                'days'        => null,
                'checked'    => false,
            );
        }

        exit( json_encode($json) );
    }

    /**
     * Редактирование информации о эксперте
     */
    function editInfo()
    {
        $sql = 'update `user_fields_expert`
            set `user_info_short` = ?, `user_info_full` = ?
            where `user_id` = ?';

        Core::getInstance()->db->query($sql, $_POST['profile-short'], $_POST['profile-long'], Core::getInstance()->user->getId() );

        $json = array(
            'result' => array (
                'text'         => 'Информация успешно обновлена.',
            )
        );

        exit( json_encode($json) );
    }

    /**
     * Загрузка фотографии
     *
     */
    function uploadPhoto()
    {
        $json = array( 'error' => array() );

        try
        {
            $image = File_Image::upload( 'profile-photo' );
        }
        catch ( File_UploadException $e )
        {


            switch ( $e->getCode() )
            {
                case UPLOAD_ERR_NO_FILE:
                    $json['error']['text'] = 'Не указано изображение!';
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    $json['error']['text'] = 'Размер изображения превышает допустимый!';
                    break;
                default:
                    $json['error']['text'] = 'Не удалось загрузить изображение !';
            }

            exit( json_encode($json) );
        }
        catch ( File_ImageException $e )
        {
            $json = array( 'error' => array( 'text' => 'Некорректный тип изображения!' ) );
        }

    $imgDir    = SYS_DIR_ROOT . '/www';

    $imgExt     = '.jpg';
    $imgSrc     = '/upload/experts/' . Core::getInstance()->user->getId();
    $imgThumbSrc = $imgSrc . '_thumb' . $imgExt;
    $imgSrc    .= $imgExt;

    if( !file_exists( $imgDir . dirname($imgSrc) ) )
    {
        mkdir( $imgDir . dirname($imgSrc), null, true );
    }

    try
    {
        $image->save( $imgDir . $imgSrc );

        $image->resize( 200, null );

        $image->save( $imgDir . $imgThumbSrc );
    }
    catch ( File_ImageException $e)
    {
        $json['error']['text'] = 'Не удалось загрузить изображение!';
        exit( json_encode($json) );
    }

    $sql = 'update `user_fields_expert`
        set `user_img` = "' . $imgSrc . '", `user_img_thumb` = "' . $imgThumbSrc . '"
        where `user_id` = ?';

    Core::getInstance()->db->query($sql, Core::getInstance()->user->getId() );

    $json = array(
        'result' => array (
            'text'         => 'Фотография загружена',
            'profile'        => array(
                'photo'        => $imgSrc,
                'smallPhoto'     => $imgThumbSrc,
            )
        )
    );

    exit( json_encode($json) );
    }

    function deletePhoto()
    {
        $imgDir    = SYS_DIR_ROOT . '/www';

    $imgExt     = '.jpg';
    $imgSrc     = '/upload/experts/' . Core::getInstance()->user->getId();
    $imgThumbSrc = $imgSrc . '_thumb' . $imgExt;
    $imgSrc    .= $imgExt;

    @unlink( $imgDir . $imgSrc );
    @unlink( $imgSrc . $imgThumbSrc );

    $sql = 'update `user_fields_expert`
        set `user_img` = "", `user_img_thumb` = ""
        where `user_id` = ?';

    Core::getInstance()->db->query($sql, Core::getInstance()->user->getId() );

    $json = array(
        'result' => array(
            'text' => 'Фотография успешно удалена.'
        )
    );

    exit( json_encode($json) );
    }

    function addCertificate()
    {
        if( !array_key_exists( 'cert-file', $_FILES ) || !is_array( $_FILES['cert-file'] ) )
        {
            exit('{error: {text: "Не указано изображение сертификата."}}');
        }

        if( $_FILES['cert-file']['error'] !== UPLOAD_ERR_OK )
        {
            exit('{error: {text: "Не удалось загрузить изображение сертификата"}}');
        }

        try
        {
            $certificate = Certificate::create( Core::getInstance()->user, $_POST['cert-subject'], $_FILES['cert-file']['tmp_name'] );
        }
        catch ( Certificate_Exception $e)
        {
            $error = array(
                'text'=> "Не удалось сохранить сертификат!",
            );

            exit( json_encode($error) );
        }

        $json = array(
        array(
                'id'        => $certificate->getId(),
                'image'     => $certificate->getImage(),
                'smallImage'     => $certificate->getThumb(),
                'comment'     => $certificate->getDetails(),
                'status'     => $certificate->getStatusCode(),
                'statusText'     => $certificate->getStatusMessage(),
            )
        );

        exit( json_encode( $json ) );
    }

    function deleteCertificate()
    {
        $json = array();
        $certId = (int)$_POST['id'];

        if( $certId )
        {
            Certificate::delete( Core::getInstance()->user, $certId );

            $json['result'] = array (
                'id'    => $certId,
                'text'     => 'Сертификат успешно удалён.'
            );
        }

        exit( json_encode($json) );
    }

    function editServices()
    {
        $json = array();

        if( !isset($_POST['service']) || !is_array($_POST['service']) )
        {
            $json['error'] = array(
            'text' => 'Некорректный запрос!'
        );
        }
        else
        {
            /**
             * Вообще конечно это неверный путь. В идеале услуги эксперта должны
             * хранится у него в сессии, и при этой операции мы должны пользоватся методами
             * User->services->get( $id )->delete(); и User->services->add( Service, $cost, $term );
             * А затем с помощью Unit Of Work в деструкторе либо в спец методе автоматически сохранять
             */

            $expertServices = new Service_Expert_Collection( Core::getInstance()->user );

            foreach ( $_POST['service'] as $serviceId => $serviceInfo )
            {
                if( $serviceInfo['checked'] != 1 ) continue;

                $expertServices->add( (int)$serviceId, (int)$serviceInfo['price'], (int)$serviceInfo['days'] );
            }

            $expertServices->save();

        $json['result'] = array(
            'text' => 'Изменения успешно сохранены.'
        );
        }

        exit( json_encode($json) );
    }
}