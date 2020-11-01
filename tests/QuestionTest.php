<?php namespace QuestionTest\Controller;

use CodeIgniter\Test\FeatureTestCase;
use App\Database\Seeds;

class QuestionTest extends FeatureTestCase
{

	protected $refresh  = true;
	protected $seed     = \MeetingSeeder::class;

	protected $questions_model;
	protected $generalCategoryId = 1;
	protected $additionalCategoryId = 2;
	protected $acceptAdditionalCategoryId = 3;
	protected $defaultProjectId = 1;
	protected $defaultUserId = 1;

	public function setUp(): void
	{
		parent::setUp();

		\Config\Services::request()->config->baseURL = $_SERVER['app.baseURL'];
		$str = \Config\Services::request()->config->baseURL;

		$this->questions_model = model('Questions_model');
	}

	public function tearDown(): void
	{
		parent::tearDown();
	}

	public function test_QuestionModelNewGeneralQuestionAdded()
	{
		$title = "Test question - 123";
		$comment = "Question comment 123";

		$this->questions_model->new_general_question($this->defaultProjectId, $title, $comment, '', '');

		$this->seeInDatabase('question', ['qs_title' => $title, 'qs_category_id' => $this->generalCategoryId]);
	}

	public function test_QuestionModelNewAdditionalQuestionAdded()
	{
		$title = "Test question - 123";

		$this->questions_model->new_additional_question($this->defaultProjectId, $title, $this->defaultUserId);

		$this->seeInDatabase('question', ['qs_title' => $title, 'qs_category_id' => $this->additionalCategoryId]);
		$this->seeInDatabase('question', ['qs_title' => $title, 'qs_category_id' => $this->acceptAdditionalCategoryId]);
	}

	public function test_QuestionModelFetchGeneralQuestionsWorks()
	{
		// Arrange
		$title = "Test question - 123";
		$comment = "Question comment 123";
		$this->questions_model->new_general_question($this->defaultProjectId, $title, $comment, '', '');

		// Act
		$q_list = $this->questions_model->fetch_general_questions($this->defaultProjectId);
		$result = $q_list->getResult();

		// Assert
		$this->assertCount(1, $result);
		$res = $result[0];

		$expected = new \stdClass;
		$expected->qs_id = 1;
		$expected->qs_project_id = $this->defaultProjectId;
		$expected->qs_category_id = $this->generalCategoryId;
		$expected->qs_title = $title;
		$expected->qs_user_id = null;
		$expected->qs_comment = $comment;
		$expected->qs_created_at = 'current_timestamp()';
		$expected->qs_base_question_id = null;

		$this->assertEquals($expected, $res);
	}


}