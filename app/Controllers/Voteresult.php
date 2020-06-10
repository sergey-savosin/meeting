<?php namespace App\Controllers;

class VoteResult extends BaseController {

	public function index() {

		// 1. get session data
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
		$users_model = model('Users_model');
		$answers_model = model('Answers_model');
		$questions_model = model('Questions_model');

		// 2. find project data
		$project = $projects_model->get_project_by_id($project_id);
		if (!$project) {
			throw new \Exception("Invalid project_id: $project_id");
		}

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
		foreach ($general_questions->getResult() as $question) {
			$answers = $answers_model->fetch_answers_and_users_for_questionid($question->qs_id);
			$general_answers[$question->qs_title] = $this->make_answers_array($answers->getResult());
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
		$page_data['total_voices'] = $total_voices_sum;
		$page_data['half_voices'] = $half_voices_sum;
		$page_data['project_general_answers'] = $project_general_answers;
		$page_data['project_accept_additional_answers'] = $project_accept_additional_answers;
		$page_data['project_additional_answers'] = $project_additional_answers;
		$page_data['question_general_answers'] = $general_answers;
		$page_data['question_accept_additional_answers'] = $accept_additional_answers;
		$page_data['question_additional_answers'] = $additional_answers;

		helper(['url']);
		$top_nav_data['uri'] = $this->request->uri;

		// show a view
		echo view('common/header');
		echo view('nav/top_nav', $top_nav_data);
		echo view('voteresult/view', $page_data);
		echo view('common/footer');

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

	/************
	 Составление массива участников и ответов по одному вопросу
	 Фильтр доп вопросов:
	 Используются только принятые доп вопросы
	 ************/
	function make_additional_answers_array($answers, $half_voices_sum) {
		$qa = array();
		foreach ($answers as $answer) {
			if ($answer->ans_yes > $half_voices_sum) {
				$ans_value = $this->convert_answer_to_string($answer->ans_number);
				$qa[$answer->qs_title] = array('ans_value' => $ans_value,
											'yes_count' => $answer->ans_yes,
											'no_count' => $answer->ans_no,
											'doubt_count' => $answer->and_doubt
										);
			}
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
