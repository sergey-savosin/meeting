<?php namespace ProjectDeleteControllerTest\Controller;

use CodeIgniter\Test\FeatureTestCase;
use CodeIgniter\Test\ControllerTester;
use CodeIgniter\Test\Mock\MockIncomingRequest;
use CodeIgniter\HTTP\Files;
use CodeIgniter\HTTP\UserAgent;
use App\Database\Seeds;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\IncomingRequest;

class ProjectDeleteControllerTest extends FeatureTestCase
{

	use ControllerTester;

	protected $refresh  = true;
	protected $seed     = \MeetingSeeder::class;

	protected $projects_model;
	protected $defaultProjectId = 1;
	protected $defaultProjectCode = 'ProjectCode-123';
	protected $defaultProjectName = 'ProjectName-123';
	protected $generalCategoryId = 1;
	protected $additionalCategoryId = 2;
	protected $acceptAdditionalCategoryId = 3;
	protected $defaultUserId = 1;
	protected $defaultUserCode = '123';
	protected $defaultQuestionId = 1;
	protected $answerType = 1;

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
	* GET project/delete_project приводит к показу ошибки
	*
	* - GET project/delete_project
	*/
	public function test_GetDeleteProjectUnauthorizedSession_ShowErrorText()
	{

		$result = $this->get('project/delete_project');
		$this->assertNotNull($result);
		$this->assertEquals(0, $result->isRedirect());
		$result->assertOK();

		$result->assertSessionMissing('redirect_from', '/project');

		$result->assertSee('Error: User not logged in.');
	}

	/**
	* GET project/edit приводит к переходу на страницу редактирования проекта
	*
	* - GET project/edit
	*/
	public function test_GetDeleteProject_Ok()
	{
		// Arrange
		$init = new \stdClass();
		$init->projectId = 101;
		$init->projectName = 'projectName-101';
		$init->userId1 = 201;
		$init->userId2 = 202;

		$init->docId = 1;
		$init->doc2Id = 2;
		$init->baseQsId = 10;

		$init->baseQsDocId = 10;
		$init->baseQs2Id = 11;
		$init->baseQs2DocId = 11;

		$init->baseQsAnswerId = 10;
		$init->baseQsAnswerUserId = $init->userId1;
		$init->baseQs2AnswerId = 11;
		$init->baseQs2AnswerUserId = $init->userId1;

		$init->addQsId = 20;
		$init->addQsDocId = 20;
		$init->addQs2Id = 21;
		$init->addQs2DocId = 21;

		$init->acptAddQsId = 30;
		$init->acptAddQsDocId = 30;
		$init->acptAddQs2Id = 31;

		$init->addQsAnswerId = 20;
		$init->addQsAnswerUserId = $init->userId1;
		$init->addQs2AnswerId = 21;
		$init->addQs2AnswerUserId = $init->userId1;

		$init->acptAddQsAnswerId = 30;
		$init->acptAddQsAnswer2Id = 31;

		$this->prepareTableForTest($init);
		
		// Act
		$result = $this
			->withSession()
			->get('project/delete_project/'.$init->projectId);
		
		// Assert
		$this->assertNotNull($result);
		$this->assertEquals(1, $result->isRedirect());
		$result->assertOK();

		$data = [
			'project_id' => $init->projectId
		];
		$this->dontSeeInDatabase('project', $data);

	}

