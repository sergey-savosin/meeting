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
		$qs_comment_value = $this->request->getPost('qs_comment');
		
		log_message('info', 'A.Q.::index. qs_title: '.$qs_title_value);

		if ($files = $this->request->getFiles())
		{
			log_message('info', 'AQ::index - uploaded files! '.json_encode($files));

			foreach ($files['documentFile'] as $file) {
				if ($file->isValid() && ! $file->hasMoved())
				{
					log_message('info', "AQ::index - file mime: ".$file->getClientMimeType());
					log_message('info', "AQ::index - file name: ".$file->getName());
					log_message('info', "AQ::index - file size: ".$file->getSize());
					log_message('info', 
						"AQ::index - file: client name: ".$file->getClientName());

					$fileSize = $file->getSize();
					$tmpName = $file->getTempName();
					$fp = fopen($tmpName, 'r'); 
					$content = fread($fp, filesize($tmpName)); 
					$content = addslashes($content);
					//log_message('info', 'file:'.$content);
					//$esc= mysql_real_escape_string(file_get_contents($fp));
					//$query = "INSERT INTO table(id,data,Dealid) VALUES (49, \".$esc.\",0)"
					fclose($fp);
				}
			}
		}

		// prepare data for view
		$questions_model = model('Questions_model');
		$page_data['additional_questions_query'] = 
			$questions_model->fetch_additional_questions_for_user($user_id);

		// setup form validation
		// $this->form_validation->set_message('required', 'Укажите значение в поле {field}.');
		// $this->form_validation->set_rules("qs_title",
		// 		lang('app.additional_questions_title'),
		// 		'required');
		$val_rules['qs_title'] = [
			'label' => 'qs_title',
			'rules' => 'required',
			'errors' => [
				'required' => 'Укажите Текст вопроса.'
			]
		];
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

			return view('common/header').
				view('nav/top_nav', $top_nav_data).
				view('additionalquestions/view', $page_data).
				view('common/footer');
		} else {
			// save data to DB
			log_message('info', 'A.Q.::index - saving add.question.');

			$res = $questions_model->new_additional_question($project_id,
				$user_id, $qs_title_value, $qs_comment_value);
			if ($res) {
				// go to default page
				return redirect()->to(base_url('/additionalquestions'));
			} else {
				// show error
			}
		}
	}



}