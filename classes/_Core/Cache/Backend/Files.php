<?php

/**
 * Бекенд. Хранение в файловой системе.
 *
 */
class _Core_Cache_Backend_Files implements _Core_Cache_Interface
{
	/**
	 * Путь к директории - хранилищу
	 *
	 * @var string
	 */
	protected $cachePath;
	
	/**
	 * Кол-во директорий в пути к файлу
	 * (связано с ограничением некоторых систем на кол-во файлов 
	 * в директории и ухудшением производительности если кол-во
	 * больше определённого значения) Обычно хватает значения по
	 * умолчанию (2)
	 *
	 * @var int
	 */
	protected $dirChunks;
	
	function __construct( $cachePath, $dirChunks = 2 )
	{
		if( !file_exists($cachePath) || !is_dir($cachePath) || !is_writable($cachePath) )
		{
			throw new _Core_Cache_Exception('The directory specified for the storage does not exist or is not writable!');
		}
		
		$this->cachePath = $cachePath;
		$this->dirChunks = (int)$dirChunks;
	}
	
	public function get( $id )
	{
		$data = null;
		
		$path = $this->getPath($id);
		
		if( file_exists($path) )
		{
			$stored = unserialize( file_get_contents($path) );
			
			// Если время хранения данных не истекло...
			if( is_null($stored['expired']) || $stored['expired'] <= filemtime( $path ) )
			{
				$data = $stored['data'];

			}
			// Можно, конечно, и удалять. Но это лишняя файловая операция.
			// проще и выгоднее реализовать очистку через cron
		}
		
		return $data;
	}
	
	/**
	 * Исключительно для удобства и реализации
	 * интерфейса.
	 *
	 * @param array $ids массив ключей
	 * @return array массив данных
	 */
	public function getMulti( array $ids )
	{
		$answer = array();
		
		while( list(,$id) = each($ids) )
		{
			$answer[$id] = $this->get($id);
		}
		
		return $answer;
	}
	
	public function set( $id, $value, $expired = null)
	{
		$data = array(
			'expired' 	=> $expired,
			'data'		=> $value,
		);
		
		$path = $this->getPath($id);
		
		if( !file_exists( dirname($path) ) )
		{
			mkdir(dirname($path), 0777, true);
		}
		
		file_put_contents( $path, serialize($data) );
	}
	
	public function clean( $id )
	{
		$path = $this->getPath($id);
		
		if( file_exists( $path ) )
		{
			unlink( $path );
			return true;
		}
		
		return false;
	}
	
	protected function getPath( $id )
	{
		$id = md5($id);
		
		// количество символов в названии директорий
		// @todo надо предусмотреть что dirChunks может быть
		// больше 16ти (т.е. $id/$lettersCount)
		$lettersCount = 2;
		
		$path = $this->cachePath . DIRECTORY_SEPARATOR;
		
		for( $i = 0; $i <= $this->dirChunks; $i++ )
		{
			$path .= substr( $id, $i*$lettersCount, $lettersCount ) . DIRECTORY_SEPARATOR;
		}
		
		$path .= DIRECTORY_SEPARATOR . $id;
		
		return $path;
	}
}