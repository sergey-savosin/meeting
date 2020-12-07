<?php namespace UserTest;

use CodeIgniter\Test\FeatureTestCase;
use App\Database\Seeds;

class UserInsertTest extends FeatureTestCase
{

	protected $refresh  = true;
	protected $seed     = \MeetingSeeder::class;

	protected $defaultProjectId = 1;
	protected $defaultProjectName = 'ProjectName-123';
	protected $defaultUserId = 1;
	protected $defaultUserLoginCode = '123';
	protected $creditorUserTypeId = 1;

	public function setUp(): void
	{
		parent::setUp();

		\Config\Services::request()->config->baseURL = $_SERVER['app.baseURL'];
		$str = \Config\Services::request()->config->baseURL;


		$_SERVER['CONTENT_TYPE'] = "application/json";
	}

	public function tearDown(): void
	{
		parent::tearDown();
	}

	/**
	* POST user добавляет пользователя
	* - POST user
	*/
	public function test_PostUserCreatesNewUser()
	{
		$loginCode = $this->defaultUserLoginCode.'00';
		$userType = 'Creditor';
		$userMemberName = 'Иван Иванович Стратосферов';
		$userCanVote = 'true';
		$userCanVoteDbValue = 1;
		$userVotesNumber = 112.5;

		// Arrange
		$params = [
			"ProjectName" => $this->defaultProjectName,
			"LoginCode" => $loginCode,
			"UserType" => $userType,
			"CanVote" => $userCanVote,
			'VotesNumber' => $userVotesNumber,
			"MemberName" => $userMemberName
		];

		// Act
		$response = $this->post("user/insert", $params);

		// Assert
		$id = $this->defaultUserId + 1;
		$response->assertStatus(201); // created
		$response->assertJSONExact(['status' => 'ok', 'id' => $id]);
		$response->assertHeader('Content-Type', 'application/json; charset=UTF-8');
		$response->assertHeader('Location', "http://localhost/user/$id");

		$criteria = [
			'user_login_code' => $loginCode,
			'user_project_id'  => $this->defaultProjectId,
			'user_usertype_id' => $this->creditorUserTypeId,
			'user_can_vote' => $userCanVoteDbValue,
			'user_votes_number' => $userVotesNumber,
			'user_member_name' => $userMemberName
		];
		$this->seeInDatabase('user', $criteria);
	}

	/**
	* POST user - валидация
	* - POST user с существующим логином
	*/
	public function test_PostUserValidateUserShowsError()
	{
		$loginCode = $this->defaultUserLoginCode;
		$userType = 'Creditor';
		$userMemberName = 'Иван Иванович Стратосферов';
		$userCanVote = 'true';
		$userCanVoteDbValue = 1;
		$userVotesNumber = 112.5;

		// Arrange
		$params = [
			"ProjectName" => $this->defaultProjectName,
			"LoginCode" => $loginCode,
			"UserType" => $userType,
			"CanVote" => $userCanVote,
			'VotesNumber' => $userVotesNumber,
			"MemberName" => $userMemberName
		];

		// $_SERVER['CONTENT_TYPE'] = "application/json";
		
		// Act
		$response = $this->post("user/insert", $params);

		// Assert
		$id = $this->defaultUserId + 1;
		$error_msg = "Invalid User POST request: User login code already exists: $loginCode.";
		$response->assertStatus(400); // error
		$response->assertJSONExact(['status' => 'error', 'error' => $error_msg]);
		$response->assertHeader('Content-Type', 'application/json; charset=UTF-8');
		$response->assertHeaderMissing('Location');

		$criteria = [
			'user_member_name' => $userMemberName
		];
		$this->dontSeeInDatabase('user', $criteria);
	}

	/**
	* POST user - валидация
	* - POST user с несуществующим проектом
	*/
	public function test_PostUserValidateProjectShowsError()
	{
		$loginCode = $this->defaultUserLoginCode.'00';
		$userType = 'Creditor';
		$userMemberName = 'Иван Иванович Стратосферов';
		$userCanVote = 'true';
		$userCanVoteDbValue = 1;
		$userVotesNumber = 112.5;
		$projectId = $this->defaultProjectId + 1;

		// Arrange
		$params = [
			"ProjectName" => $projectId,
			"LoginCode" => $loginCode,
			"UserType" => $userType,
			"CanVote" => $userCanVote,
			'VotesNumber' => $userVotesNumber,
			"MemberName" => $userMemberName
		];

		// Act
		$response = $this->post("user/insert", $params);

		// Assert
		$id = $this->defaultUserId + 1;
		$error_msg = "Invalid User POST request: Project not found: $projectId.";
		$response->assertStatus(400); // error
		$response->assertJSONExact(['status' => 'error', 'error' => $error_msg]);
		$response->assertHeader('Content-Type', 'application/json; charset=UTF-8');
		$response->assertHeaderMissing('Location');

		$criteria = [
			'user_member_name' => $userMemberName
		];
		$this->dontSeeInDatabase('user', $criteria);
	}

