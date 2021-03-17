<?php namespace ProjectDeleteUserControllerTest\Controller;

use CodeIgniter\Test\FeatureTestCase;
use CodeIgniter\Test\ControllerTester;

class ProjectDeleteUserControllerTest extends FeatureTestCase
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
	protected $answerType = 1;

	public function setUp(): void
	{
		parent::setUp();

		ini_set('memory_limit', -1);

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
	* GET project/delete_user в случае незалогиненного участника приводит к показу ошибки
	*
	* - GET project/delete_user
	*/
	public function test_GetDeleteUserUnauthorizedSession_ShowErrorText()
	{

		$result = $this->get('project/delete_user');
		$this->assertNotNull($result);
		$this->assertEquals(0, $result->isRedirect());
		$result->assertOK();

		$result->assertSee('Error: User not logged in.');
	}

	/**
	* GET project/delete_user в случае неверно залогиненного участника
	* приводит к показу ошибки
	*
	* - GET project/delete_user
	*/
	public function test_GetDeleteUserWrongUserSession_ShowErrorText()
	{
		// Arrange
		$userLoginCode = 'noUser';
		$userId = $this->defaultUserId;
		$_SESSION['user_login_code'] = $userLoginCode;

		// Act
		$result = $this
			->withSession()
			->get('project/delete_user/'.$userId);

		// Assert
		$this->assertNotNull($result);
		$this->assertEquals(0, $result->isRedirect());
		$result->assertOK();

		$result->assertSee("Error: Access denied. Usercode: $userLoginCode");
	}

	/**
	* GET project/delete_user удаляет Участника
	*
	* - GET project/delete_user
	*/
	public function test_GetDeleteUser_DeleteUserOk()
	{
		// Arrange
		$init = new \stdClass();
		$init->projectId = $this->defaultProjectId;
		$init->userId1 = 201;
		$init->userId2 = 202;
		$init->baseQsId = 10;
		$init->baseQsAnswerId = 10;
		$init->addQsId = 20;
		$init->acptAddQsId = 30;
		$init->addQsAnswerId = 20;
		$init->acptAddQsAnswerId = 30;

		$this->prepareTableForTest($init);

		$userId = $init->userId1;

		// Act
		$result = $this
			->withSession()
			->get('project/delete_user/'.$userId);

		// Assert
		$this->assertNotNull($result);
		$result->assertOK();

		$this->assertEquals(1, $result->isRedirect());

		$redirectUrl = $result->getRedirectUrl();
		$this->assertRegExp('/\/Project\/edit_user/', $redirectUrl);

		$data = [
			'user_id' => $userId
		];
		$this->dontSeeInDatabase('user', $data);

	}

	private function prepareTableForTest($init) {
		// add general question with document and without
		$this->addTestGeneralQuestion($init->projectId, $init->baseQsId, null);

		// add additional question with documents and without
		$this->addTestAdditionQuestion($init->projectId, $init->addQsId, null);

		// add accept additional question
		$this->addTestAcceptAdditionQuestion($init->projectId, $init->acptAddQsId, null, $init->addQsId);

		// add users
		$this->addTestUser($init->projectId, $init->userId1);
		$this->addTestUser($init->projectId, $init->userId2);

		// add answer for general question
		$this->addTestAnswer($init->baseQsAnswerId, $init->baseQsId, $init->userId1);

		// add answer for accept additional question
		$this->addTestAnswer($init->addQsAnswerId, $init->addQsId, $init->userId1);

		// add answer for additional question
		$this->addTestAnswer($init->acptAddQsAnswerId, $init->acptAddQsId, $init->userId1);

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