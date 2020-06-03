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

		// 2. find project data
		$project = $projects_model->get_project_by_id($project_id);
		if (!$project) {
			throw new \Exception("Invalid project_id: $project_id");
		}

		// 3. get users list for current project
		$users = $users_model->get_users_by_projectid($project_id);
		if (!$users) {
			throw new \Exception("Users are not found by ProjectId: $project_id");
		}

		$general_answers = array();
		// 4. get question and answer details for every users in current project
		foreach ($users->getResult() as $user) {
			// 4.1 general question answers
			$answers = $answers_model->fetch_general_answers($user->user_id);
			$qa = array();
			foreach ($answers->getResult() as $answer) {
				$ans_value = $this->convert_answer_to_string($answer->ans_number);
				$qa[$answer->qs_title] = $ans_value;
			}
			$general_answers[$user->user_member_name] = $qa;

			// 4.2 accept additional questions answers
			// ToDo
			
			// 4.3 additional questions answers
			// ToDo
		}

		// 5. Prepare data for a view
		$page_data['project'] = $project;
		$page_data['users_list'] = $total_answers;

		helper(['url']);
		$top_nav_data['uri'] = $this->request->uri;

		// show a view
		echo view('common/header');
		echo view('nav/top_nav', $top_nav_data);
		echo view('voteresult/view', $page_data);
		echo view('common/footer');

	}

	function convert_answer_to_string($ans_number) {
		switch ($ans_number) {
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
