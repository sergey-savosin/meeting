<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Webrequests_model extends CI_Model {
	function __construct() {
		parent::__construct();
	}

	/******
	Добавление нового запроса.
	
	Параметры:
	 method
	 uri
	 body
	 ip?
	 webclient?
	*/
	function new_webrequest($method, $uri, $body, $ip, $webclient) {
		// добавление веб-запроса
		$data = array ('webrequest_method' => $method,
				'webrequest_uri' => $uri,
				'webrequest_body' => $body);
		if ($this->db->insert('webrequest', $data)) {
			return $this->db->insert_id();
		} else {
			return false;
		}
	}
}