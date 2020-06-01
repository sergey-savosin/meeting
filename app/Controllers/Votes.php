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
			$answers_model->fetch_general_answers($project_id, $user_id);
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

	public function result() {
		helper('url');

		$uri = $this->request->uri;
		$resource = $uri->getSegment(3);

		// Return result from service
		$json = json_encode($this->request->getGet('Project'));

		http_response_code(200); // 201: resource created

		header("Content-Type: application/json");
		echo $json;
	}
}