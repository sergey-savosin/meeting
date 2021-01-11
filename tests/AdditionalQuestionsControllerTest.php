<?php namespace AdditionalQuestionsTest\Controller;

use CodeIgniter\Test\FeatureTestCase;
use CodeIgniter\Test\ControllerTester;
use App\Database\Seeds;

class AdditionalQuestionsControllerTest extends FeatureTestCase
{

	use ControllerTester;

	protected $refresh  = true;
	protected $seed     = \MeetingSeeder::class;

	protected $projects_model;
	protected $defaultProjectId = 1;
	protected $generalCategoryId = 1;
	protected $additionalCategoryId = 2;
	protected $acceptAdditionalCategoryId = 3;
	protected $defaultUserId = 1;
	protected $defaultUserCode = '123';
	protected $defaultQuestionId = 1;

	public function setUp(): void
	{
		parent::setUp();

		\Config\Services::request()->config->baseURL = $_SERVER['app.baseURL'];

		$_SESSION['user_login_code'] = $this->defaultUserCode;
		$_SESSION['user_project_id'] = $this->defaultProjectId;
		$_SESSION['user_id'] = $this->defaultUserId;

		$validation = \Config\Services::validation();
		$validation->reset();

		$this->questions_model = model('Questions_model');
	}

	public function tearDown(): void
	{
		parent::tearDown();
	}

	/**
	* POST AdditionalQuestion показывает ошибку валидации
	*
	* - AdditionalQuestion::index
	* @group mockrequest
	*/
	public function test_AdditionalQuestionsControllerPostShowValidationError()
	{
		log_message('info', '----------------------------------------------------------');
		log_message('info', '--- test: test_AdditionalQuestionsControllerPostShowValidationError ---');
		// Arrange
		$data = ['qs_title' => null];
		$request = \Config\Services::request();
		$request->setGlobal('request', $data);
		$request->setGlobal('post', $data);
		$request->setMethod('post');
		
		// Act
		$result = $this->withRequest($request)
				->withSession()
		 		->controller(\App\Controllers\AdditionalQuestions::class)
		 		->execute("index");

		//log_message('info', 'result: '.$result->getBody());
		
		// Assert
		$this->assertTrue($result->
			see('Укажите Текст вопроса.'));
	}

	/**
	* POST AdditionalQuestion сохраняет данные
	*
	* - AdditionalQuestion::index
	*/
	public function test_AdditionalQuestionsControllerPostOk()
	{
		// Arrange
		$questionTitle = 'test-question-123';
		$questionComment = 'test comment 444';
		$questionId = $this->defaultQuestionId + 1;

		$data = [
			'qs_title' => $questionTitle,
			'qs_comment' => $questionComment
		];
		$request = \Config\Services::request();
		$request->setGlobal('request', $data);
		$request->setGlobal('post', $data);
		$request->setMethod('post');
		
		// Act
		$result = $this->withRequest($request)
				->withSession()
		 		->controller(\App\Controllers\AdditionalQuestions::class)
		 		->execute("index");

		log_message('info', 'result: '.$result->getBody());

		// Assert
		$data = [
			'qs_id' => $questionId,
			'qs_title' => $questionTitle,
			'qs_comment' => $questionComment,
			'qs_category_id' => $this->additionalCategoryId
		];
		$this->seeInDatabase('question', $data);

		$data = [
			'qs_id' => $questionId+1,
			'qs_title' => $questionTitle,
			'qs_comment' => $questionComment,
			'qs_category_id' => $this->acceptAdditionalCategoryId
		];
		$this->seeInDatabase('question', $data);
	}

	/**
	* POST AdditionalQuestion с файлом сохраняет данные
	*
	* - AdditionalQuestion::index
	*/
	public function test_AdditionalQuestionsControllerPostSavesFileOk()
	{
		// Arrange
		$questionTitle = 'test-question-123';
		$questionComment = 'test comment 444';
		$questionId = $this->defaultQuestionId + 1;

		$data = [
			'qs_title' => $questionTitle,
			'qs_comment' => $questionComment,
			'documentFile[]' => '1324'
		];
		$request = \Config\Services::request();
		$request->setGlobal('request', $data);
		$request->setGlobal('post', $data);
		$request->setMethod('post');
		
		// Act
		$result = $this->withRequest($request)
				->withSession()
		 		->controller(\App\Controllers\AdditionalQuestions::class)
		 		->execute("index");

		log_message('info', 'result: '.$result->getBody());

		// Assert
		$data = [
			'qs_id' => $questionId,
			'qs_title' => $questionTitle,
			'qs_comment' => $questionComment,
			'qs_category_id' => $this->additionalCategoryId
		];
		$this->seeInDatabase('question', $data);

		$data = [
			'qs_id' => $questionId+1,
			'qs_title' => $questionTitle,
			'qs_comment' => $questionComment,
			'qs_category_id' => $this->acceptAdditionalCategoryId
		];
		$this->seeInDatabase('question', $data);
	}
}