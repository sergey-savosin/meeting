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
			log_message('info', '[AdditionalQuestions::index] User should be logged');
			// store uri to return here after login
			$session->set('redirect_from', uri_string());
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

		// prepare data for view
		$questions_model = model('Questions_model');
		$documents_model = model('Documents_model');

		// Подготовка массива вопросов и вложенных документов
		$additional_questions = 
			$questions_model->fetch_additional_questions_for_user($user_id);

		foreach ($additional_questions->getResult() as $question) {
			$documents = 
				$questions_model->fetch_documents_for_questionid($question->qs_id);
			$additional_documents[$question->qs_id] = [
				'qs_title' => $question->qs_title,
				'qs_comment' => $question->qs_comment,
				'documents' => $this->make_documents_array($documents->getResult())
			];
		}

		if (isset($additional_documents)) {
			$page_data['additional_questions'] = $additional_documents;
		} else {
			$page_data['additional_questions'] = [];
		}

		// setup form validation
		$val_rules['qs_title'] = [
			'label' => 'qs_title', //lang('app.additional_questions_title')
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

			//ToDo: add transaction
			// save Question
			$qs_id = $this->saveOneQuestion($questions_model,
				$project_id, $user_id, $qs_title_value, $qs_comment_value
			);

			if (!$qs_id) {
				log_message('info', 'AQ::index. Save question to DB error.');
			}

			// save Documents and link them to new Question
			$doc_id = -1;
			
			if ($qs_id && $files = $this->request->getFiles())
			{
				log_message('info', 'AQ::index - uploaded files! '.json_encode($files));

				foreach ($files['documentFile'] as $file) {
					if ($file->isValid() && ! $file->hasMoved())
					{
						$fileMime = $file->getClientMimeType();
						$fileSize = $file->getSize();
						$fileName = $file->getName();
						//$fileClientName = $file->getClientName();
						$tmpName = $file->getTempName();

						log_message('info', 
							"AQ::index - file name: $fileName, size: $fileSize, MIME: $fileMime, tmpName: $tmpName");

						$fileContent = file_get_contents($file->getTempName());
						
						$doc_id = $this->saveOneDocumentAndLinkToQuestion(
							$documents_model,
							$questions_model,
							$qs_id, $fileName, $fileContent);
					}
				}
			}

			if ($qs_id && $doc_id) {
				// go to default page
				return redirect()->to(base_url('/additionalquestions'));
			} else {
				$msg = $res['message'];
				log_message('info', 'A.Q.::index. Save data error: '.$msg);
				
				// ToDo: show error
			}
		}
	}

	/**
	* Сохранить один дополнительный вопрос
	*/
	private function saveOneQuestion($questions_model,
		$project_id, $user_id, $title, $comment) {

		log_message('info', 'A.Q.::index - saving add.question.');

		// save question
		$qs_id = $questions_model->new_additional_question($project_id,
			$user_id, $title, $comment);

		return $qs_id;
	}

	/**
	* Сохранить документ и связать его с вопросом
	*/
	private function saveOneDocumentAndLinkToQuestion($documents_model,
		$questions_model,
		$questionId, $fileName, $fileContent) {

		// save document
		$doc_id = $documents_model->newDocumentWithContent($fileName, $fileContent, '');

		if (!$doc_id) {
			return false;
		}

		// link question and document
		$qd_id = $questions_model->link_question_and_document($questionId, $doc_id);

		if (!$qd_id) {
			return false;
		}

		return $doc_id;
	}

	/**
	* Составление массива документов по одному вопросу
	*
	* @param $question - запрос вопросов и документов
	* @return array
	*/
	function make_documents_array($documents) {
		$qa = array();
		foreach ($documents as $document) {
			$qa[$document->doc_id] =
				array('doc_filename' => $document->doc_filename);
		}
		return $qa;
	}
}