-- Скрипт сгенерирован Devart dbForge Studio for MySQL, Версия 5.0.97.1
-- Домашняя страница продукта: http://www.devart.com/ru/dbforge/mysql/studio
-- Дата скрипта: 16.11.2012 23:45:16
-- Версия сервера: 5.1.36-community-log
-- Версия клиента: 4.1

-- 
-- Отключение внешних ключей
-- 
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

-- 
-- Установка кодировки, с использованием которой клиент будет посылать запросы на сервер
--
SET NAMES 'utf8';

-- 
-- Установка базы данных по умолчанию
--
USE webcam;

--
-- Описание для таблицы cameras
--
DROP TABLE IF EXISTS cameras;
CREATE TABLE cameras (
  id INT(11) NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) DEFAULT NULL,
  descr VARCHAR(255) DEFAULT NULL,
  url VARCHAR(255) NOT NULL,
  camtype VARCHAR(255) NOT NULL DEFAULT 'zm' COMMENT 'zm or file',
  `interval` VARCHAR(255) NOT NULL DEFAULT 'None' COMMENT 'Interval for file input between frames   ',
  queuesz INT(11) NOT NULL DEFAULT 100 COMMENT ' size of the queue (?)',
  user VARCHAR(255) DEFAULT NULL COMMENT '# Credentials for ZM server ',
  pass VARCHAR(255) DEFAULT NULL COMMENT '# Credentials for ZM server ',
  down INT(11) NOT NULL DEFAULT 0 COMMENT 'выключена?',
  PRIMARY KEY (id)
)
ENGINE = INNODB
AUTO_INCREMENT = 8
AVG_ROW_LENGTH = 2730
CHARACTER SET utf8
COLLATE utf8_general_ci;

--
-- Описание для таблицы groupcams
--
DROP TABLE IF EXISTS groupcams;
CREATE TABLE groupcams (
  id INT(11) NOT NULL AUTO_INCREMENT,
  group_id INT(11) NOT NULL,
  id_cam INT(11) NOT NULL,
  PRIMARY KEY (id)
)
ENGINE = INNODB
AUTO_INCREMENT = 27
AVG_ROW_LENGTH = 1260
CHARACTER SET utf8
COLLATE utf8_general_ci;

--
-- Описание для таблицы groups
--
DROP TABLE IF EXISTS groups;
CREATE TABLE groups (
  id INT(11) NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) DEFAULT NULL,
  descr VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (id)
)
ENGINE = INNODB
AUTO_INCREMENT = 5
AVG_ROW_LENGTH = 5461
CHARACTER SET utf8
COLLATE utf8_general_ci;

--
-- Описание для таблицы news
--
DROP TABLE IF EXISTS news;
CREATE TABLE news (
  id INT(11) NOT NULL AUTO_INCREMENT,
  header VARCHAR(255) DEFAULT NULL,
  short VARCHAR(255) DEFAULT NULL,
  full TEXT DEFAULT NULL,
  added DATETIME NOT NULL,
  PRIMARY KEY (id)
)
ENGINE = MYISAM
AUTO_INCREMENT = 17
AVG_ROW_LENGTH = 63
CHARACTER SET utf8
COLLATE utf8_general_ci;

--
-- Описание для таблицы usergroups
--
DROP TABLE IF EXISTS usergroups;
CREATE TABLE usergroups (
  id INT(11) NOT NULL AUTO_INCREMENT,
  user_id INT(11) NOT NULL,
  group_id INT(11) NOT NULL,
  PRIMARY KEY (id)
)
ENGINE = MYISAM
AUTO_INCREMENT = 39
AVG_ROW_LENGTH = 13
CHARACTER SET utf8
COLLATE utf8_general_ci;

--
-- Описание для таблицы users
--
DROP TABLE IF EXISTS users;
CREATE TABLE users (
  id INT(11) NOT NULL AUTO_INCREMENT,
  username VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  registered INT(11) NOT NULL,
  is_admin INT(11) DEFAULT 0,
  name VARCHAR(255) DEFAULT NULL,
  lastname VARCHAR(255) DEFAULT NULL,
  info VARCHAR(255) DEFAULT NULL COMMENT ' чтоб можно было добавить какую=то свободную инфу (типа телефон, адрес и тп)',
  PRIMARY KEY (id)
)
ENGINE = INNODB
AUTO_INCREMENT = 19
AVG_ROW_LENGTH = 963
CHARACTER SET utf8
COLLATE utf8_general_ci;

