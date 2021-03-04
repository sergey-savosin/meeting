<?php namespace App\Models;

use CodeIgniter\Model;

class Admins_model extends Model {
	function __construct() {
		parent::__construct();
	}

	/**
	* Добавление нового администратора.
	* 
	* returns ID
	*/
	function new_admin($login, $password, $email, $phone) {
		$data = array('admin_login' => $login,
						'admin_password' => $password,
						'admin_email' => $email,
						'admin_phone' => $phone
					);
		$db = \Config\Database::Connect();
		if ($db->table('admin')->insert($data)) {
			return $db->insertID();
		} else {
			return false;
		}
	}

	/**
	* Find admin by login
	* returns object
	*/
	function get_admin_by_logincode($login) {
		$db = $this->db;
		$query = $db->table('admin')
				->where('admin_login', $login)
				->get();

		if (!$query) {
			return false;
		}

		$row = $query->getRow();
		if (!isset($row)) {
			return false;
		} else {
			return $row;
		}
	}

	/**
	* Delete admin by admin_id
	*/
	function delete_admin($adminId) {
		$this->db
			->table('admin')
			->where('admin_id', $adminId)
			->delete();
	}
}