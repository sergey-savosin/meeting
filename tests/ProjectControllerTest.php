<?php namespace ProjectControllerTest\Controller;

use CodeIgniter\Test\FeatureTestCase;
use CodeIgniter\Test\ControllerTester;
use CodeIgniter\Test\Mock\MockIncomingRequest;
use CodeIgniter\HTTP\Files;
use CodeIgniter\HTTP\UserAgent;
use App\Database\Seeds;

class ProjectControllerTest extends FeatureTestCase
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

	//protected $incomingRequest;

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
	* POST project/add_document показывает ошибку валидации
	*
	* - Project::add_document
	*/
	public function test_AddDocumentControllerPostShowOk()
	{
		// Arrange
		$path = 'C:\xampp\htdocs\meeting\tests\Belka2.jpg';
		// $path = '.\Belka2.jpg';
		$name = 'Belka';
		$docCaption = 'Belka-caption';
		$type = 'image/png';
		$size = 143262;
		$data = [
			'ProjectCode' => '123',
			'ProjectId' => $this->defaultProjectId,
			'DocCaption' => $docCaption
		];

		//----------------------------
		$config = new \Config\App;
		$uri = new \CodeIgniter\HTTP\URI($_SERVER['app.baseURL']);
		$incomingRequest = new MockIncomingRequest(
			$config, $uri, json_encode($data), new UserAgent()
		);
		// $incomingRequest = \Config\Services::request();
		$incomingRequest->config->baseURL = $_SERVER['app.baseURL'];
		// $incomingRequest->setGlobal('request', $data);
		$incomingRequest->setGlobal('post', $data);
		$incomingRequest->setMethod('post');

		$incomingRequest->setFile('documentFile', $path, $name, $type, $size);

		// Act
		$result = $this->withRequest($incomingRequest)
				->withSession()
		 		->controller(\App\Controllers\Project::class)
		 		->execute("add_document");
		
		// Assert
		$this->assertTrue($result->isRedirect());

		$docId = 1;

		$criteria = [
			'doc_id' => $docId,
			'doc_caption' => $docCaption
		];
		$this->seeInDatabase('document', $criteria);

		$criteria = [
			'docfile_doc_id' => $docId
		];
		$this->seeInDatabase('docfile', $criteria);

		$criteria = [
			'pd_project_id' => $this->defaultProjectId,
			'pd_doc_id' => $docId
		];
		$this->seeInDatabase('project_document', $criteria);
	}

}