	/**
	* POST user - валидация наличия обязательных параметров
	* - POST user с несуществующим проектом
	* @testWith ["", "testLogin", "Creditor", "Empty ProjectName value in request."]
	* @testWith ["testPr", "", "Creditor", "Empty LoginCode value in request."]
	* @testWith ["testPr", "testLogin", "", "Empty UserType value in request."]
	*/
	public function test_PostUserValidateRequiredParamsShowsError(
		$projectName, $loginCode, $userType, $errorMessage)
	{
		$userMemberName = 'Иван Иванович Стратосферов';
		$userCanVote = 'true';
		$userCanVoteDbValue = 1;
		$userVotesNumber = 112.5;

		// Arrange
		$params = [
			"ProjectName" => $projectName,
			"LoginCode" => $loginCode,
			"UserType" => $userType,
			"CanVote" => $userCanVote,
			'VotesNumber' => $userVotesNumber,
			"MemberName" => $userMemberName
		];

		// Act
		$response = $this->post("user/insert", $params);

		// Assert
		$id = $this->defaultUserId + 1;
		$response->assertStatus(400); // error
		$response->assertHeader('Content-Type', 'application/json; charset=UTF-8');
		$response->assertHeaderMissing('Location');

		$error_msg = "Invalid User POST request: $errorMessage";
		$response->assertJSONExact(['status' => 'error', 'error' => $error_msg]);

		$criteria = [
			'user_member_name' => $userMemberName
		];
		$this->dontSeeInDatabase('user', $criteria);
	}

	/**
	* POST user - валидация наличия обязательных параметров
	* - POST user с несуществующим проектом
	* @testWith ["123-123", "VotesNumber parameter has incorrect format:"]
	* @testWith ["0,123", "VotesNumber parameter has incorrect format:"]
	* @testWith ["123 123", "VotesNumber parameter has incorrect format:"]
	*/
	public function test_PostUserValidateVotesNumberShowsError(
		$userVotesNumber, $errorMessage)
	{
		$userMemberName = 'Иван Иванович Стратосферов';

		// Arrange
		$params = [
			"ProjectName" => $this->defaultProjectName,
			"LoginCode" => $this->defaultUserLoginCode.'00',
			"UserType" => 'Creditor',
			"CanVote" => 'true',
			'VotesNumber' => $userVotesNumber,
			"MemberName" => $userMemberName
		];

		// Act
		$response = $this->post("user/insert", $params);

		// Assert
		$id = $this->defaultUserId + 1;
		$response->assertStatus(400); // error
		$response->assertHeader('Content-Type', 'application/json; charset=UTF-8');
		$response->assertHeaderMissing('Location');

		$error_msg = "Invalid User POST request: $errorMessage $userVotesNumber.";
		$response->assertJSONExact(['status' => 'error', 'error' => $error_msg]);

		$criteria = [
			'user_member_name' => $userMemberName
		];
		$this->dontSeeInDatabase('user', $criteria);
	}

	/**
	* POST user - валидация наличия обязательных параметров
	* - POST user с несуществующим проектом
	* @testWith ["Creditor", "1"]
	* @testWith ["Debtor", "2"]
	* @testWith ["Manager", "3"]
	*/
	public function test_PostUserValidateUserTypeOk($userType, $expectedUserTypeId)
	{
		$userMemberName = 'Иван Иванович Стратосферов';
		$loginCode = $this->defaultUserLoginCode.'00';

		// Arrange
		$params = [
			"ProjectName" => $this->defaultProjectName,
			"LoginCode" => $loginCode,
			"UserType" => $userType,
			"CanVote" => 'true',
			'VotesNumber' => 10,
			"MemberName" => $userMemberName
		];

		// Act
		$response = $this->post("user/insert", $params);

		// Assert
		$id = $this->defaultUserId + 1;
		$response->assertStatus(201); // created
		$response->assertHeader('Content-Type', 'application/json; charset=UTF-8');
		$response->assertJSONExact(['status' => 'ok', 'id' => $id]);

		$criteria = [
			'user_member_name' => $userMemberName,
			'user_usertype_id' => $expectedUserTypeId
		];
		$this->seeInDatabase('user', $criteria);
	}

	/**
	* POST user - валидация наличия обязательных параметров
	* - POST user с несуществующим проектом
	* @testWith ["Credetor", "User type not found: Credetor. Valid values are: Creditor, Debtor, Manager"]
	*/
	public function test_PostUserValidateUserTypeShowsError($userType, $errorMessage)
	{
		$userMemberName = 'Иван Иванович Стратосферов';
		$loginCode = $this->defaultUserLoginCode.'00';

		// Arrange
		$params = [
			"ProjectName" => $this->defaultProjectName,
			"LoginCode" => $loginCode,
			"UserType" => $userType,
			"CanVote" => 'true',
			'VotesNumber' => 10,
			"MemberName" => $userMemberName
		];

		// Act
		$response = $this->post("user/insert", $params);

		// Assert
		$response->assertStatus(400); // created
		$response->assertHeader('Content-Type', 'application/json; charset=UTF-8');
		$response->assertHeaderMissing('Location');
		$error_msg = "Invalid User POST request: $errorMessage.";
		$response->assertJSONExact(['status' => 'error', 'error' => $error_msg]);

		$criteria = [
			'user_member_name' => $userMemberName
		];
		$this->dontSeeInDatabase('user', $criteria);
	}

}