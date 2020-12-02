SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `question_document` (
  `qd_id` int(11) NOT NULL,
  `qd_question_id` int(11) NOT NULL,
  `qd_doc_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `question_document`
  ADD PRIMARY KEY (`qd_id`),
  ADD KEY `FK_question_document_document` (`qd_doc_id`),
  ADD KEY `FK_question_document_question` (`qd_question_id`);


ALTER TABLE `question_document`
  MODIFY `qd_id` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `question_document`
  ADD CONSTRAINT `FK_question_document_document` FOREIGN KEY (`qd_doc_id`) REFERENCES `document` (`doc_id`),
  ADD CONSTRAINT `FK_question_document_question` FOREIGN KEY (`qd_question_id`) REFERENCES `question` (`qs_id`);

COMMIT;
