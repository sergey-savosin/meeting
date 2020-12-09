<?php namespace App\Models;

use CodeIgniter\Model;

class Answers_model extends Model {
	function __construct() {
		parent::__construct();
	}

	/**********
	Список ответов на вопросы основной повестки	для указанного пользователя.
	
	Returns resultset.
	***********/
	function fetch_general_answers($user_id) {
		$query = "SELECT *
		FROM user u
		INNER JOIN question q
			ON q.qs_project_id = u.user_project_id
		LEFT JOIN answer a
			ON q.qs_id = a.ans_question_id
			AND a.ans_user_id = u.user_id
		WHERE q.qs_category_id = 1
		AND u.user_id = ?
		ORDER BY q.qs_id ASC
		";
		$result = $this->db->query($query, array($user_id));

		if ($result) {
			return $result;
		} else {
			return false;
		}
	}

	/****************
	Список вспомогательных вопросов-ответов для принятия доп вопросов.
	Returns resultset.
	*****************/
	function fetch_accept_additional_answers($user_id) {
		$query = "SELECT *
		FROM user u
		INNER JOIN question q
			ON q.qs_project_id = u.user_project_id
		LEFT JOIN answer a
			ON a.ans_question_id = q.qs_id
			AND a.ans_user_id = u.user_id
		WHERE u.user_id = ?
			AND q.qs_category_id = 3
		ORDER BY q.qs_id ASC
		";
		$result = $this->db->query($query, array ($user_id));

		if ($result) {
			return $result;
		} else {
			return false;
		}
	}

	/**************
	Список ответов на вопросы дополнительной повестки
	Returns resultset.
	***************/
	function fetch_additional_answers($project_id, $user_id) {
		$query = "SELECT *
		FROM question q
		LEFT JOIN answer a
			ON q.qs_id = a.ans_question_id
			AND a.ans_user_id = ?
		WHERE q.qs_category_id = 2
		and q.qs_project_id = ?
		ORDER BY q.qs_id ASC
		";
		$result = $this->db->query($query, array($user_id, $project_id));

		if ($result) {
			return $result;
		} else {
			return false;
		}
	}

	/******
	Добавление нового ответа на вопрос основной повестки.
	
	Параметры.
	Returns new id.
	*/
	function new_general_answer($question_id, $user_id, $ans_number, $ans_string, $answer_type_id) {

		$data = array ('ans_question_id' => $question_id,
				'ans_user_id' => $user_id,
				'ans_number' => $ans_number,
				'ans_string' => $ans_string,
				'ans_answer_type_id' => $answer_type_id);

		$db = \Config\Database::connect();
		if ($db->table('answer')->insert($data)) {
			return $db->insertID();
		} else {
			return false;
		}
	}

	/*******************
	 function returns object {ans_id}
	 *******************/
	function get_answer($qs_id, $user_id) {
		$query = "SELECT ans_id FROM answer a WHERE ans_question_id = ? and ans_user_id = ?";
		$result = $this->db->query($query, array($qs_id, $user_id));
		if (!$result) {
			return false;
		}

		$row = $result->getRow();
		if (!isset($row)) {
			return false;
		}
		return $row;
	}

