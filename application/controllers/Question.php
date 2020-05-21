<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Question extends MY_Controller {

	function __construct() {
		parent::__construct();
		$this->load->model('Projects_model');
		$this->load->model('Questions_model');
	}

	public function insert() {
		$wrid = $this->log_webrequest();
		$this->set_webrequest_id($wrid);
		$this->Questions_model->set_webrequest_id($wrid);

		$this->log_debug('question insert', 'start question insert');

		$data = $this->getPostData();
		$isRequestValid = true;
		$validationErrorText = "";
		$fileTargetDir = "?";

		// 1. Get request params
		$title = isset($data->Title) ? $data->Title : false;
		$projectname = isset($data->ProjectName) ? $data->ProjectName : false;

		// 2. Validate request attributes
		if (!$title || empty($title))
		{
			$validationErrorText.="Empty Title value in request. ";
			$isRequestValid = false;
		}

		if (!$projectname || empty($projectname))
		{
			$validationErrorText.="Empty ProjectName value in request. ";
			$isRequestValid = false;
		}

		if (!$isRequestValid)
		{
			$msg = "Invalid Question POST request: $validationErrorText";
			$this->log_debug('question insert', $msg);

			printf($msg);
			http_response_code(400);
			exit();
		}

		// 4. get projectId

		// 4a. Try to find projectId
		$project = $this->Projects_model->get_first_project_by_name($projectname);

		if (!$project)
		{
			$msg = "Can't find project by projectName: $projectname";
			$this->log_debug('question insert', $msg);

			printf($msg);
			http_response_code(400);

			exit();
		}

		$projectId = $project->project_id;


		// 5. Insert data to question table
		$new_id = $this->Questions_model->new_general_question($projectId, $title);

		if (!$new_id)
		{
			$msg = "Can't save question to db: $title";
			$this->log_debug('question insert', $msg);

			printf($msg);
			http_response_code(400);

			exit();
		}

		// 6. Return result from service
		$json = json_encode(array('id' => $new_id));

		$msg = "Return value: ".$json;
		$this->log_debug('document insert', $msg);

		http_response_code(201); // 201: resourse created
		$resource = $this->uri->segment(1);
		$uri = base_url("$resource/$new_id");
		header("Location: $uri");
		header("Content-Type: application/json");
		echo $json;
		
	}
}