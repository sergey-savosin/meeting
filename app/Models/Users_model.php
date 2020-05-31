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

	 returns ID
	*/
	function new_user($projectId, $loginCode, $userTypeId, $canVote) {
		$user_data = array('user_project_id' => $projectId,
						'user_login_code' => $loginCode,
						'user_usertype_id' => $userTypeId,
						'user_can_vote' => $canVote);
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
}