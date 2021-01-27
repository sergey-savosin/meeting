<?php namespace UserTest;

use CodeIgniter\Test\FeatureTestCase;
use App\Database\Seeds;

class UserLoginTest extends FeatureTestCase
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

		$validation = \Config\Services::validation();
		$validation->reset();

		$_SERVER['CONTENT_TYPE'] = "application/json";
	}

	public function tearDown(): void
	{
		parent::tearDown();
	}


	/****
	GET user/login не приводит к redirect
	
	- GET user/login
	*****/
	public function testGetUserLoginOk()
	{
		$result = $this
			->get('user/login');
		$this->assertNotNull($result);
		$this->assertEquals(0, $result->isRedirect());
		$result->assertOK();
		$result->assertStatus(200);
	}

	/****
	POST user/login для несуществующего пользователя не приводит к переходу
	
	- login 111 - несуществующий пользователь
	*****/
	public function testUserLoginForNonExistingUser_RunsRedirect()
	{
		$result = $this->post('user/login', [
			'usr_code' => '111'
			]);
		$this->assertNotNull($result);
		$this->assertEquals(0, $result->isRedirect());
		$result->assertOK();
		$result->assertStatus(200);
	}

	/****
	POST user/login для существующего пользователя приводит к переходу на начальную страницу
	
	- login 123 - существующий пользователь
	*****/
	public function testUserLoginForExistingUser_SetsCookie()
	{
		$result = $this->post('user/login', [
			'usr_code' => '123'
			]);
		$this->assertNotNull($result);

		$result->assertStatus(302); // redirect
		$result->assertOK();

		$this->assertEquals(1, $result->isRedirect());
		$redirectUrl = $result->getRedirectUrl();
		$this->assertEquals('http://localhost/Documents', $redirectUrl);

		$result->assertSessionHas('user_login_code', '123');
		$result->assertSessionHas('user_project_id', '1');
	}

	/**
	* POST user/login для существующего пользователя приводит к переходу на страницу,
	* которая указана в переменной сессии 'redirect_from'
	*
	* - login 123 - существующий пользователь
	* @testWith ["/project"]
	* 			["/project/edit"]
	* 			["/project/edit/123"]
	*/
	public function testUserLoginForExistingUserWithRedirectValueInSession_MakesRedirect(
		$sessionValue)
	{
		// $sessionValue = '/project/edit/123';
		log_message('info', 'testUserLoginForExistingUserWithRedirectValueInSession_MakesRedirect: sessionValue = '.$sessionValue);
		$_SESSION['redirect_from'] = $sessionValue;

		$result = $this
			->withSession()
			->post('user/login', [
			'usr_code' => '123'
			]);
		$this->assertNotNull($result);

		$result->assertStatus(302); // redirect
		$result->assertOK();

		$this->assertEquals(1, $result->isRedirect());
		$redirectUrl = $result->getRedirectUrl();

		$this->assertNotNull($redirectUrl, 'Value redirectUrl should be generated');
		$sessionValueReg = str_replace($sessionValue, '/', '\/');
		$this->assertRegExp("/$sessionValueReg/", $redirectUrl);

		$result->assertSessionHas('user_login_code', '123');
		$result->assertSessionHas('user_project_id', '1');
	}

}