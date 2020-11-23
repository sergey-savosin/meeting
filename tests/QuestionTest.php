<?php namespace QuestionTest\Controller;

use CodeIgniter\Test\FeatureTestCase;
use App\Database\Seeds;

class QuestionTest extends FeatureTestCase
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
	* Тест модели
	*/
	public function test_QuestionModelNewGeneralQuestionAdded()
	{
		$title = "Test question - 123";
		$comment = "Question comment 123";

		$this->questions_model->new_general_question($this->defaultProjectId, $title, $comment, '', '');

		$this->seeInDatabase('question', ['qs_title' => $title, 'qs_category_id' => $this->generalCategoryId]);
	}

	/**
	* Тест модели
	*/
	public function test_QuestionModelNewAdditionalQuestionAdded()
	{
		$title = "Test question - 123";

		$this->questions_model->new_additional_question($this->defaultProjectId, $title, $this->defaultUserId);

		$this->seeInDatabase('question', ['qs_title' => $title, 'qs_category_id' => $this->additionalCategoryId]);
		$this->seeInDatabase('question', ['qs_title' => $title, 'qs_category_id' => $this->acceptAdditionalCategoryId]);
	}

	/**
	* Тест модели
	*/
	public function test_QuestionModelFetchGeneralQuestionsWorks()
	{
		// Arrange
		$title = "Test question - 123";
		$comment = "Question comment 123";
		$this->questions_model->new_general_question($this->defaultProjectId, $title, $comment, '', '');

		// Act
		$q_list = $this->questions_model->fetch_general_questions($this->defaultProjectId);
		$result = $q_list->getResult();

		// Assert
		$this->assertCount(2, $result); // seed item + new item
		$res = $result[1];
		$expectedId = 2;

		$expected = new \stdClass;
		$expected->qs_id = $expectedId;
		$expected->qs_project_id = $this->defaultProjectId;
		$expected->qs_category_id = $this->generalCategoryId;
		$expected->qs_title = $title;
		$expected->qs_user_id = null;
		$expected->qs_comment = $comment;
		$expected->qs_created_at = 'current_timestamp()';
		$expected->qs_base_question_id = null;

		$this->assertEquals($expected, $res);
	}

	/**
	* POST question со значениями параметров, приводящих к ошибке
	*
	* - POST project
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
		$result->assertJSONExact(['error'=>$errorMessage]);
	}

	/****
	POST question с указанием существующего ProjectName добавляет вопрос
	
	- POST project
	*****/
	public function test_InsertQuestionWithExistingProjectNameWorks()
	{
		// Arrange
		$projectName = $this->defaultProjectName;
		$title = 'Question title - 123;Question title = 555';

		$params = [
			'Title'=>$title,
			'ProjectName'=>$projectName
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
		$result->assertJSONExact(['id'=>$expectedId]);

		$criteria = [
			'qs_title'=>$title,
			'qs_id'=>$expectedId,
			'qs_project_id'=>$this->defaultProjectId
		];
		$this->seeInDatabase('question', $criteria);
	}

	/****
	POST question с указанием существующего ProjectName и режима HasCsvContent.
	Результат: добавлено 2 вопроса.
	
	- POST project
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
		$result->assertJSONExact(array('id'=>[$expectedId, $expectedId+1]));

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

}