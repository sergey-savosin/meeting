<?php namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MeetingSeeder extends Seeder
{
	public function run()
	{
		$row = [
			'project_name' => 'ProjectName-123',
			'project_code' => 'ProjectCode-123',
			
		];

		$this->db->table('project')->insert($row);

		$row = [
			'user_login_code' => '123',
			'user_project_id' => 1,
			'user_usertype_id' => 1, //?

		];

		$this->db->table('user')->insert($row);
	}
}
