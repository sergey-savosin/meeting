<?php namespace DocumentTest\Controller;

use CodeIgniter\Test\FeatureTestCase;
use App\Database\Seeds;
use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Test\Mock\MockCURLRequest;

class DocumentCurlMockTest extends FeatureTestCase
{

	protected $refresh  = true;
	protected $seed     = \MeetingSeeder::class;

	protected $questions_model;
	protected $generalCategoryId = 1;
	protected $additionalCategoryId = 2;
	protected $acceptAdditionalCategoryId = 3;
	protected $defaultProjectId = 1;
	protected $defaultProjectName = 'ProjectName-123';
	protected $defaultUserId = 1;

	protected $curlrequest;

	public function setUp(): void
	{
		parent::setUp();

		\Config\Services::request()->config->baseURL = $_SERVER['app.baseURL'];

		//----------------------
		$config = new \Config\App;
		$uri = new \CodeIgniter\HTTP\URI();
		$response = new Response($config);

		$this->curlrequest = new MockCURLRequest(
			$config,
			$uri,
			$response);
		$this->curlrequest->setOutput("124");

		Services::injectMock('curlrequest', $this->curlrequest);
		//----------------------
	}

	public function tearDown(): void
	{
		parent::tearDown();

		// clear mock
		Services::injectMock('curlrequest', null);
	}

	/**
	 * Mock CurlRequest
	 * @group CurlMock
	 */
	public function test_InsertDocumentWithExistingProjectNameWorksCurlMock()
	{
		// Arrange
		$url = "https://drive.google.com/file/d/1wREX77j3brL8U8uXzbg5R9rJtlPP27xB";
		$filename =  "Untitled.jpg";

		$this->setHeaderFileNames([$filename]);

		$params = [
			'ProjectName'=>$this->defaultProjectName,
			'FileName'=>'defaultfilename.jpg',
			'FileUrl'=>$url,
			'IsForCreditor'=>'true'
		];

		$_SERVER['CONTENT_TYPE'] = "application/json";
		
		// Act
		$response = $this->post("document/insert", $params);

		// Assert
		$doc_id = 1;

		$response->assertStatus(201); // created
		$response->assertJSONExact(['status' => 'ok', 'id' => $doc_id]);
		$response->assertHeader('Content-Type', 'application/json; charset=UTF-8');
		$response->assertHeader('Location', "http://localhost/document/$doc_id");

		$criteria = [
			'doc_id'  => $doc_id,
			'doc_filename' => $filename,
			'doc_is_for_creditor' => 1,
			'doc_is_for_debtor' => 0,
			'doc_is_for_manager' => 0
		];
		$this->seeInDatabase('document', $criteria);

		$criteria = [
			'docfile_doc_id' => $doc_id,
		];
		$this->seeInDatabase('docfile', $criteria);

		$criteria = [
			'pd_project_id' => $this->defaultProjectId,
			'pd_doc_id' => $doc_id,
		];
		$this->seeInDatabase('project_document', $criteria);
	}

	protected function setHeaderFileNames($filenames) {
		$this->curlrequest->setFileNames($filenames);
	}

}