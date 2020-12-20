<?php namespace QuestionTest\Controller;

use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Test\FeatureTestCase;
use CodeIgniter\Test\Mock\MockCURLRequest;
use App\Database\Seeds;

class QuestionWebApiCurlMockTest extends FeatureTestCase
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
		$str = \Config\Services::request()->config->baseURL;

		$this->questions_model = model('Questions_model');

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

		$_SERVER['CONTENT_TYPE'] = "application/json";

	}

	public function tearDown(): void
	{
		parent::tearDown();

		// clear mock
		Services::injectMock('curlrequest', null);
	}


	/**
	* POST question с указанием существующего ProjectName и с FileUrl добавляет вопрос
	*
	* - POST question
	* @group CurlMock
	* @testWith 
	* ["https://drive.google.com/file/d/1wREX77j3brL8U8uXzbg5R9rJtlPP27xB", "Untitled.jpg"]
	*/
	public function test_InsertQuestionWithFileUrlWorksCurlMock($url, $filename)
	{
		// Arrange
		$this->setHeaderFileNames([$filename]);

		$projectName = $this->defaultProjectName;
		$title = 'Question title - 123;Question title = 555';

		$params = [
			'Title' => $title,
			'ProjectName' => $projectName,
			'DefaultFileName' => 'defaultfilename.jpg',
			'FileUrl' => $url,
		];

		// Act
		$result = $this->post('question', $params);

		// Assert
		$expectedId = 2;
		$docId = 1;

		$this->assertNotNull($result);
		$this->assertFalse($result->isRedirect());
		$result->assertStatus(201);
		$result->assertHeader('Location', "http://localhost/question/$expectedId");
		$result->assertHeader('Content-Type', 'application/json; charset=UTF-8');
		$result->assertJSONExact(['status' => 'ok', 'id' => $expectedId]);

		$criteria = [
			'qs_title'=>$title,
			'qs_id'=>$expectedId,
			'qs_project_id'=>$this->defaultProjectId
		];
		$this->seeInDatabase('question', $criteria);

		$criteria = [
			'doc_id'  => $docId,
			'doc_filename' => $filename,
			'doc_is_for_creditor' => 1,
			'doc_is_for_debtor' => 1,
			'doc_is_for_manager' => 1
		];
		//echo $this->grabFromDatabase('document', 'doc_filename', $criteria);

		$this->seeInDatabase('document', $criteria);

		$criteria = [
			'docfile_doc_id' => $docId,
		];
		$this->seeInDatabase('docfile', $criteria);

		$criteria = [
			'qd_question_id' => $expectedId,
			'qd_doc_id' => $docId,
		];
		$this->seeInDatabase('question_document', $criteria);
	}

	/**
	* POST question с указанием существующего ProjectName и режима HasCsvContent.
	* Результат: добавлено 2 вопроса.
	* 
	* - POST question
	* @group CurlMock
	* @testWith ["t1;t5","https://drive.google.com/file/d/1wREX77j3brL8U8uXzbg5R9rJtlPP27xB;https://docs.google.com/document/d/1KSROwOW-Q43AEW2Rh0iKHE0pV134dImAHmg1tb3Ofdg","t1","t5","Untitled.jpg","Собрание и голосование.docx"]
	* ["t1;t5","https://drive.google.com/file/d/1wREX77j3brL8U8uXzbg5R9rJtlPP27xB","t1","t5","Untitled.jpg",""]
	*/
	public function test_InsertTwoQuestionsWithFileUrlsNameWorksCurlMock(
		$title, $fileUrl,
		$expTitle1, $expTitle2,
		$expFileName1, $expFileName2)
	{
		// Arrange
		$this->setHeaderFileNames([$expFileName1, $expFileName2]);

		$projectName = $this->defaultProjectName;
		
		$params = [
			'Title' => $title,
			'ProjectName' => $projectName,
			'Comment' => null,
			'FileUrl' => $fileUrl,
			'HasCsvContent' => 'True'
		];

		// Act
		$result = $this->post('question', $params);

		// Assert
		$expQuestionId = 2;
		$expDocId = 1;

		$this->assertNotNull($result);
		$this->assertFalse($result->isRedirect());
		$result->assertStatus(201);
		$result->assertHeaderMissing('Location');
		$result->assertHeader('Content-Type', 'application/json; charset=UTF-8');
		$result->assertJSONExact([
			'status' => 'ok',
			'id' => [$expQuestionId, $expQuestionId + 1]
			]);

		// test Question data
		$this->seeQuestion($expTitle1, null, $expQuestionId, $this->defaultProjectId);

		$this->seeQuestion($expTitle2, null, $expQuestionId + 1, $this->defaultProjectId);

		// Only First document
		if ($expFileName1 <> '' && $expFileName2 == '') {
			$this->seeQuestionDocument($expQuestionId, $expDocId, $expFileName1);
		}

		// First and Second document
		if ($expFileName1 <> '' && $expFileName2 <> '') {
			$this->seeQuestionDocument($expQuestionId, $expDocId, $expFileName1);
			$this->seeQuestionDocument($expQuestionId + 1, $expDocId + 1, $expFileName2);
		}

	}

	/**
	* POST question с указанием существующего ProjectName и режима HasCsvContent.
	* Результат: добавлено 1 вопрос.
	* 
	* - POST question
	* @group CurlMock
	* @testWith ["t1;t5",";https://docs.google.com/document/d/1KSROwOW-Q43AEW2Rh0iKHE0pV134dImAHmg1tb3Ofdg","t1","t5","Собрание и голосование.docx"]
	*/
	public function test_InsertTwoQuestionsWithOnlySecondFileUrlNameWorksCurlMock(
		$title, $fileUrl,
		$expTitle1, $expTitle2,
		$expFileName2)
	{
		// Arrange
		$this->setHeaderFileNames([$expFileName2]);
		
		$projectName = $this->defaultProjectName;
		
		$params = [
			'Title' => $title,
			'ProjectName' => $projectName,
			'Comment' => null,
			'FileUrl' => $fileUrl,
			'HasCsvContent' => 'True'
		];

		// Act
		$result = $this->post('question', $params);

		// Assert
		$expQuestionId = 2;
		$expDocId = 1;

		$this->assertNotNull($result);
		$this->assertFalse($result->isRedirect());
		$result->assertStatus(201);
		$result->assertHeaderMissing('Location');
		$result->assertHeader('Content-Type', 'application/json; charset=UTF-8');
		$result->assertJSONExact([
			'status' => 'ok',
			'id' => [$expQuestionId, $expQuestionId + 1]
			]);

		// test Question data
		$this->seeQuestion($expTitle1, null, $expQuestionId, $this->defaultProjectId);

		$this->seeQuestion($expTitle2, null, $expQuestionId + 1, $this->defaultProjectId);

		// Only Second document
		$this->seeQuestionDocument($expQuestionId + 1, $expDocId, $expFileName2);

		//ToDo: dontsee first document

	}

	protected function seeQuestion($title, $comment, $questionId, $projectId) {
		$criteria = [
			'qs_title' => $title,
			'qs_comment' => $comment,
			'qs_id' => $questionId,
			'qs_project_id' => $projectId
		];

		$this->seeInDatabase('question', $criteria);
	}

	protected function seeQuestionDocument($questionId, $documentId, $fileName) {
		$criteriaQD = [
			'qd_question_id' => $questionId,
			'qd_doc_id' => $documentId
		];
		$criteriaD = [
			'doc_id' => $documentId,
			'doc_filename' => $fileName
		];
		$this->seeInDatabase('question_document', $criteriaQD);
		$this->seeInDatabase('document', $criteriaD);
	}

	protected function dontSeeQuestionDocument($questionId, $documentId, $fileName) {
		$criteriaQD = [
			'qd_question_id' => $questionId,
			'qd_doc_id' => $documentId
		];
		
		$criteriaD = [
			'doc_id' => $documentId,
			'doc_filename' => $fileName
		];
		
		$this->dontSeeInDatabase('question_document', $criteriaQD);
		
		if (!empty($fileName)) {
			$this->dontSeeInDatabase('document', $criteriaD);
		}
	}

	protected function setHeaderFileNames($filenames) {
		$this->curlrequest->setFileNames($filenames);
	}

}