<?php namespace QuestionTest\Controller;

use CodeIgniter\Test\FeatureTestCase;
use App\Database\Seeds;

class QuestionWebApiTest extends FeatureTestCase
{

	protected $refresh  = true;
	protected $seed     = \MeetingSeeder::class;

	protected $questions_model;
	protected $generalCategoryId = 1;
	protected $additionalCategoryId = 2;
	protected $acceptAdditionalCategoryId = 3;
	protected $defaultProjectId = 1;
	protected $defaultProjectName = 'ProjectName-123';
	protected $defaultUserId = 1;

	public function setUp(): void
	{
		parent::setUp();

		\Config\Services::request()->config->baseURL = $_SERVER['app.baseURL'];
		$str = \Config\Services::request()->config->baseURL;

		$_SERVER['CONTENT_TYPE'] = "application/json";

		$this->questions_model = model('Questions_model');
	}

	public function tearDown(): void
	{
		parent::tearDown();
	}

	/**
	* POST question со значениями параметров, приводящих к ошибке
	*
	* - POST question
	*
	* @testWith ["", "ProjectName", "Invalid Question POST request: Empty Title value in request."]
 	*			["TestTitle", "", "Invalid Question POST request: Empty ProjectName value in request."]
 	*			["", "", "Invalid Question POST request: Empty Title value in request.Empty ProjectName value in request."]
 	*			["TestTitle", "ProjectName-444", "Can't find project by projectName: ProjectName-444"]
	*/
	public function test_InsertQuestionValidationCases(string $title, string $projectName, string $errorMessage) : void
	{
		// Arrange
		$params = [
			'Title'=>$title,
			'ProjectName'=>$projectName
		];

		// Act
		$result = null;
		$result = $this->post('question', $params);

		// Assert
		$this->assertNotNull($result);
		$this->assertFalse($result->isRedirect());

		$result->assertStatus(400);
		$result->assertHeaderMissing('Location');
		$result->assertHeader('Content-Type', 'application/json; charset=UTF-8');
		$result->assertJSONExact(['status' => 'error', 'error' => $errorMessage]);
	}

	// ToDo: test when fileUrl is set, but defaultFileName is absent

	/****
	POST question с указанием существующего ProjectName добавляет вопрос
	
	- POST question
	*****/
	public function test_InsertQuestionWithExistingProjectNameWorks()
	{
		// Arrange
		$projectName = $this->defaultProjectName;
		$title = 'Question title - 123;Question title = 555';
		$comment = 'Question comment - 444';

		$params = [
			'Title' => $title,
			'ProjectName' => $projectName,
			'Comment' => $comment,
		];

		// Act
		$result = $this->post('question', $params);

		// Assert
		$expectedId = 2;
		$this->assertNotNull($result);
		$this->assertFalse($result->isRedirect());
		$result->assertStatus(201);
		$result->assertHeader('Location', "http://localhost/question/$expectedId");
		$result->assertHeader('Content-Type', 'application/json; charset=UTF-8');
		$result->assertJSONExact(['status' => 'ok', 'id' => $expectedId]);

		$criteria = [
			'qs_title'=>$title,
			'qs_id'=>$expectedId,
			'qs_project_id'=>$this->defaultProjectId,
			'qs_comment' => $comment
		];
		$this->seeInDatabase('question', $criteria);
	}

	/**
	* POST question с указанием существующего ProjectName и режима HasCsvContent.
	* Результат: добавлено 2 вопроса.
	* 
	* - POST question
	* @testWith ["Question title - 123;Question title = 555","Question title - 123", "Question title = 555", "true"]
	* ["Question title - 123;Question title = 555","Question title - 123", "Question title = 555", "True"]
	* ["Question title - 123;Question title = 555","Question title - 123", "Question title = 555", "TRUE"]
	*/
	public function test_InsertTwoQuestionsWithExistingProjectNameWorks($title,
		$exp1, $exp2, $hasCsvContent)
	{
		// Arrange
		$projectName = $this->defaultProjectName;
		
		$params = [
			'Title' => $title,
			'ProjectName' => $projectName,
			'HasCsvContent' => $hasCsvContent
		];

		// Act
		$result = $this->post('question', $params);

		// Assert
		$expectedId = 2;

		$this->assertNotNull($result);
		$this->assertFalse($result->isRedirect());
		$result->assertStatus(201);
		$result->assertHeaderMissing('Location');
		$result->assertHeader('Content-Type', 'application/json; charset=UTF-8');
		$result->assertJSONExact([
			'status' => 'ok',
			'id' => [$expectedId, $expectedId + 1]
			]);

		$criteria = [
			'qs_title' => $exp1,
			'qs_id' => $expectedId,
			'qs_project_id' => $this->defaultProjectId
		];
		$this->seeInDatabase('question', $criteria);

		$criteria = [
			'qs_title' => $exp2,
			'qs_id' => $expectedId + 1,
			'qs_project_id' => $this->defaultProjectId
		];
		$this->seeInDatabase('question', $criteria);

	}

