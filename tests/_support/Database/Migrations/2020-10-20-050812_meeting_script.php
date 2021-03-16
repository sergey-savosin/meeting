<?php namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MeetingScript extends Migration
{
	public function up()
	{
		// admin
		$this->forge->addField([
			'admin_id' => [
				'type' => 'INT',
				'auto_increment' => true,
			],
			'admin_login' => [
				'type' => 'VARCHAR',
				'constraint' => '250',
				'unique' => true,
			],
			'admin_password' => [
				'type' => 'VARCHAR',
				'constraint' => '500'
			],
			'admin_email' => [
				'type' => 'VARCHAR',
				'constraint' => '500',
				'null' => true
			],
			'admin_phone' => [
				'type' => 'VARCHAR',
				'constraint' => '100',
				'null' => true
			]
		]);
		$this->forge->addKey('admin_id', true);
		$this->forge->createTable('admin');

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
			],
			'project_admin_id' => [
				'type'		=> 'INT',
				'null'		=> true
			]
		]);
		$this->forge->addKey('project_id', true);
		$this->forge->addForeignKey('project_admin_id', 'admin', 'admin_id');
		$this->forge->createTable('project');

		// usertype
		$this->forge->addField([
			'usertype_id' => [
				'type' => 'INT'
			],
			'usertype_name' => [
				'type' => 'VARCHAR',
				'constraint' => '50'
			]
		]);
		$this->forge->addkey('usertype_id', true);
		$this->forge->createTable('usertype');

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
				//'default'		=> 0
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
		$this->forge->addForeignKey('user_project_id', 'project', 'project_id');
		$this->forge->addForeignKey('user_usertype_id', 'usertype', 'usertype_id');
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
			],
			'doc_caption' => [
				'type'       => 'VARCHAR',
				'constraint' => '255',
				'null'       => true
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
		$this->forge->addKey('docfile_doc_id', true);
		$this->forge->addForeignKey('docfile_doc_id', 'document', 'doc_id');
		$this->forge->createTable('docfile');

		// project_document
		$this->forge->addField([
			'pd_id' => [
				'type' => 'INT',
				'auto_increment' => true
			],
			'pd_project_id' => [
				'type' => 'INT',
			],
			'pd_doc_id' => [
				'type' => 'INT'
			]
		]);
		$this->forge->addKey('pd_id', true);
		$this->forge->addForeignKey('pd_project_id', 'project', 'project_id');
		$this->forge->addForeignKey('pd_doc_id', 'document', 'doc_id');
		$this->forge->createTable('project_document');

		// question_category
		$this->forge->addField([
			'qscat_id' => [
				'type' => 'INT'
			],
			'qscat_title' => [
				'type' => 'VARCHAR',
				'constraint' => '200'
			]
		]);
		$this->forge->addKey('qscat_id', true);
		$this->forge->createTable('question_category');

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
		$this->forge->addForeignKey('qs_project_id', 'project', 'project_id');
		$this->forge->addForeignKey('qs_category_id', 'question_category', 'qscat_id');
		$this->forge->addForeignKey('qs_user_id', 'user', 'user_id');
		$this->forge->addForeignKey('qs_base_question_id', 'question', 'qs_id');
		$this->forge->createTable('question');

		// question_document
		$this->forge->addField([
			'qd_id' => [
				'type' => 'int',
				'auto_increment' => true
			],
			'qd_question_id' => [
				'type' => 'int'
			],
			'qd_doc_id' => [
				'type' => 'int'
			]
		]);
		$this->forge->addKey('qd_id', true);
		$this->forge->addForeignKey('qd_question_id', 'question', 'qs_id');
		$this->forge->addForeignKey('qd_doc_id', 'document', 'doc_id');
		$this->forge->createTable('question_document');

		// answer_type
		$this->forge->addField([
			'answer_type_id' => [
				'type' => 'INT'
			],
			'answer_type_title' => [
				'type' => 'VARCHAR',
				'constraint' => '255'
			]
		]);
		$this->forge->addKey('answer_type_id', true);
		$this->forge->createTable('answer_type');

		// answer
		$this->forge->addField([
			'ans_id' => [
				'type' => 'int',
				'auto_increment' => true
			],
			'ans_question_id' => [
				'type' => 'int'
			],
			'ans_user_id' => [
				'type' => 'int'
			],
			'ans_number' => [
				'type' => 'int'
			],
			'ans_string' => [
				'type' => 'int' // ToDo: correct type?
			],
			'ans_answer_type_id' => [
				'type' => 'int'
			],
			'ans_comment' => [
				'type' => 'text',
				'null' => true
			]
		]);
		$this->forge->addKey('ans_id', true);
		$this->forge->addForeignKey('ans_question_id', 'question', 'qs_id');
		$this->forge->addForeignKey('ans_user_id', 'user', 'user_id');
		$this->forge->addForeignKey('ans_answer_type_id', 'answer_type', 'answer_type_id');
		$this->forge->createTable('answer');
	}

	//--------------------------------------------------------------------

	public function down()
	{
		$this->forge->dropTable('answer');
		$this->forge->dropTable('answer_type');
		$this->forge->dropTable('project_document');
		$this->forge->dropTable('question_document');
		$this->forge->dropTable('docfile');
		$this->forge->dropTable('document');
		$this->forge->dropTable('project');
		$this->forge->dropTable('usertype');
		$this->forge->dropTable('user');
		$this->forge->dropTable('question');
		$this->forge->dropTable('question_category');
		$this->forge->dropTable('admin');
	}
}
