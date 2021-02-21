<?php namespace App\Controllers;
use CodeIgniter\I18n\Time;

class Project extends BaseController {

	/*****************************
	 V4
	 Добавление проекта - Rest
	******************************/
	public function insert() {
		$data = $this->getPostData();
		log_message('info', "[project insert] ".json_encode($data));
		$isRequestValid = true;
		$validationErrorText = "";

		$projectName = isset($data->ProjectName) ? $data->ProjectName : false;
		$projectCode = isset($data->ProjectCode) ? $data->ProjectCode : false;
		
		$acquaintanceStartDate = isset($data->AcquaintanceStartDate) ? $data->AcquaintanceStartDate : false;
		$acquaintanceStartTime = isset($data->AcquaintanceStartTime) ? $data->AcquaintanceStartTime : false;

		$mainAgendaStartDate = isset($data->MainAgendaStartDate) ? $data->MainAgendaStartDate : false;
		$mainAgendaStartTime = isset($data->MainAgendaStartTime) ? $data->MainAgendaStartTime : false;

		$additionalAgendaStartDate = isset($data->AdditionalAgendaStartDate) ? $data->AdditionalAgendaStartDate : false;
		$additionalAgendaStartTime = isset($data->AdditionalAgendaStartTime) ? $data->AdditionalAgendaStartTime : false;

		$meetingFinishDate = isset($data->MeetingFinishDate) ? $data->MeetingFinishDate : false;
		$meetingFinishTime = isset($data->MeetingFinishTime) ? $data->MeetingFinishTime : false;

		// validate required parameters
		if (!$projectName) {
			$validationErrorText .= " Empty ProjectName value in request.";
			$isRequestValid = false;
		}

		if (!$projectCode) {
			$validationErrorText .= " Empty ProjectCode value in request.";
			$isRequestValid = false;
		}

		// if (!$acquaintanceStartDate) {
		// if (!$mainAgendaStartDate) {
		// if (!$additionalAgendaStartDate) {
		// if (!$meetingFinishDate) {

		// Converting to DateTime
		// форматы дд.мм.гг (гггг) и чч:мм
		if ($acquaintanceStartDate) {
			$acquaintanceStartDateTime = $this->makeDateTime($acquaintanceStartDate, $acquaintanceStartTime);
			if (!$acquaintanceStartDateTime) {
				$dt_str = $acquaintanceStartDate.' '.$acquaintanceStartTime;
				$validationErrorText .= " AcquaintanceStartDate has incorrect format: $dt_str.";
				$isRequestValid = false;
			}
		}

		if ($mainAgendaStartDate) {
			$mainAgendaStartDateTime = $this->makeDateTime($mainAgendaStartDate, $mainAgendaStartTime);
			if (!$mainAgendaStartDateTime) {
				$dt_str = $acquaintanceStartDate.' '.$acquaintanceStartTime;
				$validationErrorText .= ' MainAgendaStartDate has incorrect format: $dt_str.';
				$isRequestValid = false;
			}
		}

		if ($additionalAgendaStartDate) {
			$additionalAgendaStartDateTime = $this->makeDateTime($additionalAgendaStartDate, $additionalAgendaStartTime);
			if (!$additionalAgendaStartDateTime) {
				$dt_str = $additionalAgendaStartDate.' '.$additionalAgendaStartTime;
				$validationErrorText .= ' AdditionalAgendaStartDate has incorrect format: $dt_str.';
				$isRequestValid = false;
			}
		}

		if ($meetingFinishDate) {
			$meetingFinishDateTime = $this->makeDateTime($meetingFinishDate, $meetingFinishTime);
			if (!$meetingFinishDateTime) {
				$dt_str = $meetingFinishDate.' '.$meetingFinishTime;
				$validationErrorText .= ' MeetingFinishDate has incorrect format: $dt_str.';
				$isRequestValid = false;
			}
		}

		$projectsModel = model('Projects_model');
		// business logic validation
		if ($projectName && $projectsModel->check_project_name_exists($projectName)) {
			$validationErrorText .= " Project name already exists: $projectName.";
			$isRequestValid = false;
		}

		if ($projectCode && $projectsModel->check_project_code_exists($projectCode)) {
			$validationErrorText .= " Project code already exists: $projectCode.";
			$isRequestValid = false;
		}

		if (!$isRequestValid) {
			$msg = "Invalid Project POST request:$validationErrorText";
			log_message('info', "[project insert] validation error: $msg");

			return $this->response
				->setStatusCode(400)
				->removeHeader('Location')
				->setJSON([
					'status' => 'error',
					'error' => $msg
				]);
		}

		// save to database
		$mysql_format = 'Y-m-d H:i:s';
		$ac_dt = $acquaintanceStartDate ? $acquaintanceStartDateTime->format($mysql_format) : null;
		$ma_dt = $mainAgendaStartDate ? $mainAgendaStartDateTime->format($mysql_format) : null;
		$aa_dt = $additionalAgendaStartDate ? $additionalAgendaStartDateTime->format($mysql_format) : null;
		$mf_dt = $meetingFinishDate ? $meetingFinishDateTime->format($mysql_format) : null;
		$new_id = $projectsModel->new_project($projectName, $projectCode, $ac_dt, $ma_dt, $aa_dt, $mf_dt);

		// Result
		$resource = $this->request->uri->getSegment(1);
		$newuri = base_url("$resource/$new_id");
		$body = array(
			'status' => 'ok',
			'id' => $new_id
		);

		log_message('info', "[project insert] result ok: ". json_encode($body));
		return $this->response
			->setStatusCode(201) // 201: resourse created
			->setHeader("Location", $newuri)
			->setContentType("application/json")
			->setJSON($body);
	}

