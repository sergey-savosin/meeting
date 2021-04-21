<?php namespace App\Controllers;

class Document extends BaseController {

	/*******************
	 V4

	 UnitTest
	********************/
	public function insert() {
		log_message('info', '[document insert] Starting');

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
			$validationErrorText.=" Empty FileName value in request.";
			$isRequestValid = false;
		}

		if (!$fileurl || empty($fileurl))
		{
			$validationErrorText.=" Empty FileUrl value in request.";
			$isRequestValid = false;
		}

		if (!$projectname || empty($projectname))
		{
			$validationErrorText.=" Empty ProjectName value in request.";
			$isRequestValid = false;
		}

		if (!$isRequestValid)
		{
			$msg = "Invalid Document POST request:$validationErrorText";
			$body = [
				'status' => 'error',
				'error' => $msg
			];
			log_message('info', "[document insert] error: $msg");

			return $this->response
				->setStatusCode(400)
				->removeHeader("Location")
				->setJSON($body);
		}

		// 4. get projectId
		$projects_model = model('Projects_model');
		$documents_model = model('Documents_model');

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
			$body = [
				'status' => 'error',
				'error' => $msg
			];
			log_message('info', "[document insert] error: $msg");

			return $this->response
				->setStatusCode(400)
				->removeHeader("Location")
				->setJSON($body);
		}

		// 5. Correct Url
		$correctedUrl = $documents_model->correctFileDownloadUrl($fileurl);
		log_message('info', 'Document - corrected URL for download: '.$correctedUrl);

		// 5a. Download file by Url and iInsert data to document table
		$new_id = $documents_model->new_document_with_body($correctedUrl, $filename,
			$isforcreditor, $isfordebtor, $isformanager);

		if (!$new_id)
		{
			$msg = "Can't load file or save document to db: $filename from URL: $correctedUrl.";
			$body = [
				'status' => 'error',
				'error' => $msg
			];
			log_message('info', "[document insert] error: $msg");

			return $this->response
				->setStatusCode(400)
				->removeHeader("Location")
				->setJSON($body);
		}

		// 6. Link project and document
		$pd_id = $projects_model->link_project_and_document($projectId, $new_id);
		if (!$pd_id)
		{
			$msg = "Can't link project to document.";
			$body = [
				'status' => 'error',
				'error' => $msg
			];
			log_message('info', "[document insert] error: $msg");

			return $this->response
				->setStatusCode(400)
				->removeHeader("Location")
				->setJSON($body);
		}

		// 7. Return result from service
		$resource = $this->request->uri->getSegment(1);
		$newuri = base_url("$resource/$new_id");
		$body = array(
			'status' => 'ok',
			'id' => $new_id
		);

		log_message('info', "[document insert] result ok: ". json_encode($body));
		return $this->response
			->setStatusCode(201) // 201: resourse created
			->setHeader("Location", $newuri)
			->setJSON($body);
	}

	/**
	* Скачивание документа.
	* doc_id должен находиться в get-параметре:
	* http://localhost:8080/meeting/document/download/<doc_id>
	*/
	public function download() {
		// 1. get session data
		$session = session();
		$userLoginCode = $session->get('user_login_code');
		$adminLoginName = $session->get('admin_login_name');
		if (!$userLoginCode && !$adminLoginName) {
			return "User not logged in.";
		}

		// 2. Check user access
		$users_model = model('Users_model');
		$admins_model = model('Admins_model');
		$user = $users_model->get_user_by_logincode($userLoginCode);
		$admin = $admins_model->get_admin_by_name($adminLoginName);
		if (!$user && !$admin)
		{
			return "Access denied. User: $userLoginCode, Admin: $adminLoginName";
		}

		helper('url');

		$uri = $this->request->uri;

		// 3. Check url params
		$doc_id = $uri->getSegment(3);
		if (!$doc_id) {
			throw new \Exception('Empty doc_id');
		}

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
    protected function mime_detect(&$test)
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