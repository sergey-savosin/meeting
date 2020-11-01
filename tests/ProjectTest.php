<?php namespace ProjectTest\Controller;

//use CodeIgniter\Test\CIUnitTestCase;
//use CodeIgniter\Test\CIDatabaseTestCase;
use CodeIgniter\Test\FeatureTestCase;
use App\Database\Seeds;

class ProjectTest extends FeatureTestCase
{

	// protected $setUpMethods = [
	//     'mockEmail',
	//     'mockSession',
	// ];

	// protected $tearDownMethods = [
	//     'purgeRows',
	// ];

	// protected function purgeRows()
	// {
	//     // $this->model->purgeDeleted();
	//     echo "\r\n-----purgeRows----";
	// }

	protected $refresh  = true;
	protected $seed     = \MeetingSeeder::class;

	protected $projects_model;
	protected $defaultProjectId = 1;

	public function setUp(): void
	{
		parent::setUp();
		//helper('text');

		\Config\Services::request()->config->baseURL = $_SERVER['app.baseURL'];
		$str = \Config\Services::request()->config->baseURL;

		$this->projects_model = model('Projects_model');
	}

	public function tearDown(): void
	{
		parent::tearDown();
	}


	public function test_ProjectModelNewProjectAdded()
	{
		$this->projects_model->new_project('test1', 'test1', null, null, null, null);

		$this->seeInDatabase('project', ['project_name' => 'test1']);
		$this->dontSeeInDatabase('project', ['project_name' => 'test21']);
	}

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
		$this->assertEquals($expected, $res);
	}

	/****
	GET project приводит к переходу на страницу авторизации
	
	- GET project
	*****/
	public function test_UnauthorizedGetProjectStartsRedirect()
	{
		$result = $this->get('project/index');
		$this->assertNotNull($result);
		$this->assertEquals(1, $result->isRedirect());
		$redirectUrl = $result->getRedirectUrl();
		$this->assertRegExp('/\/User\/login/', $redirectUrl);
		$result->assertOK();
	}

	/****
	GET project приводит к переходу на страницу проектов
	
	- POST existing user
	- GET project
	*****/
	public function test_AuthorizedGetProjectShowsProjectList()
	{
		// Arrange
		$userResult = $this->post('user/login', [
			'usr_code' => '123'
		]);
		$this->assertNotNull($userResult);
 
 		$userResult->assertSessionHas('user_login_code', '123');
		$userResult->assertSessionHas('user_project_id', '1');

 		// Act
		$response = $this->withSession()->get('project/index');

		// Assert
		$response->assertStatus(200);
		$response->assertOK();
		// print("--\r\n--");
		$content = $response->getJSON();
		// print($content);
		$response->assertSee('Начало голосования');
		$response->assertSee('ProjectName-123');
		$response->assertSee('ProjectCode-123');
	}

	/**
	* POST project добавляет проект

	- POST project
	*/
	public function test_PostProjectCreatesNewProject()
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
		$response->assertJSONExact(['id'=>$id]);
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