<?php namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MeetingScript extends Migration
{
	public function up()
	{
		// project
		$this->forge->addField([
			'project_id'     => [
				'type'           => 'INT',
				'auto_increment' => true,
			],
			'project_name' => [
				'type'       => 'VARCHAR',
				'constraint' => '250',
			],
			'project_created_at'  => [
				'type'       => 'timestamp',
				'default'		=> 'current_timestamp()',
			],
			'project_code' => [
				'type'		=> 'VARCHAR',
				'constraint'=> '20'
			],
			'project_acquaintance_start_date' => [
				'type'		=> 'datetime',
				'null'		=> true
			],
			'project_main_agenda_start_date' => [
				'type'		=> 'datetime',
				'null'		=> true
			],
			'project_additional_agenda_start_date' => [
				'type'		=> 'datetime',
				'null'		=> true
			],
			'project_meeting_finish_date' => [
				'type'		=> 'datetime',
				'null'		=> true
			]
		]);
		$this->forge->addKey('project_id', true);
		$this->forge->createTable('project');

		// user
		$this->forge->addField([
			'user_id'     => [
				'type'           => 'INT',
				'auto_increment' => true,
			],
			'user_login_code' => [
				'type'       => 'VARCHAR',
				'constraint' => '50',
				'unique'	=> true
			],
			'user_project_id'  => [
				'type'       => 'INT',
			],
			'user_created_at' => [
				'type'		=> 'timestamp',
				'default'	=> 'current_timestamp()'
			],
			'user_usertype_id' => [
				'type'		=> 'int',
			],
			'user_can_vote' => [
				'type'		=> 'bit',
				'default'		=> 0
			],
			'user_votes_number' => [
				'type'		=> 'DECIMAL',
				//'constraint'	=> '10,2',
				'null'		=> true
			],
			'user_member_name' => [
				'type'		=> 'VARCHAR',
				'constraint'	=> '1000',
				'null'		=> true
			]
		]);
		$this->forge->addKey('user_id', true);
		$this->forge->createTable('user');

		// document
		$this->forge->addField([
			'doc_id'	=> [
				'type'		=> 'INT',
				'auto_increment' => true,
			],
			'doc_filename'	=> [
				'type'		=> 'VARCHAR',
				'constraint'	=> '200',
			],
			'doc_created_at'	=> [
				'type' 		=> 'DATETIME',
				'default'	=> 'current_timestamp()',
			],
			'doc_is_for_creditor'	=> [
				'type'		=> 'BIT'
			],
			'doc_is_for_debtor'		=> [
				'type'		=> 'BIT'
			],
			'doc_is_for_manager'	=> [
				'type'		=> 'BIT'
			]
		]);
		$this->forge->addKey('doc_id', true);
		$this->forge->createTable('document');

		// doc_file
		$this->forge->addField([
			'docfile_doc_id' => [
				'type'		=> 'INT'
			],
			'docfile_body' => [
				'type'		=> 'LONGBLOB'
			],
		]);
		$this->forge->createTable('docfile');

		// question
		$this->forge->addField([
			'qs_id'     => [
				'type'           => 'INT',
				'auto_increment' => true,
			],
			'qs_title' => [
				'type'       => 'TEXT',
			],
			'qs_project_id'  => [
				'type'       => 'INT',
			],
			'qs_category_id' => [
				'type'		=> 'int',
			],
			'qs_user_id'	=> [
				'type'		=> 'int',
				'null'		=> true,
			],
			'qs_created_at'	=> [
				'type'		=> 'datetime',
				'default'	=> 'current_timestamp()'
			],
			'qs_base_question_id' => [
				'type'	=> 'int',
				'null'		=> true,
			],
			'qs_comment'	=> [
				'type'	=> 'text',
				'null'		=> true,
			]
		]);
		$this->forge->addKey('qs_id', true);
		$this->forge->createTable('question');
	}

	//--------------------------------------------------------------------

	public function down()
	{
		$this->forge->dropTable('docfile');
		$this->forge->dropTable('document');
		$this->forge->dropTable('project');
		$this->forge->dropTable('user');
		$this->forge->dropTable('question');

	}
}
