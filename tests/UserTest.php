<?php namespace UserTest\Controller;

//use CodeIgniter\Test\CIUnitTestCase;
//use CodeIgniter\Test\CIDatabaseTestCase;
use CodeIgniter\Test\FeatureTestCase;
use App\Database\Seeds;

class UserTest extends FeatureTestCase
{

	protected $refresh  = true;
	protected $seed     = \MeetingSeeder::class;

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


	/****
	GET user/login не приводит к redirect
	
	- GET user/login
	*****/
	public function testGetUserLoginOk()
	{
		$result = $this->get('user/login');
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
		$this->assertEquals('http://localhost', $redirectUrl);

		$result->assertSessionHas('user_login_code', '123');
		$result->assertSessionHas('user_project_id', '1');
	}

}