	/**
	* POST question с указанием существующего ProjectName и режима HasCsvContent.
	* Результат: добавлено 3 вопроса.
	* 
	* - POST question
	* @testWith ["1. \u041e\u0442\u0447\u0451\u0442 \u0444\u0438\u043d\u0430\u043d\u0441\u043e\u0432\u043e\u0433\u043e \u0443\u043f\u0440\u0430\u0432\u043b\u044f\u044e\u0449\u0435\u0433\u043e; 2. \u041f\u0440\u043e\u0434\u043b\u0435\u043d\u0438\u0435 \u043f\u0440\u043e\u0446\u0435\u0434\u0443\u0440\u044b \u0440\u0435\u0430\u043b\u0438\u0437\u0430\u0446\u0438\u0438 \u0438\u043c\u0443\u0449\u0435\u0441\u0442\u0432\u0430; 3. \u0423\u0442\u0432\u0435\u0440\u0436\u0434\u0435\u043d\u0438\u0435 \u043f\u0440\u043e\u0436\u0438\u0442\u043e\u0447\u043d\u043e\u0433\u043e \u043c\u0438\u043d\u0438\u043c\u0443\u043c\u0430 \u0434\u043e\u043b\u0436\u043d\u0438\u043a\u0430.", "1. Отчёт финансового управляющего", " 2. Продление процедуры реализации имущества", " 3. Утверждение прожиточного минимума должника."]
	*/
	public function test_InsertThreeQuestionsWithExistingProjectNameWorks($title,
		$exp1, $exp2, $exp3)
	{
		// Arrange
		$projectName = $this->defaultProjectName;
		
		$params = [
			'Title'=>$title,
			'ProjectName'=>$projectName,
			'HasCsvContent'=>'True'
		];

		// Act
		$result = $this->post('question', $params);

		// Assert
		$expectedId = 2;

		$this->assertNotNull($result);
		$this->assertFalse($result->isRedirect());
		$result->assertStatus(201);
		$result->assertJSONExact([
			'status' => 'ok',
			'id' => [$expectedId, $expectedId+1, $expectedId+2]
			]);

		$result->assertHeaderMissing('Location');
		$result->assertHeader('Content-Type', 'application/json; charset=UTF-8');

		$criteria = [
			'qs_title'=>$exp1,
			'qs_id'=>$expectedId,
			'qs_project_id'=>$this->defaultProjectId
		];
		$this->seeInDatabase('question', $criteria);

		$criteria = [
			'qs_title'=>$exp2,
			'qs_id'=>$expectedId + 1,
			'qs_project_id'=>$this->defaultProjectId
		];
		$this->seeInDatabase('question', $criteria);

		$criteria = [
			'qs_title'=>$exp3,
			'qs_id'=>$expectedId + 2,
			'qs_project_id'=>$this->defaultProjectId
		];
		$this->seeInDatabase('question', $criteria);

		$criteria = [
			'qs_id'=>$expectedId + 2,
			'qs_project_id'=>$this->defaultProjectId
		];
	}