-- 
-- Вывод данных для таблицы cameras
--
INSERT INTO cameras VALUES 
  (1, 'Самая первая веб-камера Истры', 'Расположена в окрестностях высокого холма сразу за рекой', 'http://bee/cgi-bin/nph-zms?mode=jpeg&monitor=1&scale=100&maxfps=5&buffer=1000&auth=b95d6e849338d6a413ced101a9fe2d45&connkey=731816&rand=1352302382', 'zm', 'None', 100, NULL, NULL, 0),
  (3, 'Противовандальная камера', 'Над крыльцом, слева. Помашите рукой!', 'http://bee/cgi-bin/nph-zms?mode=jpeg&monitor=1&scale=100&maxfps=5&buffer=1000&auth=b95d6e849338d6a413ced101a9fe2d45&connkey=731816&rand=1352302382', 'zm', 'None', 100, NULL, NULL, 0),
  (4, 'Левая камера над администрацией', 'Расположена рядом с пивным баром', 'http://bee/cgi-bin/nph-zms?mode=jpeg&monitor=1&scale=100&maxfps=5&buffer=1000&auth=b95d6e849338d6a413ced101a9fe2d45&connkey=731816&rand=1352302382', 'zm', 'None', 100, NULL, NULL, 0),
  (5, 'Правая камера над администрацией', 'Можете передать привет!!', 'http://bee/cgi-bin/nph-zms?mode=jpeg&monitor=1&scale=100&maxfps=5&buffer=1000&auth=b95d6e849338d6a413ced101a9fe2d45&connkey=731816&rand=1352302382', 'zm', 'None', 100, NULL, NULL, 0),
  (6, 'Веб-камера перед библиотекой', 'Вообще-то это Лапландия, полярный круг', 'http://www.santaclauslive.com/cam/cam.jpg', 'zm', 'None', 100, NULL, NULL, 1),
  (7, 'tst1', 'Вводим через форму', 'http://bee/cgi-bin/nph-zms?mode=jpeg&monitor=1&scale=100&maxfps=5&buffer=1000&auth=b95d6e849338d6a413ced101a9fe2d45&connkey=731816&rand=1352302382', 'zm', 'None', 100, 'гuser1', 'pass', 1);

-- 
-- Вывод данных для таблицы groupcams
--
INSERT INTO groupcams VALUES 
  (14, 2, 6),
  (15, 2, 4),
  (16, 3, 5),
  (17, 3, 3),
  (18, 3, 1),
  (19, 1, 4),
  (20, 1, 3),
  (21, 1, 1),
  (22, 4, 6),
  (23, 4, 4),
  (24, 4, 5),
  (25, 4, 3),
  (26, 4, 1);

-- 
-- Вывод данных для таблицы groups
--
INSERT INTO groups VALUES 
  (1, 'Анонимная', 'Группа камер для свободного доступа'),
  (2, 'test111', 'просто так'),
  (3, 'тест222', 'ищо просто так');

-- 
-- Вывод данных для таблицы news
--
INSERT INTO news VALUES 
  (16, 'эээ', 'ять!', 'опять ничего не работает!!!', '2012-11-13 23:22:15'),
  (15, 'sdfs', 'ds', 'kjhk\n', '2012-11-13 23:19:10'),
  (13, '3', '3', '3', '2012-11-13 21:24:08'),
  (14, '4', '4sfsd', '4', '2012-11-13 21:24:11');

-- 
-- Вывод данных для таблицы usergroups
--
INSERT INTO usergroups VALUES 
  (19, 9, 2),
  (7, 2, 1),
  (4, 1, 3),
  (8, 2, 3),
  (35, 3, 3),
  (34, 3, 2),
  (18, 9, 1),
  (38, 16, 3),
  (20, 9, 3),
  (37, 16, 2),
  (36, 16, 1),
  (25, 12, 1);

