<?php namespace App\Controllers;

class AdditionalQuestions extends BaseController {

	/***********
	* CI4
	* Unit Test
	*/
	public function index() {
		// get session data
		$session = session();
		$user = $session->get('user_login_code');
		if ($user == FALSE) {
			return redirect()->to(base_url('User/login'));
		}

		// validation
		$project_id = $session->get('user_project_id');
		if (!$project_id) {
			throw new \Exception('Empty project_id');
		}

		$user_id = $session->get('user_id');
		if (!$user_id) {
			throw new \Exception('Empty user_id');
		}

		$qs_title_value = $this->request->getPost('qs_title');

		// prepare data for view
		$questions_model = model('Questions_model');
		$page_data['additional_questions_query'] = 
			$questions_model->fetch_additional_questions_for_user($user_id);

		// setup form validation
		// $this->form_validation->set_message('required', 'Укажите значение в поле {field}.');
		// $this->form_validation->set_rules("qs_title",
		// 		lang('app.additional_questions_title'),
		// 		'required');
		$val_rules['qs_title'] = 'required';
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
			echo view('additionalquestions/view', $page_data);
			echo view('common/footer');
		} else {
			// save data to DB
			$res = $questions_model->new_additional_question($project_id, $qs_title_value, $user_id);
			if ($res) {
				// go to default page
				return redirect()->to(base_url('/additionalquestions'));
			} else {
				// show error
			}
		}
	}



}