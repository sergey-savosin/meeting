<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Votes extends MY_Controller {
	function __construct() {
		parent::__construct();
		$this->load->model('Questions_model');
		$this->load->model('Answers_model');
		$this->load->model('Projects_model');
		$this->load->helper('date');
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<div class="alert alert-danger">',
			'</div>');
	}

	public function index() {
		$wrid = $this->log_webrequest();
		$this->set_webrequest_id($wrid);

		// log POST data
		if (1==1 && $this->input->post()) {
			$postdata = $this->input->post();
			// $this->log_debug('Votes/index', json_encode($postdata));
			ob_start();
			var_dump($postdata);
			$result = ob_get_clean();
			$this->log_debug('Votes/index', $result);
		} else {
			$this->log_debug('Votes/index', 'no POST data');
		}

		// get session data
		$user = $this->session->userdata('user_login_code');
		if ($user == FALSE) {
			redirect('user/login');
		}

		$project_id = $this->session->userdata('user_project_id');
		if (!$project_id) {
			$this->log_debug('Votes/index', 'Empty project_id');
			show_error('Empty project_id', 500);
		}

		$user_id = $this->session->userdata('user_id');
		if (!$user_id) {
			$this->log_debug('Votes/index', 'Empty user_id');
			show_error('Empty user_id', 500);
		}

		$current_date = mdate('%Y-%m-%d %H:%i:%s', now('Europe/Moscow'));
		$stage_state = $this->Projects_model->getStageStatus($project_id, $current_date, 'main_agenda');
		$this->log_debug('stage_state', $stage_state);

		// data for view
		$page_data['questions_query'] = 
			$this->Answers_model->fetch_general_answers($project_id, $user_id);
		$page_data['accept_additional_question_query'] =
			$this->Answers_model->fetch_accept_additional_answers($user_id);
		$page_data['opened_questions_count'] =
			$this->Answers_model->get_opened_user_questions_count($user_id, 1)->cnt + // general questions
			$this->Answers_model->get_opened_user_questions_count($user_id, 3)->cnt; // accept additional questions
		$page_data['main_agenda_stage_state'] = $stage_state;
		$page_data['current_date'] = $current_date;

		// setup form validation
		foreach ($page_data['questions_query']->result() as $qs) {
			$qs_id = $qs->qs_id;
			$this->form_validation->set_rules("optradio[$qs_id]",
				'Вопрос "'.$qs->qs_title.'"',
				'required');
		}

		foreach ($page_data['accept_additional_question_query']->result() as $qs) {
			$qs_id = $qs->qs_id;
			$this->form_validation->set_rules("optradio[$qs_id]",
				'Вопрос "'.$qs->qs_title.'"',
				'required');
		}

		// show view
		if ($this->form_validation->run() == FALSE) {
			$this->load->view('common/header');
			$this->load->view('nav/top_nav');
			$this->load->view('votes/view', $page_data);
			$this->load->view('common/footer');
		} else {
			// save data to DB

			foreach ($postdata['optradio'] as $key => $value) {
				$qs_id = $key;
				$ans_num = $value;
				$ans_string = $value;
				$answer_type_id = 1; // yes, no, abstain
				$answer = $this->Answers_model->get_answer($qs_id, $user_id);
				$this->log_debug('Votes/index', "ans_id: $answer->ans_id");
				if ($answer) {
					$this->log_debug('Votes/index', "updating answer...");
					$res = $this->Answers_model->update_general_answer($answer->ans_id, $ans_num, $ans_string, $answer_type_id);
				} else {
					$this->log_debug('Votes/index', "inserting answer...");
					$res = $this->Answers_model->new_general_answer($qs_id, $user_id, $ans_num, $ans_string, $answer_type_id);
				}
				if (!$res) {
					break;
				}
			}
			// go to default page
			if ($res) {
				redirect('/');
			}
		}
	}



}