-- 
-- Вывод данных для таблицы users
--
INSERT INTO users VALUES 
  (2, 'usser', 'usser@mail.ru', 'pass', 0, NULL, NULL, NULL, NULL),
  (3, 'admin', 'my@mail.com', '$2a$08$8qKjK8t7cpDJUI9/1bdm4.q0RquYC6PuOP5NOkZuU6nma1bbI.sZ6', 1347543002, 1, 'Сергей', 'Иванов', 'он же "админ" для камер'' and pass=1'),
  (4, 'ivanov', 'my@mail.com', '$2a$08$5qGhTUESS.opyA3jw2/lcuMPEpt7JtwNdHLA3y1BgrM2xigjjmdoC', 1347547513, 0, NULL, 'ф2', NULL),
  (5, 'qweqweqwe', 'admin@a.ru', '$2a$08$D4qFXS.ie0DrAZwWsvaW6.suYbEvTVaTFYaf8ljmdWqjmnfn638pa', 1349280261, 0, NULL, 'фф3', NULL),
  (6, 'ivanovv', 'admin@m.r', '$2a$08$STJIfwerqvFzbW4u.ZCEZOoyAhDW7pNbjDrwkQYqxgJuQycY8m3E6', 1349286143, 0, NULL, NULL, NULL),
  (7, 'ivanovvq', 'admin@m.rw', '$2a$08$Q2iPetUm8vsipS7Ugiw29ujdKzqNgNT544HK8s0CFD.bHipLu2DRW', 1349286235, 0, NULL, NULL, NULL),
  (8, 'ivanovvqg', 'admin@m.rwe', '$2a$08$2.fiYu74bgd4jAWZ5fBMeeuBcG9/MfoH.aMcZB4Cbde7jAs5nA2E6', 1349286301, 0, NULL, NULL, NULL),
  (9, 'ivanova', 'my@mail.com2', '$2a$08$Zpyvji/75KcgY2N3.03qVOs62IWYhzJbG083JLg.UN2Lnu.uIJqOC', 1349293348, 0, NULL, NULL, NULL),
  (10, 'dfgdf', 'admin@ee.y', '$2a$08$41e8Gl5l80eJqb2CJ0pIU.ZBPyw/s0iuJA6UkPsgkQN8yxJDoHBBu', 1349293375, 0, NULL, NULL, NULL),
  (11, 'sdfsd', 'abs@maol.riu', '$2a$08$6zzpHdit/GwCIiWI1YmbGeMz5nsQIakMk9HEG2HAilvclVK9okqUa', 1351785948, 0, NULL, NULL, NULL),
  (12, 'anonymous', '', 'password', 0, NULL, NULL, NULL, NULL),
  (13, 'vassy', 'vassy@c.t', '$2a$08$GjiAdo3KkvC8ApkJsNjynONv4YqorWJwB3YzcaaDqG2mhQvt6dxUq', 1352385999, 0, '', '', NULL),
  (14, 'admin1', 'admin@hoe.r', '$2a$08$U0nhzhwIh7EZ0G0Gte/She37Sqxz3/BNVq7L7bwJdjPomqxD/WlQa', 1352455339, 0, 'Имя', 'Фамиллия', NULL),
  (15, 'admin2', 'admin@hoe.rw', '$2a$08$3.duIWDY1HqIt09udwYj2ur.bfa6ogcwVB0oJJxFx1pTaax3XFRfK', 1352455390, 0, 'Имя', 'Фамиллия', NULL),
  (16, '1234567', 'adminsfa@ssf.re', '$2a$08$DHy/AewkoCxZNUQFnDYZZe/H4AtG4LScOiS.GCz9h9/nOyc3DLjz6', 1352456094, 0, 'Sd', 'Fsdgfs ', NULL),
  (17, 'admin007', 'adminadg@dds.ee', '$2a$08$VEQkdIke/ETI.RuLkggXvephrNW5rC/UADGLq0QzC.y0Kn/29Es9u', 1352456256, 0, 'eg', 'dfg', NULL),
  (18, 'camadmin', 'istra@m.ru', '$2a$08$PWV4zT//jTQcY0efe64hp.qQsBeEeJhMs/SDMyv5647OKDqcBWwc2', 1352893733, 0, 'camera', 'admin', NULL);

-- 
-- Включение внешних ключей
-- 
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;