	private function prepareTableForTest($init) {
		// add project
		$this->addTestProject($init->projectId, $init->projectName);

		// add document
		$this->addTestProjectDocument($init->projectId, $init->docId);
		$this->addTestProjectDocument($init->projectId, $init->doc2Id);

		// add general question with document and without
		$this->addTestGeneralQuestion($init->projectId, $init->baseQsId, $init->baseQsDocId);
		$this->addTestGeneralQuestion($init->projectId, $init->baseQs2Id, null);

		// add additional question with documents and without
		$this->addTestAdditionQuestion($init->projectId, $init->addQsId, $init->addQsDocId);
		$this->addTestAdditionQuestion($init->projectId, $init->addQs2Id, null);

		// add accept additional question
		$this->addTestAcceptAdditionQuestion($init->projectId,
			$init->acptAddQsId, $init->acptAddQsDocId, $init->addQsId);
		$this->addTestAcceptAdditionQuestion($init->projectId,
			$init->acptAddQs2Id, null, $init->addQs2Id);

		// add answer for general question
		$this->addTestAnswer($init->baseQsAnswerId, $init->baseQsId, $init->userId1);

		// add answer for accept additional question
		$this->addTestAnswer($init->addQsAnswerId, $init->addQsId, $init->userId1);

		// add answer for additional question
		$this->addTestAnswer($init->acptAddQsAnswerId, $init->acptAddQsId, $init->userId1);

		// add users
		$this->addTestUser($init->projectId, $init->userId1);
		$this->addTestUser($init->projectId, $init->userId2);

	}

	private function addTestProject($projectId, $projectName) {
		$data = [
			'project_id' => $projectId,
			'project_name' => $projectName,
			'project_code' => $projectName
		];
		$this->hasInDatabase('project', $data);

	}

	private function addTestProjectDocument($projectId, $docId) {
		// document
		$data = [
			'doc_id' => $docId,
			'doc_filename' => 'test',
			'doc_is_for_creditor' => true,
			'doc_is_for_debtor' => true,
			'doc_is_for_manager' => true
		];
		$this->hasInDatabase('document', $data);

		// docfile
		$data = [
			'docfile_doc_id' => $docId,
			'docfile_body' => 'abc'
		];
		$this->hasInDatabase('docfile', $data);

		// project_document
		$data = [
			'pd_doc_id' => $docId,
			'pd_project_id' => $projectId,
		];
		$this->hasInDatabase('project_document', $data);
	}

	private function addTestQuestion($projectId, $qsId, $docId, $categoryId, $baseQsId) {

		// question
		$data = [
			'qs_id' => $qsId,
			'qs_title' => 'qs_title',
			'qs_project_id' => $projectId,
			'qs_category_id' => $categoryId,
			'qs_base_question_id' => $baseQsId
		];
		$this->hasInDatabase('question', $data);

		// document
		if (isset($docId)) {
			$data = [
				'doc_id' => $docId,
				'doc_filename' => 'test',
				'doc_is_for_creditor' => true,
				'doc_is_for_debtor' => true,
				'doc_is_for_manager' => true
			];
			$this->hasInDatabase('document', $data);

			$data = [
				'docfile_doc_id' => $docId,
				'docfile_body' => 'abc'
			];
			$this->hasInDatabase('docfile', $data);

			$data = [
				'qd_question_id' => $qsId,
				'qd_doc_id' => $docId,
			];
			$this->hasInDatabase('question_document', $data);
		}
	}

	private function addTestGeneralQuestion($projectId, $qsId, $docId) {
		$this->addTestQuestion($projectId, $qsId, $docId, $this->generalCategoryId, null);
	}

	private function addTestAdditionQuestion($projectId, $qsId, $docId) {
		$this->addTestQuestion($projectId, $qsId, $docId, $this->additionalCategoryId, null);
	}

	private function addTestAcceptAdditionQuestion($projectId, $qsId, $docId, $baseQsId) {
		$this->addTestQuestion($projectId, $qsId, $docId, $this->acceptAdditionalCategoryId,
			$baseQsId);
	}

	private function addTestAnswer($ansId, $qsId, $userId) {
		$data = [
			'ans_id' => $ansId,
			'ans_question_id' => $qsId,
			'ans_user_id' => $userId,
			'ans_number' => '1',
			'ans_string' => '1',
			'ans_answer_type_id' => $this->answerType,
			'ans_comment' => 'comment'
		];
		$this->hasInDatabase('answer', $data);
	}

	private function addTestUser($projectId, $userId) {
		$data = [
			'user_id' => $userId,
			'user_project_id' => $projectId,
			'user_login_code' => 'code'.$userId,
			'user_usertype_id' => 1, // debtor
			'user_can_vote' => true,
			'user_votes_number' => 1,
			'user_member_name' => 'member-name'
		];
		$this->hasInDatabase('user', $data);

	}

}