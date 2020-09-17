<?php namespace App\Controllers;

class Document extends BaseController {

	public function insert() {
		// $wrid = $this->log_webrequest();
		// $this->set_webrequest_id($wrid);
		// $this->Documents_model->set_webrequest_id($wrid);

		// $this->log_debug('document insert', 'start document insert');

		$data = $this->getPostData();
		$isRequestValid = true;
		$validationErrorText = "";
		$fileTargetDir = "?";

		// 1. Get request params
		$filename = isset($data->FileName) ? $data->FileName : false;
		$projectname = isset($data->ProjectName) ? $data->ProjectName : false;
		$isforcreditor = isset($data->IsForCreditor) && ($data->IsForCreditor === "true") ? true : false;
		$isfordebtor = isset($data->IsForDebtor) && ($data->IsForDebtor === "true") ? true : false;
		$isformanager = isset($data->IsForManager) && ($data->IsForManager) === "true" ? true : false;
		$fileurl = isset($data->FileUrl) ? $data->FileUrl : false;

		// 2. Validate request attributes
		if (!$filename || empty($filename))
		{
			$validationErrorText.="Empty FileName value in request. ";
			$isRequestValid = false;
		}

		if (!$fileurl || empty($fileurl))
		{
			$validationErrorText.="Empty FileUrl value in request. ";
			$isRequestValid = false;
		}

		if (!$projectname || empty($projectname))
		{
			$validationErrorText.="Empty ProjectName value in request. ";
			$isRequestValid = false;
		}

		if (!$isRequestValid)
		{
			$msg = "Invalid Document POST request: $validationErrorText";
			// $this->log_debug('document insert', $msg);

			printf($msg);
			http_response_code(400);
			exit();
		}

		// 4. get projectId

		$projects_model = model('Projects_model');
		// 4a. Try to find projectId
		$projectname = urldecode($projectname);
		$project = $projects_model->get_project_by_name($projectname);
		$projectId = false;

		if (!$project) {
			// 4b. Try to insert new project when project not found
			$projectId = $projects_model->new_project($projectname, $projectname, null, null, null, null);
		} else {
			$projectId = $project->project_id;
		}

		if (!$projectId)
		{
			$msg = "Can't add projectId with projectName: $projectname";
			// $this->log_debug('document insert', $msg);

			printf($msg);
			http_response_code(400);

			exit();
		}

		// 5. Insert data to document table
		// 3. Correct Url
		$documents_model = model('Documents_model');

		$correctedUrl = $documents_model->correctFileDownloadUrl($fileurl);
		log_message('info', 'Document - corrected URL for download: '.$correctedUrl);

		// 3a. Download file by Url
		$doc_id = $documents_model->new_document_with_body($correctedUrl, $filename, $projectId, $isforcreditor, $isfordebtor, $isformanager);
		// ToDo: Try-Catch

		if (!$doc_id)
		{
			$msg = "Can't load file or save document to db: $filename from URL: $correctedUrl";
			// $this->log_debug('document insert', $msg);

			printf($msg);
			http_response_code(400);

			exit();
		}

		// 6. Return result from service
		$json = json_encode(array('id' => $doc_id));

		$msg = "Return value: ".$json;
		// $this->log_debug('document insert', $msg);

		http_response_code(201); // 201: resourse created
		$site = 'https://vprofy.ru';
		header("Location: $site/" . $_SERVER['REQUEST_URI'] . "/$doc_id");
		header("Content-Type: application/json");
		print $json;
		
	}

