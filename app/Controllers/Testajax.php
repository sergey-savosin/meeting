<?php namespace App\Controllers;

class Testajax extends BaseController {

	public function index() {
		helper(['form', 'url']);

		$top_nav_data['uri'] = $this->request->uri;

		echo view('common/header');
		echo view('nav/top_nav', $top_nav_data);
		echo view('testajax/view');
		echo view('common/footer');
	}

	public function register()
	{
		// set rules
		//$this->form_validation->set_rules('email','EMAIL','trim|required|valid_email|is_unique[utilisateurs.email]');
		if (1==2)//($this->form_validation->run() == FALSE)
		{
			echo validation_errors();
		} else {
			$email = $this->request->getPost('email');
			$data = array(
						'email' => $email
					);

			//$this->blog_m->registre($data);

			echo "<div class='alert'>Inscription success</div>";
			echo "email";
		}
	}
}