	/********************
	 @user_id
	 @qs_category_id: 1 - general, 2 - additional, 3 - accept additional

	 function returns object {cnt}
	 ********************/
	function get_opened_user_questions_count($user_id, $qs_category_id) {
		$query = "SELECT count(1) cnt
			FROM user u
			INNER JOIN question q
				ON u.user_project_id = q.qs_project_id
			LEFT JOIN answer a
				ON a.ans_question_id = q.qs_id
				AND a.ans_user_id = u.user_id
			WHERE u.user_id = ?
			AND q.qs_category_id = ?
			AND a.ans_id IS NULL
			ORDER BY q.qs_id";
		$result =$this->db->query($query, array($user_id, $qs_category_id));
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

	/************************
	 Список доп вопросов с результатом голосования:
	 - кол-во ЗА
	 - кол-во ПРОТИВ
	 - всего дано голосов
	 - ID доп вопроса

	 returns resultset
	 ************************/
	function fetch_additional_answers_with_votes($user_id) {
		$query = "SELECT
			SUM(CASE WHEN a_acc.ans_number = 0 THEN 1 ELSE 0 END) ans_yes, 
			SUM(CASE WHEN a_acc.ans_number = 1 THEN 1 ELSE 0 END) ans_no,
			SUM(CASE WHEN a_acc.ans_number = 2 THEN 1 ELSE 0 END) and_doubt,
			COUNT(1) ans_total,
			q_base.qs_id,
			q_base.qs_title,
			a_base.ans_number
		FROM user u
		INNER JOIN question q_acc
			ON q_acc.qs_project_id = u.user_project_id
		INNER JOIN answer a_acc
			ON a_acc.ans_question_id = q_acc.qs_id 
		INNER JOIN question q_base
			ON q_base.qs_id = q_acc.qs_base_question_id
		LEFT JOIN answer a_base
			ON a_base.ans_question_id = q_base.qs_id
			AND a_base.ans_user_id = u.user_id
		WHERE u.user_id = ?
			AND q_acc.qs_category_id = 3 /* accept additional */
		GROUP BY q_base.qs_id, q_base.qs_title
		ORDER BY q_base.qs_id";

		$result = $this->db->query($query, array ($user_id));
		
		if (!$result) {
			return false;
		} else {
			return $result;
		}
	}

	/***********************
	 Returns boolean
	 ***********************/
	function update_general_answer($ans_id, $ans_number, $ans_string, $answer_type_id) {
		$data = [
			'ans_number' => $ans_number,
			'ans_string' => $ans_string,
			'ans_answer_type_id' => $answer_type_id
		];

		$db = \Config\Database::connect();
		$builder = $db->table('answer');
		$builder->where('ans_id', $ans_id);
		if ($builder->update($data)){
			return true;
		} else {
			return false;
		}
	}

	/************************
	 v4
	 Returns questions with answer information
	 $project_id
	 $qs_category_id: 1 - general, 2 - additional, 3 - accept_additional

	 Returns resultset
	 ************************/
	function fetch_answers_for_projectid($project_id, $qs_category_id) {
		$query = "SELECT q.qs_id, q.qs_title,
			SUM(u.user_votes_number) AS AnsweredVotesNumber,
			SUM(CASE WHEN a.ans_number = 0 THEN u.user_votes_number ELSE 0 END) AS YesVotesNumber,
			SUM(CASE WHEN a.ans_number = 1 THEN u.user_votes_number ELSE 0 END) AS NoVotesNumber,
			SUM(CASE WHEN a.ans_number = 2 THEN u.user_votes_number ELSE 0 END) AS AbstainVotesNumber,
			SUM(CASE WHEN a.ans_question_id IS NULL THEN u.user_votes_number ELSE 0 END) AS MissedVotesNumber

			FROM question q
			INNER JOIN user u ON u.user_project_id = q.qs_project_id
			LEFT JOIN answer a
				ON a.ans_question_id = q.qs_id
				AND a.ans_user_id = u.user_id

			WHERE q.qs_project_id = ?
			AND q.qs_category_id = ?
			GROUP BY q.qs_id
			ORDER BY q.qs_id
		";

		$result = $this->db->query($query, array ($project_id, $qs_category_id));

		if (!$result) {
			return false;
		} else {
			return $result;
		}
	}

	/************************
	 v4
	 Расчёт итого голосования по вопросам

	 return array
	*************************/
	function calc_answers_for_projectid($project_id, $qs_category_id, $total_voices_sum) {
		if (!$total_voices_sum || $total_voices_sum === 0) {
			throw new \Exception('total_voices_sum param should be positive.');
			return false;
		}

		$answers = $this->fetch_answers_for_projectid($project_id, $qs_category_id);
		$result = [];
		$iter = 1;
		foreach ($answers->getResult() as $a) {
			if ($total_voices_sum == 0) {
				$correctedVoicesSum = 100;
			} else {
				$correctedVoicesSum = $total_voices_sum;
			}

			if ($a->YesVotesNumber == 0
					&& $a->NoVotesNumber == 0 
					&& $a->AbstainVotesNumber == 0) {
				$voicesYesResult = '';
			} else {
				$voicesYesResult = 
					($a->YesVotesNumber / $correctedVoicesSum >0.5 ?
							'Принят' : 'Отклонён');
			}

			$result[$iter] = array('qs_id' => $a->qs_id,
							'qs_title' => $a->qs_title,
							'AnsweredVotesNumber' => $a->AnsweredVotesNumber,
							'YesVotesNumber' => $a->YesVotesNumber,
							'NoVotesNumber' => $a->NoVotesNumber,
							'AbstainVotesNumber' => $a->AbstainVotesNumber,
							'MissedVotesNumber' => $a->MissedVotesNumber,
							'YesVotesPercent' => round(100.0 * $a->YesVotesNumber / 
								$correctedVoicesSum, 2),
							'NoVotesPercent' => round(100.0 * $a->NoVotesNumber / 
								$correctedVoicesSum, 2),
							'AbstainVotesPercent' => round(100.0 * $a->AbstainVotesNumber / 
								$correctedVoicesSum, 2),
							'QuestionVotingResult' => $voicesYesResult
						);
			$iter++;
		}

		return $result;
	}

	/************************
	v4
	Returns questions with answer and user detailed information
	$project_id
	$qs_category_id: 1 - general, 2 - additional, 3 - accept_additional

	Returns resultset
	************************/
	function fetch_answers_and_users_for_questionid($question_id) {
		$query = "SELECT q.qs_title,
			u.user_member_name,
			u.user_votes_number,
			a.ans_number

			FROM question q
			INNER JOIN user u ON u.user_project_id = q.qs_project_id
			LEFT JOIN answer a
				ON a.ans_question_id = q.qs_id
				AND a.ans_user_id = u.user_id

			WHERE q.qs_id = ?
			ORDER BY u.user_id
		";

		$result = $this->db->query($query, array ($question_id));

		if (!$result) {
			return false;
		} else {
			return $result;
		}
	}

}