	/********************************
	 V4
	 Просмотр списка проектов - Web-UI
	*********************************/
	public function index() {
		// get session data
		$session = session();
		$user = $session->get('user_login_code');
		if ($user == FALSE) {
			// store uri to return here after login
			$session->set('redirect_from', uri_string());
			return redirect()->to(base_url('User/login'));
		}

		// Form data
		$newProjectCode = trim($this->request->getPost('projectCode'));
		$newProjectName = trim($this->request->getPost('projectName'));

		$projects_model = model('Projects_model');

		// data for view
		$page_data['projects_query'] = 
			$projects_model->getProjectList();

		helper(['form', 'url']);

		$top_nav_data['uri'] = $this->request->uri;

		// setup form validation
		// ToDo: проверка projectCode на уникальность
		$val_rules['projectCode'] = [
			'label' => 'Код проекта',
			'rules' => 'required|is_unique[project.project_code]',
			'errors' => [
				'required' => 'Укажите Код проекта',
				'is_unique' => 'Параметр "{field}" должен быть уникальным'
			]
		];
		$val_rules['projectName'] = [
			'label' => 'Название проекта',
			'rules' => 'required|is_unique[project.project_name]',
			'errors' => [
				'required' => 'Укажите Название проекта',
				'is_unique' => 'Параметр "{field}" должен быть уникальным'
			]
		];

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
				view('projects/list_view', $page_data).
				view('common/footer');
		} else {
			// save data to db
			$projects_model->new_project($newProjectName, $newProjectCode,
				null, null, null, null);
		}
		return redirect()->to(base_url("Project/edit/$newProjectCode"));

	}

	/********************************
	 V4
	 Настройка одного проекта - Web-UI
	*********************************/
	public function edit() {
		// get session data
		$session = session();
		$user = $session->get('user_login_code');
		if ($user == FALSE) {
			// store uri to return here after login
			$session->set('redirect_from', uri_string());
			return redirect()->to(base_url('User/login'));
		}

		helper(['url', 'form']);
		$projects_model = model('Projects_model');
		$documents_model = model('Documents_model');
		$users_model = model('Users_model');
		$questions_model = model('Questions_model');

		// get request params
		$uri = $this->request->uri;

		// find ProjectCode in segment (Get) or in request (Post)
		log_message('info', '[project::edit] request='.json_encode($this->request));
		$project_code = $uri->getSegment(3);
		// if (!$project_code) {
		// 	$project_code = $this->request->getPost('ProjectCode');
		// }

		if (!$project_code) {
			throw new \Exception('Empty project_code segment');
		}

		$project_code = urldecode($project_code);

		$project = $projects_model->get_project_by_code($project_code);
		if (!$project) {
			throw new \Exception("Empty project row for project_code: $project_code");
		}

		$project_id = $project->project_id;

		// Form data
		$newProjectCode = trim($this->request->getPost('project_code'));
		$newProjectName = trim($this->request->getPost('project_name'));

		// Подготовка массива вопросов и вложенных документов
		$base_questions = 
			$questions_model->fetch_general_questions($project_id);

		foreach ($base_questions->getResult() as $question) {
			$documents = 
				$questions_model->fetch_documents_for_questionid($question->qs_id);
			$base_documents[$question->qs_id] = [
				'qs_id' => $question->qs_id,
				'qs_title' => $question->qs_title,
				'qs_comment' => $question->qs_comment,
				'documents' => $this->make_documents_array($documents->getResult())
			];
		}

		// data for view
		$page_data['project_query'] = $project;
		$page_data['documents_query'] = $documents_model->fetch_documents($project_id);
		$page_data['users_query'] = $users_model->get_users_by_projectid($project_id);
		
		if (isset($base_documents)) {
			$page_data['base_questions'] = $base_documents;
		} else {
			$page_data['base_questions'] = [];
		}

		$top_nav_data['uri'] = $this->request->uri;

		// setup form validation
		// ToDo: проверка projectCode на уникальность
		$val_rules['project_code'] = [
			'label' => 'project_code',
			'rules' => 'required',
			'errors' => [
				'required' => 'Укажите Код проекта'
			]
		];
		$val_rules['project_name'] = [
			'label' => 'project_name',
			'rules' => 'required',
			'errors' => [
				'required' => 'Укажите Название проекта'
			]
		];

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
				view('projects/edit_view', $page_data).
				view('common/footer');
		} else {
			// save data to db
			$projects_model->update_project($project_id, $newProjectCode, $newProjectName);
		}
		return redirect()->to(base_url("Project/edit/$newProjectCode"));
	}

	/**
	* Удаление проекта через контроллер.
	* project_id должен находиться в get-параметре:
	* http://<site>/meeting/project/delete_project/<project_id>
	*/
	public function delete_project() {
		// 1. get session data
		$session = session();
		$userLoginCode = $session->get('user_login_code');
		if (!$userLoginCode) {
			return "Error: User not logged in.";
		}

		// 2. Check user access
		$users_model = model('Users_model');
		$user = $users_model->get_user_by_logincode($userLoginCode);
		if (!$user)
		{
			return "Error: Access denied. Usercode: $userLoginCode";
		}

		helper('url');

		$uri = $this->request->uri;

		// 3. Check url params
		$projectId = $uri->getSegment(3);
		log_message('info', '[project::delete_project] uri(3) '.$projectId);

		if (!$projectId) {
			return 'Error: Empty projectId param.';
		}

		//$document_model = model('Documents_model');
		$project_model = model('Projects_model');
		
		// 4. validate document and project
		$project = $project_model->get_project_by_id($projectId);
		if (!$project) {
			return "Can't find project by projectId: $projectId";
		}
		$projectCode = $project->project_code;

		// 5. delete document from tables
		$project_model->delete_project($projectCode);
		log_message('info', "[project::delete_project] Project deleted: $projectId".
			" for ProjectCode: $projectCode");

		// 6. go to project edit page
		return redirect()->to(base_url("/Project"));
	}

	/***********************
	 V4
	 Удаление одного проекта - Rest
	************************/
	public function delete() {
		$data = $this->getPostData();

		$isRequestValid = true;
		$validationErrorText = "";

		$projectCode = isset($data->ProjectCode) ? $data->ProjectCode : false;
		log_message('info', "[project-delete] projectCode: $projectCode");
		
		if (!$projectCode) {
			$validationErrorText .= " Empty ProjectCode value in request.";
			$isRequestValid = false;
		}

		$projectsModel = model('Projects_model');

		// business logic validation
		if ($projectCode && !$projectsModel->check_project_code_exists($projectCode)) {
			$validationErrorText .= " Project code does not exists: $projectCode.";
			$isRequestValid = false;
		}

		if (!$isRequestValid) {
			$msg = "Invalid Project DELETE request:$validationErrorText";
			log_message('info', "[project delete] validation error:$msg");

			return $this->response
				->setStatusCode(400)
				->removeHeader('Location')
				->setJSON([
					'status' => 'error',
					'error' => $msg
				]);
		}

		$res = $projectsModel->delete_project($projectCode);

		// Result
		$resource = $this->request->uri->getSegment(1);
		$body = array(
			'status' => 'ok'
		);

		log_message('info', "[project delete] result ok: ". json_encode($body));
		return $this->response
			->setStatusCode(200) // 200: ok
			->setContentType("application/json")
			->setJSON($body);
	}

	/**
	 * Converting string to DateTime.
	 * форматы дд.мм.гг (гггг) и чч:мм
	 *
	 * @param string $date
	 * @param string $time
	 *
	 * @return \CodeIgniter\I18n\Time
	 * @throws \Exception
	 */
	protected function makeDateTime($date, $time) {
		if (!$date) {
			return false;
		}

		$dt_format = 'j.m.Y H:i O';

		if ($time) {
			$dt_str = $date. ' '.$time.' +0300';
		} else {
			$dt_str = $date. ' 00:00 +0300';
		}
		return Time::createFromFormat($dt_format, $dt_str);
	}

	/**
	* Удаление документа.
	* doc_id должен находиться в get-параметре:
	* http://<site>/meeting/project/delete/<doc_id>
	*/
	public function delete_document() {
		// 1. get session data
		$session = session();
		$userLoginCode = $session->get('user_login_code');
		if (!$userLoginCode) {
			return "Error: User not logged in.";
		}

		// 2. Check user access
		$users_model = model('Users_model');
		$user = $users_model->get_user_by_logincode($userLoginCode);
		if (!$user)
		{
			return "Error: Access denied. Usercode: $userLoginCode";
		}

		helper('url');

		$uri = $this->request->uri;

		// 3. Check url params
		$doc_id = $uri->getSegment(3);
		// log_message('info', '[project::delete_document] uri(3) '.$uri->getSegment(3));

		if (!$doc_id) {
			return 'Error: Empty doc_id.';
		}

		//$document_model = model('Documents_model');
		$project_model = model('Projects_model');
		
		// 4. validate document and project
		$project = $project_model->get_project_by_document_id($doc_id);
		if (!$project) {
			return "Can't find project by doc_id: $doc_id";
		}
		$project_code = $project->project_code;

		// 5. delete document from tables
		$project_model->delete_project_document_with_tran($doc_id);
		log_message('info', "[project::delete_document] Document deleted: $doc_id".
			" for ProjectCode: $project_code");

		// 6. go to project edit page
		return redirect()->to(base_url("/Project/edit_document/$project_code"));
	}

	/**
	* WebUI - редактирование одного документа
	* Принимает POST-запрос из представления
	*/
	public function edit_document() {
		log_message('info', '[Project::edit_document] Start');

		// get session data
		$session = session();
		$user = $session->get('user_login_code');
		if ($user == FALSE) {
			log_message('info', '[Project::edit_document] User should be logged');
			// store uri to return here after login
			$session->set('redirect_from', uri_string());
			return redirect()->to(base_url('User/login'));
		}

		// 1. Get request params
		$uri = $this->request->uri;

		log_message('info', '[project::edit_document] uri:'.$uri);

		// find ProjectCode in segment (Get) or in request (Post)
		$project_code = $uri->getSegment(3);
		if (!$project_code) {
			$project_code = $this->request->getPost('ProjectCode');
		}

		if (!$project_code) {
			throw new \Exception('Empty project_code segment');
		}
		$project_code = urldecode($project_code);

		// Form data
		$docCaption = trim($this->request->getPost('DocCaption'));

		// 2. Prepare data for view
		$projects_model = model('Projects_model');
		$documents_model = model('Documents_model');


		$project = $projects_model->get_project_by_code($project_code);
		if (!$project) {
			throw new \Exception("Empty project row for project_code: $project_code");
		}
		$project_id = $project->project_id;

		$page_data['project_query'] = $project;
		$page_data['documents_query'] =
			$documents_model->fetch_documents($project_id);

		// setup form validation
		$val_rules['documentFile'] = [
			'label' => 'documentFile',
			'rules' => 'uploaded[documentFile]',
			'errors' => [
				'uploaded' => 'Выберите файл'
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
				view('projects/editdocument_view', $page_data).
				view('common/footer');
		} else {
			// save data to DB
			// Работа с переданным файлом
			$files = $this->request->getFiles();
			if ($files) {
				foreach ($files['documentFile'] as $file) {
					if ($file->isValid() && ! $file->hasMoved()) {
							$fileMime = $file->getClientMimeType();
							$fileSize = $file->getSize();
							$fileName = $file->getName();
							$tmpName = $file->getTempName();

							$fileContent = file_get_contents($file->getTempName());

							$doc_id = $this->saveOneDocumentAndLinkToProject(
								$documents_model,
								$projects_model,
								$project_id, $fileName, $fileContent, $docCaption);
							
					} else {
						// ToDo: return normal webResult with error text
						return('Error adding document. Error message: '.
							$file->getErrorString().
							'('.$file->getError().')'
						);
					}
				}
			}
		}
		return redirect()->to(base_url("Project/edit_document/$project_code"));
	}

	/**
	* WebUI - удаление вопроса основной повестки
	* qs_id должен находиться в get-параметре:
	* http://<site>/meeting/project/delete_basequestion/<qs_id>
	*/
	public function delete_basequestion() {
		// 1. get session data
		$session = session();
		$userLoginCode = $session->get('user_login_code');
		if (!$userLoginCode) {
			throw new \Exception("User not logged in.");
		}

		// 2. Check user access
		$users_model = model('Users_model');
		$user = $users_model->get_user_by_logincode($userLoginCode);
		if (!$user)
		{
			throw new \Exception("Access denied. Usercode: $userlogin");
		}

		helper('url');

		$uri = $this->request->uri;

		// 3. Check url params
		$qs_id = $uri->getSegment(3);
		// log_message('info', '[project::delete_basequestion] uri(3): '.$uri->getSegment(3));

		if (!$qs_id) {
			throw new \Exception('Empty qs_id');
		}

		$question_model = model('Questions_model');
		
		// 4. validate question
		$project = $question_model->get_project_by_question_id($qs_id);
		if (!$project) {
			throw new \Exception("Can't find project by qs_id: $qs_id");
		}
		$projectCode = $project->project_code;

		// 5. delete question from tables
		$question_model->delete_general_question($qs_id);

		// 6. go to project edit page
		return redirect()->to(base_url("/Project/edit_basequestion/$projectCode"));
	}

	/**
	* WebUI - редактирование списка вопросов основной повестки
	* Принимает POST-запрос из представления
	*/
	public function edit_basequestion() {
		log_message('info', '[Project::edit_basequestion] Start');

		// get session data
		$session = session();
		$user = $session->get('user_login_code');
		if ($user == FALSE) {
			log_message('info', '[Project::edit_basequestion] User should be logged');
			// store uri to return here after login
			$session->set('redirect_from', uri_string());
			return redirect()->to(base_url('user/login'));
		}

		// 1. Get request params
		$uri = $this->request->uri;

		log_message('info', '[project::edit_basequestion] uri:'.$uri);

		// find ProjectCode in segment (Get) or in request (Post)
		$project_code = $uri->getSegment(3);
		if (!$project_code) {
			$project_code = $this->request->getPost('ProjectCode');
		}

		if (!$project_code) {
			throw new \Exception('Empty project_code segment');
		}
		$project_code = urldecode($project_code);

		// Form data
		$qsTitle = trim($this->request->getPost('QsTitle'));
		$qsComment = trim($this->request->getPost('QsComment'));

		// prepare data for view
		$projects_model = model('Projects_model');
		$questions_model = model('Questions_model');
		$documents_model = model('Documents_model');
	

		$project = $projects_model->get_project_by_code($project_code);
		if (!$project) {
			throw new \Exception("Empty project row for project_code: $project_code");
		}
		$project_id = $project->project_id;

		// Подготовка массива вопросов и вложенных документов
		$base_questions = 
			$questions_model->fetch_general_questions($project_id);

		foreach ($base_questions->getResult() as $question) {
			$documents = 
				$questions_model->fetch_documents_for_questionid($question->qs_id);
			$base_documents[$question->qs_id] = [
				'qs_id' => $question->qs_id,
				'qs_title' => $question->qs_title,
				'qs_comment' => $question->qs_comment,
				'documents' => $this->make_documents_array($documents->getResult())
			];
		}

		$page_data['project_query'] = $project;
		if (isset($base_documents)) {
			$page_data['base_questions'] = $base_documents;
		} else {
			$page_data['base_questions'] = [];
		}

		// setup form validation
		$val_rules['QsTitle'] = [
			'label' => 'QsTitle',
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
				view('projects/editbasequestion_view', $page_data).
				view('common/footer');
		} else {
			// save data to DB

			//ToDo: add transaction
			// save Question
			$qs_id = $this->saveOneQuestion($questions_model,
				$project_id, $qsTitle, $qsComment
			);

			if (!$qs_id) {
				log_message('info', '[Project::edit_basequestion] Save question to DB error.');
			}

			// save Documents and link them to new Question
			$doc_id = -1;
			
			if ($qs_id && $files = $this->request->getFiles())
			{
				log_message('info', '[Project::edit_basequestion] - uploaded files! '.json_encode($files));

				foreach ($files['documentFile'] as $file) {
					if ($file->isValid() && ! $file->hasMoved())
					{
						$fileMime = $file->getClientMimeType();
						$fileSize = $file->getSize();
						$fileName = $file->getName();
						//$fileClientName = $file->getClientName();
						$tmpName = $file->getTempName();

						log_message('info', 
							"[Project::edit_basequestion] - file name: $fileName, size: $fileSize, MIME: $fileMime, tmpName: $tmpName");

						$fileContent = file_get_contents($file->getTempName());
						
						$doc_id = $this->saveOneDocumentAndLinkToQuestion(
							$documents_model,
							$questions_model,
							$qs_id, $fileName, $fileContent);
					}
				}
			}

			if ($qs_id && $doc_id) {
				// go to project edit page
				return redirect()->to(base_url("Project/edit_basequestion/$project_code"));
			} else {
				$msg = $res['message'];
				log_message('info', '[Project::edit_basequestion] Save data error: '.$msg);
				
				// ToDo: show error
			}
		}
		
	}

	public function delete_user() {

	}

	public function edit_user() {

	}

	/**
	* Сохранить документ и связать его с проектом
	*/
	private function saveOneDocumentAndLinkToProject($documents_model,
		$projects_model,
		$projectId, $fileName, $fileContent,
		$docCaption) {

		// save document
		$doc_id = $documents_model->newDocumentWithContent($fileName, $fileContent, $docCaption);

		if (!$doc_id) {
			return false;
		}

		// link project and document
		$pd_id = $projects_model->link_project_and_document($projectId, $doc_id);

		if (!$pd_id) {
			return false;
		}

		return $doc_id;
	}

	/**
	* Сохранить один основной вопрос
	*/
	private function saveOneQuestion($questions_model,
		$project_id, $title, $comment) {

		log_message('info', '[Project::saveOneQuestion] - saving question.');

		// save question
		$qs_id = $questions_model->new_general_question($project_id,
			$title, $comment);

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
	* @param $documents - запрос документов
	* @return array
	*/
	function make_documents_array($documents) {
		$qa = array();
		foreach ($documents as $document) {
			$qa[$document->doc_id] =
				array(
					'doc_filename' => $document->doc_filename,
					'doc_caption' => $document->doc_caption,
					'doc_id' => $document->doc_id
				);
		}
		return $qa;
	}

}