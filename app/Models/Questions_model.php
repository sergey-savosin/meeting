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


	/******
	v4
	Добавление нового общего вопроса.
	
	Параметры:
		project_id
		title
	Возвращает:
		Скаляр
	*/
	function new_general_question($project_id, $title) {

		$question_data = array ('qs_project_id' => $project_id,
				'qs_title' => $title,
				'qs_category_id' => 1); /* general question */
		
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
		title
		user_id
	Возвразает:
		Скаляр
	************/
	function new_additional_question($project_id, $title, $user_id) {
		// ToDo: add transation here
		$question_data = ['qs_project_id' => $project_id,
				'qs_user_id' => $user_id,
				'qs_title' => $title,
				'qs_category_id' => 2 /* additional question */
		];

		$db = $this->db;

		$db->transStart();
		if ($db->table('question')->insert($question_data)) {
			$base_id = $db->insertID();
		} else {
			$db->transRollback();
			return false;
		}

		$secondary_data = ['qs_project_id' => $project_id,
				'qs_user_id' => $user_id,
				'qs_title' => $title,
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

}