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

	/****
	POST question с указанием существующего ProjectName и режима HasCsvContent.
	Результат: добавлено 2 вопроса.
	
	- POST question
	*****/
	public function test_InsertTwoQuestionsWithExistingProjectNameWorks()
	{
		// Arrange
		$projectName = $this->defaultProjectName;
		$title1 = 'Question title - 123';
		$title2 = 'Question title = 555';
		
		$params = [
			'Title'=>"$title1;$title2",
			'ProjectName'=>$projectName,
			'HasCsvContent'=>'true'
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
			'qs_title'=>$title1,
			'qs_id'=>$expectedId,
			'qs_project_id'=>$this->defaultProjectId
		];
		$this->seeInDatabase('question', $criteria);

		$criteria = [
			'qs_title'=>$title2,
			'qs_id'=>$expectedId + 1,
			'qs_project_id'=>$this->defaultProjectId
		];
		$this->seeInDatabase('question', $criteria);

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