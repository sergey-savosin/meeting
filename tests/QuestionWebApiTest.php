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

		$_SERVER['CONTENT_TYPE'] = "application/json";

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

		$_SERVER['CONTENT_TYPE'] = "application/json";

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

		$_SERVER['CONTENT_TYPE'] = "application/json";

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
			'id' => [$expectedId, $expectedId+1]
			]);

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

		$_SERVER['CONTENT_TYPE'] = "application/json";

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
		
		//$res = $this->grabFromDatabase('question', 'qs_title', $criteria);
		//var_dump($res);
	}

	/**
	* POST question с указанием существующего ProjectName и с FileUrl добавляет вопрос
	*
	* - POST question
	* @testWith 
	* ["https://drive.google.com/file/d/1wREX77j3brL8U8uXzbg5R9rJtlPP27xB", "Untitled.jpg"]
	*/
	public function test_InsertQuestionWithFileUrlWorks($url, $filename)
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

		$_SERVER['CONTENT_TYPE'] = "application/json";

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
}