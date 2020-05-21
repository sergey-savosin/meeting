<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class User extends MY_Controller {
	function __construct() {
		parent::__construct();
		$this->load->library('form_validation');

		$this->load->model('Users_model');
		$this->load->model('Projects_model');
	}

	public function insert() {
		$wrid = $this->log_webrequest();
		$this->set_webrequest_id($wrid);

		$data = $this->getPostData();
		$this->log_debug("User insert", json_encode($data));

		$isRequestValid = true;
		$validationErrorText = "";

		//var_dump($data);
		$projectName = isset($data->ProjectName) ? $data->ProjectName : false;
		$loginCode = isset($data->LoginCode) ? $data->LoginCode : false;
		$userType = isset($data->UserType) ? $data->UserType : false;
		$canVote = isset($data->CanVote) ? $data->CanVote : false;

		// validate required parameters
		if (!$projectName) {
			$validationErrorText .= "Empty ProjectName value in request. ";
			$isRequestValid = false;
		}

		if (!$loginCode) {
			$validationErrorText .= "Empty LoginCode value in request. ";
			$isRequestValid = false;
		}

		if (!$userType) {
			$validationErrorText .= "Empty UserType value in request. ";
			$isRequestValid = false;
		}

		if ($canVote && strtoupper($canVote) === "TRUE") {
			$canVoteBit = 1;
		} else {
			$canVoteBit = 0;
		}

		//Get ProjectId By ProjectName
		$project = $this->Projects_model->get_first_project_by_name($projectName);
		$projectId = false;

		if (!$project) {
			$validationErrorText .= "Project not found: $projectName. ";
			$isRequestValid = false;
		} else {
			$projectId = $project->project_id;
		}

		//Get UserTypeId By UserTypeName
		$userTypeId = $this->Users_model->get_usertypeid_by_usertypename($userType);

		if ( !$userTypeId ) {
			$validationErrorText .= "User type for found: $userType. Valid values are: Creditor, Debtor, Manager. ";
			$isRequestValid = false;
		}

		//Check Is User Exists By LoginCode
		$user = $this->Users_model->get_first_user_by_logincode($loginCode);

		if ($user) {
			$validationErrorText .= "User login code already exists: $loginCode. ";
			$isRequestValid = false;
		}

		if (!$isRequestValid) {
			$this->log_debug("User insert", $validationErrorText);
			echo "Invalid User POST request: $validationErrorText";
			http_response_code(400);
			exit();
		}

		// User Insert
		$new_id = $this->Users_model->new_user($projectId, $loginCode, $userTypeId, $canVoteBit);

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
		$wrid = $this->log_webrequest();
		$this->set_webrequest_id($wrid);

		$this->form_validation->set_rules('usr_code',
			$this->lang->line('user_login_code'),
			'required|min_length[1]|max_length[255]');

		if ($this->form_validation->run() == FALSE) {
			$this->load->view('common/login_header');
			$this->load->view('nav/top_nav');
			$this->load->view('user/login');
			$this->load->view('common/footer');
		} else {
			$usr_code = $this->input->post('usr_code');
			$this->log_debug("User login", $usr_code);

			$user = $this->Users_model->get_first_user_by_logincode($usr_code);
			if (!$user) {
				$this->log_debug("User login", "login failed: $usr_code");

				$page_data['login_fail'] = true;
				$this->load->view('common/login_header');
				$this->load->view('nav/top_nav');
				$this->load->view('user/login', $page_data);
				$this->load->view('common/footer');
				return;
			}

			$data = array (
				'user_login_code' => $user->user_login_code,
				'user_project_id' => $user->user_project_id,
				'user_type_id' => $user->user_usertype_id,
				'user_can_vote' => $user->user_can_vote,
				'user_id' => $user->user_id
			);

			// Save data to session
			$this->log_debug("User login", "Succefull loginned: $usr_code");
			$this->session->set_userdata($data);
			redirect('documents/index');
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
			$this->log_debug("User logout", "");
			$this->session->unset_userdata($data);
			redirect('documents/index');
	}

}