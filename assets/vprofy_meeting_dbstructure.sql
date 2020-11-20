-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Ноя 20 2020 г., 04:52
-- Версия сервера: 10.4.11-MariaDB
-- Версия PHP: 7.4.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `vprofy_meeting`
--

-- --------------------------------------------------------

--
-- Структура таблицы `answer`
--

CREATE TABLE `answer` (
  `ans_id` int(11) NOT NULL,
  `ans_question_id` int(11) NOT NULL,
  `ans_user_id` int(11) NOT NULL,
  `ans_number` int(11) NOT NULL,
  `ans_string` int(11) NOT NULL,
  `ans_answer_type_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `answer_type`
--

CREATE TABLE `answer_type` (
  `answer_type_id` int(11) NOT NULL,
  `answer_type_title` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `answer_type`
--

INSERT INTO `answer_type` (`answer_type_id`, `answer_type_title`) VALUES
(1, 'Yes, No, Abstain'),
(2, 'Yes, No');

-- --------------------------------------------------------

--
-- Структура таблицы `ci_sessions`
--

CREATE TABLE `ci_sessions` (
  `id` varchar(128) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `timestamp` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `data` blob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `docfile`
--

CREATE TABLE `docfile` (
  `docfile_doc_id` int(11) NOT NULL,
  `docfile_body` longblob NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `document`
--

CREATE TABLE `document` (
  `doc_id` int(11) NOT NULL,
  `doc_body` longblob DEFAULT NULL,
  `doc_filename` varchar(200) NOT NULL,
  `doc_created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `doc_is_for_creditor` bit(1) NOT NULL,
  `doc_is_for_debtor` bit(1) NOT NULL,
  `doc_is_for_manager` bit(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `journal`
--

CREATE TABLE `journal` (
  `journal_id` int(11) NOT NULL,
  `journal_webrequest_id` int(11) NOT NULL,
  `journal_module_name` varchar(2000) NOT NULL,
  `journal_message` varchar(2000) NOT NULL,
  `journal_params` varchar(2000) NOT NULL,
  `journal_created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `migrations`
--

CREATE TABLE `migrations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `version` varchar(255) NOT NULL,
  `class` text NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `batch` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `project`
--

CREATE TABLE `project` (
  `project_id` int(11) NOT NULL,
  `project_name` varchar(250) NOT NULL,
  `project_created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `project_code` varchar(20) NOT NULL,
  `project_acquaintance_start_date` datetime DEFAULT NULL,
  `project_main_agenda_start_date` datetime DEFAULT NULL,
  `project_additional_agenda_start_date` datetime DEFAULT NULL,
  `project_meeting_finish_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `project_document`
--

CREATE TABLE `project_document` (
  `pd_id` int(11) NOT NULL,
  `pd_project_id` int(11) NOT NULL,
  `pd_doc_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `question`
--

CREATE TABLE `question` (
  `qs_id` int(11) NOT NULL,
  `qs_title` text NOT NULL,
  `qs_project_id` int(11) NOT NULL,
  `qs_category_id` int(4) NOT NULL,
  `qs_user_id` int(11) DEFAULT NULL,
  `qs_created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `qs_base_question_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `question_category`
--

CREATE TABLE `question_category` (
  `qscat_id` int(11) NOT NULL,
  `qscat_title` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `question_category`
--

INSERT INTO `question_category` (`qscat_id`, `qscat_title`) VALUES
(1, 'General question'),
(2, 'Additional question'),
(3, 'Accept additional question');

-- --------------------------------------------------------

--
-- Структура таблицы `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `user_login_code` varchar(50) NOT NULL,
  `user_project_id` int(11) NOT NULL,
  `user_created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `user_usertype_id` int(11) NOT NULL,
  `user_can_vote` bit(1) NOT NULL DEFAULT b'0',
  `user_votes_number` decimal(10,2) DEFAULT NULL,
  `user_member_name` varchar(1000) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `usertype`
--

CREATE TABLE `usertype` (
  `usertype_id` int(11) NOT NULL,
  `usertype_name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `usertype`
--

INSERT INTO `usertype` (`usertype_id`, `usertype_name`) VALUES
(1, 'Creditor'),
(2, 'Debtor'),
(3, 'Manager');

-- --------------------------------------------------------

--
-- Структура таблицы `webrequest`
--

CREATE TABLE `webrequest` (
  `webrequest_id` int(11) NOT NULL,
  `webrequest_method` varchar(200) NOT NULL,
  `webrequest_uri` varchar(200) NOT NULL,
  `webrequest_body` varchar(4000) NOT NULL,
  `webrequest_created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `answer`
--
ALTER TABLE `answer`
  ADD PRIMARY KEY (`ans_id`),
  ADD UNIQUE KEY `UQ_answer_question_user` (`ans_question_id`,`ans_user_id`),
  ADD KEY `FK_answer_user` (`ans_user_id`),
  ADD KEY `FK_answer_answer_type` (`ans_answer_type_id`);

--
-- Индексы таблицы `answer_type`
--
ALTER TABLE `answer_type`
  ADD PRIMARY KEY (`answer_type_id`);

--
-- Индексы таблицы `ci_sessions`
--
ALTER TABLE `ci_sessions`
  ADD PRIMARY KEY (`id`,`ip_address`),
  ADD KEY `ci_sessions_timestamp` (`timestamp`);

--
-- Индексы таблицы `docfile`
--
ALTER TABLE `docfile`
  ADD PRIMARY KEY (`docfile_doc_id`);

--
-- Индексы таблицы `document`
--
ALTER TABLE `document`
  ADD PRIMARY KEY (`doc_id`);

--
-- Индексы таблицы `journal`
--
ALTER TABLE `journal`
  ADD PRIMARY KEY (`journal_id`),
  ADD KEY `FK_journal_webrequest` (`journal_webrequest_id`);

--
-- Индексы таблицы `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `project`
--
ALTER TABLE `project`
  ADD PRIMARY KEY (`project_id`),
  ADD UNIQUE KEY `UQ_project_project_code` (`project_code`) USING BTREE,
  ADD UNIQUE KEY `UQ_project_project_name` (`project_name`) USING BTREE;

--
-- Индексы таблицы `project_document`
--
ALTER TABLE `project_document`
  ADD PRIMARY KEY (`pd_id`),
  ADD KEY `FK_project_document_document` (`pd_doc_id`),
  ADD KEY `FK_project_document_project` (`pd_project_id`);

--
-- Индексы таблицы `question`
--
ALTER TABLE `question`
  ADD PRIMARY KEY (`qs_id`),
  ADD KEY `FK_question_user` (`qs_user_id`),
  ADD KEY `FK_question_project` (`qs_project_id`),
  ADD KEY `FK_question_question_category` (`qs_category_id`),
  ADD KEY `IX_question_qs_base_question_id` (`qs_base_question_id`);

--
-- Индексы таблицы `question_category`
--
ALTER TABLE `question_category`
  ADD PRIMARY KEY (`qscat_id`);

--
-- Индексы таблицы `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `user_login_code` (`user_login_code`) USING BTREE,
  ADD KEY `FK_user_project` (`user_project_id`),
  ADD KEY `FK_user_usertype` (`user_usertype_id`);

--
-- Индексы таблицы `usertype`
--
ALTER TABLE `usertype`
  ADD PRIMARY KEY (`usertype_id`);

--
-- Индексы таблицы `webrequest`
--
ALTER TABLE `webrequest`
  ADD PRIMARY KEY (`webrequest_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `answer`
--
ALTER TABLE `answer`
  MODIFY `ans_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `document`
--
ALTER TABLE `document`
  MODIFY `doc_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `journal`
--
ALTER TABLE `journal`
  MODIFY `journal_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `project`
--
ALTER TABLE `project`
  MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `project_document`
--
ALTER TABLE `project_document`
  MODIFY `pd_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `question`
--
ALTER TABLE `question`
  MODIFY `qs_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `webrequest`
--
ALTER TABLE `webrequest`
  MODIFY `webrequest_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `answer`
--
ALTER TABLE `answer`
  ADD CONSTRAINT `FK_answer_answer_type` FOREIGN KEY (`ans_answer_type_id`) REFERENCES `answer_type` (`answer_type_id`),
  ADD CONSTRAINT `FK_answer_question` FOREIGN KEY (`ans_question_id`) REFERENCES `question` (`qs_id`),
  ADD CONSTRAINT `FK_answer_user` FOREIGN KEY (`ans_user_id`) REFERENCES `user` (`user_id`);

--
-- Ограничения внешнего ключа таблицы `journal`
--
ALTER TABLE `journal`
  ADD CONSTRAINT `FK_journal_webrequest` FOREIGN KEY (`journal_webrequest_id`) REFERENCES `webrequest` (`webrequest_id`);

--
-- Ограничения внешнего ключа таблицы `project_document`
--
ALTER TABLE `project_document`
  ADD CONSTRAINT `FK_project_document_document` FOREIGN KEY (`pd_doc_id`) REFERENCES `document` (`doc_id`),
  ADD CONSTRAINT `FK_project_document_project` FOREIGN KEY (`pd_project_id`) REFERENCES `project` (`project_id`);

--
-- Ограничения внешнего ключа таблицы `question`
--
ALTER TABLE `question`
  ADD CONSTRAINT `FK_question_base_question` FOREIGN KEY (`qs_base_question_id`) REFERENCES `question` (`qs_id`),
  ADD CONSTRAINT `FK_question_project` FOREIGN KEY (`qs_project_id`) REFERENCES `project` (`project_id`),
  ADD CONSTRAINT `FK_question_question_category` FOREIGN KEY (`qs_category_id`) REFERENCES `question_category` (`qscat_id`),
  ADD CONSTRAINT `FK_question_user` FOREIGN KEY (`qs_user_id`) REFERENCES `user` (`user_id`);

--
-- Ограничения внешнего ключа таблицы `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `FK_user_project` FOREIGN KEY (`user_project_id`) REFERENCES `project` (`project_id`),
  ADD CONSTRAINT `FK_user_usertype` FOREIGN KEY (`user_usertype_id`) REFERENCES `usertype` (`usertype_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
