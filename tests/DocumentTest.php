<?php namespace DocumentTest\Controller;

use CodeIgniter\Test\FeatureTestCase;
use App\Database\Seeds;
use CodeIgniter\Config\Services;
use CodeIgniter\Test\Mock\MockCURLRequest;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;

class DocumentTest extends FeatureTestCase
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

	public function setUp(): void
	{
		parent::setUp();

		\Config\Services::request()->config->baseURL = $_SERVER['app.baseURL'];
		$str = \Config\Services::request()->config->baseURL;
	}

	public function tearDown(): void
	{
		parent::tearDown();
	}

	/**
	* POST document с указанием существующего ProjectName добавляет документ
	*
	* - POST document
	* neg: ["https://aubot.azurewebsites.net/api/download/1pvOz47s671c1OJb5c_hwI2Woo7EJQosLIXHyv5TWdxU", "test"]
	* big: ["https://docs.google.com/document/d/1bVxQsgvBLoGJtfJL6gUsm6zzFOaC_KIc65ntsHShcpU", "Арантас ФА часть 1.docx"]
	* norm: ["https://docs.google.com/spreadsheets/d/1-vTMclGuOJeaw4yqm3AWvg04q-C4sHyQ0zmKiQTY9aY/export?format=xlsx", "Автобанкротство - тайм шит.xlsx"]
	* @group Curl
	* @testWith 
	* ["https://drive.google.com/file/d/1wREX77j3brL8U8uXzbg5R9rJtlPP27xB", "Untitled.jpg"]
	*****/
	public function test_Curl_InsertDocumentWithExistingProjectNameWorks($url, $filename)
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
		$response = $this->post("document/insert", $params);

		//$content = $response->getJSON();

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



}