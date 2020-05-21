<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Users_model extends CI_Model {
	function __construct() {
		parent::__construct();
	}

	/******
	Добавление нового пользователя.
	
	Параметры:
	 projectId
	 userCode
	 userTypeId
	 canVote
	*/
	function new_user($projectId, $loginCode, $userTypeId, $canVote) {
		$user_data = array('user_project_id' => $projectId,
						'user_login_code' => $loginCode,
						'user_usertype_id' => $userTypeId,
						'user_can_vote' => $canVote);
		if ($this->db->insert('user', $user_data)) {
			return $this->db->insert_id();
		} else {
			return false;
		}
	}

	function get_usertype_by_usertypename($usertype_name) {
		$query = "SELECT * FROM usertype ut WHERE ut.usertype_name = ?";
		$result = $this->db->query($query, array($usertype_name));
		
		if ($result) {
			return $result;
		} else {
			return false;
		}
	}

	function get_first_user_by_logincode($loginCode) {
		$query = "SELECT * FROM user u WHERE u.user_login_code = ?";
		$result = $this->db->query($query, array($loginCode));

		if ( (!$result) || ($result->num_rows() == 0) ) {
			return false;
		} else {
			return $result->result()[0];
		}
	}

	function get_usertypeid_by_usertypename($usertype_name) {
		$usertype = $this->get_usertype_by_usertypename($usertype_name);

		if ( (!$usertype) || ($usertype->num_rows() == 0) ) {
			return false;
		}

		return $usertype->result()[0]->usertype_id;
	}

}