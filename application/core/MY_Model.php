<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class MY_Model extends CI_Model {
	public $_webrequest_id;

	function __construct() {
		parent::__construct();

		$this->_webrequest_id = false;
	}

	// log message to journal
	function log_journal($webrequest_id, $level, $module, $message) {
		log_message($level, "[$webrequest_id: $module] $message");
	}


	// short form for logging debug messages
	function log_debug($module, $message)
	{
		//ToDo: check _webrequest_id for false
		$this->log_journal($this->_webrequest_id, 'debug', $module, $message);
	}

	function set_webrequest_id($id) {
		$this->_webrequest_id = $id;
	}
}