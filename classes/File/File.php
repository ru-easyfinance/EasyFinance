<?php

class FIle
{
    /**
     * Путь к файлу (источнику)
     *
     * @var string
     */
    private $filePath;

    /**
     * Mime тип файла
     *
     * @var string
     */
    private $fileType;

    /**
     * Имя файла
     *
     * @var string
     */
    private $fileName;

    /**
     * Размер загруженного файла, в байтах
     *
     * @var integer
     */
    private $fileSize;

    public static function upload( $inputName, $multiple = false )
    {
        if( $multiple )
        {
            //for ...
            //File::upload( $inputName );
        }

        if( !array_key_exists( $inputName, $_FILES ) )
        {
            throw new File_UploadException('File from input named "' . $inputName . '" was not uploaded !', UPLOAD_ERR_NO_FILE );
        }

        if( $_FILES[ $inputName ]['error'] !== UPLOAD_ERR_OK )
        {
            throw new File_UploadException('Unknown error while uploading file "' . $inputName . '" !', $_FILES[ $postKey ]['error']);
        }

        return new File();
    }

    /**
     * Сохранение загруженного файла
     *
     * @param string $destDir директория для загрузки файла
     * @param string $fileName название файла (если null - берётся исходное)
     * @param boolean $ignoreExisted игнорирование существующего файла
     */
    public function save( $destDir, $fileName = null, $ignoreExisted = true)
    {
        if( is_null($fileName) )
        {
            $fileName = $this->fileName;
        }

        $fullName = $destDir . DIRECTORY_SEPARATOR . $fileName;

        if( $ignoreExisted && file_exists( $fullName ) )
        {
            if( !unlink( $fullName ) )
            {
                throw new File_UploaderException('Unable to delete existed file !');
            }
        }

        if( !copy( $this->filePath, $fullName )  )
        {
            throw new File_UploaderException( 'Unable to copy file to "' . $destDir .'"!' );
        }

        return true;
    }
}