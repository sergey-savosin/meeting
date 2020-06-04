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
	function new_user($projectId, $loginCode, $userTypeId, $canVote, $votesNumber, $memberName) {
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

	/************
	 v4

	 return resultset
	 ************/
	 function get_users_by_projectid($projectId) {
	 	$query = "SELECT * FROM user u WHERE u.user_project_id = ?";
	 	$db = $this->db;
	 	$result = $db->query($query, array($projectId));

	 	if (!$result) {
	 		return false;
	 	} else {
	 		return $result;
	 	}
	 }

	 /*******************
	  v4
	  Общее кол-во голосов по всем участникам

	  returns scalar
	  *******************/
	 function get_users_total_voices_by_projectid($project_id) {
	 	$query = "SELECT SUM(1) total_count
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
	 		return $row->total_count;
	 	}
	 }
}