	/**
	* POST question с указанием существующего ProjectName и с FileUrl добавляет вопрос
	*
	* - POST question
	* @group Curl
	* @testWith 
	* ["https://drive.google.com/file/d/1wREX77j3brL8U8uXzbg5R9rJtlPP27xB", "Untitled.jpg"]
	*/
	public function test_Curl_InsertQuestionWithFileUrlWorks($url, $filename)
	{
		// Arrange
		$projectName = $this->defaultProjectName;
		$title = 'Question title - 123;Question title = 555';

		$params = [
			'Title'=>$title,
			'ProjectName'=>$projectName,
			'FileName'=>'test.xlsx',
			'FileUrl'=>$url,
		];

		// Act
		$result = $this->post('question', $params);

		// Assert
		$expectedId = 2;
		$docId = 1;

		$this->assertNotNull($result);
		$this->assertFalse($result->isRedirect());
		$result->assertStatus(201);
		$result->assertHeader('Location', "http://localhost/question/$expectedId");
		$result->assertHeader('Content-Type', 'application/json; charset=UTF-8');
		$result->assertJSONExact(['status' => 'ok', 'id' => $expectedId]);

		$criteria = [
			'qs_title'=>$title,
			'qs_id'=>$expectedId,
			'qs_project_id'=>$this->defaultProjectId
		];
		$this->seeInDatabase('question', $criteria);

		$criteria = [
			'doc_id'  => $docId,
			'doc_filename' => $filename,
			'doc_is_for_creditor' => 1,
			'doc_is_for_debtor' => 1,
			'doc_is_for_manager' => 1
		];
		$this->seeInDatabase('document', $criteria);

		$criteria = [
			'docfile_doc_id' => $docId,
		];
		$this->seeInDatabase('docfile', $criteria);

		$criteria = [
			'qd_question_id' => $expectedId,
			'qd_doc_id' => $docId,
		];
		$this->seeInDatabase('question_document', $criteria);

	}

	/**
	* POST question с указанием существующего ProjectName и режима HasCsvContent.
	* Результат: добавлено 2 вопроса.
	* 
	* - POST question
	* @group Curl
	* @testWith ["t1;t5","https://drive.google.com/file/d/1wREX77j3brL8U8uXzbg5R9rJtlPP27xB;https://docs.google.com/document/d/1KSROwOW-Q43AEW2Rh0iKHE0pV134dImAHmg1tb3Ofdg","t1","t5","Untitled.jpg","Собрание и голосование.docx"]
	* ["t1;t5","https://drive.google.com/file/d/1wREX77j3brL8U8uXzbg5R9rJtlPP27xB","t1","t5","Untitled.jpg",""]
	*/
	public function test_Curl_InsertTwoQuestionsWithFileUrlsNameWorks(
		$title, $fileUrl,
		$expTitle1, $expTitle2,
		$expFileName1, $expFileName2)
	{
		// Arrange
		$projectName = $this->defaultProjectName;
		
		$params = [
			'Title' => $title,
			'ProjectName' => $projectName,
			'Comment' => null,
			'FileUrl' => $fileUrl,
			'HasCsvContent' => 'True'
		];

		// Act
		$result = $this->post('question', $params);

		// Assert
		$expQuestionId = 2;
		$expDocId = 1;

		$this->assertNotNull($result);
		$this->assertFalse($result->isRedirect());
		$result->assertStatus(201);
		$result->assertHeaderMissing('Location');
		$result->assertHeader('Content-Type', 'application/json; charset=UTF-8');
		$result->assertJSONExact([
			'status' => 'ok',
			'id' => [$expQuestionId, $expQuestionId + 1]
			]);

		// test Question data
		$this->seeQuestion($expTitle1, null, $expQuestionId, $this->defaultProjectId);

		$this->seeQuestion($expTitle2, null, $expQuestionId + 1, $this->defaultProjectId);

		// Only First document
		if ($expFileName1 <> '' && $expFileName2 == '') {
			$this->seeQuestionDocument($expQuestionId, $expDocId, $expFileName1);
		}

		// First and Second document
		if ($expFileName1 <> '' && $expFileName2 <> '') {
			$this->seeQuestionDocument($expQuestionId, $expDocId, $expFileName1);
			$this->seeQuestionDocument($expQuestionId + 1, $expDocId + 1, $expFileName2);
		}

	}

