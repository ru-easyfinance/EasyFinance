<?php

/**
 * Интерфейс кеширования.
 *
 */
interface _Core_Cache_Interface
{
	/**
	 * Получение данных
	 *
	 * @param string $id ключ
	 */
	public function get( $id );
	
	/**
	 * Получение нескольких значений за раз
	 *
	 * @param array $ids массив ключей
	 */
	public function getMulti( array $ids );
	
	/**
	 * Сохранение данных в кеш
	 *
	 * @param string $id Идентификатор 
	 * @param mixed $value Данные
	 * @param integer $expire Кол-во секунд до протухания данных (по умолчанию - никогда)
	 */
	public function set( $id, $value, $expire = null);
	
	/**
	 * Очистка (удаление) данных
	 *
	 * @param string $id ключ
	 */
	public function clean( $id );
}
