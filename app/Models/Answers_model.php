<?php namespace App\Models;

use CodeIgniter\Model;

class Answers_model extends Model {
	function __construct() {
		parent::__construct();
	}

	/**********
	Список ответов на вопросы основной повестки	для указанного пользователя.
	
	Returns resultset.
	***********/
	function fetch_general_answers($user_id) {
		$query = "SELECT *
		FROM user u
		INNER JOIN question q
			ON q.qs_project_id = u.user_project_id
		LEFT JOIN answer a
			ON q.qs_id = a.ans_question_id
			AND a.ans_user_id = u.user_id
		WHERE q.qs_category_id = 1
		AND u.user_id = ?
		ORDER BY q.qs_id ASC
		";
		$result = $this->db->query($query, array($user_id));

		if ($result) {
			return $result;
		} else {
			return false;
		}
	}

	/****************
	Список вспомогательных вопросов-ответов для принятия доп вопросов.
	Returns resultset.
	*****************/
	function fetch_accept_additional_answers($user_id) {
		$query = "SELECT *
		FROM user u
		INNER JOIN question q
			ON q.qs_project_id = u.user_project_id
		LEFT JOIN answer a
			ON a.ans_question_id = q.qs_id
			AND a.ans_user_id = u.user_id
		WHERE u.user_id = ?
			AND q.qs_category_id = 3
		ORDER BY q.qs_id ASC
		";
		$result = $this->db->query($query, array ($user_id));

		if ($result) {
			return $result;
		} else {
			return false;
		}
	}

	/**************
	Список ответов на вопросы дополнительной повестки
	Returns resultset.
	***************/
	function fetch_additional_answers($project_id, $user_id) {
		$query = "SELECT *
		FROM question q
		LEFT JOIN answer a
			ON q.qs_id = a.ans_question_id
			AND a.ans_user_id = ?
		WHERE q.qs_category_id = 2
		and q.qs_project_id = ?
		ORDER BY q.qs_id ASC
		";
		$result = $this->db->query($query, array($user_id, $project_id));

		if ($result) {
			return $result;
		} else {
			return false;
		}
	}

	/******
	Добавление нового ответа на вопрос основной повестки.
	
	Параметры.
	Returns new id.
	*/
	function new_general_answer($question_id, $user_id, $ans_number, $ans_string, $answer_type_id) {

		$data = array ('ans_question_id' => $question_id,
				'ans_user_id' => $user_id,
				'ans_number' => $ans_number,
				'ans_string' => $ans_string,
				'ans_answer_type_id' => $answer_type_id);

		$db = \Config\Database::connect();
		if ($db->table('answer')->insert($data)) {
			return $db->insertID();
		} else {
			return false;
		}
	}

	/*******************
	 function returns object {ans_id}
	 *******************/
	function get_answer($qs_id, $user_id) {
		$query = "SELECT ans_id FROM answer a WHERE ans_question_id = ? and ans_user_id = ?";
		$result = $this->db->query($query, array($qs_id, $user_id));
		if (!$result) {
			return false;
		}

		$row = $result->getRow();
		if (!isset($row)) {
			return false;
		}
		return $row;
	}

	/********************
	 @user_id
	 @qs_category_id: 1 - general, 2 - additional, 3 - accept additional

	 function returns object {cnt}
	 ********************/
	function get_opened_user_questions_count($user_id, $qs_category_id) {
		$query = "SELECT count(1) cnt
			FROM user u
			INNER JOIN question q
				ON u.user_project_id = q.qs_project_id
			LEFT JOIN answer a
				ON a.ans_question_id = q.qs_id
				AND a.ans_user_id = u.user_id
			WHERE u.user_id = ?
			AND q.qs_category_id = ?
			AND a.ans_id IS NULL
			ORDER BY q.qs_id";
		$result =$this->db->query($query, array($user_id, $qs_category_id));
		if (!$result) {
			return false;
		}
		
		$row = $result->getRow();
		if (!isset($row)) {
			return false;
		} else {
			return $row;
		}
	}

	/************************
	 Список доп вопросов с результатом голосования:
	 - кол-во ЗА
	 - кол-во ПРОТИВ
	 - всего дано голосов
	 - ID доп вопроса

	 returns resultset
	 ************************/
	function fetch_additional_answers_with_votes($project_id, $user_id) {
		$query = "SELECT
			SUM(CASE WHEN a.ans_number = 0 THEN 1 ELSE 0 END) ans_yes, 
			SUM(CASE WHEN a.ans_number = 1 THEN 1 ELSE 0 END) ans_no,
			COUNT(1) ans_total,
			bq.qs_id,
			bq.qs_title,
			ba.ans_number
		FROM question q 
		JOIN answer a 
			ON a.ans_question_id = q.qs_id 
		JOIN question bq
			ON bq.qs_id = q.qs_base_question_id
		LEFT JOIN answer ba
			ON bq.qs_id = ba.ans_question_id
			AND ba.ans_user_id = ?
		WHERE q.qs_project_id=?
			AND q.qs_category_id = 3
		GROUP BY bq.qs_id, bq.qs_title
		ORDER BY bq.qs_id";

		$result = $this->db->query($query, array ($user_id, $project_id));
		
		if (!$result) {
			return false;
		} else {
			return $result;
		}
	}

	/***********************
	 Returns boolean
	 ***********************/
	function update_general_answer($ans_id, $ans_number, $ans_string, $answer_type_id) {
		$data = [
			'ans_number' => $ans_number,
			'ans_string' => $ans_string,
			'ans_answer_type_id' => $answer_type_id
		];

		$db = \Config\Database::connect();
		$builder = $db->table('answer');
		$builder->where('ans_id', $ans_id);
		if ($builder->update($data)){
			return true;
		} else {
			return false;
		}
	}

}