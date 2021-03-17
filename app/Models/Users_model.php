<?php namespace App\Models;

use CodeIgniter\Model;

class Users_model extends Model {
	function __construct() {
		parent::__construct();
	}

	/******
	v4
	Добавление нового пользователя.
	
	Параметры:
	 projectId
	 userCode
	 userTypeId
	 canVote
	 votesNumber
	 memberName

	 returns ID
	*/
	function new_user($projectId, $loginCode, $userTypeId, 
		$canVote, $votesNumber, $memberName) {
		$user_data = array('user_project_id' => $projectId,
						'user_login_code' => $loginCode,
						'user_usertype_id' => $userTypeId,
						'user_can_vote' => $canVote,
						'user_votes_number' => $votesNumber,
						'user_member_name' => $memberName
					);
		$db = \Config\Database::Connect();
		if ($db->table('user')->insert($user_data)) {
			return $db->insertID();
		} else {
			return false;
		}
	}

	/*************
	 v4

	 returns object
	 *************/
	function get_usertype_by_usertypename($usertype_name) {
		$query = "SELECT * FROM usertype ut WHERE ut.usertype_name = ?";
		$db = \Config\Database::connect();
		$result = $db->query($query, array($usertype_name));
		
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

	/***************
	 v4

	 returns object
	 ***************/
	function get_user_by_logincode($loginCode) {
		$query = "SELECT * FROM user u WHERE u.user_login_code = ?";
		$db = \Config\Database::connect();
		$result = $db->query($query, array($loginCode));

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
	* v4
	*
	* returns resultset
	*/
	function get_users_by_projectid($projectId) {

		$db = $this->db;

		$query = $db->table('user u')
			->join('usertype ut', 'ut.usertype_id = u.user_usertype_id')
			->where('u.user_project_id', $projectId)
			->get();

		if (!$query) {
			return false;
		} else {
			return $query;
		}
	}

	/*******************
	v4
	Общее кол-во голосов по всем участникам

	returns scalar
	*******************/
	function get_users_total_voices_sum_by_projectid($project_id) {
		$query = "SELECT SUM(IFNULL(u.user_votes_number, 1)) total_sum
		FROM user u
		WHERE u.user_project_id = ?";
		$result = $this->db->query($query, array($project_id));

		if (!$result) {
			return false;
		}

		$row = $result->getRow();
		if (!$row) {
			return false;
		} else {
			//если нет пользователей, то лучше вернуть 1, чтобы на 0 не было деления
			return $row->total_sum ?? 1;
		}
	}

	/**
	* delete user
	*/
	function delete_user($userId) {
		$db = \Config\Database::connect();

		$db->transBegin();

		// delete answers
		$this->delete_answer_for_user($db, $userId);

		// delete accept additional questions
		$this->delete_acceptAdditionalQuestion_for_user($db, $userId);

		// delete additional question questionDocuments
		$this->delete_additionalQuestion_questionDocument_for_user($db, $userId);
		// delete additional question docfiles
		$this->delete_additionalQuestion_docfile_for_user($db, $userId);
		// delete additional question documents
		$this->delete_additionalQuestion_document_for_user($db, $userId);
		// delete additional questions
		$this->delete_additionalQuestion_question_for_user($db, $userId);


		// delete user
		$db->table('user')
			->where('user_id', $userId)
			->delete();

		$db->transCommit();

		return true;
	}

	/**
	* delete answers for userId
	*/
	private function delete_answer_for_user($db, $userId) {
		$db->table('answer')
			->where('ans_user_id', $userId)
			->delete();
	}

	/**
	* delete accept additional question for userId
	*/
	private function delete_acceptAdditionalQuestion_for_user($db, $userId) {
		$db->table('question')
			->where('qs_category_id', 3) //accept_additional_question
			->where('qs_user_id', $userId)
			->delete();
	}

	/**
	* delete additional-questions docfile for userId
	*/
	private function delete_additionalQuestion_docfile_for_user($db, $userId) {
		$builder = $db
			->table('docfile')
			->whereIn('docfile_doc_id', function($builder) use ($userId) {
				return $builder
					->select('d.doc_id')
					->from('question q')
					->join('question_document qd', 'qd.qd_question_id = q.qs_id')
					->join('document d', 'd.doc_id = qd.qd_doc_id')
					->where('q.qs_category_id', 2) //additional question
					->where('q.qs_user_id', $userId)
				;
			});
		$this->build_delete($db, $builder);
	}

	/**
	* delete additional-questions document for userId
	*/
	private function delete_additionalQuestion_document_for_user($db, $userId) {
		$builder = $db
			->table('document')
			->whereIn('doc_id', function($builder) use ($userId) {
				return $builder
					->select('qd.qd_doc_id')
					->from('question q')
					->join('question_document qd', 'qd.qd_question_id = q.qs_id')
					->where('q.qs_category_id', 2) // additional question
					->where('q.qs_user_id', $userId)
				;
			});
		$this->build_delete($db, $builder);
	}

	/**
	* delete additional-questions questions_document for user
	*/
	private function delete_additionalQuestion_questionDocument_for_user($db, $userId) {
		$builder = $db
			->table('question_document')
			->whereIn('qd_question_id', function($builder) use ($userId) {
				return $builder
					->select('q.qs_id')
					->from('question q')
					->where('q.qs_category_id', 2) // additional question
					->where('q.qs_user_id', $userId)
				;
			});
		$this->build_delete($db, $builder);
	}

	/**
	* delete additional-question question for user
	*/
	private function delete_additionalQuestion_question_for_user($db, $userId) {
		$db->table('question')
			->where('qs_category_id', 2) //additional question
			->where('qs_user_id', $userId)
			->delete();
	}

	/**
	* Make delete query from select query. And run it.
	*/
	private function build_delete($db, $builder) {
		$sql = $builder->getCompiledSelect();
		$len = strlen("SELECT *");
		$sql = substr_replace($sql, 'DELETE', 0, $len);

		//log_message('info', "[build_delete] sql: $sql");
		$db->query($sql);
	}

}