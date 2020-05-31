<?php namespace App\Controllers;
use CodeIgniter\I18n\Time;

class Project extends BaseController {

	public function insert() {
		// $wrid = $this->log_webrequest();
		// $this->set_webrequest_id($wrid);

		$data = $this->getPostData();
		// $data = $this->request->getJSON();
		// $this->log_debug("project insert", json_encode($data));
		$isRequestValid = true;
		$validationErrorText = "";

		//var_dump($data);
		$projectName = isset($data->ProjectName) ? $data->ProjectName : false;
		$projectCode = isset($data->ProjectCode) ? $data->ProjectCode : false;
		
		$acquaintanceStartDate = isset($data->AcquaintanceStartDate) ? $data->AcquaintanceStartDate : false;
		$acquaintanceStartTime = isset($data->AcquaintanceStartTime) ? $data->AcquaintanceStartTime : false;

		$mainAgendaStartDate = isset($data->MainAgendaStartDate) ? $data->MainAgendaStartDate : false;
		$mainAgendaStartTime = isset($data->MainAgendaStartTime) ? $data->MainAgendaStartTime : false;

		$additionalAgendaStartDate = isset($data->AdditionalAgendaStartDate) ? $data->AdditionalAgendaStartDate : false;
		$additionalAgendaStartTime = isset($data->AdditionalAgendaStartTime) ? $data->AdditionalAgendaStartTime : false;

		$meetingFinishDate = isset($data->MeetingFinishDate) ? $data->MeetingFinishDate : false;
		$meetingFinishTime = isset($data->MeetingFinishTime) ? $data->MeetingFinishTime : false;

		// validate required parameters
		if (!$projectName) {
			$validationErrorText .= "Empty ProjectName value in request. ";
			$isRequestValid = false;
		}

		if (!$projectCode) {
			$validationErrorText .= "Empty ProjectCode value in request. ";
			$isRequestValid = false;
		}

		// if (!$acquaintanceStartDate) {
		// 	$validationErrorText .= "Empty AcquaintanceStartDate value in request. ";
		// 	$isRequestValid = false;
		// }

		// if (!$mainAgendaStartDate) {
		// 	$validationErrorText .= "Empty MainAgendaStartDate value in request. ";
		// 	$isRequestValid = false;
		// }

		// if (!$additionalAgendaStartDate) {
		// 	$validationErrorText .= "Empty AdditionalAgendaStartDate value in request. ";
		// 	$isRequestValid = false;
		// }

		// if (!$meetingFinishDate) {
		// 	$validationErrorText .= "Empty MeetingFinishDate value in request. ";
		// 	$isRequestValid = false;
		// }

		// Converting to DateTime
		// форматы дд.мм.гг (гггг) и чч:мм
		if ($acquaintanceStartDate) {
			$acquaintanceStartDateTime = $this->makeDateTime($acquaintanceStartDate, $acquaintanceStartTime);
			if (!$acquaintanceStartDateTime) {
				$dt_str = $acquaintanceStartDate.' '.$acquaintanceStartTime;
				$validationErrorText .= "AcquaintanceStartDate has incorrect format: $dt_str. ";
				$isRequestValid = false;
			}
		}

		if ($mainAgendaStartDate) {
			$mainAgendaStartDateTime = $this->makeDateTime($mainAgendaStartDate, $mainAgendaStartTime);
			if (!$mainAgendaStartDateTime) {
				$dt_str = $acquaintanceStartDate.' '.$acquaintanceStartTime;
				$validationErrorText .= 'MainAgendaStartDate has incorrect format: $dt_str. ';
				$isRequestValid = false;
			}
		}

		if ($additionalAgendaStartDate) {
			$additionalAgendaStartDateTime = $this->makeDateTime($additionalAgendaStartDate, $additionalAgendaStartTime);
			if (!$additionalAgendaStartDateTime) {
				$dt_str = $additionalAgendaStartDate.' '.$additionalAgendaStartTime;
				$validationErrorText .= 'AdditionalAgendaStartDate has incorrect format: $dt_str. ';
				$isRequestValid = false;
			}
		}

		if ($meetingFinishDate) {
			$meetingFinishDateTime = $this->makeDateTime($meetingFinishDate, $meetingFinishTime);
			if (!$meetingFinishDateTime) {
				$dt_str = $meetingFinishDate.' '.$meetingFinishTime;
				$validationErrorText .= 'MeetingFinishDate has incorrect format: $dt_str. ';
				$isRequestValid = false;
			}
		}

		$projectsModel = model('Projects_model');
		// business logic validation
		if ($projectName && $projectsModel->check_project_name_exists($projectName)) {
			$validationErrorText .= "Project name already exists: $projectName. ";
			$isRequestValid = false;
		}

		if ($projectCode && $projectsModel->check_project_code_exists($projectCode)) {
			$validationErrorText .= "Project code already exists: $projectCode. ";
			$isRequestValid = false;
		}

		if (!$isRequestValid) {
			// $this->log_debug("project insert", $validationErrorText);

			echo "Invalid Project POST request: $validationErrorText";
			http_response_code(400);
			exit();
		}

		// save to database
		$mysql_format = 'Y-m-d H:i:s';
		$ac_dt = $acquaintanceStartDate ? $acquaintanceStartDateTime->format($mysql_format) : null;
		$ma_dt = $mainAgendaStartDate ? $mainAgendaStartDateTime->format($mysql_format) : null;
		$aa_dt = $additionalAgendaStartDate ? $additionalAgendaStartDateTime->format($mysql_format) : null;
		$mf_dt = $meetingFinishDate ? $meetingFinishDateTime->format($mysql_format) : null;
		$new_id = $projectsModel->new_project($projectName, $projectCode, $ac_dt, $ma_dt, $aa_dt, $mf_dt);

		// Result		
		$json = json_encode(array("id" => $new_id));

		http_response_code(201); // 201: resource created
		$resource = $this->request->uri->getSegment(1);
		$newuri = base_url("$resource/$new_id");
		header("Location: $newuri");
		header("Content-Type: application/json");
		echo $json;
	}

	public function get() {
		echo 'get test';
	}

	// Converting to DateTime
	// форматы дд.мм.гг (гггг) и чч:мм
	function makeDateTime($date, $time) {
		if (!$date) {
			return false;
		}

		$dt_format = 'j.m.Y H:i O';

		if ($time) {
			$dt_str = $date. ' '.$time.' +0300';
		} else {
			$dt_str = $date. ' 00:00 +0300';
		}
		return Time::createFromFormat($dt_format, $dt_str);
	}

}