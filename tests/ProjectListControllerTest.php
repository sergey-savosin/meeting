<?php namespace ProjectListControllerTest\Controller;

use CodeIgniter\Test\FeatureTestCase;
use App\Database\Seeds;

class ProjectListControllerTest extends FeatureTestCase
{

	protected $refresh  = true;
	protected $seed     = \MeetingSeeder::class;

	protected $projects_model;
	protected $defaultProjectId = 1;
	protected $defaultProjectName = 'ProjectName-123';
	protected $defaultUserId = 1;
	protected $defaultUserCode = '123';

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
	}


	/**
	* GET project/index приводит к переходу на страницу авторизации
	*
	* - GET project/index
	*/
	public function test_GetProjectIndexUnauthorizedSession_StartsRedirect()
	{
		// Act
		$result = $this->get('project/index');

		// Assert
		$this->assertNotNull($result);
		$this->assertEquals(1, $result->isRedirect());
		$redirectUrl = $result->getRedirectUrl();
		$this->assertRegExp('/\/User\/login/', $redirectUrl);
		$result->assertOK();

		$result->assertSessionHas('redirect_from', '/project/index');
	}

	/**
	* GET project/index приводит к переходу на страницу проектов
	*
	* - GET project/index
	*/
	public function test_GetProjectIndexAuthorizedSession_ShowsProjectList()
	{
 		// Act
		$response = $this->withSession()->get('project/index');

		// Assert
		$response->assertStatus(200);
		$response->assertOK();
		// $content = $response->getJSON();
		// print($content);
		$response->assertSee('Начало голосования');
		$response->assertSee('ProjectName-123');
		$response->assertDontSee('ProjectCode-123');
	}


	/**
	* POST project/index приводит к успешному добавлению проекта
	*
	* - POST project/index
	*/
	public function test_PostProjectIndex_AddProject()
	{
		$newProjectName = 'new Name 456';
		$newProjectId = 1;

		// Arrange
		$data = [
			'projectName' => $newProjectName,
		];

 		// Act
		$result = $this
			->withSession()
			->post('project/index', $data);

		// Assert
		$this->assertTrue($result->isRedirect());
		$redirectUrl = $result->getRedirectUrl();
		$this->assertRegExp('/\/Project/', $redirectUrl);
		$result->assertOK();

		$data = [
			'project_name' => $newProjectName,
		];
		$this->seeInDatabase('project', $data);

		$data = [
			'project_name' => $newProjectName,
			'project_code' => null
		];
		$this->dontSeeInDatabase('project', $data);

	}

	/**
	* POST project/index - валидация параметров
	*
	* - POST project/index
	* @testWith ["", "Укажите Название проекта"]
	*/
	public function test_PostProjectIndex_ShowValidationRequiredError(
		$newProjectName, $validationMessage)
	{
		$newProjectName = empty($newProjectName) ? null : $newProjectName;

		// Arrange
		$data = [
			'projectName' => $newProjectName,
		];

 		// Act
		$result = $this
			->withSession()
			->post('project/index', $data);

		// Assert
		$this->assertFalse($result->isRedirect());
		$result->assertOK();

		$result->assertSee($validationMessage);

		$data = [
			'project_name' => $newProjectName,
		];
		$this->dontSeeInDatabase('project', $data);
	}

	/**
	* POST project/index - валидация параметров
	*
	* - POST project/index
	* @testWith ["ProjectName-123", "Название проекта"]
	*/
	public function test_PostProjectIndex_ShowValidationProjectNameUniqueError(
		$newProjectName, $validationField)
	{
		// Arrange
		$data = [
			'projectName' => $newProjectName,
		];

 		// Act
		$result = $this
			->withSession()
			->post('project/index', $data);

		// Assert
		$this->assertFalse($result->isRedirect());
		$result->assertOK();

		$result->assertSee('Параметр "'.$validationField.'" должен быть уникальным');

	}

}