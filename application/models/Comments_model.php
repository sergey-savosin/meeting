<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Comments_model extends CI_Model {
	function __construct() {
		parent::__construct();
	}

	function fetch_comments($ds_id) {
		$query = "SELECT *
		FROM comments c
		INNER JOIN discussions d ON d.ds_id = c.ds_id
		INNER JOIN users u ON u.usr_id = c.usr_id
		WHERE d.ds_id = ?
		AND c.cm_is_active = '1'
		ORDER BY c.cm_created_at DESC
		";
		$result = $this->db->query($query, array($ds_id));

		if ($result) {
			return $result;
		} else {
			return false;
		}
	}

	function new_comment($data) {
		// try to seek user by email
		$usr_email = $data['usr_email'];
		$query = "SELECT *
		FROM users u
		WHERE u.usr_email = ?
		";
		$result = $this->db->query($query, array($usr_email));

		if ($result->num_rows() > 0) {
			foreach ($result->result() as $rows) {
				$data['usr_id'] = $rows->usr_id;
			}
		} else {
			$password = '123';//random_string('alnum', 16);
			$hash = $password; //$this->encrypt->sha1($password);

			$user_data = array('usr_email' => $data['usr_email'],
								'usr_name' => $data['usr_name'],
								'usr_is_active' => '1',
								'usr_level' => '1',
								'usr_hash' => $hash);
			if ($this->db->insert('users', $user_data)) {
				$data['usr_id'] = $this->db->insert_id();
				// send email this password?
			}
		}

		$comment_data = array('cm_body' => $data['cm_body'],
							'ds_id' => $data['ds_id'],
							'cm_is_active' => '1',
							'usr_id' => $data['usr_id']);

		if ($this->db->insert('comments', $comment_data)) {
			return $this->db->insert_id();
		} else {
			return false;
		}
	}

	function flag($cm_id) {
		$this->db->where('cm_id', $cm_id);
		if ($this->db->update('comments', array('cm_is_active' => '0'))) {
			return true;
		} else {
			return false;
		}
	}
}