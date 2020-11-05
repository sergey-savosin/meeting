<?php namespace DocumentTest\Controller;

use CodeIgniter\Test\FeatureTestCase;
use App\Database\Seeds;
use CodeIgniter\Config\Services;
use CodeIgniter\Test\Mock\MockCURLRequest;

class DocumentTest extends FeatureTestCase
{

	protected $refresh  = true;
	protected $seed     = \MeetingSeeder::class;

	protected $questions_model;
	protected $generalCategoryId = 1;
	protected $additionalCategoryId = 2;
	protected $acceptAdditionalCategoryId = 3;
	protected $defaultProjectId = 1;
	protected string $defaultProjectName = 'ProjectName-123';
	protected $defaultUserId = 1;

	public function setUp(): void
	{
		parent::setUp();

		\Config\Services::request()->config->baseURL = $_SERVER['app.baseURL'];
		$str = \Config\Services::request()->config->baseURL;

		// $this->questions_model = model('Questions_model');
	}

	public function tearDown(): void
	{
		parent::tearDown();
	}

	/****
	Mock CurlRequest
	
	*****/
	public function test_CurlRequestMock()
	{
		// Arrange
		$config = new \Config\App;
		$uri = new \CodeIgniter\HTTP\URI();
    	$curlrequest = $this->getMockBuilder('CodeIgniter\HTTP\CURLRequest')
                        ->setMethods(['request'])
                        ->setConstructorArgs([$config, $uri])
                        ->getMock();

        $mock = new \CodeIgniter\Test\Mock\MockCURLRequest($config, $uri);
        Services::injectMock('curlrequest', $curlrequest);
	}

	/**
	* POST document с указанием существующего ProjectName добавляет документ
	
	- POST document
	*****/
	public function test_InsertDocumentWithExistingProjectNameWorks()
	{
		// Arrange
		$params = [
			'ProjectName'=>'ProjectName-125',
			'FileName'=>'test.xlsx',
			'FileUrl'=>
		'https://docs.google.com/spreadsheets/d/1-vTMclGuOJeaw4yqm3AWvg04q-C4sHyQ0zmKiQTY9aY/export?format=xlsx',
			'IsForCreditor'=>'true'

		];

		$_SERVER['CONTENT_TYPE'] = "application/json";
		
		// Act
		$response = $this->post("document/insert", $params);

		// $content = $response->getJSON();

		// Assert
		$doc_id = 1;
		$doc_filename = 'Автобанкротство - тайм шит.xlsx';

		$response->assertStatus(201); // created
		$response->assertJSONExact(['id' => $doc_id]);
		$response->assertHeader('Content-Type', 'application/json; charset=UTF-8');
		$response->assertHeader('Location', "http://localhost/document/$doc_id");

		$criteria = [
			'doc_id'  => $doc_id,
			'doc_filename' => $doc_filename,
			'doc_is_for_creditor' => 1,
			'doc_is_for_debtor' => 0,
			'doc_is_for_manager' => 0
		];
		$this->seeInDatabase('document', $criteria);

		$criteria = [
			'docfile_doc_id' => $doc_id,
		];
		$this->seeInDatabase('docfile', $criteria);
	}



}