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
	POST question с указанием существующего ProjectName добавляет вопрос
	
	- POST project
	*****/
	public function test_InsertDocumentWithExistingProjectNameWorks()
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

}