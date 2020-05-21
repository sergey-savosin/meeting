<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Documents extends MY_Controller {
	function __construct() {
		parent::__construct();
		$this->load->model('Documents_model');
		$this->load->model('Questions_model');
		$this->load->model('Projects_model');
		$this->load->helper('date');
	}

	public function index() {
		$wrid = $this->log_webrequest();
		$this->set_webrequest_id($wrid);

		// get session data
		$user = $this->session->userdata('user_login_code');
		if ($user == FALSE) {
			redirect('user/login');
		}

		$project_id = $this->session->userdata('user_project_id');
		if (!$project_id) {
			$this->log_debug('Documents/index', 'Empty project_id');
			show_error('Empty project_id', 500);
		}

		$user_id = $this->session->userdata('user_id');
		if (!$user_id) {
			$this->log_debug('Document/index', 'Empty user_id');
			show_error('Empty user_id', 500);
		}


		$current_date = mdate('%Y-%m-%d %H:%i:%s', now('Europe/Moscow'));
		$stage_state = $this->Projects_model->getStageStatus($project_id, $current_date, 'acquaintance');
		$this->log_debug('stage_state', $stage_state);

		// load view
		$page_data['documents_query'] = 
			$this->Documents_model->fetch_documents($project_id);
		$page_data['general_questions_query'] = 
			$this->Questions_model->fetch_general_questions($project_id);
		$page_data['additional_questions_query'] =
			$this->Questions_model->fetch_additional_questions_for_project($project_id);
		$page_data['acquaintance_stage_state'] = $stage_state;
		$page_data['current_date'] = $current_date;

		$this->load->view('common/header');
		$this->load->view('nav/top_nav');
		$this->load->view('documents/view', $page_data);
		$this->load->view('common/footer');
	}



}