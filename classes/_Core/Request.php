<?php

class _Core_Request
{
	private $method; // POST,GET,PUT etc
	private $scheme; // http, https
	private $post 	= array();
	private $get 		= array();
	
	private $domain; // easyfinance.ru www.easyfinance.ru, demo.easyfinance.ru
}
