<?php namespace VotesControllerTest\Controller;

use CodeIgniter\Test\FeatureTestCase;
use CodeIgniter\Test\ControllerTester;
use App\Database\Seeds;

class VotesControllerTest extends FeatureTestCase
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
		$str = \Config\Services::request()->config->baseURL;

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
	* POST votes показывает ошибку валидации

	- POST project
	*/
	public function test_VotesControllerPostShowValidationError()
	{
		// Arrange
		$questionTitle = 'test-question-123';

		$data = ["optradio" => []];
		$request = \Config\Services::request();
		$request->setGlobal('request', $data);
		$request->setMethod('post');
		// $request->setGlobal('post', $data);
		
		// Act
		$result = $this->withRequest($request)
				->withSession()
		 		->controller(\App\Controllers\Votes::class)
		 		->execute("index");

		// Assert
		$this->assertTrue($result->
			see('Выберите ответ на вопрос "'.$questionTitle.'".'));
	}

	/**
	* POST project обновляет вопрос по умолчанию

	- POST project
	*/
	public function test_VotesControllerPostUpdatesDefaultAnswer()
	{
		// Arrange
		$questionId = $this->defaultQuestionId;
		$ans_number = 1;
		$data = ["optradio" => [$questionId => $ans_number]];
		
		$request = \Config\Services::request();
		$request->setGlobal('request', $data);
		$request->setMethod('post');
		// $emptydata = [];
		// $request->setGlobal('post', $emptydata);
		
		// Act
		$result = $this->withRequest($request)
				->withSession()
		 		->controller(\App\Controllers\Votes::class)
		 		->execute("index");

		// Assert
		$data = ['ans_question_id' => $questionId, 'ans_number' => $ans_number];
		$this->seeInDatabase('answer', $data);
	}

	/**
	* POST project обновляет вопрос по умолчанию

	- POST project
	*/
	public function test_VotesControllerPostCreatesNewAnswer()
	{
		// Arrange

		$firstQuestionId = $this->defaultQuestionId;
		$newQuestionId = $firstQuestionId + 1;
		$first_ans_number = 2;
		$ans_number = 1;

		// add new question
		$title = "Test question - 456";
		$comment = "Question comment 456";

		$this->questions_model->new_general_question($this->defaultProjectId, $title, $comment, '', '');

		$this->seeInDatabase('question',
				[
					'qs_title' => $title, 
					'qs_category_id' => $this->generalCategoryId
				]);

		// prepare request
		$data = ["optradio" =>
			[
				$firstQuestionId => $first_ans_number,
				$newQuestionId => $ans_number,
			]];
		
		$request = \Config\Services::request();
		$request->setGlobal('request', $data);
		$request->setMethod('post');
		// $emptydata = [];
		// $request->setGlobal('post', $emptydata);
		
		// Act
		$result = $this->withRequest($request)
				->withSession()
		 		->controller(\App\Controllers\Votes::class)
		 		->execute("index");

		// Assert
		$data = ['ans_question_id' => $firstQuestionId, 'ans_number' => $first_ans_number];
		$this->seeInDatabase('answer', $data);

		$data = ['ans_question_id' => $newQuestionId, 'ans_number' => $ans_number];
		$this->seeInDatabase('answer', $data);
	}

}