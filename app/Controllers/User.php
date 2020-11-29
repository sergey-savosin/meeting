<?php namespace App\Controllers;

class User extends BaseController {
	// function __construct() {
	// 	parent::__construct();
	// 	$this->load->library('form_validation');

	// 	$this->load->model('Users_model');
	// 	$this->load->model('Projects_model');
	// }

	public function insert() {
		// $wrid = $this->log_webrequest();
		// $this->set_webrequest_id($wrid);

		$data = $this->getPostData();
		// $this->log_debug("User insert", json_encode($data));

		$isRequestValid = true;
		$validationErrorText = "";

		$projectName = isset($data->ProjectName) ? $data->ProjectName : false;
		$loginCode = isset($data->LoginCode) ? $data->LoginCode : false;
		$userType = isset($data->UserType) ? $data->UserType : false;
		$canVote = isset($data->CanVote) ? $data->CanVote : false;
		$votesNumber = isset($data->VotesNumber) ? $data->VotesNumber : false;
		$memberName = isset($data->MemberName) && !empty(trim($data->MemberName)) ? trim($data->MemberName) : null;

		// validate required parameters
		if (!$projectName) {
			$validationErrorText .= " Empty ProjectName value in request.";
			$isRequestValid = false;
		}

		if (!$loginCode) {
			$validationErrorText .= " Empty LoginCode value in request.";
			$isRequestValid = false;
		}

		if (!$userType) {
			$validationErrorText .= " Empty UserType value in request.";
			$isRequestValid = false;
		}

		if ($canVote && strtoupper($canVote) === "TRUE") {
			$canVoteBit = true;
		} else {
			$canVoteBit = false;
		}

		if ($votesNumber && !is_numeric($votesNumber)) {
			$validationErrorText .= 
				" VotesNumber parameter has incorrect format: $votesNumber.";
			$isRequestValid = false;
		}

		$projects_model = model('Projects_model');
		$users_model = model('Users_model');

		//Get ProjectId By ProjectName
		if (!empty($projectName)) {
			$project = $projects_model->get_project_by_name($projectName);
			$projectId = false;

			if (!$project) {
				$validationErrorText .= " Project not found: $projectName.";
				$isRequestValid = false;
			} else {
				$projectId = $project->project_id;
			}
		}

		// Get UserTypeId By UserTypeName
		if (!empty($userType)) {
			$userTypeRow = $users_model->get_usertype_by_usertypename($userType);

			if ( !$userTypeRow ) {
				$validationErrorText .=
					" User type not found: $userType. ".
					"Valid values are: Creditor, Debtor, Manager.";
				$isRequestValid = false;
			} else {
				$userTypeId = $userTypeRow->usertype_id;
			}
		}

		// Check Is User Exists By LoginCode
		if (!empty($loginCode)) {
			$user = $users_model->get_user_by_logincode($loginCode);

			if ($user) {
				$validationErrorText .= " User login code already exists: $loginCode.";
				$isRequestValid = false;
			}
		}

		if (!$isRequestValid) {
			$msg = "Invalid User POST request:$validationErrorText";
			log_message('info', "[user::insert] validation error:$msg");

			return $this->response
				->setStatusCode(400)
				->removeHeader('Location')
				->setJSON(['error'=>$msg]);
		}

		// User Insert
		$new_id = $users_model->new_user(
			$projectId, $loginCode, $userTypeId,
			$canVoteBit, $votesNumber, $memberName);

		helper('url');

		// Return result from service

		$resource = $this->request->uri->getSegment(1);
		$newuri = base_url("$resource/$new_id");
		$body = array("id" => $new_id);
		return $this->response
			->setStatusCode(201) // 201: resourse created
			->setHeader("Location", $newuri)
			->setContentType("application/json")
			->setJSON($body);

	}


	public function login() {
		helper(['form', 'url']);

		// $wrid = $this->log_webrequest();
		// $this->set_webrequest_id($wrid);

		$top_nav_data['uri'] = $this->request->uri;
		$val_rules['usr_code'] = 'required|min_length[1]|max_length[255]';

		if ($this->request->getMethod() === 'get' || !$this->validate($val_rules))
		{
			if ($this->request->getMethod() === 'get') {
				$validation = null;
			} else {
				$validation = $this->validator;
				log_message('info', "[user/login] validation: ".$validation->getError('usr_code'));
			}
			echo view('common/login_header');
			echo view('nav/top_nav', $top_nav_data);
			echo view('user/login', ['validation' => $validation]);
			echo view('common/footer');
		} else {
			$usr_code = $this->request->getVar('usr_code');
			log_message('info', "[user/login] usr_code: $usr_code");
			$users_model = model('Users_model');
			$user = $users_model->get_user_by_logincode($usr_code);
			if (!$user) {
				//$this->log_debug("User login", "login failed: $usr_code");

				echo view('common/login_header');
				echo view('nav/top_nav', $top_nav_data);
				echo view('user/login', ['validation' => $this->validator, 'login_fail' => true]);
				echo view('common/footer');
				return;
			}

			// Save data to session
			$data = array (
				'user_login_code' => $user->user_login_code,
				'user_project_id' => $user->user_project_id,
				'user_type_id' => $user->user_usertype_id,
				'user_can_vote' => $user->user_can_vote,
				'user_id' => $user->user_id
			);

			$session = session();
			$session->set($data);

			// 	$this->log_debug("User login", "Succefull loginned: $usr_code");
			if ($session->has('redirect_from')) {
				$redirect_from = $session->get('redirect_from');
				$session->remove('redirect_from');

				return redirect()->to(base_url($redirect_from));
			} else {
				return redirect()->to(base_url('/'));
			}


		}
	}

	public function logout() {
			$data = array (
				'user_login_code',
				'user_project_id',
				'user_type_id',
				'user_can_vote',
				'user_id'
			);

			// Save data to session
			//$this->log_debug("User logout", "");
			$session = session();
			$session->remove($data);
			return redirect()->to(base_url('Documents'));
	}

}