	/*
	WebUI - редактирование одного документа
	*/
	public function edit() {
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

		// ToDo: проверить, что файл передан.
		// Возможно, лучше использовать getFiles()
		// https://codeigniter.com/user_guide/incoming/incomingrequest.html?highlight=getfile
		$file = $this->request->getFile('fileToUpload');
		//if ($file->getName)
		if (! $file->isValid())
		{
			throw new \RuntimeException($file->getErrorString().'('.$file->getError().')');
		}

		$filename = $file->getName();
		$filebody = file_get_contents($file->getTempName());

		// Save file to database
		$data = array ('doc_project_id' => $project_id,
					'doc_filename' => $filename,
					'doc_body' => $filebody,
					'doc_is_for_creditor' => $isforcreditor,
					'doc_is_for_debtor' => $isfordebtor,
					'doc_is_for_manager' => $isformanager);
		$db = \Config\Database::connect();
		if ($db->table('document')->insert($data)) {
			$doc_id = $db->insertID();
		} else {
			$doc_id = false;
		}
		$db->close();

		helper(['url', 'form']);
		$projects_model = model('Projects_model');
		$documents_model = model('Documents_model');

		// $project = $projects_model->get_project_by_code($project_code);
		// if (!$project) {
		// 	throw new \Exception("Empty project row for project_code: $project_code");
		// }

		// continue to edit project
		// $page_data['project_query'] = $project;
		// $page_data['documents_query'] =
		// 	$documents_model->fetch_documents($project_id);

		// $top_nav_data['uri'] = $this->request->uri;

		// echo view('common/header');
		// echo view('nav/top_nav', $top_nav_data);
		// echo view('projects/edit_view', $page_data);
		// echo view('common/footer');

		return redirect()->to(base_url("Project/edit/$project_code"));
	}

	public function download() {

		helper('url');

		$uri = $this->request->uri;

		$doc_id = $uri->getSegment(3);
		if (!$doc_id) {
			// $this->log_debug('Document/download', 'Empty doc_id');
			throw new \Exception('Empty doc_id');
			// show_error('Empty doc_id', 500);
		}
	// 2. Check user access
	// $val = dbIsUserExistsByLoginCode($userlogin);
	// if (!$val)
	// {
	// 	printf("Access denied. Usercode: $s", $userlogin);
	// 	exit();
	// }
		$doc_model = model('Documents_model');
		$doc_query = $doc_model->get_document($doc_id);
		if (!$doc_query) {
			throw new \Exception("Can't find document by doc_id: $doc_id");
		}

		$filename = trim($doc_query->doc_filename);

		// заставляем браузер показать окно сохранения файла
		$content_type = $this->mime_detect($doc_query->doc_body);
		$length = $doc_query->doc_length;
		$calc_length = strlen($doc_query->doc_body);
		log_message('info', "download filename: $filename, content-type: $content_type, length: $length / $calc_length.");

		// $this->response->setHeader('Content-Description', 'File Transfer')
		// ->setHeader('Content-Type', 'application/octet-stream')
		// ->setHeader('Content-Disposition', 'attachment; filename="'.basename($filename).'"'
		// 		."; filename*=UTF-8''".rawurlencode($filename))
		// ->setHeader('Content-Transfer-Encoding', 'binary')
		// ->setHeader('Expires', '0')
		// ->setHeader('Cache-Control', 'must-revalidate')
		// ->setHeader('Pragma', 'public')
		// ->setHeader('Content-Length', $length);
		// // читаем файл и отправляем его пользователю
		// echo $doc_query->doc_body;

		// return $this->response;
		log_message('info', '[*] memory: '.ini_get('memory_limit'));
		ini_set('memory_limit', '255M');
		log_message('info', '[*] memory: '.ini_get('memory_limit'));

		return $this->response->download($filename, $doc_query->doc_body);
	}

	/**
     * Tries to detect MIME type of content.
     *
     * @param string $test First few bytes of content to use for detection
     *
     * @return string
     */
    public function mime_detect(&$test)
    {
        $len = mb_strlen($test);
        if ($len >= 2 && $test[0] == chr(0xff) && $test[1] == chr(0xd8)) {
            return 'image/jpeg';
        }
        if ($len >= 3 && substr($test, 0, 3) == 'GIF') {
            return 'image/gif';
        }
        if ($len >= 4 && mb_substr($test, 0, 4) == "\x89PNG") {
            return 'image/png';
        }
        return 'application/octet-stream';
    }
}