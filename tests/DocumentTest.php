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
	*
	* - POST document
	* neg: ["https://aubot.azurewebsites.net/api/download/1pvOz47s671c1OJb5c_hwI2Woo7EJQosLIXHyv5TWdxU", "test"]
	* big: ["https://docs.google.com/document/d/1bVxQsgvBLoGJtfJL6gUsm6zzFOaC_KIc65ntsHShcpU", "Арантас ФА часть 1.docx"]
	* @testWith 
	* ["https://docs.google.com/spreadsheets/d/1-vTMclGuOJeaw4yqm3AWvg04q-C4sHyQ0zmKiQTY9aY/export?format=xlsx", "Автобанкротство - тайм шит.xlsx"]
	* ["https://drive.google.com/file/d/1wREX77j3brL8U8uXzbg5R9rJtlPP27xB", "Untitled.jpg"]
	*****/
	public function test_InsertDocumentWithExistingProjectNameWorks($url, $filename)
	{
		// Arrange
		$params = [
			'ProjectName'=>$this->defaultProjectName,
			'FileName'=>'test.xlsx',
			'FileUrl'=>$url,
			'IsForCreditor'=>'true'

		];

		$_SERVER['CONTENT_TYPE'] = "application/json";
		
		// Act
		ob_start();
		$response = $this->post("document/insert", $params);
		$output = ob_get_clean(); // in case you want to check the actual body

		// $content = $response->getJSON();

		// Assert
		$doc_id = 1;

		$response->assertStatus(201); // created
		$response->assertJSONExact(['id' => $doc_id]);
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
	}



}