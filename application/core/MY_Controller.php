<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class MY_Controller extends CI_Controller {
	public $_webrequest_id;

	function __construct() {
		parent::__construct();
		$this->load->helper('form');
		$this->load->helper('url');
		$this->load->helper('security');
		$this->load->helper('language');
		// Load lang file
		$this->lang->load('en_admin', 'english');

		$this->load->model('Webrequests_model');
		$this->_webrequest_id = false;
	}

	// Get update parameters
	function getPostData() {
		if ($this->input->method() != "post"){
			return false;
		}

		$body = $this->input->raw_input_stream;
		$content_type = $this->input->get_request_header('Content-Type');

		switch(strtolower($content_type))
		{
			case "application/json":
				$data = json_decode($body);
				break;
			case "text/xml":
				return false;
				break;
			default:
				return false;
				break;
		}

		return $data;
	}

	// create and get new web request id
	function log_webrequest() {
		$method = $this->input->method();
		$body = $this->input->raw_input_stream;
		$resource = $this->uri->segment(1) ?? 'default';
		//log_message('debug', 'uri: '.json_encode($this->uri));
		
		$webrequest_id = $this->Webrequests_model->new_webrequest($method, $resource, $body, null, null);
		
		return $webrequest_id;
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