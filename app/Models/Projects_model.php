<?php namespace App\Models;

use CodeIgniter\Model;

class Projects_model extends Model {
	function __construct() {
		parent::__construct();
	}

	/******
	Добавление нового проекта.
	
	Параметры:
	 projectName
	 projectCode
	 acquaintanceStartDate
	 mainAgendaStartDate
	 additionalAgendaStartDate
	 meetingFinishData
	*/
	function new_project($projectName, $projectCode, $acquaintanceStartDate, $mainAgendaStartDate, $additionalAgendaStartDate,
		$meetingFinishDate) {

		$project_data = array ('project_name' => $projectName,
				'project_code' => $projectCode,
				'project_acquaintance_start_date' => $acquaintanceStartDate,
				'project_main_agenda_start_date' => $mainAgendaStartDate,
				'project_additional_agenda_start_date' => $additionalAgendaStartDate,
				'project_meeting_finish_date' => $meetingFinishDate);
		$db = \Config\Database::connect();
		if ($db->table('project')->insert($project_data)) {
			return $db->insertID();
		} else {
			return false;
		}
	}

	/*********************
	 Удаление проекта
	 V4

	 Параметра:
	  projectCode
	**********************/
	function delete_project($projectCode) {
		$db = \Config\Database::connect();

		$db->transStart(); // test mode with rollback

		// 1. delete document
		$this->delete_project_child_entity($db, $projectCode, 'document', 'doc_project_id');
		// 2. delete answer
		$this->delete_project_question_child_entity($db, $projectCode, 'answer', 'ans_question_id');
		// 3. delete question->base_question
		$this->delete_base_question_for_project($db, $projectCode);
		// 4. delete question
		$this->delete_project_child_entity($db, $projectCode, 'question', 'qs_project_id');
		// 5. delete user
		$this->delete_project_child_entity($db, $projectCode, 'user', 'user_project_id');

		// 6. delete project
		$builder = $db->table('project')
			->where('project_code', $projectCode);
		$builder->delete();

		$db->transComplete();

		return true;
	}

	/***********************
	  Удаление из сущностей, связанных с проектом
	***********************/
	function delete_project_child_entity($db, $projectCode, $tableName, $tablePKField) {
		$builder = $db
			->table('project')
			->join($tableName, $tableName.'.'.$tablePKField.' = project.project_id')
			->where('project_code', $projectCode)
			;

		$sql = $builder->getCompiledSelect();
		$len = strlen("SELECT *");
		$sql = substr_replace($sql, 'DELETE '.$tableName, 0, $len);

		log_message('info', "[delete_project] sql: $sql");

		$db->query($sql);
	}

	/****************
	 Удаление сущностей answer для project->question
	*****************/
	function delete_project_question_child_entity($db, $projectCode, $tableName, $tablePKField) {
		$builder = $db
			->table('project')
			->join('question', 'question.qs_project_id = project.project_id')
			->join($tableName, $tableName.'.'.$tablePKField.' = question.qs_id')
			->where('project_code', $projectCode)
			;
		$sql = $builder->getCompiledSelect();
		$len = strlen("SELECT *");
		$sql = substr_replace($sql, 'DELETE '.$tableName, 0, $len);

		log_message('info', "[delete_project] sql: $sql");

		$db->query($sql);
	}

	/***********************
	 delete base_question for project->question
	************************/
	function delete_base_question_for_project($db, $projectCode) {
		$builder = $db
			->table('project')
			->join('question q1', 'q1.qs_project_id = project.project_id')
			->join('question q2', 'q2.qs_base_question_id = q1.qs_id')
			->where('project_code', $projectCode)
			;
		$sql = $builder->getCompiledSelect();
		$len = strlen("SELECT *");
		$sql = substr_replace($sql, 'DELETE q2', 0, $len);

		log_message('info', "[delete_project] sql: $sql");
		$db->query($sql);
	}

	// returns one row
	function get_project_by_name($project_name) {
		$query = "SELECT * FROM project p WHERE p.project_name = ?";
		$result = $this->db->query($query, array($project_name));
		
		if (!$result) {
			return false;
		}

		$row = $result->getRow();
		if (!$row) {
			return false;
		} else {
			return $row;
		}
	}

	function get_project_by_code($project_code) {
		$query = "SELECT * FROM project p WHERE p.project_code = ?";
		$result = $this->db->query($query, array($project_code));
		
		if (!$result) {
			return false;
		}

		$row = $result->getRow();
		if (!$row) {
			return false;
		} else {
			return $row;
		}
	}

	/********************
	 v4

	 return row object
	 ********************/
	function get_project_by_id($project_id) {
		$query = "SELECT * FROM project p WHERE p.project_id = ?";
		$result = $this->db->query($query, array($project_id));

		if (!$result) {
			return false;
		}

		$row = $result->getRow();
		if (!$row) {
			return false;
		} else {
			return $row;
		}
	}


	function check_project_name_exists($project_name) {
		$result = $this->get_project_by_name($project_name);
		if ($result) {
			return true;
		} else {
			return false;
		}
	}

	function check_project_code_exists($project_code) {
		$result = $this->get_project_by_code($project_code);
		if ($result) {
			return true;
		} else {
			return false;
		}
	}

	/***************
	 returns object(start_date, end_date)
	 ***************/
	function getStagesDates($project_id) {
		$query = "SELECT p.project_acquaintance_start_date
		, p.project_main_agenda_start_date
		, p.project_additional_agenda_start_date
		, p.project_meeting_finish_date
		FROM project p
		WHERE p.project_id = ?
		";
		$result = $this->db->query($query, array($project_id));

		if (!$result) {
			return false;
		}

		$row = $result->getRow();
		if (!isset($row)) {
			return false;
		} else {
			return $row;
		}
	}

	/**********************
	 $stage_name: one of 'acquaintance', 'main_agenda', 'additional_agenda'
	 returns string: 'early', 'current', 'late'
	 **********************/
	function getStageStatus($project_id, $current_date, $stage_name) {
		$dates = $this->getStagesDates($project_id);
		if (!$dates) {
			return false;
		}

		if ($stage_name == 'acquaintance') {
			$date1 = isset($dates->project_acquaintance_start_date) ?
				date_create($dates->project_acquaintance_start_date) : null;
			$date2 = isset($dates->project_main_agenda_start_date) ?
				date_create($dates->project_main_agenda_start_date) : null;
		} elseif ($stage_name == 'main_agenda') {
			$date1 = isset($dates->project_main_agenda_start_date) ?
				date_create($dates->project_main_agenda_start_date) : null;
			$date2 = isset($dates->project_additional_agenda_start_date) ?
				date_create($dates->project_additional_agenda_start_date) : null;
		} elseif ($stage_name == 'additional_agenda') {
			$date1 = isset($dates->project_additional_agenda_start_date) ?
				date_create($dates->project_additional_agenda_start_date) : null;
			$date2 = isset($dates->project_meeting_finish_date) ?
				date_create($dates->project_meeting_finish_date) : null;
		} else {
			return false;
		}

		$dateC = date_create($current_date);

		if ($date1 != null && $dateC < $date1) {
			$stage_state = 'early';
		} elseif ($date2 && $dateC > $date2) {
			$stage_state = 'late';
		} else {
			$stage_state = 'active';
		}

		return $stage_state;
	}

	/***********************
	v4
	Список проектов

	returns resultset
	************************/
	function getProjectList() {
		$query = "SELECT * FROM project p ORDER BY p.project_id DESC";
		$result = $this->db->query($query);
		
		if (!$result) {
			return false;
		} else {
			return $result;
		}
	}
}