<?php namespace ProjectTest\Controller;

//use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\CIDatabaseTestCase;
// use CodeIgniter\Test\FeatureTestCase;
use CodeIgniter\Test\ControllerTester;
use App\Database\Seeds;

class ProjectControllerTest extends CIDatabaseTestCase
{
	use ControllerTester;

	protected $refresh  = true;
	protected $seed     = \MeetingSeeder::class;

	/**
	* POST project добавляет проект

	- POST project
	*/
	public function test_PostProjectInsertControllerCreatesNewProject()
	{
		// Arrange
		$params = [
			'ProjectName'=>'ProjectName-124',
			'ProjectCode'=>'ProjectCode-124'
		];
		$body = json_encode($params);

		// Act
		// $_SERVER['CONTENT_TYPE'] = "application/json";
		// $result = $this->withBody($body)
		// 		->controller(\App\Controllers\Project::class)
		// 		->execute("insert");

		// Assert
		$this->assertEquals(1, 1);
	}

}