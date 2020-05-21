<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class AdditionalQuestions extends MY_Controller {
	function __construct() {
		parent::__construct();
		$this->load->model('Questions_model');
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<div class="alert alert-danger">',
			'</div>');
	}

	public function index() {
		$wrid = $this->log_webrequest();
		$this->set_webrequest_id($wrid);

		// log POST data
		if (1==1 && $this->input->post()) {
			$data = $this->input->post();
			$qs_title_value = $data['qs_title'];

			// $this->log_debug('AdditionalQuestions/index', json_encode($data));
			ob_start();
			var_dump($data);
			$result = ob_get_clean();
			$this->log_debug('AdditionalQuestions/index', $result);
		} else {
			$this->log_debug('AdditionalQuestions/index', 'no POST data');
		}

		// get session data
		$user = $this->session->userdata('user_login_code');
		if ($user == FALSE) {
			redirect('user/login');
		}

		$project_id = $this->session->userdata('user_project_id');
		if (!$project_id) {
			$this->log_debug('AdditionalQuestions/index', 'Empty project_id');
			show_error('Empty project_id', 500);
		}

		$user_id = $this->session->userdata('user_id');
		if (!$user_id) {
			$this->log_debug('AdditionalQuestions/index', 'Empty user_id');
			show_error('Empty user_id', 500);
		}

		// data for view
		$page_data['additional_questions_query'] = 
			$this->Questions_model->fetch_additional_questions_for_user($user_id);

		// setup form validation
		$this->form_validation->set_message('required', 'Укажите значение в поле {field}.');
		$this->form_validation->set_rules("qs_title",
				$this->lang->line('additional_questions_title'),
				'required');

		// show view
		if ($this->form_validation->run() == FALSE) {
			$this->load->view('common/header');
			$this->load->view('nav/top_nav');
			$this->load->view('additionalquestions/view', $page_data);
			$this->load->view('common/footer');
		} else {
			// save data to DB
			$res = $this->Questions_model->new_additional_question($project_id, $qs_title_value, $user_id);
			if ($res) {
				// go to default page
				redirect('/additionalquestions');
			} else {
				// show error
			}
		}
	}



}