<?php namespace ProjectListControllerTest\Controller;

use CodeIgniter\Test\FeatureTestCase;
use App\Database\Seeds;

class ProjectListControllerTest extends FeatureTestCase
{

	protected $refresh  = true;
	protected $seed     = \MeetingSeeder::class;

	protected $projects_model;
	protected $defaultProjectId = 1;
	protected $defaultProjectCode = 'ProjectCode-123';
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
		$response->assertSee('ProjectCode-123');
	}


	/**
	* POST project/index приводит к успешному добавлению проекта
	*
	* - POST project/index
	*/
	public function test_PostProjectIndex_AddProject()
	{
		$newProjectCode = 'new-Code-456';
		$newProjectName = 'new Name 456';

		// Arrange
		$data = [
			'projectCode' => $newProjectCode,
			'projectName' => $newProjectName,
		];

 		// Act
		$result = $this
			->withSession()
			->post('project/index', $data);

		// Assert
		$this->assertTrue($result->isRedirect());
		$redirectUrl = $result->getRedirectUrl();
		$this->assertRegExp('/\/Project\/edit\/'.$newProjectCode.'/', $redirectUrl);
		$result->assertOK();

		$data = [
			'project_name' => $newProjectName,
			'project_code' => $newProjectCode
		];
		$this->seeInDatabase('project', $data);
	}

	/**
	* POST project/index - валидация параметров
	*
	* - POST project/index
	* @testWith ["", "ProjectCode-098", "Укажите Название проекта"]
	*			["ProjectName-098", "", "Укажите Код проекта"]
	*			["", "", "Укажите Название проекта"]
	*			["", "", "Укажите Код проекта"]
	*/
	public function test_PostProjectIndex_ShowValidationRequiredError(
		$newProjectName, $newProjectCode, $validationMessage)
	{
		$newProjectName = empty($newProjectName) ? null : $newProjectName;
		$newProjectCode = empty($newProjectCode) ? null : $newProjectCode;

		// Arrange
		$data = [
			'projectName' => $newProjectName,
			'projectCode' => $newProjectCode,
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
			'project_code' => $newProjectCode
		];
		$this->dontSeeInDatabase('project', $data);
	}

	/**
	* POST project/index - валидация параметров
	*
	* - POST project/index
	* @testWith ["newProjectName-456", "ProjectCode-123", "Код проекта"]
	*/
	public function test_PostProjectIndex_ShowValidationProjectCodeUniqueError(
		$newProjectName, $newProjectCode, $validationField)
	{
		// Arrange
		$data = [
			'projectName' => $newProjectName,
			'projectCode' => $newProjectCode,
		];

 		// Act
		$result = $this
			->withSession()
			->post('project/index', $data);

		// Assert
		$this->assertFalse($result->isRedirect());
		$result->assertOK();

		$result->assertSee('Параметр "'.$validationField.'" должен быть уникальным');

		$data = [
			'project_name' => $newProjectName,
		];
		$this->dontSeeInDatabase('project', $data);
	}

	/**
	* POST project/index - валидация параметров
	*
	* - POST project/index
	* @testWith ["ProjectName-123", "newProjectCode-123", "Название проекта"]
	*/
	public function test_PostProjectIndex_ShowValidationProjectNameUniqueError(
		$newProjectName, $newProjectCode, $validationField)
	{
		// Arrange
		$data = [
			'projectName' => $newProjectName,
			'projectCode' => $newProjectCode,
		];

 		// Act
		$result = $this
			->withSession()
			->post('project/index', $data);

		// Assert
		$this->assertFalse($result->isRedirect());
		$result->assertOK();

		$result->assertSee('Параметр "'.$validationField.'" должен быть уникальным');

		$data = [
			'project_code' => $newProjectCode,
		];
		$this->dontSeeInDatabase('project', $data);
	}

}