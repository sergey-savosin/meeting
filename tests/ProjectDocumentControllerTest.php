<?php namespace ProjectDocumentControllerTest\Controller;

use CodeIgniter\Test\FeatureTestCase;
use CodeIgniter\Test\ControllerTester;
use CodeIgniter\Test\Mock\MockIncomingRequest;
use CodeIgniter\HTTP\Files;
use CodeIgniter\HTTP\UserAgent;
use App\Database\Seeds;

class ProjectDocumentControllerTest extends FeatureTestCase
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
	* GET project/edit_document приводит к переходу на страницу авторизации
	*
	* - GET project/edit_document
	*/
	public function test_GetEditDocumentUnauthorizedSessionStartsRedirect()
	{

		$result = $this->get('project/edit_document');
		$this->assertNotNull($result);
		$this->assertEquals(1, $result->isRedirect());
		$redirectUrl = $result->getRedirectUrl();
		$this->assertRegExp('/\/Admin\/login/', $redirectUrl);
		$result->assertOK();

		$result->assertSessionHas('redirect_from', '/project/edit_document');
	}

	/**
	* GET project/edit_document приводит к отображению страницы редактирования документов
	*
	* - GET project/edit_document
	*/
	public function test_GetEditDocumentAuthorizedSessionShowView()
	{
		// Act
		$result = $this
			->withSession()
			->get('project/edit_document/'.$this->defaultProjectId);
		
		// Assert
		$this->assertNotNull($result);
		$this->assertEquals(0, $result->isRedirect());
		$result->assertOK();
		$result->assertSessionMissing('redirect_from');

		$result->assertSee('Проект "'
				.$this->defaultProjectName
				.'". Редактирование документов.');
	}

	/**
	* POST project/edit_document успешно добавляет документ
	*
	* - Project::edit_document
	* @group mockrequest
	* @testWith ["Belka-caption11", "Belka", "Belka-caption11"]
	* ["", "Belka", "Belka"]
	*/
	public function test_EditDocumentControllerPostFileOk($docCaption,
		$fileName, $expectedName)
	{
		log_message('info', '----------------------------------------------------------');
		log_message('info', '--- test: test_EditDocumentControllerPostFileOk ---');

		// Arrange
		$docCaption = empty($docCaption) ? null : $docCaption;

		$path = ROOTPATH.'.\tests\Belka2.jpg';
		$type = 'image/png';
		$size = 143262;
		$data = [
			'ProjectId' => $this->defaultProjectId,
			'DocCaption' => $docCaption
		];

		//----------------------------
		$config = new \Config\App;
		$baseUrl = $_SERVER['app.baseURL'];
		$uri = new \CodeIgniter\HTTP\URI(
			$baseUrl.'/project/edit_document/'.$this->defaultProjectId
		);

		$incomingRequest = new MockIncomingRequest(
			$config, $uri, json_encode($data), new UserAgent()
		);
		$incomingRequest->config->baseURL = $baseUrl;

		// $incomingRequest->setGlobal('request', $data);
		$incomingRequest->setGlobal('post', $data);
		$incomingRequest->setMethod('post');

		$incomingRequest->setFile('documentFile', $path, $fileName, $type, $size);

		// Act
		$result = $this->withRequest($incomingRequest)
				->withSession()
		 		->controller(\App\Controllers\Project::class)
		 		->execute("edit_document");
		
		// Assert
		$this->assertTrue($result->isRedirect());

		// Сообщение валидации отсутствует
		$this->assertTrue($result->dontSee('alert-danger'));

		$criteria = [
			'doc_caption' => $docCaption
		];
		$this->seeInDatabase('document', $criteria);

		$criteria = [
			'doc_filename' => $fileName,
		];
		$this->seeInDatabase('document', $criteria);
		$docId = $this->grabFromDatabase('document', 'doc_id', $criteria);

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
			'ProjectId' => $this->defaultProjectId,
			'DocCaption' => $docCaption
		];

		//----------------------------
		$config = new \Config\App;
		$baseUrl = $_SERVER['app.baseURL'];
		$uri = new \CodeIgniter\HTTP\URI(
			$baseUrl.'/project/edit_document/'.$this->defaultProjectId
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
		$this->assertFalse($result->isRedirect());

		// page
		// echo $result->getBody();

		// Сообщение валидации присутствует
		$this->assertTrue($result->see('alert-danger'));
		$this->assertTrue($result->see('Выберите', 'div'));

		// database
		$criteria = [
			'doc_caption' => $docCaption
		];
		$this->dontSeeInDatabase('document', $criteria);

		$criteria = [
			'pd_project_id' => $this->defaultProjectId,
		];
		$this->dontSeeInDatabase('project_document', $criteria);
	}

}