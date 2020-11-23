<?php namespace AdditionalQuestionTest\Controller;

use CodeIgniter\Test\FeatureTestCase;
use App\Database\Seeds;

class AdditionalQuestionTest extends FeatureTestCase
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
	GET additionalquestion приводит к переходу на страницу авторизации
	
	- GET additionalquestion
	*****/
	public function test_GetAdditionalQuestionUnauthorizedSessionStartsRedirect()
	{
		$result = $this->get('additionalquestions/index');
		$this->assertNotNull($result);
		$this->assertTrue($result->isRedirect());
		$redirectUrl = $result->getRedirectUrl();
		$this->assertRegExp('/\/User\/login/', $redirectUrl);
		$result->assertOK();
	}

	/****
	GET additionalquestion приводит к показу списка доп. вопросов
	
	- POST existing user
	- GET additionalquestion
	*****/
	public function test_GetAdditionalQuestionAuthorizedSessionShowsQuestionsList()
	{
		// Arrange
		$userResult = $this->post('user/login', [
			'usr_code' => '123'
		]);
		$this->assertNotNull($userResult);

		$userResult->assertSessionHas('user_login_code', '123');
		$userResult->assertSessionHas('user_project_id', '1');

		$title = "Test question - 123";

		// add additional question
		$this->questions_model->new_additional_question($this->defaultProjectId, $title, $this->defaultUserId);

		$this->seeInDatabase('question', ['qs_title' => $title, 'qs_category_id' => $this->additionalCategoryId]);
		$this->seeInDatabase('question', ['qs_title' => $title, 'qs_category_id' => $this->acceptAdditionalCategoryId]);

		// Act
		$response = $this->withSession()->get('additionalquestions/index');

		// Assert
		$response->assertStatus(200);
		$response->assertOK();
		$response->assertSee('Список ваших дополнительных вопросов');
		$response->assertSee($title);
	}

	/****
	POST additionalquestion приводит к добавлению доп. вопроса
	
	- POST existing user
	- GET additionalquestion
	*****/
	public function test_PostAdditionalQuestionAuthorizedSessionAddsAdditionalQuestion()
	{
		// Arrange
		$userResult = $this->post('user/login', [
			'usr_code' => '123'
		]);
		$this->assertNotNull($userResult);

		$userResult->assertSessionHas('user_login_code', '123');
		$userResult->assertSessionHas('user_project_id', '1');

		$title = "Test question - 123";

		// Act
		$data = ['qs_title'=>$title];
		$response = $this->withSession()->post('additionalquestions/index', $data);
		
		// Assert
		$this->assertNotNull($response);
		$response->assertOK();
		$this->assertTrue($response->isRedirect());
		$redirectUrl = $response->getRedirectUrl();
		$this->assertRegExp('/\/additionalquestions/', $redirectUrl);

		$this->seeInDatabase('question',
			[
				'qs_title' => $title,
				'qs_category_id' => $this->additionalCategoryId
			]);
		$this->seeInDatabase('question', 
			[
				'qs_title' => $title, 
				'qs_category_id' => $this->acceptAdditionalCategoryId
			]);

	}

}