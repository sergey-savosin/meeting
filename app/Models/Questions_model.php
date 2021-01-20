<?php namespace App\Models;

use CodeIgniter\Model;

class Questions_model extends Model {
	function __construct() {
		parent::__construct();
	}

	/*****************
	 v4

	 returns resultset
	 *****************/
	function fetch_general_questions($project_id) {
		$query = "SELECT *
		FROM question q
		WHERE q.qs_project_id = ?
		AND q.qs_category_id = 1
		ORDER BY q.qs_id ASC
		";
		$result = $this->db->query($query, array($project_id));

		if ($result) {
			return $result;
		} else {
			return false;
		}
	}

	/**
	* Документы вопроса основной повестки.
	*/
	function fetch_documents_for_questionid($questionId) {
		$query = "SELECT d.doc_id, d.doc_filename, d.doc_caption
		FROM question_document qd
		INNER JOIN document d ON d.doc_id = qd.qd_doc_id
		WHERE qd.qd_question_id = ?
		";
		$result = $this->db->query($query, array($questionId));

		if ($result) {
			return $result;
		} else {
			return false;
		}
	}

	/******************
	 v4

	 returns resultset
	 ******************/
	function fetch_additional_questions_for_user($user_id) {
		$query = "SELECT *
		FROM question q
		WHERE q.qs_user_id = ?
		AND q.qs_category_id = 2
		ORDER BY q.qs_id ASC
		";
		$result = $this->db->query($query, array($user_id));

		if ($result) {
			return $result;
		} else {
			return false;
		}
	}

	/******************
	 v4

	 returns resultset
	 ******************/
	function fetch_additional_questions_for_project($project_id) {
		$query = "SELECT *
		FROM question q
		WHERE q.qs_project_id = ?
		AND q.qs_category_id = 2
		ORDER BY q.qs_id ASC
		";
		$result = $this->db->query($query, array($project_id));

		if ($result) {
			return $result;
		} else {
			return false;
		}
	}

	/******************
	v4

	returns resultset
	******************/
	function fetch_questions_by_project_and_category($project_id, $category_id) {
		$query = "SELECT *
		FROM question q
		WHERE q.qs_project_id = ?
		AND q.qs_category_id = ?
		ORDER BY q.qs_id ASC
		";
		$result = $this->db->query($query, array($project_id, $category_id));

		if ($result) {
			return $result;
		} else {
			return false;
		}
	}


	/******
	v4
	Добавление нового общего вопроса.
	
	Параметры:
		project_id
		title
		comment
	Возвращает:
		Скаляр

	UnitTest
	*/
	function new_general_question($project_id, $title, $comment) {

		$question_data = array ('qs_project_id' => $project_id,
				'qs_title' => $title,
				'qs_category_id' => 1, /* general question */
				'qs_comment' => $comment
			);
		
		$db = \Config\Database::connect();
		if ($db->table('question')->insert($question_data)) {
			return $db->insertID();
		} else {
			return false;
		}
	}

	/***********
	v4
	Добавление дополнительного вопроса и вспомогательного вопроса

	Параметры:
		project_id
		user_id
		title
		comment
	Возвращает:
		Скаляр
	************/
	function new_additional_question($project_id, $user_id, $title, $comment) {
		// ToDo: add transation here
		$question_data = ['qs_project_id' => $project_id,
				'qs_user_id' => $user_id,
				'qs_title' => $title,
				'qs_comment' => $comment,
				'qs_category_id' => 2 /* additional question */
		];

		$db = $this->db;

		$db->transBegin();
		if ($db->table('question')->insert($question_data)) {
			$base_id = $db->insertID();
		} else {
			$db->transRollback();
			return false;
		}

		$secondary_data = ['qs_project_id' => $project_id,
				'qs_user_id' => $user_id,
				'qs_title' => $title,
				'qs_comment' => $comment,
				'qs_category_id' => 3, /* accept additional question */
				'qs_base_question_id' => $base_id
			];

		if ($db->table('question')->insert($secondary_data)) {
			$child_id = $db->insertID();
		} else {
			$db->transRollback();
			return false;
		}

		if ($db->transStatus() === FALSE) {
			$db->transRollback();
		} else {
			$db->transCommit();
		}

		return $base_id;
	}

	/**
	*
	*/
	function link_question_and_document($qs_id, $doc_id) {
		$db = $this->db;

		$data = [
			'qd_question_id' => $qs_id,
			'qd_doc_id' => $doc_id,
			];
		
		if ($db->table('question_document')->insert($data)) {
			return $db->insertID();
		} else {
			return false;
		}
	}

}