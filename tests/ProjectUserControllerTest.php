<?php namespace ProjectUserControllerTest\Controller;

use CodeIgniter\Test\FeatureTestCase;
use CodeIgniter\Test\ControllerTester;

class ProjectUserControllerTest extends FeatureTestCase
{

	use ControllerTester;

	protected $refresh  = true;
	protected $seed     = \MeetingSeeder::class;

	protected $projects_model;
	protected $defaultProjectId = 1;
	protected $defaultProjectName = 'ProjectName-123';
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

	}

	public function tearDown(): void
	{
		parent::tearDown();

		// clear mock
		\Config\Services::injectMock('request', null);
	}

	/**
	* GET project/edit_user приводит к переходу на страницу авторизации
	*
	* - GET project/edit_user
	*/
	public function test_GetEditUserUnauthorizedSessionStartsRedirect()
	{

		$result = $this->get('project/edit_user');
		$this->assertNotNull($result);
		$this->assertEquals(1, $result->isRedirect());
		$redirectUrl = $result->getRedirectUrl();
		$this->assertRegExp('/\/User\/login/', $redirectUrl);
		$result->assertOK();

		$result->assertSessionHas('redirect_from', '/project/edit_user');
	}

	/**
	* GET project/edit_user приводит к отображению страницы редактирования участников
	*
	* - GET project/edit_user
	*/
	public function test_GetEditUserAuthorizedSessionShowView()
	{
		// Act
		$result = $this
			->withSession()
			->get('project/edit_user/'.$this->defaultProjectId);
		
		// Assert
		$this->assertNotNull($result);
		$this->assertEquals(0, $result->isRedirect());
		$result->assertOK();
		$result->assertSessionMissing('redirect_from');

		$result->assertSee('Проект "'
				.$this->defaultProjectName
				.'". Редактирование списка участников.');
	}

	/**
	* POST project/edit_user успешно добавляет участника собрания
	*
	* - Project::edit_user
	* @testWith ["1", "canVote", "12", "Ivan Vasiljevich"]
	*			["1", "cannotVote", "12", "Ivan Vasiljevich"]
	*			["1", "", "12", "Ivan Vasiljevich"]
	*			["2", "canVote", "12", "Ivan Vasiljevich"]
	*			["3", "canVote", "12", "Ivan Vasiljevich"]
	*			["3", "canVote", "12", ""]
	*/
	public function test_PostEditUserControllerOk($userTypeId,
		$userCanVote, $userVotesNumber, $userMemberName)
	{
		// Arrange
		$project_id = $this->defaultProjectId;

		$data = [
			"UserTypeId" => $userTypeId,
			"UserCanVote" => $userCanVote,
			"UserVotesNumber" => $userVotesNumber,
			"UserMemberName" => $userMemberName
		];

		// Act
		$result = $this
				->withSession()
		 		->post('project/edit_user/'.$project_id, $data);
		
		// Assert
		$this->assertTrue($result->isRedirect());

		$criteria = [
			'user_usertype_id' => (int)$userTypeId,
			'user_can_vote' => $userCanVote == 'canVote' ? 1 : 0,
			'user_votes_number' => (int)$userVotesNumber,
			'user_member_name' => $userMemberName,
		];
		$this->seeInDatabase('user', $criteria);
	}

	/**
	* POST project/edit_user показывает ошибку валидации
	*
	* - Project::edit_user
	* @testWith ["", "canVote", "12", "Ivan Vasiljevich", "Укажите тип участника"]
	*			["1", "canVote", "", "Ivan Vasiljevich", "Укажите количество голосов"]
	*/
	public function test_PostEditUserControllerShowValidationError($userTypeId,
		$userCanVote, $userVotesNumber, $userMemberName, $errorMessage)
	{

		// Arrange
		$project_id = $this->defaultProjectId;

		$data = [
			"UserTypeId" => $userTypeId,
			"UserCanVote" => $userCanVote,
			"UserVotesNumber" => $userVotesNumber,
			"UserMemberName" => $userMemberName
		];

		// Act
		$result = $this
				->withSession()
		 		->post('project/edit_user/'.$project_id, $data);
		
		// Assert
		$this->assertFalse($result->isRedirect());

		// page
		// echo $result->getBody();

		// Сообщение валидации присутствует
		$result->assertSee('alert-danger');
		$result->assertSee($errorMessage, 'div');

		// database
		$criteria = [
			'user_usertype_id' => (int)$userTypeId,
			'user_can_vote' => $userCanVote == 'canVote' ? 1 : 0,
			'user_votes_number' => (int)$userVotesNumber,
			'user_member_name' => $userMemberName,
		];
		$this->dontSeeInDatabase('user', $criteria);
		
	}

}