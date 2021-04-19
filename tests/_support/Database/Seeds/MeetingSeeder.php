<?php namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MeetingSeeder extends Seeder
{
	public function run()
	{
		// admin
		$row = [
			'admin_id' => 1,
			'admin_login' => 'admin',
			'admin_password' => 'pass',
		];
		$this->db->table('admin')->insert($row);

		// project
		$row = [
			'project_name' => 'ProjectName-123',
			'project_code' => 'ProjectCode-123',
			'project_id' => 1,
			'project_admin_id' => 1,
		];
		$this->db->table('project')->insert($row);

		// usertype
		$row = ['usertype_id' => 1, 'usertype_name' => 'Creditor'];
		$this->db->table('usertype')->insert($row);
		$row = ['usertype_id' => 2, 'usertype_name' => 'Debtor'];
		$this->db->table('usertype')->insert($row);
		$row = ['usertype_id' => 3, 'usertype_name' => 'Manager'];
		$this->db->table('usertype')->insert($row);

		// user
		$row = [
			'user_login_code' => '123',
			'user_project_id' => 1,
			'user_usertype_id' => 1, //?
			'user_can_vote' => 1

		];
		$this->db->table('user')->insert($row);

		// question_category
		$row = [
			'qscat_id' => 1,
			'qscat_title' => 'General question'
		];
		$this->db->table('question_category')->insert($row);

		$row = [
			'qscat_id' => 2,
			'qscat_title' => 'Additional question'
		];
		$this->db->table('question_category')->insert($row);
		$row = [
			'qscat_id' => 3,
			'qscat_title' => 'Accept additional question'
		];
		$this->db->table('question_category')->insert($row);

		// general question
		$row = [
			'qs_id' => 1,
			'qs_project_id' => 1,
			'qs_title' => 'test-question-123',
			'qs_category_id' => 1, // general question
			'qs_user_id' => 1,
		];
		$this->db->table('question')->insert($row);

		// answer_type
		$row = [
			'answer_type_id' => 1,
			'answer_type_title' => 'Yes, No, Abstain'
		];
		$this->db->table('answer_type')->insert($row);

		$row = [
			'answer_type_id' => 2,
			'answer_type_title' => 'Yes, No'
		];
		$this->db->table('answer_type')->insert($row);

		// answer
		$row = [
			'ans_id' => 1,
			'ans_question_id' => 1,
			'ans_user_id' => 1,
			'ans_answer_type_id' => 1,
			'ans_number' => 0,
			'ans_string' => 0
		];
		$this->db->table('answer')->insert($row);

	}
}
