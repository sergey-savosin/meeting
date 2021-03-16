<?php namespace ProjectTest\Controller;

use CodeIgniter\Test\FeatureTestCase;
use App\Database\Seeds;

class ProjectTest extends FeatureTestCase
{

	protected $refresh  = true;
	protected $seed     = \MeetingSeeder::class;

	protected $projects_model;
	protected $defaultProjectId = 1;

	public function setUp(): void
	{
		parent::setUp();

		\Config\Services::request()->config->baseURL = $_SERVER['app.baseURL'];

		$validation = \Config\Services::validation();
		$validation->reset();

		$this->projects_model = model('Projects_model');
	}

	public function tearDown(): void
	{
		parent::tearDown();
	}

	/**
	* Тест модели
	*/
	public function test_ProjectModelNewProjectAdded()
	{
		$this->projects_model->new_project('test1', 'test1', null, null, null, null);

		$this->seeInDatabase('project', ['project_name' => 'test1']);
		$this->dontSeeInDatabase('project', ['project_name' => 'test21']);
	}

	/**
	* Тест модели
	*/
	public function test_ProjectModelGetProjectListWorks()
	{
		// Arrange
		$projectName = 'test1';
		$projectCode = 'testCode1';
		$this->projects_model->new_project($projectName, $projectCode, null, null, null, null);

		// Act
		$p_list = $this->projects_model->getProjectList();

		// Assert
		$expectedProjectId = $this->defaultProjectId + 1;

		$result = $p_list->getResult();
		$this->assertCount(2, $result);

		$idx = array_search($expectedProjectId, array_column($result, 'project_id'));
		$this->assertNotFalse($idx);
		$res = $result[$idx];

		$expected = new \stdClass;
		$expected->project_id = $expectedProjectId;
		$expected->project_name = $projectName;
		$expected->project_code = $projectCode;
		$expected->project_acquaintance_start_date = null;
		$expected->project_main_agenda_start_date = null;
		$expected->project_additional_agenda_start_date = null;
		$expected->project_meeting_finish_date = null;
		$expected->project_created_at = 'current_timestamp()';
		$expected->project_admin_id = null;
		$this->assertEquals($expected, $res);
	}

	/**
	* POST project/insert (WebApi) - валидация параметров приводит к ошибке
	*
	* - POST project/insert
	*
	* @testWith ["", "ProjectCode123", "Empty ProjectName value in request."]
 	*			["ProjectName123", "", "Empty ProjectCode value in request."]
 	*			["", "", "Empty ProjectName value in request. Empty ProjectCode value in request."]
 	*			["ProjectName-123", "ProjectCode-123", "Project name already exists: ProjectName-123. Project code already exists: ProjectCode-123."]
 	*			["ProjectName-123", "ProjectCode-444", "Project name already exists: ProjectName-123."]
 	*			["ProjectName-444", "ProjectCode-123", "Project code already exists: ProjectCode-123."]
	*/
	public function test_PostProjectInsert_ParametersValidation(string $projectName, string $projectCode, string $expectedErrorMessage)
	{
		// Arrange
		$params = [
			'ProjectName'=>$projectName,
			'ProjectCode'=>$projectCode
		];

		$_SERVER['CONTENT_TYPE'] = "application/json";
		
		// Act
		$response = $this->post("project/insert", $params);

		// $content = $response->getJSON();

		// Assert
		$response->assertStatus(400);
		$response->assertHeaderMissing('Location');
		$response->assertHeader('Content-Type', 'application/json; charset=UTF-8');
		
		$msg = 'Invalid Project POST request: '.$expectedErrorMessage;
		$response->assertJSONExact(['status' => 'error', 'error' => $msg]);

		$newId = 2;
		$criteria = [
			'Project_Name' => $params['ProjectName'],
			'Project_Code' => $params['ProjectCode'],
			'Project_id'	=> $newId,
		];
		$this->dontSeeInDatabase('project', $criteria);
	}

	/**
	* POST project/insert (WebApi) добавляет проект

	- POST project/insert
	*/
	public function test_PostProjectInsert_CreatesNewProject()
	{
		// Arrange
		$params = [
			'ProjectName'=>'ProjectName-125',
			'ProjectCode'=>'ProjectCode-125'
		];

		$_SERVER['CONTENT_TYPE'] = "application/json";
		
		// Act
		$response = $this->post("project/insert", $params);

		// $content = $response->getJSON();

		// Assert
		$id = 2;
		$response->assertStatus(201); // created
		$response->assertJSONExact(['status' => 'ok', 'id' => $id]);
		$response->assertHeader('Content-Type', 'application/json; charset=UTF-8');
		$response->assertHeader('Location', "http://localhost/project/$id");

		$criteria = [
			'Project_id'  => $id,
			'Project_Name' => $params['ProjectName'],
			'Project_Code' => $params['ProjectCode'],
			
		];
		$this->seeInDatabase('project', $criteria);
	}

}