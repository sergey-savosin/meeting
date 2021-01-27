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

	/**
	* Получить ProjectId по qs_id
	* returns row (object)
	*/
	function get_project_by_question_id($qs_id) {
		$query = "SELECT p.*
		FROM question q 
		INNER JOIN project p on p.project_id = q.qs_project_id
		WHERE q.qs_id = ?";
		
		$db = $this->db;;
		$result = $db->query($query, array($qs_id));
		
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

	/**
	* Удаление вопроса Основное повестки и связанного с ним документа
	*/
	function delete_general_question($qs_id) {
		// docfile
		// document
		// question_document
		// base_question?
		// answer
		// question
		$db = \Config\Database::connect();

		// get doc_id
		$query = "SELECT qd.qd_doc_id
		FROM question_document qd
		WHERE qd.qd_question_id = ?
		";
		$result = $this->db->query($query, array($qs_id));

		$db->transBegin();

		$row = $result->getRow();
		
		// 1. delete document
		if (isset($row)) {
			$docId = $row->qd_doc_id;

			$builder = $db->table('question_document');
			$builder->where('qd_doc_id', $docId)
				->delete();

			$builder = $db->table('docfile');
			$builder->where('docfile_doc_id', $docId)
				->delete();
			$builder = $db->table('document');
			$builder->where('doc_id', $docId)
				->delete();
			log_message('info', '[question_model::delete_general_question] qd deleted. doc_id:'.$docId);
		} else {
			log_message('info', '[question_model::delete_general_question] No qd row.');
		}

		// delete answers
		$builder = $db->table('answer');
		$builder->where('ans_question_id', $qs_id)
			->delete();

		// delete question
		$builder = $db->table('question');
		$builder->where('qs_id', $qs_id)
			->delete();

		$db->transComplete();
	}

}