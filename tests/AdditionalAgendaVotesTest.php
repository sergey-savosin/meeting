<?php namespace AdditionalAgendaVotesTest\Controller;

use CodeIgniter\Test\FeatureTestCase;
use App\Database\Seeds;

class AdditionalAgendaVotesTest extends FeatureTestCase
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
		$this->answers_model = model('Answers_model');
	}

	public function tearDown(): void
	{
		parent::tearDown();
	}

	/**
	* GET additionalagendavotes приводит к переходу на страницу авторизации
	*
	* - GET additionalagendavotes
	*/
	public function test_GetAdditionalAgendaVotesUnauthorizedSessionStartsRedirect()
	{
		$result = $this->get('additionalagendavotes/index');
		$this->assertNotNull($result);
		$this->assertTrue($result->isRedirect());
		$redirectUrl = $result->getRedirectUrl();
		$this->assertRegExp('/\/User\/login/', $redirectUrl);
		$result->assertOK();
	}

	/**
	* GET additionalagendavotes с пустым списком доп вопросом приводит
	* к показу пустого списка доп вопросов для голосования
	*
	* - POST existing user
	* - GET additionalagendavotes
	*/
	public function test_GetAdditionalAgendaVotesWithEmptyDataShowsEmptyQuestionsList()
	{
		// Arrange
		$userResult = $this->post('user/login', [
			'usr_code' => '123'
		]);
		$this->assertNotNull($userResult);

		$userResult->assertSessionHas('user_login_code', '123');
		$userResult->assertSessionHas('user_project_id', '1');

		// check absence of additional question
		$title = 'test-question-123';
		$comment = "Question comment 123";

		$this->dontSeeInDatabase('question',
			[
				'qs_category_id' => $this->additionalCategoryId
			]);

		// Act
		$response = $this->withSession()->get('additionalagendavotes/index');

		// Assert
		$response->assertStatus(200);
		$response->assertOK();
		$response->assertSee('Вопросы дополнительной повестки');
		$response->assertDontSee($title);
	}

	/**
	* GET additionalagendavotes с списком доп вопросом без принятия приводит
	* к показу пустого списка доп вопросов
	*
	* - POST existing user
	* - Add additional question
	* - GET additionalagendavotes
	*/
	public function test_GetAdditionalAgendaVotesWithNonacceptedQuestionShowsQuestionsList()
	{
		$title = 'test-question-444';
		$comment = "Question comment 444";

		// Arrange
		$userResult = $this->post('user/login', [
			'usr_code' => '123'
		]);
		$this->assertNotNull($userResult);

		$userResult->assertSessionHas('user_login_code', '123');
		$userResult->assertSessionHas('user_project_id', '1');

		// prepare additional question
		$qs_id = $this->questions_model->new_additional_question($this->defaultProjectId,
			$this->defaultUserId, $title, $comment);

		$this->seeInDatabase('question',
			[
				'qs_id' => $qs_id,
				'qs_category_id' => $this->additionalCategoryId,
				'qs_title' => $title,
				'qs_user_id' => $this->defaultUserId
			]);

		// Act
		$response = $this->withSession()->get('additionalagendavotes/index');

		// Assert
		$response->assertStatus(200);
		$response->assertOK();
		$response->assertSee('Вопросы дополнительной повестки');
		$response->assertDontSee($title);
	}

	/**
	* GET additionalagendavotes с списком доп вопросом с принятием приводит
	* к показу списка доп вопросов
	*
	* - POST existing user
	* - Add additional question
	* - Add accept vote
	* - GET additionalagendavotes
	*/
	public function test_GetAdditionalAgendaVotesWithAcceptedQuestionShowsQuestionsList()
	{
		$title = 'test-question-444';
		$comment = "Question comment 444";

		// Arrange
		$userResult = $this->post('user/login', [
			'usr_code' => '123'
		]);
		$this->assertNotNull($userResult);

		$userResult->assertSessionHas('user_login_code', '123');
		$userResult->assertSessionHas('user_project_id', '1');

		// prepare additional question
		$qs_id = $this->questions_model->new_additional_question($this->defaultProjectId,
			$this->defaultUserId, $title, $comment);

		$accept_qs_id = $qs_id + 1;

		$this->seeInDatabase('question',
			[
				'qs_id' => $qs_id,
				'qs_category_id' => $this->additionalCategoryId,
				'qs_title' => $title,
				'qs_user_id' => $this->defaultUserId
			]);

		$this->seeInDatabase('question',
			[
				'qs_id' => $accept_qs_id,
				'qs_category_id' => $this->acceptAdditionalCategoryId,
				'qs_title' => $title,
				'qs_user_id' => $this->defaultUserId
			]);
		
		// add 'Yes' vote for accept additional question
		$this->answers_model->new_general_answer($accept_qs_id, $this->defaultUserId, 0, '0', 1, 'comment');

		// Act
		$response = $this->withSession()->get('additionalagendavotes/index');

		// Assert
		$response->assertStatus(200);
		$response->assertOK();
		$response->assertSee('Вопросы дополнительной повестки');
		$response->assertSee($title);
	}

	/****
	POST additionalagendavotes приводит к сохранению результата голосования
	
	- POST existing user
	- POST additionalagendavotes
	*****/
	public function mute_PostAdditionalAgendaVotesAuthorizedSessionSavesAnswer()
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
		$response = $this->withSession()->post('additionalagendavotes/index', $data);

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