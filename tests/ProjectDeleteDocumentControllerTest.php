<?php namespace ProjectDeleteDocumentControllerTest\Controller;

use CodeIgniter\Test\FeatureTestCase;
use CodeIgniter\Test\ControllerTester;
use CodeIgniter\Test\Mock\MockIncomingRequest;
use CodeIgniter\HTTP\Files;
use CodeIgniter\HTTP\UserAgent;
use App\Database\Seeds;

class ProjectDeleteDocumentControllerTest extends FeatureTestCase
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
	* GET project/delete_document без логина приводит к Exception
	*/
	public function test_DeleteDocumentControllerUnauthorizedSession_ShowError()
	{
		$result = $this->get('project/delete_document');
		$this->assertNotNull($result);
		$this->assertEquals(0, $result->isRedirect());
		$result->assertOK();

		$result->assertSee('Error: User not logged in.');
	}

	/**
	* GET project/delete_document без логина приводит к Exception
	*/
	public function test_DeleteDocumentControllerWrongUser_ShowError()
	{
		$userLoginCode = '444-not-exists';
		$data = [
			'user_login_code' => $userLoginCode
		];
		$result = $this
			->withSession($data)
			->get('project/delete_document/1');
		$this->assertNotNull($result);
		$this->assertEquals(0, $result->isRedirect());
		$result->assertOK();

		$result->assertSee("Error: Access denied. Usercode: $userLoginCode");
	}

	/**
	* GET project/delete_document без логина приводит к Exception
	*/
	public function test_DeleteDocumentControllerWithoutDocId_ShowError()
	{
		$result = $this
			->withSession()
			->get('project/delete_document');
		$this->assertNotNull($result);
		$this->assertEquals(0, $result->isRedirect());
		$result->assertOK();

		$result->assertSee('Error: Empty doc_id.');
	}

	/**
	* GET project/delete_document без логина приводит к Exception
	*/
	public function test_DeleteDocumentControllerWithWrongDocId_ShowError()
	{
		$doc_id = '123456';
		$result = $this
			->withSession()
			->get('project/delete_document/'.$doc_id);
		$this->assertNotNull($result);
		$this->assertEquals(0, $result->isRedirect());
		$result->assertOK();

		$result->assertSee("Can't find project by doc_id: $doc_id");
	}

	/**
	* GET project/delete_document удаляет документ
	*/
	public function test_DeleteDocumentController_DeletesDocumentOk()
	{
		$doc_id = 111;
		$doc_filename = 'newFileName111';

		// Arrange
		$data = [
			'doc_id' => $doc_id,
			'doc_filename' => $doc_filename,
			'doc_is_for_creditor' => true,
			'doc_is_for_debtor' => true,
			'doc_is_for_manager' => true,
			'doc_caption' => 'a caption'
		];
		$this->hasInDatabase('document', $data);

		$data = [
			'docfile_doc_id' => $doc_id,
			'docfile_body' => 1
		];
		$this->hasInDatabase('docfile', $data);

		$data = [
			'pd_project_id' => $this->defaultProjectId,
			'pd_doc_id' => $doc_id
		];
		$this->hasInDatabase('project_document', $data);

		// Act
		$result = $this
			->withSession()
			->get('project/delete_document/'.$doc_id);

		// Assert
		$this->assertNotNull($result);
		$this->assertEquals(1, $result->isRedirect(), 'Answer should have Redirect section');
		$redirectUrl = $result->getRedirectUrl();
		$this->assertRegExp('/\/Project\/edit_document/', $redirectUrl);
		$result->assertOK();

		$data = [
			'docfile_doc_id' => $doc_id
		];
		$this->dontSeeInDatabase('docfile', $data);

		$data = [
			'doc_id' => $doc_id
		];
		$this->dontSeeInDatabase('document', $data);

		$data = [
			'pd_doc_id' => $doc_id
		];
		$this->dontSeeInDatabase('project_document', $data);
	}

}