	/**
	* POST question с указанием существующего ProjectName и режима HasCsvContent.
	* Результат: добавлено 2 вопроса.
	* 
	* - POST question
	* @group Curl
	* @testWith ["t1;t5",";https://docs.google.com/document/d/1KSROwOW-Q43AEW2Rh0iKHE0pV134dImAHmg1tb3Ofdg","t1","t5","Собрание и голосование.docx"]
	*/
	public function test_Curl_InsertTwoQuestionsWithSecondFileUrlNameWorks(
		$title, $fileUrl,
		$expTitle1, $expTitle2,
		$expFileName2)
	{
		// Arrange
		$projectName = $this->defaultProjectName;
		
		$params = [
			'Title' => $title,
			'ProjectName' => $projectName,
			'Comment' => null,
			'FileUrl' => $fileUrl,
			'HasCsvContent' => 'True'
		];

		// Act
		$result = $this->post('question', $params);

		// Assert
		$expQuestionId = 2;
		$expDocId = 1;

		$this->assertNotNull($result);
		$this->assertFalse($result->isRedirect());
		$result->assertStatus(201);
		$result->assertHeaderMissing('Location');
		$result->assertHeader('Content-Type', 'application/json; charset=UTF-8');
		$result->assertJSONExact([
			'status' => 'ok',
			'id' => [$expQuestionId, $expQuestionId + 1]
			]);

		// test Question data
		$this->seeQuestion($expTitle1, null, $expQuestionId, $this->defaultProjectId);

		$this->seeQuestion($expTitle2, null, $expQuestionId + 1, $this->defaultProjectId);

		// Only Second document
		$this->seeQuestionDocument($expQuestionId + 1, $expDocId, $expFileName2);
		// ToDo: dontsee first document
	}

	/**
	* POST question с указанием существующего ProjectName и режима HasCsvContent.
	* Результат: добавлено 2 вопроса.
	* 
	* - POST question
	* @testWith ["t1;t5","c1;c2","t1","t5","c1","c2"]
	* ["t1;t5","c1","t1","t5","c1",null]
	* ["t1;t5",";c2","t1","t5",null,"c2"]
	* ["t1;t5","","t1","t5",null,null]
	*/
	public function test_InsertTwoQuestionsWithCommentWorks(
		$title, $comment,
		$expTitle1, $expTitle2,
		$expComment1, $expComment2)
	{
		// Arrange
		$projectName = $this->defaultProjectName;
		
		$params = [
			'Title' => $title,
			'ProjectName' => $projectName,
			'Comment' => $comment,
			'FileUrl' => null,
			'HasCsvContent' => 'True'
		];

		// Act
		$result = $this->post('question', $params);

		// Assert
		$expQuestionId = 2;
		$expDocId = 1;

		$this->assertNotNull($result);
		$this->assertFalse($result->isRedirect());
		$result->assertStatus(201);
		$result->assertHeaderMissing('Location');
		$result->assertHeader('Content-Type', 'application/json; charset=UTF-8');
		$result->assertJSONExact([
			'status' => 'ok',
			'id' => [$expQuestionId, $expQuestionId + 1]
			]);

		// test Question data
		$this->seeQuestion($expTitle1, $expComment1, $expQuestionId, $this->defaultProjectId);

		$this->seeQuestion($expTitle2, $expComment2, $expQuestionId + 1, $this->defaultProjectId);

		// No documents
		$this->dontSeeQuestionDocument($expQuestionId, $expDocId, null);
	}

	protected function seeQuestion($title, $comment, $questionId, $projectId) {
		$criteria = [
			'qs_title' => $title,
			'qs_comment' => $comment,
			'qs_id' => $questionId,
			'qs_project_id' => $projectId
		];

		$this->seeInDatabase('question', $criteria);
	}

	protected function seeQuestionDocument($questionId, $documentId, $fileName) {
		$criteriaQD = [
			'qd_question_id' => $questionId,
			'qd_doc_id' => $documentId
		];
		$criteriaD = [
			'doc_id' => $documentId,
			'doc_filename' => $fileName
		];
		$this->seeInDatabase('question_document', $criteriaQD);
		$this->seeInDatabase('document', $criteriaD);
	}

	protected function dontSeeQuestionDocument($questionId, $documentId, $fileName) {
		$criteriaQD = [
			'qd_question_id' => $questionId,
			'qd_doc_id' => $documentId
		];
		
		$criteriaD = [
			'doc_id' => $documentId,
			'doc_filename' => $fileName
		];
		
		$this->dontSeeInDatabase('question_document', $criteriaQD);
		
		if (!empty($fileName)) {
			$this->dontSeeInDatabase('document', $criteriaD);
		}
	}

}