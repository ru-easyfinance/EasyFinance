-- phpMyAdmin SQL Dump
-- version 2.6.1
-- http://www.phpmyadmin.net
-- 
-- Хост: localhost
-- Время создания: Окт 30 2009 г., 12:05
-- Версия сервера: 5.0.45
-- Версия PHP: 5.2.4

SET FOREIGN_KEY_CHECKS=0;
-- 
-- БД: `hm`
-- 

-- --------------------------------------------------------

-- 
-- Структура таблицы `info_calc`
-- 

DROP TABLE IF EXISTS `info_calc`;
CREATE TABLE IF NOT EXISTS `info_calc` (
  `m_r` int(11) default NULL,
  `m_y` int(11) default NULL,
  `m_b` int(11) default NULL,
  `c_r` int(11) default '1',
  `c_y` int(11) default '2',
  `c_g` int(11) default '3',
  `u_r` int(11) default '0',
  `u_y` int(11) default '1',
  `u_g` int(11) default '2',
  `weight` int(11) default NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Дамп данных таблицы `info_calc`
-- 

INSERT INTO `info_calc` (`m_r`, `m_y`, `m_b`, `c_r`, `c_y`, `c_g`, `u_r`, `u_y`, `u_g`, `weight`) VALUES (0, 2, 5, 1, 2, 3, 0, 1, 2, 35);
INSERT INTO `info_calc` (`m_r`, `m_y`, `m_b`, `c_r`, `c_y`, `c_g`, `u_r`, `u_y`, `u_g`, `weight`) VALUES (70, 40, 0, 1, 2, 3, 0, 1, 2, 15);
INSERT INTO `info_calc` (`m_r`, `m_y`, `m_b`, `c_r`, `c_y`, `c_g`, `u_r`, `u_y`, `u_g`, `weight`) VALUES (97, 85, 0, 1, 2, 3, 0, 1, 2, 20);
INSERT INTO `info_calc` (`m_r`, `m_y`, `m_b`, `c_r`, `c_y`, `c_g`, `u_r`, `u_y`, `u_g`, `weight`) VALUES (0, 5, 10, 1, 2, 3, 0, 1, 2, 30);

-- --------------------------------------------------------

-- 
-- Структура таблицы `info_desc`
-- 

DROP TABLE IF EXISTS `info_desc`;
CREATE TABLE IF NOT EXISTS `info_desc` (
  `type` varchar(11) default NULL,
  `title` varchar(11) default NULL,
  `min` int(11) default NULL,
  `color` int(11) default NULL,
  `description` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Дамп данных таблицы `info_desc`
-- 

INSERT INTO `info_desc` (`type`, `title`, `min`, `color`, `description`) VALUES ('money', 'Деньги', 0, 0, '0');
INSERT INTO `info_desc` (`type`, `title`, `min`, `color`, `description`) VALUES ('money', 'Деньги', 2, NULL, NULL);
INSERT INTO `info_desc` (`type`, `title`, `min`, `color`, `description`) VALUES ('money', 'Деньги', 5, NULL, NULL);
INSERT INTO `info_desc` (`type`, `title`, `min`, `color`, `description`) VALUES ('credit', 'Кредиты', 97, NULL, NULL);
INSERT INTO `info_desc` (`type`, `title`, `min`, `color`, `description`) VALUES ('credit', 'Кредиты', 85, NULL, NULL);
INSERT INTO `info_desc` (`type`, `title`, `min`, `color`, `description`) VALUES ('credit', 'Кредиты', 0, NULL, NULL);
INSERT INTO `info_desc` (`type`, `title`, `min`, `color`, `description`) VALUES ('expens', 'Расходы', 97, NULL, NULL);
INSERT INTO `info_desc` (`type`, `title`, `min`, `color`, `description`) VALUES ('expens', 'Расходы', 85, NULL, NULL);
INSERT INTO `info_desc` (`type`, `title`, `min`, `color`, `description`) VALUES ('expens', 'Расходы', 0, NULL, NULL);
INSERT INTO `info_desc` (`type`, `title`, `min`, `color`, `description`) VALUES ('upper', 'Бюджет', 0, NULL, NULL);
INSERT INTO `info_desc` (`type`, `title`, `min`, `color`, `description`) VALUES ('upper', 'Бюджет', 5, NULL, NULL);
INSERT INTO `info_desc` (`type`, `title`, `min`, `color`, `description`) VALUES ('upper', 'Бюджет', 10, NULL, NULL);
INSERT INTO `info_desc` (`type`, `title`, `min`, `color`, `description`) VALUES ('fin_cond', 'Фин.состоян', 0, NULL, NULL);
INSERT INTO `info_desc` (`type`, `title`, `min`, `color`, `description`) VALUES ('fin_cond', 'Фин.состоян', 100, NULL, NULL);
INSERT INTO `info_desc` (`type`, `title`, `min`, `color`, `description`) VALUES ('fin_cond', 'Фин.состоян', 150, NULL, NULL);

SET FOREIGN_KEY_CHECKS=1;
