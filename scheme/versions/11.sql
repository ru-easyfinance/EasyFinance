-- phpMyAdmin SQL Dump
-- version 3.1.3.1
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Авг 26 2009 г., 11:59
-- Версия сервера: 5.1.33
-- Версия PHP: 5.2.9

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- База данных: `homemoney`
--

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(100) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Наш новый ИД для пользователей',
  `user_name` varchar(100) DEFAULT NULL COMMENT 'Псевдоним, который будет виден остальным на форуме',
  `user_login` varchar(100) NOT NULL COMMENT 'Логин пользователя',
  `user_pass` varchar(40) NOT NULL COMMENT 'Пароль пользователя в формате SHA1',
  `user_mail` varchar(100) DEFAULT NULL COMMENT 'Почта пользователя',
  `user_created` date NOT NULL DEFAULT '0000-00-00' COMMENT 'Дата создания пользователя',
  `user_active` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Если 0, значит забанен',
  `user_new` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Если 1, значит новый',
  `user_currency_default` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Валюта пользователя по умолчанию',
  `user_currency_list` varchar(255) NOT NULL DEFAULT 'a:2:{i:0;i:1;i:1;i:2;}' COMMENT 'Сериализованный массив валют пользователя',
  `user_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'тип пользователя 0-юзер 1-админ 2-эксперт',
  PRIMARY KEY (`id`),
  KEY `user_login` (`user_login`,`user_pass`),
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=2 ;
