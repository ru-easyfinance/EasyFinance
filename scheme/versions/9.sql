-- phpMyAdmin SQL Dump
-- version 3.1.3.1
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Авг 25 2009 г., 17:42
-- Версия сервера: 5.1.33
-- Версия PHP: 5.2.9

--
-- База данных: `homemoney`
--

-- --------------------------------------------------------

--
-- Структура таблицы `feedback_message`
--

DROP TABLE IF EXISTS `feedback_message`;
CREATE TABLE IF NOT EXISTS `feedback_message` (
  `uid` int(100) NOT NULL COMMENT 'id пользователя',
  `user_settings` text NOT NULL COMMENT 'сис настройки пользователя',
  `messages` text NOT NULL COMMENT 'сообщение',
  `user_name` varchar(32) NOT NULL COMMENT 'имя пользователя',
  `new` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'новое',
  `rating` int(8) NOT NULL DEFAULT '0' COMMENT 'рэйтинг сообщения',
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'индекс сообщений для быстрого пользователя',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='таблица сообщений от тестеров';
