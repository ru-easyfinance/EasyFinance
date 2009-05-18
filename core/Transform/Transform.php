<?php
/**
 * Осуществляет преобразование данных из одного формата в другой
 *
 * @author   Евгений Панин <varenich@gmail.com>, Люберцы, Россия, 2008
 * @link  http://www.usefulclasses.com, http://www.phpAddDict.com
 * @package  Transform
 * @version  1.0
 */


/**
 * Абстрактный класс
 * 
 * @package  Transform
 * @access   public
 *
 */ 
class Transform {

 
  /**
   * Конфигурационный хэш конкретного контейнера данных
   *
   * @var      array
   * @access   private
   */
  protected $_conf;

  //--------------------------------

  /**
   * Конструктор объекта
   *
   * @param array $conf Конфигурационный хэш
   * @throws Exception
   * @access public
   */ 
	public function __construct($conf){
		$this->_conf = $conf;
	} // __construct

 
  /**
   * Возвращает экземпляр заданного подкласса
   *
   * @param string $name Название конкретного контейнера
   * @param array  $conf Конфигурационный хэш контейнера
   * @throws Exception
   * @access public
   */
  public function factory($name,$conf) {
  	// Имя подкласса
    $cn = "Transform_".$name;
    $dn = dirname(__FILE__);
    // Название файла, в котором хранится подкласс
    $fileName = $dn."/".$cn.".php";
	if (!file_exists($fileName)) throw new Exception("Формат _ $name _ не поддерживается",1);
	require_once($fileName);
	// Создаем экземпляр объекта подкласса
    $obj = new $cn($conf);
    return $obj;
  } // factory

} // class

/**
 * Интерефейс, который обязаны реализовывать конечные преобразователи
 * 
 * @package  Transform
 * @access   public
 *
 */ 
interface iTransform
{
	/**
   * Трансформирует указанные данные в формат конечного преобразователя
   *
   * @param mixed $data Данные
   * @throws Exception
   * @access public
   */
    public function transform($data);
}
?>