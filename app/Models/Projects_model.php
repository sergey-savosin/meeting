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
		if ($this->db->insert('project', $project_data)) {
			return $this->db->insert_id();
		} else {
			return false;
		}
	}

	function get_project_by_name($project_name) {
		$query = "SELECT * FROM project p WHERE p.project_name = ?";
		$result = $this->db->query($query, array($project_name));
		
		if ($result) {
			return $result;
		} else {
			return false;
		}
	}

	function get_project_by_code($project_code) {
		$query = "SELECT * FROM project p WHERE p.project_code = ?";
		$result = $this->db->query($query, array($project_code));
		
		if ($result) {
			return $result;
		} else {
			return false;
		}
	}


	function check_project_name_exists($project_name) {
		$result = $this->get_project_by_name($project_name);
		if ($result->num_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}

	function check_project_code_exists($project_code) {
		$result = $this->get_project_by_code($project_code);
		if ($result->num_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}

	function get_first_project_by_name($projectName) {
		$project = $this->get_project_by_name($projectName);
		
		if ( (!$project) || ($project->num_rows() == 0) ) {
			return false;
		}

		return $project->result()[0];
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

}