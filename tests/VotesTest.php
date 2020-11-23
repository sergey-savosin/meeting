<?php namespace VotesTest\Controller;

use CodeIgniter\Test\FeatureTestCase;
use App\Database\Seeds;

class VotesTest extends FeatureTestCase
{

	protected $refresh  = true;
	protected $seed     = \MeetingSeeder::class;

	protected $projects_model;
	protected $defaultProjectId = 1;
	protected $generalCategoryId = 1;
	protected $additionalCategoryId = 2;
	protected $acceptAdditionalCategoryId = 3;
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

	/****
	GET votes приводит к переходу на страницу авторизации
	
	- GET votes
	*****/
	public function test_GetVotesUnauthorizedSessionStartsRedirect()
	{
		$result = $this->get('votes/index');
		$this->assertNotNull($result);
		$this->assertTrue($result->isRedirect());
		$redirectUrl = $result->getRedirectUrl();
		$this->assertRegExp('/\/User\/login/', $redirectUrl);
		$result->assertOK();
	}

	/****
	GET votes приводит к показу списка вопросов для голосования
	
	- POST existing user
	- GET votes
	*****/
	public function test_GetVotesAuthorizedSessionShowsQuestionsList()
	{
		// Arrange
		$userResult = $this->post('user/login', [
			'usr_code' => '123'
		]);
		$this->assertNotNull($userResult);

		$userResult->assertSessionHas('user_login_code', '123');
		$userResult->assertSessionHas('user_project_id', '1');

		// check general question
		$title = 'test-question-123';
		$comment = "Question comment 123";

		$this->seeInDatabase('question',
			[
				'qs_title' => $title,
				'qs_category_id' => $this->generalCategoryId
			]);

		// Act
		$response = $this->withSession()->get('votes/index');

		// Assert
		$response->assertStatus(200);
		$response->assertOK();
		$response->assertSee('Вопросы основной повестки');
		$response->assertSee($title);
	}

	/****
	POST votes приводит к сохранению результата голосования
	
	- POST existing user
	- POST votes
	*****/
	public function test_PostVotesAuthorizedSessionSavesAnswer()
	{
		// Arrange
		$userResult = $this->post('user/login', [
			'usr_code' => '123'
		]);
		$this->assertNotNull($userResult);

		$userResult->assertSessionHas('user_login_code', '123');
		$userResult->assertSessionHas('user_project_id', '1');

		// check general question
		$title = 'test-question-123';
		$comment = "Question comment 123";

		$this->seeInDatabase('question',
			[
				'qs_title' => $title,
				'qs_category_id' => $this->generalCategoryId
			]);

		// Act
		$data = ['optradio[1]'=>1];
		$response = $this->withSession()->post('votes/index', $data);

		//print("--\r\n--");
		//$content = $response->getJSON();
		//print($content);

		// Assert

		$response->assertStatus(200);
		$response->assertOK();
		//$this->AssertTrue($response->isRedirect());
		$this->dontSeeInDatabase('answer',
			[
				'ans_question_id' => 1,
				'ans_number' => 1
			]);

		$response->assertSee('Выберите ответ на вопрос "test-question-123".');
		//$response->assertSee($title);
	}
}