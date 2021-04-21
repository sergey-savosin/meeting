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
	 admin_id
	*/
	function new_project($projectName, $projectCode, $acquaintanceStartDate,
		$mainAgendaStartDate, $additionalAgendaStartDate,
		$meetingFinishDate, $admin_id) {

		$project_data = array ('project_name' => $projectName,
				'project_code' => $projectCode,
				'project_acquaintance_start_date' => $acquaintanceStartDate,
				'project_main_agenda_start_date' => $mainAgendaStartDate,
				'project_additional_agenda_start_date' => $additionalAgendaStartDate,
				'project_meeting_finish_date' => $meetingFinishDate,
				'project_admin_id' => $admin_id);
		$db = \Config\Database::connect();
		if ($db->table('project')->insert($project_data)) {
			return $db->insertID();
		} else {
			return false;
		}
	}

	function link_project_and_document($projectId, $docId) {

		$data = array ('pd_project_id' => $projectId,
						'pd_doc_id' => $docId);
		$db = $this->db;
		if ($db->table('project_document')->insert($data)) {
			return $db->insertID();
		} else {
			return false;
		}
	}

	function get_project_by_document_id($docId) {
		$query = "SELECT p.project_id, p.project_name
		FROM project_document pd
		INNER JOIN project p ON p.project_id = pd_project_id
		WHERE pd.pd_doc_id = ?";
		$result = $this->db->query($query, array($docId));
		
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

	function get_project_by_user_id($userId) {
		$query = $this->db->table('user u')
			->join('project p', 'p.project_id = u.user_project_id')
			->where('u.user_id', $userId)
			->get();
		//$result = $query->getResult();

		// if (!$result) {
		// 	return false;
		// }

		$row = $query->getRow();
		if (!$row) {
			return false;
		} else {
			return $row;
		}
	}

	/**
	* Удалить запись в таблице project_document
	*/
	function delete_project_document_with_tran($docId) {
		$db = \Config\Database::connect();

		$db->transBegin();

		$this->delete_project_document_db($db, $docId);
		$this->delete_docfile_db($db, $docId);
		$this->delete_document_db($db, $docId);

		$db->transComplete();
	}

	/*********************
	 Удаление проекта
	 V4

	 Параметра:
	  projectCode
	**********************/
	function delete_project($projectCode) {
		$db = \Config\Database::connect();

		$db->transBegin(); // test mode with rollback

		// 1. delete document
		$this->delete_project_documents_db($db, $projectCode);
		
		// 2. delete answer
		$this->delete_project_answer_for_question($db, $projectCode);
		$this->delete_project_answer_for_base_question($db, $projectCode);
		$this->delete_project_answer_for_user($db, $projectCode);

		// 3. delete question->base_question
		$this->delete_secondary_question_documents_for_project($db, $projectCode);
		$this->delete_secondary_question_for_project($db, $projectCode);
		
		// 4. delete question
		$this->delete_question_documents_for_project($db, $projectCode);
		$this->delete_question_for_project($db, $projectCode);
		
		// 5. delete user
		$this->delete_project_user($db, $projectCode);

		// 6. delete project
		$builder = $db->table('project')
			->where('project_code', $projectCode);
		$builder->delete();

		$db->transComplete();

		return true;
	}

	/**
	* Update project
	*
	* @return boolean
	*/
	function update_project($project_id, $project_name,
		$acquaintance_start_date, $main_agenda_start_date,
		$additional_agenda_start_date, $meeting_finish_date) {
		$data = [
			'project_name' => $project_name,
			'project_acquaintance_start_date' => $acquaintance_start_date,
			'project_main_agenda_start_date' => $main_agenda_start_date,
			'project_additional_agenda_start_date' => $additional_agenda_start_date,
			'project_meeting_finish_date' => $meeting_finish_date
		];

		$db = \Config\Database::connect();
		$builder = $db->table('project');
		$builder->where('project_id', $project_id);
		if ($builder->update($data)){
			return true;
		} else {
			return false;
		}
	}

	/**
	* new
	*/
	private function delete_project_documents_db($db, $projectCode) {
		// get document ids
		$docs = $this->get_project_document_list_db($db, $projectCode);

		foreach ($docs as $docId) {
			$this->delete_docfile_db($db, $docId);
			$this->delete_project_document_db($db, $docId);
			$this->delete_document_db($db, $docId);
		}
	}

	/**
	* new
	*/
	private function delete_secondary_question_documents_for_project($db, $projectCode) {
		// get document ids
		$data = $this->get_secondary_question_document_list_db($db, $projectCode);
		log_message('info', '[delete_secondary_question_for_project] data: '
			.json_encode($data));

		foreach ($data as $docIdJson) {
			$docId = $docIdJson['qd_doc_id'];
			log_message('info', '[delete_question_documents_for_project] docId: '
				.$docId);

			$this->delete_docfile_db($db, $docId);
			$this->delete_question_document_db($db, $docId);
			$this->delete_document_db($db, $docId);
		}
	}

	/**
	* new
	*/
	private function delete_question_documents_for_project($db, $projectCode) {
		// get document ids
		$data = $this->get_question_document_list_db($db, $projectCode);
		log_message('info', '[delete_question_documents_for_project] data: '
			.json_encode($data));

		foreach ($data as $docIdJson) {
			$docId = $docIdJson['qd_doc_id'];
			log_message('info', '[delete_question_documents_for_project] docId: '
				.$docId);

			$this->delete_docfile_db($db, $docId);
			$this->delete_question_document_db($db, $docId);
			$this->delete_document_db($db, $docId);
		}
	}

	/**
	* make doc_id list
	*/
	private function get_project_document_list_db($db, $projectCode) {
		$builder = $db
			->table('project')
			->join('project_document', 'project_document.pd_project_id = project.project_id')
			->where('project_code', $projectCode)
			;
		$sql = $builder->getCompiledSelect();
		$query = $db->query($sql);

		$docs = [];
		foreach ($query->getResult() as $row) {
			$docs[] = $row->pd_doc_id;
		}

		log_message('info', '[get_project_document_list_db] docs: '.json_encode($docs));

		return $docs;
	}

	private function get_question_document_list_db($db, $projectCode) {
		$data = $db
			->table('project p')
			->join('question q1', 'q1.qs_project_id = p.project_id')
			->join('question_document qd', 'qd.qd_question_id = q1.qs_id')
			->where('p.project_code', $projectCode)
			->select('qd.qd_doc_id')
			->get()
			->getResultArray()
			;

		return $data;
	}

	private function get_secondary_question_document_list_db($db, $projectCode) {
		$data = $db
			->table('project p')
			->join('question q1', 'q1.qs_project_id = p.project_id')
			->join('question q2', 'q2.qs_base_question_id = q1.qs_id')
			->join('question_document qd', 'qd.qd_question_id = q2.qs_id')
			->select('qd.qd_doc_id')
			->get()
			->getResultArray()
			;

		return $data;
	}

	/**
	* delete docfile record
	*/
	private function delete_docfile_db($db, $docId) {
		$db->table('docfile')
			->where('docfile_doc_id', $docId)
			->delete();
	}

	/**
	* delete project_document record
	*/
	private function delete_project_document_db($db, $docId) {
		$db->table('project_document')
			->where('pd_doc_id', $docId)
			->delete();
	}

	/**
	* delete question_document record
	*/
	private function delete_question_document_db($db, $docId) {
		$db->table('question_document')
			->where('qd_doc_id', $docId)
			->delete();
	}

	/**
	* delete document record
	*/
	private function delete_document_db($db, $docId) {
		$db->table('document')
			->where('doc_id', $docId)
			->delete();
	}

	/***********************
	 delete base_question for project->question
	************************/
	private function delete_secondary_question_for_project($db, $projectCode) {
		$builder = $db
			->table('question')
			->whereIn('qs_base_question_id', function($builder) use ($projectCode) {
				return $builder
					->select('q1.qs_id')
					->from('project p')
					->join('question q1', 'q1.qs_project_id = p.project_id')
					->where('p.project_code', $projectCode)
				;
			});

		$this->build_delete($db, $builder);
	}

	private function delete_question_for_project($db, $projectCode) {
		$builder = $db
			->table('question')
			->whereIn('qs_project_id', function($builder) use ($projectCode) {
				return $builder
					->select('project_id')
					->from('project')
					->where('project_code', $projectCode)
				;
			});
		$this->build_delete($db, $builder);
	}

	private function delete_project_answer_for_question($db, $projectCode) {
		$builder = $db
			->table('answer')
			->whereIn('ans_question_id', function($builder) use ($projectCode) {
				return $builder
					->select('q.qs_id')
					->from('project p')
					->join('question q', 'q.qs_project_id = p.project_id')
					->where('p.project_code', $projectCode)
				;
			});
		$this->build_delete($db, $builder);
	}

	private function delete_project_answer_for_base_question($db, $projectCode) {
		$builder = $db
			->table('answer')
			->whereIn('ans_question_id', function($builder) use ($projectCode) {
				return $builder
					->select('q2.qs_id')
					->from('project p')
					->join('question q1', 'q1.qs_project_id = p.project_id')
					->join('question q2', 'q2.qs_base_question_id = q1.qs_id')
					->where('p.project_code', $projectCode)
				;
			});
		$this->build_delete($db, $builder);
	}

	private function delete_project_answer_for_user($db, $projectCode) {
		$builder = $db
			->table('answer')
			->whereIn('ans_user_id', function($builder) use ($projectCode) {
				return $builder
					->select('u.user_id')
					->from('project p')
					->join('user u', 'u.user_project_id = p.project_id')
					->where('p.project_code', $projectCode)
				;
			});
		$this->build_delete($db, $builder);
	}

	private function delete_project_user($db, $projectCode) {
		$builder = $db
			->table('user')
			->whereIn('user_project_id', function($builder) use ($projectCode) {
				return $builder
					->select('project_id')
					->from('project')
					->where('project_code', $projectCode)
				;
			});

		$this->build_delete($db, $builder);
	}

	/**
	* Make delete query from select query. And run it.
	*/
	private function build_delete($db, $builder) {
		$sql = $builder->getCompiledSelect();
		$len = strlen("SELECT *");
		$sql = substr_replace($sql, 'DELETE', 0, $len);

		//log_message('info', "[build_delete] sql: $sql");
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

	function is_project_exists_by_name_and_adminId($projectName, $adminId) {
		$query = $this->db
			->table('project')
			->where('project_name', $projectName)
			->where('project_admin_id', $adminId)
			->selectCount('project_id', 'cnt')
			->get();
		
		if (!$query) {
			return false;
		}

		$row = $query->getRow();
		if (!$row) {
			return false;
		}

		return $row->cnt == 1 ? true : false;
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

	/*
	* Список проектов администратора
	* returns query.
	*
	* Use $query->getResult()
	*/
	function getProjectListByAdminId($admin_id) {
		$db = $this->db;
		$query = $db->table('project')
				->where('project_admin_id', $admin_id)
				->get();

		return $query;
	}
}