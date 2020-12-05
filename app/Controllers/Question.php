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
		$documents_model = model('Documents_model');

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
		
			// 0. ToDo: begin tran

			// 1. save question
			log_message('info', "Question::insert. Title: $title");
			$new_id = $this->save_one_question($questions_model, $projectId, $title,
				$comment);
			if (!$new_id)
				{
					$msg = " Can't save question to db: $title.";
					log_message('info', "Question::save_one_document - error:$msg");
					
					return $this->response
						->setStatusCode(400)
						->removeHeader("Location")
						->setJSON($body);
				}
			
			// 2. save document
			if (!empty($fileUrl)) {
				$doc_id = $this->save_one_document($documents_model, 
					$fileUrl, $defaultFileName);

				if (!$doc_id) {
					$msg = " Can't load file or save document to db: $filename from URL: $correctedUrl.";
					$body = ['error' => $msg];
					log_message('info', "Question::save_one_document - error:$msg");

					return $this->response
						->setStatusCode(400)
						->removeHeader("Location")
						->setJSON($body);
				}

				// 3. link document and question
				$qd_id = $this->link_question_and_document($questions_model, $new_id, $doc_id);
			}
		
			// 4. ToDo: commit tran

		} else {
			log_message('info', "Question::insert. Title with csv: $title");
			$titles = explode(';', $title);
			$comments = explode(';', $comment);
			$fileUrls = explode(';', $fileUrl);
			foreach ($titles as $titlePart) {
				// todo: get comment, title from array
				$temp_id = $this->save_one_question($questions_model, $projectId, $titlePart, '');
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

	/**
	* Сохранить в БД вопрос
	*/
	function save_one_question($questions_model, $projectId, $title, $comment) {
		$new_id = $questions_model->new_general_question($projectId,
			$title, $comment);
		
		return $new_id;
	}

	/**
	* Скачать и сохранить в БД документ
	* @return doc_id
	*/
	function save_one_document($documents_model, $fileUrl, $defaultFileName) {
		// 1. Correct Url
		$correctedUrl = $documents_model->correctFileDownloadUrl($fileUrl);
		log_message('info', 
			'Question::save_one_document - corrected URL for download: '.$correctedUrl);

		// 2. Download file by Url and iInsert data to document table
		$new_id = $documents_model->new_document_with_body($correctedUrl,
			$defaultFileName,
			true, true, true);

		return $new_id;
	}

	/**
	* Связать документ и вопрос
	*/
	function link_question_and_document($questions_model, $qs_id, $doc_id) {
		$new_id = $questions_model->link_question_and_document($qs_id, $doc_id);

		return $new_id;
	}
}