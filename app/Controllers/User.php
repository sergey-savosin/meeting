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

		// $data = $this->getPostData();
		// $this->log_debug("User insert", json_encode($data));

		$isRequestValid = true;
		$validationErrorText = "";

		// $projectName = isset($data->ProjectName) ? $data->ProjectName : false;
		// $loginCode = isset($data->LoginCode) ? $data->LoginCode : false;
		// $userType = isset($data->UserType) ? $data->UserType : false;
		// $canVote = isset($data->CanVote) ? $data->CanVote : false;

		// validate required parameters
		// if (!$projectName) {
		// 	$validationErrorText .= "Empty ProjectName value in request. ";
		// 	$isRequestValid = false;
		// }

		// if (!$loginCode) {
		// 	$validationErrorText .= "Empty LoginCode value in request. ";
		// 	$isRequestValid = false;
		// }

		// if (!$userType) {
		// 	$validationErrorText .= "Empty UserType value in request. ";
		// 	$isRequestValid = false;
		// }

		// if ($canVote && strtoupper($canVote) === "TRUE") {
		// 	$canVoteBit = 1;
		// } else {
		// 	$canVoteBit = 0;
		// }

		//Get ProjectId By ProjectName
		// $project = $this->Projects_model->get_first_project_by_name($projectName);
		// $projectId = false;

		// if (!$project) {
		// 	$validationErrorText .= "Project not found: $projectName. ";
		// 	$isRequestValid = false;
		// } else {
		// 	$projectId = $project->project_id;
		// }

		//Get UserTypeId By UserTypeName
		// $userTypeId = $this->Users_model->get_usertypeid_by_usertypename($userType);

		// if ( !$userTypeId ) {
		// 	$validationErrorText .= "User type for found: $userType. Valid values are: Creditor, Debtor, Manager. ";
		// 	$isRequestValid = false;
		// }

		//Check Is User Exists By LoginCode
		// $user = $this->Users_model->get_first_user_by_logincode($loginCode);

		// if ($user) {
		// 	$validationErrorText .= "User login code already exists: $loginCode. ";
		// 	$isRequestValid = false;
		// }

		// if (!$isRequestValid) {
		// 	$this->log_debug("User insert", $validationErrorText);
		// 	echo "Invalid User POST request: $validationErrorText";
		// 	http_response_code(400);
		// 	exit();
		// }

		// User Insert
		// $new_id = $this->Users_model->new_user($projectId, $loginCode, $userTypeId, $canVoteBit);

		// Return result from service		
		$json = json_encode(array("id" => $new_id));

		http_response_code(201); // 201: resource created
		$resource = $this->uri->segment(1);
		$uri = base_url("$resource/$new_id");
		header("Location: $uri");
		header("Content-Type: application/json");
		echo $json;
	}


	public function login() {
		helper(['form', 'url']);

		// $wrid = $this->log_webrequest();
		// $this->set_webrequest_id($wrid);

		$top_nav_data['uri'] = $this->request->uri;

		if ($this->request->getMethod() === 'get' || !$this->validate(['usr_code' => 'required|min_length[1]|max_length[255]']))
		{
			if ($this->request->getMethod() === 'get') {
				$validation = null;
			} else {
				$validation = $this->validator;
			}
			echo view('common/login_header');
			echo view('nav/top_nav', $top_nav_data);
			echo view('user/login', ['validation' => $validation]);
			echo view('common/footer');
		} else {
			$usr_code = $this->request->getVar('usr_code');
			$users_model = model('Users_model');
			$user = $users_model->get_first_user_by_logincode($usr_code);
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

			return redirect()->to(base_url('Documents'));
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