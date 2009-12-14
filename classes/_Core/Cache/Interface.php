<?php

interface _Core_Cache_Interface
{
	public function get( $id );
	
	public function getMulti( array $ids );
	
	public function set( $id, $value, $expired, array $tags );
	
	public function clean( $id );
}
