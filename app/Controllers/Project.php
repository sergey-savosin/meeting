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

		//var_dump($data);
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
			$session->set('redirect_from', $this->request->uri->getSegment(1));
			return redirect()->to(base_url('User/login'));
		}

		$projects_model = model('Projects_model');

		// data for view
		$page_data['projects_query'] = 
			$projects_model->getProjectList();

		helper(['form', 'url']);

		$top_nav_data['uri'] = $this->request->uri;

		// show view
		if ($this->request->getMethod() === 'get' ) {
			echo view('common/header');
			echo view('nav/top_nav', $top_nav_data);
			echo view('projects/list_view', $page_data);
			echo view('common/footer');

		}
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
			return redirect()->to(base_url('User/login'));
		}

		helper(['url', 'form']);
		$projects_model = model('Projects_model');
		$documents_model = model('Documents_model');

		$uri = $this->request->uri;

		$project_code = $uri->getSegment(3);
		if (!$project_code) {
			throw new \Exception('Empty project_code segment');
		}
		$project_code = urldecode($project_code);

		$project = $projects_model->get_project_by_code($project_code);
		if (!$project) {
			throw new \Exception("Empty project row for project_code: $project_code");
		}

		$project_id = $project->project_id;

		// data for view
		$page_data['project_query'] = $project;
		$page_data['documents_query'] =
			$documents_model->fetch_documents($project_id);

		$top_nav_data['uri'] = $this->request->uri;

		// show view
		if ($this->request->getMethod() === 'get' ) {
			return view('common/header').
				view('nav/top_nav', $top_nav_data).
				view('projects/edit_view', $page_data).
				view('common/footer');
		}
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
	* http://localhost:8080/meeting/document/delete/<doc_id>
	*/
	public function delete_document() {
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
		$doc_id = $uri->getSegment(3);
		log_message('info', 'uri: 3: '.$uri->getSegment(3));

		if (!$doc_id) {
			throw new \Exception('Empty doc_id');
		}

		$document_model = model('Documents_model');
		$project_model = model('Projects_model');
		
		// 4. validate document and project
		$project = $project_model->get_project_by_document_id($doc_id);
		if (!$project) {
			throw new \Exception("Can't find project by doc_id: $doc_id");
		}
		$project_code = $project->project_code;

		// 5. delete document from tables
		$project_model->delete_project_document($doc_id);

		// 6. go to project edit page
		return redirect()->to(base_url("/Project/Edit/$project_code"));
	}

	/**
	* WebUI - редактирование одного документа
	* Принимает POST-запрос из представления
	*/
	public function add_document() {
		// get session data
		$session = session();
		$user = $session->get('user_login_code');
		if ($user == FALSE) {
			redirect()->to(base_url('user/login'));
		}

		$isRequestValid = true;
		$validationErrorText = "";

		// 1. Get request params
		$project_id = $this->request->getPost('ProjectId');
		$project_code = $this->request->getPost('ProjectCode');
		$isforcreditor = true; //$this->request->getPost('IsForCreditor');
		$isfordebtor = true; //$this->request->getPost('IsForDebtor');
		$isformanager = true; //$this->request->getPost('IsForManager');
		$docCaption = trim($this->request->getPost('docCaption'));

		// 2. Validate request attributes
		if (!isset($project_id) || empty($project_id))
		{
			$isRequestValid = false;
			$validationErrorText = "Parameter ProjectId is empty.";
		}

		if (!isset($project_code) || empty($project_code))
		{
			$isRequestValid = false;
			$validationErrorText = "Parameter ProjectCode is empty.";
		}

		if (!$isRequestValid)
		{
			$msg = "Invalid Document POST request: $validationErrorText";
			printf($msg);
			http_response_code(400);
			exit();
		}

		$projects_model = model('Projects_model');
		$documents_model = model('Documents_model');

		// Работа с переданным файлом
		$files = $this->request->getFiles();
		if ($files) {
			log_message('info', 'project::edit_document - saving files');
			foreach ($files['documentFile'] as $file) {
				log_message('info', 'found file');
				if ($file->isValid() && ! $file->hasMoved()) {
						$fileMime = $file->getClientMimeType();
						$fileSize = $file->getSize();
						$fileName = $file->getName(); //ToDo: заменить на ручное название из представления
						$tmpName = $file->getTempName();

						log_message('info', 
							"Proj::edit_doc - file name: $fileName, size: $fileSize, MIME: $fileMime, tmpName: $tmpName");

						$fileContent = file_get_contents($file->getTempName());

						$doc_id = $this->saveOneDocumentAndLinkToProject(
							$documents_model,
							$projects_model,
							$project_id, $fileName, $fileContent, $docCaption);
						
				} else {
					throw new 
					\RuntimeException($file->getErrorString().'('.$file->getError().')');
				}
			}
		}

		return redirect()->to(base_url("Project/edit/$project_code"));
	}

	/**
	* Сохранить документ и связать его с проектом
	*/
	private function saveOneDocumentAndLinkToProject($documents_model,
		$projects_model,
		$projectId, $fileName, $fileContent,
		$docCaption) {

		// save document
		log_message('info', 'project:: save doc to DB');
		$doc_id = $documents_model->newDocumentWithContent($fileName, $fileContent, $docCaption);

		if (!$doc_id) {
			return false;
		}

		// link project and document
		log_message('info', "project:: link doc [$doc_id] to project [$projectId]");
		$pd_id = $projects_model->link_project_and_document($projectId, $doc_id);

		if (!$pd_id) {
			return false;
		}

		log_message('info', "project:: new project_document ID: $pd_id");
		return $doc_id;
	}



}