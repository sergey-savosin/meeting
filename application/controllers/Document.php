<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Document extends MY_Controller {

	function __construct() {
		parent::__construct();
		$this->load->model('Projects_model');
		$this->load->model('Documents_model');
	}

	public function insert() {
		$wrid = $this->log_webrequest();
		$this->set_webrequest_id($wrid);
		$this->Documents_model->set_webrequest_id($wrid);

		$this->log_debug('document insert', 'start document insert');

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
			$this->log_debug('document insert', $msg);

			printf($msg);
			http_response_code(400);
			exit();
		}

		// 4. get projectId

		// 4a. Try to find projectId
		$project = $this->Projects_model->get_first_project_by_name($projectname);
		$projectId = false;

		if (!$project) {
			// 4b. Try to insert new project when project not found
			$projectId = $this->Projects_model->new_project($projectname, $projectname, null, null, null, null);
		} else {
			$projectId = $project->project_id;
		}

		if (!$projectId)
		{
			$msg = "Can't add projectId by projectName: $projectname";
			$this->log_debug('document insert', $msg);

			printf($msg);
			http_response_code(400);

			exit();
		}

		// 5. Insert data to document table
		// 3. Correct Url
		$correctedUrl = $this->Documents_model->correctFileDownloadUrl($fileurl);

		// 3a. Download file by Url
		$doc_id = $this->Documents_model->new_document_with_body($correctedUrl, $filename, $projectId, $isforcreditor, $isfordebtor, $isformanager);
		// ToDo: Try-Catch

		if (!$doc_id)
		{
			$msg = "Can't load file or save document to db: $filename from URL: $correctedUrl";
			$this->log_debug('document insert', $msg);

			printf($msg);
			http_response_code(400);

			exit();
		}

		// 6. Return result from service
		$json = json_encode(array('id' => $doc_id));

		$msg = "Return value: ".$json;
		$this->log_debug('document insert', $msg);

		http_response_code(201); // 201: resourse created
		$site = 'https://vprofy.ru';
		header("Location: $site/" . $_SERVER['REQUEST_URI'] . "/$doc_id");
		header("Content-Type: application/json");
		print $json;
		
	}

	public function download() {
		$doc_id = $this->uri->segment(3);
		if (!$doc_id) {
			$this->log_debug('Document/download', 'Empty doc_id');
			show_error('Empty doc_id', 500);
		}
	// 2. Check user access
	// $val = dbIsUserExistsByLoginCode($userlogin);
	// if (!$val)
	// {
	// 	printf("Access denied. Usercode: $s", $userlogin);
	// 	exit();
	// }

		$doc_query = $this->Documents_model->get_document($doc_id);
		$filename = $doc_query->result()[0]->doc_filename;

		// заставляем браузер показать окно сохранения файла
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="'.basename($filename).'"'
				."; filename*=UTF-8''".rawurlencode($filename));
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . strlen($doc_query->result()[0]->doc_body));
		// читаем файл и отправляем его пользователю
		print($doc_query->result()[0]->doc_body);
	}

	/**
     * Tries to detect MIME type of content.
     *
     * @param string $test First few bytes of content to use for detection
     *
     * @return string
     */
    public static function mime_detect(&$test)
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