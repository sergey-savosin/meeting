<?php namespace App\Controllers;
use CodeIgniter\I18n\Time;

class Votes extends BaseController {

	public function index() {
		// $wrid = $this->log_webrequest();
		// $this->set_webrequest_id($wrid);

		// get session data
		$session = session();
		$user = $session->get('user_login_code');
		if ($user == FALSE) {
			return redirect()->to(base_url('User/login'));
		}

		$project_id = $session->get('user_project_id');
		if (!$project_id) {
			//$this->log_debug('Votes/index', 'Empty project_id');
			throw new \Exception('Empty project_id');
		}

		$user_id = $session->get('user_id');
		if (!$user_id) {
			//$this->log_debug('Votes/index', 'Empty user_id');
			throw new \Exception('Empty user_id');
		}

		$projects_model = model('Projects_model');
		$answers_model = model('Answers_model');

		$time = Time::now('Europe/Moscow');
		$current_date = $time->toDateTimeString();
		$stage_state = $projects_model->getStageStatus($project_id, $current_date, 'main_agenda');
		// $this->log_debug('stage_state', $stage_state);

		// data for view
		$page_data['questions_query'] = 
			$answers_model->fetch_general_answers($user_id);
		$page_data['accept_additional_question_query'] =
			$answers_model->fetch_accept_additional_answers($user_id);
		$page_data['opened_questions_count'] =
			$answers_model->get_opened_user_questions_count($user_id, 1)->cnt + // general questions
			$answers_model->get_opened_user_questions_count($user_id, 3)->cnt; // accept additional questions
		$page_data['main_agenda_stage_state'] = $stage_state;
		$page_data['current_date'] = $current_date;

		// setup form validation
		foreach ($page_data['questions_query']->getResult() as $qs) {
			$qs_id = $qs->qs_id;
			$val_rules["optradio.$qs_id"] = [
				'label' => "$qs->qs_title",
				'rules' => 'required',
				'errors' => [
					'required' => 'Выберите ответ на вопрос "{field}".'
				]
			];
		}

		foreach ($page_data['accept_additional_question_query']->getResult() as $qs) {
			$qs_id = $qs->qs_id;
			$val_rules["optradio.$qs_id"] = [
				'label' => "$qs->qs_title",
				'rules' => 'required',
				'errors' => [
					'required' => 'Выберите ответ на принятие доп вопроса "{field}".'
				]
			];
		}

		helper(['form', 'url']);

		$top_nav_data['uri'] = $this->request->uri;

		// show view
		if ($this->request->getMethod() === 'get' || !$this->validate($val_rules) ) {

			if ($this->request->getMethod() === 'get') {
				$validation = null;
			} else {
				$validation = $this->validator;
			}

			$page_data['validation'] = $validation;

			echo view('common/header');
			echo view('nav/top_nav', $top_nav_data);
			echo view('votes/view', $page_data);
			echo view('common/footer');
		} else {
			// save data to DB

			foreach ($this->request->getPost('optradio') as $key => $value) {
				$qs_id = $key;
				$ans_num = $value;
				$ans_string = $value;
				$answer_type_id = 1; // yes, no, abstain
				$answer = $answers_model->get_answer($qs_id, $user_id);
				//$this->log_debug('Votes/index', "ans_id: $answer->ans_id");
				if ($answer) {
					//$this->log_debug('Votes/index', "updating answer...");
					$res = $answers_model->update_general_answer($answer->ans_id, $ans_num, $ans_string, $answer_type_id);
				} else {
					// $this->log_debug('Votes/index', "inserting answer...");
					$res = $answers_model->new_general_answer($qs_id, $user_id, $ans_num, $ans_string, $answer_type_id);
				}
				if (!$res) {
					break;
				}
			}
			// go to default page
			if ($res) {
				return redirect()->to(base_url('/'));
			}
		}
	}

	/************************
	 v4

	 WebAPI: generates json vote result
	 ************************/
	public function result() {
		// helper('url');
		// $uri = $this->request->uri;
		// $resource = $uri->getSegment(3);
		$isRequestValid = true;
		$validationErrorText = "";

		// 1. Get request param
		$project_code = $this->request->getGet('ProjectCode');
		if (!isset($project_code)) {
			$validationErrorText.="ProjectCode Get param is empty. ";
			$isRequestValid = false;
		}

		$users_model = model('Users_model');
		$projects_model = model('Projects_model');

		// 2. find project by GET param
		$project = $projects_model->get_project_by_code($project_code);
		if (isset($project_code) && !$project) {
			$validationErrorText.="Project is not found by ProjectCode: $project_code. ";
			$isRequestValid = false;
		}

		if ($project) {
			$project_id = $project->project_id;

			// 3. find all users in this project
			$users = $users_model->get_users_by_projectid($project_id);
			if (!$users) {
				$validationErrorText.="Users are not found by ProjectCode: $project_code. ";
				$isRequestValid = false;
			}
		}

		if (!$isRequestValid)
		{
			$msg = "Invalid Document GET request: $validationErrorText";
			// $this->log_debug('document insert', $msg);

			printf($msg);
			http_response_code(400);
			exit();
		}

		// 3. обработка списка пользователей
		$res = array();

		foreach ($users->getResult() as $u) {
			$ans = 
			$res[$u->user_login_code] = 1;
		}

		// Return result from service
		$json = json_encode($res);

		http_response_code(200); // 201: resource created

		header("Content-Type: application/json");
		echo $json;
	}
}