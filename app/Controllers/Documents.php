<?php namespace App\Controllers;
use CodeIgniter\I18n\Time;

class Documents extends BaseController {

	public function index() {
		//$wrid = $this->log_webrequest();
		//$this->set_webrequest_id($wrid);

		// get session data
		$session = session();
		$user = $session->get('user_login_code');
		if (1==1 && $user == FALSE) {
			return redirect()->to(base_url('User/login'));
		}

		$project_id = $session->get('user_project_id');
		if (!$project_id) {
			//$this->log_debug('Documents/index', 'Empty project_id');
			throw new \Exception('Empty project_id');
		}

		$user_id = $session->get('user_id');
		if (!$user_id) {
			//$this->log_debug('Document/index', 'Empty user_id');
			throw new \Exception('Empty user_id');
		}

		$docs_model = model('Documents_model');
		$projects_model = model('Projects_model');
		$questions_model = model('Questions_model');

		$time = Time::now('Europe/Moscow');
		$current_date = $time->toDateTimeString();
		$stage_state = $projects_model->getStageStatus($project_id, $current_date, 'acquaintance');
		//$this->log_debug('stage_state', $stage_state);

		// load view
		$page_data['documents_query'] = 
		 	$docs_model->fetch_documents($project_id);
		$page_data['general_questions_query'] = 
			$questions_model->fetch_general_questions($project_id);
		$page_data['additional_questions_query'] =
			$questions_model->fetch_additional_questions_for_project($project_id);
		$page_data['acquaintance_stage_state'] = $stage_state;
		$page_data['current_date'] = $current_date;

		$top_nav_data['uri'] = $this->request->uri;

		echo view('common/header');
		echo view('nav/top_nav', $top_nav_data);
		echo view('documents/view', $page_data);
		echo view('common/footer');
	}
}