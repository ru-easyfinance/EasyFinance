<?php
/**
 * Класс для работы с изображениями
 * 
 * @copyright http://easyfinance.ru/
 * @author Andrew Tereshko aka mamonth
 */
class File_Image extends File 
{
	/**
	 * Изображение
	 *
	 * @var resource
	 */
	private $image;
		
	/**
	 * Конструктор, инициализирует изображение по пути к файлу
	 *
	 * @param string $fileName путь к изображению
	 */
	public function __construct( $fileName )
	{
		list(,,$imageType) = getimagesize( $fileName );

		switch( $imageType )
		{
			case IMAGETYPE_JPEG:
				$this->image = imagecreatefromjpeg( $fileName );
				break;
			case IMAGETYPE_GIF:
				$this->image = imagecreatefromgif( $fileName );
				break;
			case IMAGETYPE_PNG:
				$this->image = imagecreatefrompng( $fileName );
				break;
                        case IMAGETYPE_BMP:
                                $this->image = imagecreatefromwbmp( $filename );
			default:
				throw new File_ImageException( 'Unknown type of image !' );
		}
	}
	
	/**
	 * Изменение размера изображения. Если не указано
	 * одно из измерений, изменяем пропорционально
	 *
	 * @param integer $width
	 * @param integer $height
	 */
	public function resize( $width = null, $height=null )
	{
		if( !$width )
		{
			$ratio = $height / $this->getHeight();
			$width = $this->getWidth() * $ratio;
		}
		elseif ( !$height )
		{
			$ratio = $width / $this->getWidth();
			$height = $this->getHeight() * $ratio;
		}
		
		$imageResized = imagecreatetruecolor($width, $height);
		
		imagecopyresampled($imageResized, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
		
		$this->image = $imageResized;
	}
	
	/**
	 * Сохранение изображения
	 *
	 * @param string $fileName
	 * @param integer $imageType
	 * @param boolean $ignoreExisted
	 * @return boolean
	 */
	public function save( $fileName, $imageType = IMAGETYPE_JPEG , $ignoreExisted = true )
	{
		if( !file_exists( dirname($fileName) ) ) // Проверяем целевую директорию на существование
		{
			throw new File_ImageException('Can\'t save image to non-existed directory "' . dirname($fileName) . '"!'  );
		}
		
		if( file_exists($fileName) ) // Если файл уже существует
		{
			if( $ignoreExisted ) // ... и указан флаг игнорирования ...
			{
				unlink( $fileName );
			}
			else
			{
				throw new File_ImageException('File with given name already exists!');
			}
		}
		
		switch ( $imageType )
		{
			case IMAGETYPE_JPEG:
				$function = 'imagejpeg';
				break;
			case IMAGETYPE_GIF:
				$function = 'imagegif';
				break;
			case IMAGETYPE_PNG:
				$function = 'imagepng';
				break;
			default:
				throw new File_ImageException('Unknown image type given!');
				break;
		}
		
		if( !$function($this->image, $fileName) )
		{
			throw new File_ImageException('Can\t save image!');
		}
		
		return true;
	}
	
	/**
	 * Возвращает текущую ширину изображения
	 *
	 * @return integer
	 */
	public function getWidth()
	{
		return imagesx($this->image);
	}
	
	/**
	 * Возвращает текущую высоту изображения
	 *
	 * @return ineger
	 */
	public function getHeight()
	{
		return imagesy($this->image);
	}
	
	/**
	 * Создание обьекта из изображения пришедшего в post запросе
	 *
	 * @param string $inputName имя поля с выбором файла
	 * @param boolean $multiple не используется на данный момент
	 * @return File_Image
	 * 
	 * @example $image = File_Image::upload( 'imageInputName' );
	 * @example File_Image::upload( 'imageInputName' )->save( $destination );
	 */
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
		
		return new File_Image( $_FILES[ $inputName ]['tmp_name'] );
	}
}
