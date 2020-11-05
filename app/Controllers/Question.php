<?php namespace App\Controllers;
use CodeIgniter\I18n\Time;

class Question extends BaseController {

	/****************
	 * V4
	 * UnitTest
	 */
	public function insert() {
		$projects_model = model('Projects_model');
		$questions_model = model('Questions_model');

		log_message('info', '[question insert] starts.');

		$data = $this->getPostData();
		$isRequestValid = true;
		$validationErrorText = "";
		$fileTargetDir = "?";

		// 1. Get request params
		$title = isset($data->Title) ? $data->Title : false;
		$projectname = isset($data->ProjectName) ? $data->ProjectName : false;
		$hasCsvContent = isset($data->HasCsvContent) && ($data->HasCsvContent === "true") ? true : false;
		$comment = isset($data->Comment) ? $data->Comment : false;
		$fileUrl = isset($data->FileUrl) ? $data->FileUrl : false;
		$defaultFileName = isset($data->DefaultFileName) ? $data->DefaultFileName : false;

		// 2. Validate request attributes
		if (!$title || empty($title))
		{
			$validationErrorText.="Empty Title value in request.";
			$isRequestValid = false;
		}

		if (!$projectname || empty($projectname))
		{
			$validationErrorText.="Empty ProjectName value in request.";
			$isRequestValid = false;
		}

		if (!$isRequestValid)
		{
			$msg = "Invalid Question POST request: $validationErrorText";
			log_message('info', "[question insert] error: $msg");

			return $this->response
				->setStatusCode(400)
				->removeHeader('Location')
				->setJSON(['error'=>$msg]);
		}

		// 4. get projectId

		// 4a. Try to find projectId
		$project = $projects_model->get_project_by_name($projectname);

		if (!$project)
		{
			$msg = "Can't find project by projectName: $projectname";
			log_message('info', "[question insert] error: $msg");

			return $this->response
				->setStatusCode(400)
				->removeHeader('Location')
				->setJSON(['error'=>$msg]);
		}

		$projectId = $project->project_id;


		// 5. Insert data to question table
		$new_ids = array();
		$new_id = false;
		if (!$hasCsvContent) {
			$new_id = $this->save_one_question($questions_model, $projectId, $title, $comment, $fileUrl,
				$defaultFileName);
		} else {
			$titles = explode(';', $title);
			$comments = explode(';', $comment);
			$fileUrls = explode(';', $fileUrl);
			foreach ($titles as $titlePart) {
				// todo: get comment, title from array
				$temp_id = $this->save_one_question($questions_model, $projectId, $titlePart, '', '', '');
				$new_ids[] = $temp_id;
			}
		}

		// 6. Return result from service

		if ($new_id) {
			// Если вызов в режиме одной вставки, то возвращаем ссылку на новый ресурс
			$resource = $this->request->uri->getSegment(1);
			$newUri = base_url("$resource/$new_id");
			$body = array('id' => $new_id);

			log_message('info', "[question insert] result ok: ". json_encode($body));

			return $this->response
				->setStatusCode(201) // 201: resourse created
				->setHeader("Location", $newUri)
				->setJSON($body);
		} else {
			// добавлено несколько строк
			$body = array('id' => $new_ids);
			return $this->response
				->setStatusCode(201) // 201: resourse created
				->removeHeader('Location')
				->setJSON($body);
		}
		
	}

	function save_one_question($questions_model, $projectId, $title, $comment, $fileUrl, $defaultFileName) {
		$new_id = $questions_model->new_general_question($projectId, $title, '', '', '');
		
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