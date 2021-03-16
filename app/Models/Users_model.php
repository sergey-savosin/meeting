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
		$this->db
			->table('user')
			->where('user_id', $userId)
			->delete();
	}
}