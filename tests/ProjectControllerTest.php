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
	protected $defaultProjectCode = 'ProjectCode-123';
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
	* POST project/edit_document успешно добавляет документ
	*
	* - Project::edit_document
	* @group mockrequest
	*/
	public function test_EditDocumentControllerPostFileOk()
	{
		log_message('info', '----------------------------------------------------------');
		log_message('info', '--- test: test_EditDocumentControllerPostFileOk ---');

		// Arrange
		$path = ROOTPATH.'.\tests\Belka2.jpg';
		$name = 'Belka';
		$docCaption = 'Belka-caption11';
		$type = 'image/png';
		$size = 143262;
		$data = [
			'ProjectCode' => $this->defaultProjectCode,
			'ProjectId' => $this->defaultProjectId,
			'DocCaption' => $docCaption
		];

		//----------------------------
		$config = new \Config\App;
		$baseUrl = $_SERVER['app.baseURL'];
		$uri = new \CodeIgniter\HTTP\URI(
			$baseUrl.'/project/edit_document/'.$this->defaultProjectCode
		);

		$incomingRequest = new MockIncomingRequest(
			$config, $uri, json_encode($data), new UserAgent()
		);
		$incomingRequest->config->baseURL = $baseUrl;

		// $incomingRequest->setGlobal('request', $data);
		$incomingRequest->setGlobal('post', $data);
		$incomingRequest->setMethod('post');

		$incomingRequest->setFile('documentFile', $path, $name, $type, $size);

		// Act
		$result = $this->withRequest($incomingRequest)
				->withSession()
		 		->controller(\App\Controllers\Project::class)
		 		->execute("edit_document");
		
		// Assert
		// $this->assertTrue($result->isRedirect());

		$docId = 1;
		// ToDo: проверять DocId, docfile_doc_id, pd_doc_id

		$criteria = [
			//'doc_id' => $docId,
			'doc_caption' => $docCaption
		];
		$this->seeInDatabase('document', $criteria);

		// $criteria = [
		// 	'docfile_doc_id' => $docId
		// ];
		// $this->seeInDatabase('docfile', $criteria);

		$criteria = [
			'pd_project_id' => $this->defaultProjectId,
			//'pd_doc_id' => $docId
		];
		$this->seeInDatabase('project_document', $criteria);
	}

	/**
	* POST project/edit_document без загруженного файла показывает ошибку валидации
	*
	* - Project::edit_document
	*/
	public function test_EditDocumentControllerPostEmptyFileShowValidationError()
	{

		// Arrange
		$docCaption = 'Belka-caption11';
		$data = [
			'ProjectCode' => $this->defaultProjectCode,
			'ProjectId' => $this->defaultProjectId,
			'DocCaption' => $docCaption
		];

		//----------------------------
		$config = new \Config\App;
		$baseUrl = $_SERVER['app.baseURL'];
		$uri = new \CodeIgniter\HTTP\URI(
			$baseUrl.'/project/edit_document/'.$this->defaultProjectCode
		);

		$incomingRequest = new MockIncomingRequest(
			$config, $uri, json_encode($data), new UserAgent()
		);
		$incomingRequest->config->baseURL = $baseUrl;

		// $incomingRequest->setGlobal('request', $data);
		$incomingRequest->setGlobal('post', $data);
		$incomingRequest->setMethod('post');

		// Act
		$result = $this->withRequest($incomingRequest)
				->withSession()
		 		->controller(\App\Controllers\Project::class)
		 		->execute("edit_document");
		
		// Assert
		// $this->assertFalse($result->isRedirect());

		// page
		// echo $result->getBody();

		// ToDo: проверить текст ошибки

		// database
		$docId = 1;

		$criteria = [
			'doc_id' => $docId,
			'doc_caption' => $docCaption
		];
		$this->dontSeeInDatabase('document', $criteria);

		$criteria = [
			'docfile_doc_id' => $docId
		];
		$this->dontSeeInDatabase('docfile', $criteria);

		$criteria = [
			'pd_project_id' => $this->defaultProjectId,
		];
		$this->dontSeeInDatabase('project_document', $criteria);
	}
}