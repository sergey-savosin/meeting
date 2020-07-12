<?php namespace App\Controllers;
use CodeIgniter\I18n\Time;

class Question extends BaseController {

	public function insert() {
		// $wrid = $this->log_webrequest();
		// $this->set_webrequest_id($wrid);
		$projects_model = model('Projects_model');
		$questions_model = model('Questions_model');

		// $questions_model->set_webrequest_id($wrid);

		// $this->log_debug('question insert', 'start question insert');

		$data = $this->getPostData();
		$isRequestValid = true;
		$validationErrorText = "";
		$fileTargetDir = "?";

		// 1. Get request params
		$title = isset($data->Title) ? $data->Title : false;
		$projectname = isset($data->ProjectName) ? $data->ProjectName : false;
		$hasCsvContent = isset($data->HasCsvContent) && ($data->HasCsvContent === "true") ? true : false;

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
			// $this->log_debug('question insert', $msg);

			printf($msg);
			http_response_code(400);
			exit();
		}

		// 4. get projectId

		// 4a. Try to find projectId
		$project = $projects_model->get_project_by_name($projectname);

		if (!$project)
		{
			$msg = "Can't find project by projectName: $projectname";
			// $this->log_debug('question insert', $msg);

			printf($msg);
			http_response_code(400);

			exit();
		}

		$projectId = $project->project_id;


		// 5. Insert data to question table
		if ($hasCsvContent) {
			$new_id = $this->save_one_question($questions_model, $projectId, $title);
		} else {
			$parts = explode(';', $title);
			foreach ($parts as $part) {
				$this->save_one_question($questions_model, $projectId, $part);
			}
			$new_id = false;
		}

		// 6. Return result from service
		$json = json_encode(array('id' => $new_id));

		$msg = "Return value: ".$json;
		// $this->log_debug('document insert', $msg);

		http_response_code(201); // 201: resourse created

		// Если вызов в режиме одной вставки, то возвращаем ссылку на новый ресурс
		if ($new_id) {
			$resource = $this->request->uri->getSegment(1);
			$newUri = base_url("$resource/$new_id");
			header("Location: $newUri");
			header("Content-Type: application/json");
			echo $json;
		}
		
	}

	function save_one_question($questions_model, $projectId, $title) {
		$new_id = $questions_model->new_general_question($projectId, $title);
		
		if (!$new_id)
		{
			$msg = "Can't save question to db: $title";
			printf($msg);
			http_response_code(400);

			exit();
		}

		return $new_id;
	}
}