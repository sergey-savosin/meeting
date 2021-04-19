<?php namespace App\Controllers;

class Admin extends BaseController {

	/**
	* admin::login form. Get and Post
	*/
	public function login() {
		helper(['form', 'url']);

		log_message('info', 'admin::login started. Method: '.$this->request->getMethod());

		$top_nav_data['uri'] = $this->request->uri;
		$val_rules['admin_login_name'] = 'required|min_length[1]|max_length[255]';
		$val_rules['admin_login_password'] = 'required|min_length[1]|max_length[255]';

		if ($this->request->getMethod() === 'get' || !$this->validate($val_rules))
		{
			if ($this->request->getMethod() === 'get') {
				$validation = null;
			} else {
				$validation = $this->validator;
			}

			$page_data['validation'] = $validation;
			$page_data['login_name_correct'] = true;
			$page_data['login_password_correct'] = true;
			return view('common/header').
				view('nav/top_nav', $top_nav_data).
				view('admins/login_view', $page_data).
				view('common/footer');
		} else {
			$admin_login_name = $this->request->getVar('admin_login_name');
			$admin_login_password = $this->request->getVar('admin_login_password');
			log_message('info', "[admin::login] admin_name: $admin_login_name");
			$admins_model = model('Admins_model');
			$admin = $admins_model->
				get_admin_by_name_password($admin_login_name, $admin_login_password);
			
			if (!$admin) {
				// admin login not found
				log_message('info',
					"[admin::login] Login not found for admin login: $admin_login_name");

				$page_data['validation'] = null;
				$page_data['login_name_correct'] = false;
				$page_data['login_password_correct'] = false;
				return view('common/header').
					view('nav/top_nav', $top_nav_data).
					view('admins/login_view', $page_data).
					view('common/footer');
			}
			elseif ($admin->admin_password != $admin_login_password) {
				//test for correct password
				// password incorrect

				log_message('info',
					"[admin::login] Password incorrect for admin login: $admin_login_name");

				$page_data['validation'] = null;
				$page_data['login_name_correct'] = true;
				$page_data['login_password_correct'] = false;
				return view('common/header').
					view('nav/top_nav', $top_nav_data).
					view('admins/login_view', $page_data).
					view('common/footer');
			}


			// Save data to session
			$data = array (
				'admin_login_name' => $admin->admin_login,
			);

			$session = session();
			$session->set($data);

			log_message('info', "[admin::login] Succefull loginned: $admin_login_name");

			if ($session->has('redirect_from')) {
				$redirect_from = $session->get('redirect_from');
				log_message('info', '[admin::login] redirected_from: '.$redirect_from);
				$session->remove('redirect_from');

				return redirect()->to(base_url($redirect_from));
			}

			return redirect()->to(base_url('Project'));
		}
	}

	public function logout() {
			$data = array (
				'admin_login_name',
			);

			// Save data to session
			log_message('info', "[admin::logout] logout");
			$session = session();
			$session->remove($data);
			return redirect()->to(base_url('Project'));
	}

	public function add() {
		// get session data
		$session = session();
		$admin_name = $session->get('admin_login_name');
		if ($admin_name == TRUE) {
			return 'Сначала нужно выйти из системы.';
		}

		helper(['url', 'form']);
		$admins_model = model('Admins_model');

		// get request params
		$uri = $this->request->uri;

		// Form data
		$newAdminName = trim($this->request->getPost('admin_name'));
		$newAdminPassword = trim($this->request->getPost('admin_password'));

		// data for view
		//$page_data['project_query'] = $project;
		
		$top_nav_data['uri'] = $this->request->uri;

		// setup form validation
		$val_rules['admin_name'] = [
			'label' => 'admin_name',
			'rules' => 'required|is_unique[admin.admin_login]',
			'errors' => [
				'required' => 'Укажите имя учётной записи',
				'is_unique' => 'Данное имя учётной записи уже используется'
			]
		];
		$val_rules['admin_password'] = [
			'label' => 'admin_password',
			'rules' => 'required',
			'errors' => [
				'required' => 'Укажите пароль'
			]
		];

		// show view
		if ($this->request->getMethod() === 'get' || !$this->validate($val_rules) ) {
			if ($this->request->getMethod() === 'get') {
				$validation = null;
			} else {
				$validation = $this->validator;
			}

			$page_data['validation'] = $validation;

			return view('common/header').
				view('nav/top_nav', $top_nav_data).
				view('admins/add_view', $page_data).
				view('common/footer');
		} else {
			// save data to db
			$admins_model->new_admin($newAdminName, $newAdminPassword, '', '');
		}
		return redirect()->to(base_url("Admin/login"));
	}

}