<?php namespace ProjectEditControllerTest\Controller;

use CodeIgniter\Test\FeatureTestCase;
use CodeIgniter\Test\ControllerTester;
use CodeIgniter\Test\Mock\MockIncomingRequest;
use CodeIgniter\HTTP\Files;
use CodeIgniter\HTTP\UserAgent;
use App\Database\Seeds;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\IncomingRequest;

class ProjectEditControllerTest extends FeatureTestCase
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

		$_SESSION['admin_login_name'] = 'admin';

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
	* GET project/edit приводит к переходу на страницу авторизации
	*
	* - GET project/edit
	*/
	public function test_GetEditProjectUnauthorizedSessionStartsRedirect()
	{

		$result = $this->get('project/edit');
		$this->assertNotNull($result);
		$this->assertEquals(1, $result->isRedirect());
		$redirectUrl = $result->getRedirectUrl();
		$this->assertRegExp('/\/Admin\/login/', $redirectUrl);
		$result->assertOK();

		$result->assertSessionHas('redirect_from', '/project/edit');
	}

	/**
	* GET project/edit после логина участника голосования 
	* переход на страницу Project/edit приводит к автопереходу на авторизации Admin
	* - POST user/login
	* - GET project/edit/<project_id>
	*/
	public function test_GetEditProjectAuthorizedUserRedirectsAdminLogin()
	{
		// Arrange
		$userResult = $this->post('user/login', [
			'usr_code' => '123'
		]);
		$this->assertNotNull($userResult);
		$userResult->assertSessionHas('user_login_code', '123');
		$userResult->assertSessionHas('user_project_id', '1');

		// Act
		$projectEditUrl = '/project/edit/'.$this->defaultProjectId;
		$result = $this
			->withSession()
			->get($projectEditUrl);
		
		// Assert
		$this->assertNotNull($result);
		$this->assertEquals(1, $result->isRedirect());
		$redirectUrl = $result->getRedirectUrl();
		$this->assertRegExp('/\/Admin\/login/', $redirectUrl);
		$result->assertOK();

		$result->assertSessionHas('redirect_from', $projectEditUrl);
	}

	/**
	* GET project/edit приводит к переходу на страницу редактирования проекта
	*
	* - GET project/edit
	*/
	public function test_GetEditProjectAuthorizedSessionShowProjectEditPage()
	{
		// Arrange

		// Act
		$result = $this
			->withSession()
			->get('project/edit/'.$this->defaultProjectId);
		
		// Assert
		$this->assertNotNull($result);
		$this->assertEquals(0, $result->isRedirect());
		$result->assertOK();

		$result->assertSessionMissing('redirect_from');

		$result->assertSee('Редактирование проекта', 'div');
		$result->assertSee($this->defaultProjectName, 'div');
	}

	/**
	* POST project/edit успешно обновляет проект
	*
	* - Project::edit
	* @testWith ["ProjectName-098"]
	*/
	public function test_PostEditProjectControllerOk($projectName)
	{
		$projectId = $this->defaultProjectId;

		// Arrange
		$criteria = [
			'project_id' => $this->defaultProjectId,
		];
		$this->seeInDatabase('project', $criteria);


		$data = [
			'project_name' => $projectName,
		];

		// Act
		$result = $this
			->withSession()
			->post('project/edit/'.$projectId, $data);
		
		// Assert
		$result->assertOK();
		$result->assertRedirect();
		$redirectUrl = $result->getRedirectUrl();
		$this->assertRegExp("/\/Project\/edit\/$projectId/", $redirectUrl);

		// Сообщение валидации отсутствует
		//$result->assertSee('alert-danger');

		$criteria = [
			'project_id' => $this->defaultProjectId,
			'project_name' => $projectName,
		];
		$this->seeInDatabase('project', $criteria);
	}


	/**
	* POST project/edit проверяет входные данные
	*
	* - Project::edit
	* @testWith [""]
	*/
	public function test_PostEditProjectControllerValidationMessage($projectName)
	{
		// Arrange
		$projectId = $this->defaultProjectId;
		$projectName = empty($projectName) ? null : $projectName;

		$data = [
			'project_name' => $projectName,
		];

		// Act
		$result = $this
			->withSession()
			->post('project/edit/'.$projectId, $data);
		
		// Assert
		$result->assertOK();
		$this->assertFalse($result->isRedirect());

		// Сообщение валидации на экране
		//echo $result->response->getBody();
		$result->assertSee('alert-danger');
		$result->assertSee('Укажите Наименование собрания');

		$criteria = [
			'project_id' => $projectId,
			'project_name' => $projectName,
		];
		$this->dontSeeInDatabase('project', $criteria);
	}

}