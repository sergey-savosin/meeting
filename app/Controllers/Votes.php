<?php namespace App\Controllers;
use CodeIgniter\I18n\Time;

class Votes extends BaseController {

	public function index() {
		log_message('info', 'Votes::index started.');

		// get session data
		$session = session();
		$user = $session->get('user_login_code');
		if ($user == FALSE) {
			log_message('info', 'Votes::index. Empty user_login_code (session variable).');
			return redirect()->to(base_url('User/login'));
		}

		$project_id = $session->get('user_project_id');
		if (!$project_id) {
			log_message('info', 'Votes::index. Empty project_id (session variable).');
			throw new \Exception('Empty project_id');
		}

		$user_id = $session->get('user_id');
		if (!$user_id) {
			log_message('info', 'Votes::index. Empty user_id (session variable).');
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
				log_message('info', 'Votes::index - get branch');
				$validation = null;
			} else {
				$validation = $this->validator;
				log_message('info', 'Votes::index - validation: '
					.$validation->listErrors('my_list'));
			}

			$page_data['validation'] = $validation;

			return view('common/header').
				view('nav/top_nav', $top_nav_data).
				view('votes/view', $page_data).
				view('common/footer');
		} else {
			log_message('info', "Votes::index posted optradio");
			// save data to DB
			foreach ($this->request->getVar('optradio') as $key => $value) {
				$qs_id = $key;
				$ans_num = $value;
				$ans_string = $value;
				$answer_type_id = 1; // yes, no, abstain
				$answer = $answers_model->get_answer($qs_id, $user_id);
				// log_message('info', "Votes::index key-value: $key, $value");

				if ($answer) {
					// log_message('info', "Votes::index updating answer: $key = $value.");
					$res = $answers_model->update_general_answer($answer->ans_id, $ans_num, $ans_string, $answer_type_id);
				} else {
					// log_message('info', "Votes::index inserting answer: $key = $value.");
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
		$project_name = $this->request->getGet('ProjectName');
		if (!isset($project_name)) {
			$validationErrorText.="ProjectName Get param is empty. ";
			$isRequestValid = false;
		}

		$project_name = urldecode($project_name);

		$users_model = model('Users_model');
		$projects_model = model('Projects_model');

		// 2. find project by GET param
		$project = $projects_model->get_project_by_name($project_name);
		if (isset($project_name) && !$project) {
			$validationErrorText.="Project is not found by ProjectName: $project_name. ";
			$isRequestValid = false;
		}

		if ($project) {
			$project_id = $project->project_id;
		}

		if (!$isRequestValid)
		{
			$msg = "Invalid Document GET request: $validationErrorText";
			// $this->log_debug('document insert', $msg);

			print($msg);
			http_response_code(400);
			exit();
		}

		// 3. обработка данных
		$projects_model = model('Projects_model');
		$users_model = model('Users_model');
		$answers_model = model('Answers_model');
		$questions_model = model('Questions_model');

		// 3. get questions list for current project
		$general_questions = $questions_model->fetch_questions_by_project_and_category($project_id, 1);
		$accept_additional_questions = $questions_model->fetch_questions_by_project_and_category($project_id, 3);
		$additional_questions = $questions_model->fetch_questions_by_project_and_category($project_id, 2);

		$total_voices_sum = $users_model->get_users_total_voices_sum_by_projectid($project_id);
		if (!$total_voices_sum) {
			throw new \Exception("Can't calculate total voices count for ProjectId: $project_id");
		}

		$general_answers = array();
		$accept_additional_answers = array();
		$additional_answers = array();
		// Половина голосов - нужно для принятия вопроса
		$half_voices_sum = $total_voices_sum /2;

		// 4a. prepare voting results for general questions
		$project_general_answers = 
			$answers_model->calc_answers_for_projectid($project_id, 1, $total_voices_sum); // 1 = general question type
		$project_accept_additional_answers = 
			$answers_model->calc_answers_for_projectid($project_id, 3, $total_voices_sum); // 3 = accept additional question type
		$project_additional_answers = 
			$answers_model->calc_answers_for_projectid($project_id, 2, $total_voices_sum); // 2 = additional question type

		// 4. get question and answer details for every users in current project
		// 4.1 general question answers
		$num_line = 1;
		foreach ($general_questions->getResult() as $question) {
			$answers = $answers_model->fetch_answers_and_users_for_questionid($question->qs_id);
			$general_answers[$question->qs_title] = $this->make_answers_array($answers->getResult());
			$num_line += 1;
		}

		// 4.2 accept additional questions answers
		foreach ($accept_additional_questions->getResult() as $question) {
			$answers = $answers_model->fetch_answers_and_users_for_questionid($question->qs_id);
			$accept_additional_answers[$question->qs_title] = $this->make_answers_array($answers->getResult());
		}

		// 4.3 additional questions answers
		foreach ($additional_questions->getResult() as $question) {
			$answers = $answers_model->fetch_answers_and_users_for_questionid($question->qs_id);
			$additional_answers[$question->qs_title] = 
				$this->make_answers_array($answers->getResult());
		}

		// 5. Prepare data for a view
		$page_data['project'] = $project;
		$page_data['project_general_answers'] = $project_general_answers;
		$page_data['project_accept_additional_answers'] = $project_accept_additional_answers;
		$page_data['project_additional_answers'] = $project_additional_answers;
		$page_data['question_general_answers'] = $general_answers;
		$page_data['question_accept_additional_answers'] = $accept_additional_answers;
		$page_data['question_additional_answers'] = $additional_answers;

		// Return result from service
		$json = json_encode($page_data);

		http_response_code(200); // 201: resource created

		header("Content-Type: application/json");
		echo $json;
	}

	/*************
	 Составление массива участников и ответов по одному вопросу
	************/
	function make_answers_array($answers) {
		$qa = array();
		foreach ($answers as $answer) {
			$ans_value = $this->convert_answer_to_string($answer->ans_number);
			$qa[$answer->user_member_name] = array('ans_value' => $ans_value,
												'user_voices' => $answer->user_votes_number);
		}
		return $qa;
	}

	/*************
	 Преобразование номера ответа в строку
	*************/
	function convert_answer_to_string($ans_number) {
		switch ($ans_number) {
			case null:
				return '--';
				break;
			case 0:
				return 'Да';
				break;
			
			case 1:
				return 'Нет';
				break;

			case 2:
				return 'Воздержался';
				break;

			default:
				return 'Ошибка интерпретации ответа';
				break;
		}
	}
}